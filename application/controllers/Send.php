<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// A test comment
require REST; 

class Send extends REST_Controller {

    /**
     * Index POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function index_post() {
        
        //Get the body of request
        $data = $this->request->body;
        
        //Check for valid json
        if(count($data) == 0) $this->response(array("status" => "error",  "error" => "Invalid JSON"));
        
        //Essential proberties
        $required_params = array('batch', 'user', 'chargeto', 'reason', 'messages');
        
        //Errors
        $request_errors = array();
        
        //check request
        foreach($required_params as $param) {
            if(!array_key_exists($param, $data)) $request_errors[] = $param . " - Parameter is not set";
        }
        
        //Respond errors
        if(!empty($request_errors)) $this->response(array("status" => "error",  "error" => $request_errors));
        
        //Email Request output
        $email_request = array(
            'batch' => $data['batch'],
            'messages' => array()
        );
        
        //SMS Request output
        $sms_request = array(
            'batch' => $data['batch'],
            'messages' => array()
        );
        
        //PN Request output
        $pn_request = array(
            'batch' => $data['batch'],
            'messages' => array()
        );
        
        //Loop over the messages to seperate
        foreach($data['messages'] as $index=>$message) {
            //Check for type
            if(!array_key_exists('type', $message)) $this->response(array("status" => "error",  "error" => "type - Parameter is not set in message at index " . $index));
        
            //Handle correct type
            switch ($message['type']) {
                case 1:
                    $email_request['messages'][] = $message;
                    break;
                case 2:
                    $sms_request['messages'][] = $message;
                    break;
                case 3:
                    $pn_request['messages'][] = $message;
                    break;
                default:
                   $this->response(array("status" => "error",  "error" =>  $message['type'] . " - is not valid type for message at index " . $index));
            }
            
        }
        
        //Validate the email request
        $this->mail_request->set_data($email_request)->validate();
        
        //Validate the sms request
        $this->sms_request->data($sms_request)->validate();
         
        //Validate the pn request
        $this->pn_request->data($pn_request)->validate();
        
        //Get valid email message
        $valid_email = $this->mail_request->get_result();
        
        //Get valid sms message
        $valid_sms = $this->sms_request->result();
        
        //Get valid pn message
        $valid_pn = $this->pn_request->result();
        
        //Check sms for push notifications generated to be send before sms
        if($sms_pn = $this->sms_request->isPN()) $valid_pn['messages'] = $sms_pn;
        
        //Check if no valid message
        if(empty($valid_email['messages']) && empty($valid_sms['messages']) && empty($valid_pn['messages'])) $this->response(array("status" => "error", "message" => "No valid message to be sent", "error" => array_merge($this->mail_request->get_error(), $this->sms_request->error())));
        
        //Get domain info
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));
        
        //Check for if we have emails to queue
        if($valid_email) {
 			   //Define the domain for message store
 			   $this->mail_store->batch($valid_email['batch'])->domain($domain);
			   
 			   //Loop over messages and store
  			  foreach($valid_email['messages'] as $email_message) {
				  
  				  //Cretae a lookup ID
  				  $email_message['lookup'] = bin2hex(openssl_random_pseudo_bytes(64));

  				  //Store the message
 				   $this->mail_store->message($email_message)->store();
            }
        }
        
        //Check for if we have sms to queue
        if($valid_sms) {
            
            //Store messages into queue
            $this->sms_store->batch($valid_sms['batch'])->moe($domain['moe']);

            //Store message
            foreach($valid_sms['messages'] as $sms_message) {
                $this->sms_store->message($sms_message)->store();
            }
        }
        
        //Check for if we have pn to queue
        if($valid_pn) {
            
            //Store messages into queue
            $this->pn_store->batch($valid_pn['batch'])->moe($domain['moe']);

            //Store message
            foreach($valid_pn['messages'] as $pn_message) {
                $this->pn_store->message($pn_message)->store();
            }
        }
        
        //We have messages to send lets get totals
        $result_count = array(
            'email_total' => count($email_request['messages']),
            'sms_total' => count($sms_request['messages']),
            'pn_total' => count($pn_request['messages']),
            'email_queued' => count($valid_email['messages']),
            'sms_queued' => count($valid_sms['messages']),
            'pn_queued' => count($valid_pn['messages'])
        );
        
        //Build batch store data
        $batch_store = array_merge(array(
            'batch' => $data['batch'],
            'user' => $data['user'],
            'dept' => $data['chargeto'],
            'reason' => $data['reason'],
            'date' => new mongodate()
        ), $result_count);
        
        //Store this batch
        $this->batch_store->moe($domain['moe'])->data($batch_store)->store();
        
        //respond
        $this->response(array_merge(array(
                "status" => "success",
                "email_error" => $this->mail_request->get_error(), 
                "sms_error" => $this->sms_request->error()
            ), $result_count)
        );     

    }
    
    /**
     * SMS POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function sms_post() {
        //Get the body of request
        $data = $this->request->body;
        
        //Check for valid json
        if(count($data) == 0) $this->response(array("status" => "error",  "error" => "Invalid JSON"));
        
        //Get domain info
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));
        
        //Validate the request
        if(!$this->sms_request->data($data)->validate()) $this->response(array("status" => "error", 'message' => "No valid message to be sent.", "error" => $this->mail_request->get_error()));
        
        //Get result
        $result = $this->sms_request->result();

        //Store messages into queue
        $this->sms_store->batch($result['batch'])->moe($domain['moe']);
        
        //Store message
        foreach($result['messages'] as $message) {
            $this->sms_store->message($message)->store();
        }
        
        //respond
        $this->response(array("status" => "success", "valid" => count($result['messages']), "invalid" => (count($data['messages']) - count($result['messages'])), "error" => $this->sms_request->error()));        
    }
    
    /**
     * Email POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function email_post() {
        
        //Get the body of request
        $data = $this->request->body;
        
        //Check for valid json
        if(count($data) == 0) $this->response(array("status" => "error",  "error" => "Invalid JSON"));
        
        //Get domain info
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));
        
        //Tidy domain object
        $domain = tidy_domain_for_email($domain);

        //Send request to be validated
        if(!$this->mail_request->set_data($data)->validate()) $this->response(array("status" => "error", 'message' => "No valid message to be sent.", "error" => $this->mail_request->get_error()));
        
        //Get result
        $result = $this->mail_request->get_result();
        
        //Define the moe and domain for message store
        $this->mail_store->batch($result['batch'])->domain($domain);
        
        //Loop over messages and store
        foreach($result['messages'] as $message) {
            
            //Cretae a lookup ID
            $message['lookup'] = bin2hex(openssl_random_pseudo_bytes(64));

            //Store the message
            $this->mail_store->message($message)->store();
        }
        
        //Ok will send something so respond
        $this->response(array("status" => "success", "valid" => count($result['messages']), "invalid" => (count($data['messages']) - count($result['messages'])), "error" => $this->mail_request->get_error()));
    }
    
    /**
     * Cancel Batch POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function cancel_batch_post() {
        
        //Get the body of request
        $data = $this->request->body;
        
        //Check for valid json
        if(count($data) == 0) $this->response(array("status" => "error",  "error" => "Invalid JSON"));
        
        //Check for student
        if(!array_key_exists('batch', $data)) $this->response(array("status" => "error",  "error" => "batch - Parameter is not set"));
        
        foreach($data['batch'] as $batch) {
            //Remove from the queue any other waiting..
            $this->outbox_store->batch($batch)->remove_from_queue();
        }
        
        $this->response(array("status" => "success"));
    }
    
}