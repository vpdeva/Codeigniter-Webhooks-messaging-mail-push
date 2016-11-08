<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Domain_Request {
    
    /**
     * Private variables
     */
    private $data = false;
    private $result = false;
    private $error = array();
    
    private $required = array('moe', 'domain', 'name', 'url');
        
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

        //Check that request conatins what it should
        foreach($this->required as $param) {
            if(!array_key_exists($param, $this->data)) $this->error[] = $param . " - Is not set.";
        }
        if(count($this->error) > 0) return false;
        
        //Check that the domain is available
        $domain = strtolower($this->data['domain']);
        if($info = $this->CI->domain_store->domain($domain)->get()) {
            $this->result = $info;
            $this->error[] = 'The domain ' . $domain . ' is in use.';
            return false;
        }
        
        return true;
    }
    
    /**
     * Build the domain with the data. Its more like just adding a token to the request data hahah
     *
     * @access public
     *
     * @return BOOL
     */
    public function build() {

        //create token
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        
        //Insert into the api key table
        $data = array(
           'key' => $token ,
           'level' => 1 ,
           'ignore_limits' => 0,
           'is_private_key' => 0,
           'ip_addresses' => "",
           'date_created' => time()
        );
        if(!$this->CI->db->insert('keys_message', $data)) {
            $this->error[] = "error creating token.";
            return false;
        } 

        //Update data
        $this->data['token'] = $token;
        
        //Add header text
        $this->data['header_text'] = NULL;
        
        //Add header image
        $this->data['header_image'] = NULL;
        
        //Add footer text
        $this->data['footer_text'] = NULL;
        
        //Add footer image
        $this->data['footer_image'] = NULL;
        
        //Set result
        $this->result = $this->data;
        
        return $this->result;
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
    public function get_errors() {
        return (count($this->error) == 0 ? false : $this->error);
    }
}
