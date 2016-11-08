<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reply_Request {
    
    /**
     * Private variables
     */
    private $data = false;
    private $type = false;
        
    function __construct() {
       $this->CI = CI_Controller::get_instance();
    }
    
    /**
     * Build reply from data
     *
     * @access public
     *
     * @return BOOL
     */
    public function build() {
        
        //Check for type
        switch ($this->type) {
            case 1:
                return $this->build_email();
                break;
            case 2:
                return $this->build_sms();
                break;
            case 3:
                return $this->build_pn();
                break;
            default:
               return false;
        }
    }
    
    /**
     * Build an email
     *
     * @access private
     *
     * @return array
     */
    private function build_email() {
        $reply = array(
            'reply' => $this->get_email_original(),
            'domain' => $this->get_domain(),
            'to' => $this->data['To'],
            'from' => $this->data['sender'],
            'subject' => $this->data['subject'],
            'body' => $this->data['stripped-text'],
            'date' => new mongodate($this->data['timestamp']),
            'type' => 1
        );
        
        return $reply;
    }
    
    /**
     * Build an pn
     *
     * @access private
     *
     * @return array
     */
    private function build_sms() {
        $reply = array(
            'reply' => $this->get_sms_original(),
            'from' => $this->data['mobile'],
            'body' => $this->data['message'],
            'code' => $this->data['code'],
            'date' => new mongodate(),
            'type' => 2
        );
        return $reply;
    }
    
    /**
     * Build an sms
     *
     * @access private
     *
     * @return array
     */
    private function build_pn() {
        
        $reply = array(
            'reply' => $this->get_pn_original(),
            'from' => $this->data['pnid'],
            'body' => $this->data['message'],
            'date' => new mongodate(),
            'type' => 3
        );
        
        return $reply;
    }
    
    /**
     * Get Email Original
     *
     * @access private
     *
     * @return array
     */
    private function get_email_original() {
        
        //Check for parameter
        if(!array_key_exists('In-Reply-To', $this->data)) return false;
        
        //Remove <> wrapping
        preg_match('~<(.*?)>~', $this->data['In-Reply-To'], $output);
        $mid = $output[1];

        //Parse message ID
        $mid = explode("@", $mid, 2)[0];
        
        //Get the message
        $message = $this->CI->outbox_store->mid($mid)->getEmail();
        
        //Check for message else return false cause we have no record
        if(empty($message)) return false;
        
        //Get first message
        $message = $message[0];
        
        //Build info we need
        $message = array(
            'mid' => $message['mid'],
            'uuid' => $message['uuid'],
            'batch' => $message['batch'],
        );
        
        //Return the ID
        return $message;
    }
    
    /**
     * Get Email Original
     *
     * @access private
     *
     * @return array
     */
    private function get_sms_original() {
        
        //Build todays date at 00:00:00
        $today = new mongodate(strtotime('today midnight'));
        
        //Get messages
        $messages = $this->CI->outbox_store->mobile(mobile_all_versions($this->data['mobile']))->set_date($today)->getSMS();
        
        //Our origional
        $original = false;
        
        //Loop over messages
        foreach($messages as $message) {
            if(!array_key_exists('replied', $message)) {
                $original = array(
                    'mid' => $message['mid'],
                    'uuid' => $message['uuid'],
                    'batch' => $message['batch']
                );
                break;
            }
        }
        
        //Return
        return $original;
    }
    
    /**
     * Get PN Original
     *
     * @access private
     *
     * @return array
     */
    private function get_pn_original() {
        
        //Get messages
        $message = $this->CI->outbox_store->mid($this->data['reply_to'])->getPN();
        
        //No messages then false
        if(count($message) != 1) return false;
        
        //Return
        return array(
            'mid' => $message[0]['mid'],
            'batch' => $message[0]['batch']
        );
    }
    
    /**
     * Get Domain
     *
     * @access private
     *
     * @return array
     */
    private function get_domain() {
        
        //Remove <> wrapping
        preg_match('~<(.*?)>~', $this->data['To'], $output);
        $domain = $output[1];

        //Parse get domain url
        $domain = explode("@", $domain, 2)[1];
        
        //Get the first param
        $domain = explode(".", $domain, 2)[0];
        
        //Get the domain and return
        return $this->CI->domain_store->domain($domain)->get();
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
     * Set the data from request
     *
     * @access public
     *
     * @return Bool/String
     */
    public function type($type) {
        $this->type = $type;
        return $this;
    }
    
    
    /**
     * Get the errors
     *
     * @access public
     *
     * @return $this
     */
    public function get_error() {
        return (count($this->error) == 0 ? false : $this->error);
    }
}
