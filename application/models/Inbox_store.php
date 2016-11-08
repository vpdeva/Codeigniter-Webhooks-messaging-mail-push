<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Inbox_store extends CI_Model {

    /**
     * Private variables
     */
    private $batch = false;
    private $message = false;
    private $from = false;
    private $date_gt = false;
    private $date_eq = false;
    
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
        
        //Insert
        $this->mdb->insert('inbox', $this->message);
        
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
     * Get
     */
    public function get() {
        
        //Try by batch
        if($this->batch) $this->mdb->where_in('reply.batch', $this->batch);
        if($this->from) $this->mdb->where_in('from', $this->from);
        if($this->date_gt) $this->mdb->where_gt('date', $this->date_gt);
        if($this->date_eq) $this->mdb->where('date', $this->date_eq);
        
        //Always get emails
        //$this->mdb->where(array('type' => 1));
        
        //Results
        $result = $this->mdb->get('inbox');
        
        //Get em
        return $result;
    }

    /**
     * Setters
     */
    public function batch($batch) {
        $this->batch = $batch;
        return $this;
    }
    public function message($message) {
        $this->message = $message;
        return $this;
    }
    public function from($from) {
        $this->from = $from;
        return $this;
    }
    public function date_gt($date_gt) {
        $this->date_gt = $date_gt;
        return $this;
    }
    public function date_eq($date_eq) {
        $this->date_eq = $date_eq;
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