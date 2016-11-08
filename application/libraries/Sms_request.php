<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_Request {

    /**
     * Private variables
     */
    private $data = false;
    private $result = false;
    private $messages = array();
    private $messages_pn = array();
    private $error = array();

    //Validation arrays
    private $requestRequired = array('batch', 'messages');
    private $messageRequired = array('delay', 'uuid', 'to', 'message');
    private $messageLimit = 100;
    private $smsDelay = 1;

    function __construct() {
       $this->CI = CI_Controller::get_instance();
    }

    /**
     * Validates the data has required parameters
     *
     * @access public
     *
     * @return BOOL
     */
    public function validate() {

        //Check for data
        if(!$this->data) {
            $this->error[] = "Data not valid or set";
            return false;
        }

        //Check for requeired request values
        foreach($this->requestRequired as $param) {
            if(!array_key_exists($param, $this->data)) $this->error[] = $param . " - Is not set.";
        }
        if(count($this->error) > 0) return false;

        //Check if message exceed limit
        if(count($this->data['messages']) > $this->messageLimit) {
            $this->error[] = "Message limit exceded. " . count($this->data['messages']) . " Message requested.";
            return false;
        }

        //Loop over messages and validate
        foreach($this->data['messages'] as $index=>$message) {
            //Message has error
            $hasErr = false;

            //Check params
            foreach($this->messageRequired as $param) {
                if(!array_key_exists($param, $message)) {
                    $this->error[] = $param . " - Is not set in message at index " . $index . ". (SMS index.)";
                    $hasErr = true;
                }
            }

            //Check if has error
            if(!$hasErr) {

                //Make sure number has no spaces
                $message['to'] = str_replace(' ', '', $message['to']);

                //Check if we can send push notification
                $pnid = $this->CI->pnid_store->mobile_array(mobile_all_versions($message['to']))->enabled()->get();

                //Check if pnids
                if($pnid) {
                    //Loop over pnid and create push notification
                    foreach($pnid as $pn) {
                        $pn_ = array(
                            'delay' => $message['delay'],
                            'uuid' => $message['uuid'],
                            'pnid' => $pn['pnid'],
                            'to' => $message['to'],
                            'message' => $message['message'],
                            'pn_type' => $pn['type']
                        );

                        $this->messages_pn[] = $pn_;
                    }
                }

                //Check if there is valid pnid to send to, mark sms delay to default delay
                if(!empty($pnid)) $message['delay'] += $this->smsDelay;

                //Add message
                $this->messages[] = $message;
            }
        }

        //Set the result
        $this->result = array(
            'batch' => $this->data['batch'],
            'messages' => $this->messages
        );

        return (empty($this->messages) ? false : true);
    }

    /**
     * Set the data from request
     *
     * @access public
     *
     * @return Bool/String
     */
    public function data($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Set the final result
     *
     * @access public
     *
     * @return $this
     */
    public function result() {
        return $this->result;
    }

    /**
     * Check for push notifications
     *
     * @access public
     *
     * @return $this
     */
    public function isPN() {
        return (!empty($this->messages_pn) ? $this->messages_pn : false);
    }

    /**
     * Get the errors
     *
     * @access public
     *
     * @return $this
     */
    public function error() {
        return (count($this->error) == 0 ? false : $this->error);
    }
}
