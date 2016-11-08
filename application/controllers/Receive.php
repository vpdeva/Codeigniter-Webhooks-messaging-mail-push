<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require REST;

class Receive extends REST_Controller {

    /**
     * Results POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function batch_results_get() {

        //Try and get the batch ID
        if(!$batch = $this->uri->segment(3)) $this->response(array("status" => "error",  "error" => "batch not specified."));

        //Get the results
        $messages = $this->outbox_store->batch($batch)->getEmail();

        //Set the result
        $results = array();

        //Parse the data
        foreach($messages as $message) {

            //Build status tag
            $status = 'Error';
            if($message['sent']) $status = 'In Transit';
            if($message['delivered']) $status = 'Delivered Successfully';
            if($message['opened']) $status = 'Read';
            if($message['bounced']) $status = 'Bounced';


            $_result = array(
                'uuid' => $message['uuid'],
                'sent' => $message['sent'],
                'delivered' => $message['delivered'],
                'opened' => $message['opened'],
                'bounced' => $message['bounced'],
                'status' => $status,
                'result' => $message['result']
            );

            $results[] = $_result;
        }

        $this->response(array("status" => "success",  "messages" => $results));
    }

    /**
     * Results POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function en_student_results_post() {

        //Get the body of request
        $data = $this->request->body;

        //Check for valid json
        if(count($data) == 0) $this->response(array("status" => "error",  "error" => "Invalid JSON"));

        //Check for student
        if(!array_key_exists('students', $data)) $this->response(array("status" => "error",  "error" => "students - Parameter is not set"));

        //build todays date
        $today = new mongodate(strtotime(date('Y-m-d')));

        //Define results
        $results = array();

        //Loop over the students and find results
        foreach($data['students'] as $id=>$contacts) {

            $address = array();

            //Build addresses
            foreach($contacts as $contact) {
                switch ($contact['type']) {
                    case 1:
                        $address[] = $contact['value'];
                        break;
                    case 2:
                        $address = array_merge($address, mobile_all_versions($contact['value']));
                        break;
                }
            }

            //Get messages
            $messages = $this->inbox_store->date_gt($today)->from($address)->get();

            //Messages results
            $message_results = array();

            //Loop over messages
            foreach($messages as $message) {

                //Parsed message
                $_res = false;

                //Handle text and email
                switch ($message['type']) {
                    case 1:
                        $_res = array(
                            'batch' => ($message['reply'] ? $message['reply']['batch'] : false),
                            'from' => $message['from'],
                            'body' => $message['body'],
                            'date' => $message['date']->toDateTime(),
                            'type' => 'email'
                        );
                        break;
                    case 2:
                        $_res = array(
                            'from' => $message['from'],
                            'body' => $message['body'],
                            'date' => $message['date']->toDateTime(),
                            'type' => 'sms'
                        );
                        break;
                }

                //Add to results if not false
                if($_res) $message_results[] = $_res;
            }

            //If no messages then false for id
            $results[$id] = (!empty($message_results) ? $message_results : null);
        }

        //Respond
        $this->response(array("status" => "success",  "result" => $results));

    }

    /**
     * Results POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function en_results_post() {

        //Get the body of request
        $data = $this->request->body;

        //Check for valid json
        if(count($data) == 0) $this->response(array("status" => "error",  "error" => "Invalid JSON"));

        //Check for batchs param
        if(!array_key_exists('batch', $data)) $this->response(array("status" => "error",  "error" => "batch - Parameter not found."));

        //Check batchs are against that domain... Or something, uhh duno!..
        //...

        //Get the Messages in the batch
        $messages = $this->inbox_store->batch($data['batch'])->get();

        //Build result array
        $result = array();
        //Loop over the requested batchs and build null if not set
        foreach($data['batch'] as $batch) {
            if(!isset($result[$batch])) $result[$batch] = array();
        }

        //Loop over and get out what we need
        foreach($messages as $message) {

            //Build the message result
            $_message = array(
                'status' => 'Reply',
                'from' => $message['from'],
                'subject' => ($message['type'] == 1 ? $message['subject'] : null),
                'body' => $message['body'],
                'ts' => date('d/m/Y H:i:s', $message['date']->sec),
                'type' => message_type_tostr($message['type'])
            );

            //Add to batch array
            $result[$message['reply']['batch']][] = $_message;
        }

        //Get the batchs without any replies
        foreach($result as $batch=>$res) {
            //Check if batch is empty
            if(empty($res)) {
                $queued = $this->outbox_store->batch($batch)->getQueue();
                foreach($queued as $queue) {
                    $message_ = array(
                        'status' => 'Queued',
                        'type' => message_type_tostr($queue['type']),
                        'to' => $queue['to'],
                        'tts' => queued_send_time($queue['lastUpdate'], $queue['delay'])
                    );
                    $result[$batch][] = $message_;
                }

                $outboxed = $this->outbox_store->batch($batch)->getOutbox();
                foreach($outboxed as $outbox) {
                    $message_ = array(
                        'status' => message_status_tostr($outbox),
                        'type' => message_type_tostr($outbox['type']),
                        'to' => (array_key_exists('to', $outbox) ? $outbox['to'] : false),
                        'lastUpdate' => date('d/m/Y H:i:s', $outbox['lastUpdate']->sec)
                    );
                    $result[$batch][] = $message_;
                }
            }
        }

        $this->response(array("status" => "success",  "result" => $result));
    }


    /**
     * Domain usage get Endpoint
     *
     * @access public
     *
     * @return
     */
    public function domain_usage_get() {

        $token = false;
        if(array_key_exists('key', $this->rest)) $token = $this->rest->key;
        else if(!$token = $this->uri->segment(3)) $this->response(array("status" => "error",  "error" => "domain not specified."));

        //Try get date range
        $start = false;
        if($s = $this->input->get('s')) {
            $start = date_create_from_format('d/m/Y', $s);
            $start->setTime(0,0);
        }
        $end = false;
        if($e = $this->input->get('e')) {
            $end = date_create_from_format('d/m/Y', $e);
            $end->setTime(0,0);
        }

        //Get domain
        $domain = get_domain($token);

        //Get batch info
        $usage_info = $this->batch_store->start_date($start)->end_date($end)->moe($domain['moe'])->get();

        //Build the usage
        $this->domain_usage->data($usage_info)->build();




        $this->load->view('header');
        $this->load->view('usage_table', array('total' => $this->domain_usage->total(), 'department' => $this->domain_usage->dept(), 'user' => $this->domain_usage->user()));
        $this->load->view('footer');
    }

