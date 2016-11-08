<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pn_Request {
    
    /**
     * Private variables
     */
    private $data = false;
    private $result = false;
    private $messages = array();
    private $error = array();
    
    //Validation arrays
    private $requestRequired = array('batch', 'messages');
    private $messageRequired = array('delay', 'stuid', 'level', 'message');
    private $messageLimit = 100;
        
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
            
            //Check for error
            if(!$hasErr) {
                //Get the APNs for message
                $apns = $this->CI->pnid_store->stuid($message['stuid'])->get();

                //Loops over apns then add that message to array
                foreach($apns as $apn) {
                    $_message = array(
                        'pnid' => $apn['pnid'],
                        'pn_type' => $apn['type'],
                        'message' => $message['message']
                    );
                    
                    
                    //Add message to array!
                    $this->messages[] = $_message;
                }
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
