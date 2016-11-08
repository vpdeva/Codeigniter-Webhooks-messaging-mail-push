<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Mail_store extends CI_Model {

    /**
     * Private variables
     */
    private $moe = false;
    private $domain = false;
    private $message = false;
    private $mid = false;
    private $lookup = false;
    private $data = array();
    private $batch = false;
    
    /**
     * Construct this shizz
     */
    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }
    
    /**
     * Store message into queue
     */
    public function store() {
        
        //User defaults
        $store = array_merge(array('batch' => $this->batch, 'domain' => $this->domain, 'type' => 1, 'lastUpdate' => new MongoDate()), $this->message);
        
        $this->mdb->insert('queue', $store);
        //Clear
        $this->clear_();
    }
    
    /**
     * Update message
     */
    public function update() {
        
		//Define record to update/insert
		$this->mdb->where(array('mid' => $this->mid));

		//Set the data
		foreach($this->data as $param=>$val) {
			$this->mdb->set($param, $val);
		}
        
	   //Do it!
	   $this->mdb->update('outbox', array('upsert' => false));
        
        //Clear
        $this->clear_();
    }
    
    /**
     * Get message into queue
     */
    public function get() {
        
        //Search by id
        if($this->mid) $this->mdb->where(array('mid' => $this->mid));
        if($this->lookup) $this->mdb->where(array('lookup' => $this->lookup));
        
        //Results
        $result = $this->mdb->get('outbox');
        
        //If no results then return
        if(count($result) != 1) return false;
        
        //Get em
        return $result[0];
    }
	
    /**
     * Setters
     */
    public function moe($moe) {
        $this->moe = $moe;
        return $this;
    }
    public function domain($domain) {
        $this->domain = $domain;
        return $this;
    }
    public function message($message) {
        $this->message = $message;
        return $this;
    }
    public function mid($mid) {
        $this->mid = $mid;
        return $this;
    }
    public function lookup($lookup) {
        $this->lookup = $lookup;
        return $this;
    }
    public function data($data) {
        $this->data = $data;
        return $this;
    }
    public function batch($batch) {
        $this->batch = $batch;
        return $this;
    }
    /**
     * Reset er thing
     */
    private function clear_() {
//        $this->moe = false;
//        $this->domain = false;
        $this->message = false;
    }
    
    
}


?>