    /**
     * Domain usage POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function domain_usage_post() {

        //Get the body of request
        $data = $this->request->body;

        //Check for valid json
        //if(count($data) == 0) $this->response(array("status" => "error",  "error" => "Invalid JSON"));

        //Get domain
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));

        //Get date range for current month
        $first_day_this_month = date('m-01-Y');
        $last_day_this_month  = date('m-t-Y');

        //Result
        $result = array(
            'total' => array(
                'email_requested' => 0,
                'sms_requested' => 0,
                'pn_requested' => 0,
                'email_sent' => 0,
                'sms_sent' => 0,
                'pn_sent' => 0
            ),
            'batch' => array(
            ),
            'department' => array(
            )
        );

        //Get batch info
        $batch_info = $this->batch_store->moe($domain['moe'])->get();

        //Build the stats
        foreach($batch_info as $batch) {

            //Sum up totals
            $result['total']['email_requested'] += $batch['email_total'];
            $result['total']['sms_requested'] += $batch['sms_total'];
            $result['total']['pn_requested'] += $batch['pn_total'];
            $result['total']['email_sent'] += $batch['email_queued'];
            $result['total']['sms_sent'] += $batch['sms_queued'];
            $result['total']['pn_sent'] += $batch['pn_queued'];

            //Check if batch is defined if not define that shizz!
            if(!array_key_exists($batch['batch'], $result['batch'])) {
                $result['batch'][$batch['batch']] = array(
                    'email_requested' => 0,
                    'sms_requested' => 0,
                    'pn_requested' => 0,
                    'email_sent' => 0,
                    'sms_sent' => 0,
                    'pn_sent' => 0,
                    'user' => 0,
                    'reason' => 0,
                );
            }

            //Do le Math
            $result['batch'][$batch['batch']]['email_requested'] += $batch['email_total'];
            $result['batch'][$batch['batch']]['sms_requested'] += $batch['sms_total'];
            $result['batch'][$batch['batch']]['pn_requested'] += $batch['pn_total'];
            $result['batch'][$batch['batch']]['email_sent'] += $batch['email_queued'];
            $result['batch'][$batch['batch']]['sms_sent'] += $batch['sms_queued'];
            $result['batch'][$batch['batch']]['pn_sent'] += $batch['pn_queued'];


            //Check if department is defined
            if(!array_key_exists($batch['dept'], $result['department'])) {
                $result['department'][$batch['dept']] = array(
                    'email_requested' => 0,
                    'sms_requested' => 0,
                    'pn_requested' => 0,
                    'email_sent' => 0,
                    'sms_sent' => 0,
                    'pn_sent' => 0,
                    'users' => array()
                );
            }

            //Do le Math
            $result['department'][$batch['dept']]['email_requested'] += $batch['email_total'];
            $result['department'][$batch['dept']]['sms_requested'] += $batch['sms_total'];
            $result['department'][$batch['dept']]['pn_requested'] += $batch['pn_total'];
            $result['department'][$batch['dept']]['email_sent'] += $batch['email_queued'];
            $result['department'][$batch['dept']]['sms_sent'] += $batch['sms_queued'];
            $result['department'][$batch['dept']]['pn_sent'] += $batch['pn_queued'];

            //Check user in dept
            if(!array_key_exists($batch['user'], $result['department'][$batch['dept']]['users'])) {
                $result['department'][$batch['dept']]['users'][$batch['user']] = array(
                    'email_requested' => 0,
                    'sms_requested' => 0,
                    'pn_requested' => 0,
                    'email_sent' => 0,
                    'sms_sent' => 0,
                    'pn_sent' => 0,
                );
            }

            //Do le Math for user
            $result['department'][$batch['dept']]['users'][$batch['user']]['email_requested'] += $batch['email_total'];
            $result['department'][$batch['dept']]['users'][$batch['user']]['sms_requested'] += $batch['sms_total'];
            $result['department'][$batch['dept']]['users'][$batch['user']]['pn_requested'] += $batch['pn_total'];
            $result['department'][$batch['dept']]['users'][$batch['user']]['email_sent'] += $batch['email_queued'];
            $result['department'][$batch['dept']]['users'][$batch['user']]['sms_sent'] += $batch['sms_queued'];
            $result['department'][$batch['dept']]['users'][$batch['user']]['pn_sent'] += $batch['pn_queued'];




        }
                                print_r($result);
        die();

    }
}
