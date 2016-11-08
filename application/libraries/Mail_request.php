<?php
//DDV less magical comment
defined('BASEPATH') OR exit('No direct script access allowed');

class Mail_Request {

    /**
     * Private variables
     */
    private $data = true;
    private $result = false;
    private $messages = array();
    private $error = array();

    //Validation arrays
    private $requestRequired = array('batch', 'messages');
    private $messageRequired = array('uuid', 'delay', 'template', 'to', 'from', 'subject', 'emailData');
    private $templateRequired = array('default' => array('title', 'blocks'), 'default_noheader' => array('title', 'blocks'), 'absence_default' => array('title', 'blocks', 'alert'));
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
                    $this->error[] = $param . " - Is not set in message at index " . $index . ". (Email index)";
                    $hasErr = true;
                }
            }

            //Check template if no errors
            if(!$hasErr) {

                //Check template is ok
                if(!array_key_exists($message['template'], $this->templateRequired)) {
                    $this->error[] = $message['template'] . " -  Template is not available. Message at index " . $index . ". (Email index)";
                    $hasErr = true;
                }
                else {
                    //Check message meets template requirments
                    foreach($this->templateRequired[$message['template']] as $param) {
                        if(!array_key_exists($param, $message['emailData'])) {
                            $this->error[] = $param . " - Is not set in messageData. Message at index " . $index . ". (Email index)";
                            $hasErr = true;
                        }
                    }
                }

                //Check and load attachments
                $attachments = array();
                if(array_key_exists('attachments', $message)) {
                    foreach($message['attachments'] as $attach) {
                        $info = $this->CI->file_store->id($attach)->get();
                        if(!$info) {
                            $this->error[] = "Error finding attachment ".$attach.". Message at index " . $index . ". (Email index)";
                            $hasErr = true;
                        }
                        else $attachments[] = $info;
                    }
                }

            }

            //If no errors during all that then add it to the messages array
            if(!$hasErr) {
                //Set attachments
                $message['attachments'] = $attachments;
                //Add message
                $this->messages[] = $message;
            }
        }

        //If we have no valid message then return false
        if(count($this->messages) == 0) return false;

        //Build final result
        $this->result = array(
            'batch' => $this->data['batch'],
            'messages' => $this->messages
        );

        return true;
    }

    /**
     * Set the data from request
     *
     * @access public
     *
     * @return Bool/String
     */
    public function set_data($data) {
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
    public function get_result() {
        return $this->result;
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
