<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Domain_store extends CI_Model {

    /**
     * Private variables
     */
    private $id = false;
    private $token = false;
    private $domain = false;
    private $data = false;
    
    
    /**
     * Construct this shizz
     */
    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }
    
    /**
     * Store file info
     */
    public function store() {
        
        //Insert
        $result = $this->mdb->insert('domain', $this->data);
        
        //Clear
        $this->clear_();
        
        //Return ID
        return $result->{'$id'};
    }
    
    /**
     * Store file info
     */
    public function update() {
        
        $this->mdb->where('_id', $this->id);
        
        //Set the data
        foreach($this->data as $param=>$val) {
            $this->mdb->set($param, $val); 
        }
        
        //Do it!
        $this->mdb->update('domain', array('upsert' => false));
    }
    
    /**
     * Store file info
     */
    public function get() {
        
        //Set where to domain
        if($this->domain) $this->mdb->where('domain', $this->domain);
        if($this->token) $this->mdb->where('token', $this->token);
        
        //Results
        $result = $this->mdb->get('domain');
        
        //If no results then return
        if(count($result) != 1) return false;
        
        //Get em
        return $result[0];
    }
    
    

    /**
     * Setters
     */
    public function id($id) {
        $this->id = $id;
        return $this;
    }
    public function token($token) {
        $this->token = $token;
        return $this;
    }
    public function domain($domain) {
        $this->domain = $domain;
        return $this;
    }
    public function data($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Reset er thing
     */
    private function clear_() {
        $this->id = false;
        $this->token = false;
        $this->filename = false;
        $this->file_md5 = false;
        $this->path = false;
    }
    
    
}


?>