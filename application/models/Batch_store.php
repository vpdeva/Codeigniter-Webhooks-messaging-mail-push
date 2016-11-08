<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Batch_store extends CI_Model {

    /**
     * Private variables
     */
    private $batch = false;
    private $moe = false;
    private $data = false;
    private $start_date = false;
    private $end_date = false;
    
    
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
        $result = $this->mdb->insert('batch', array_merge(array('moe' => $this->moe), $this->data));
        
        //Return ID
        return true;
    }
    
    /**
     * Store file info
     */
    public function update() {
        
        if($this->batch) $this->mdb->where('batch', $this->batch);
        
        //Set the data
        foreach($this->data as $param=>$val) {
            switch($param) {
                case('email_queued'):
                    $this->mdb->inc('email_queued', $val);
                    break;
                case('sms_queued'):
                    $this->mdb->inc('sms_queued', $val);
                    break;
                case('pn_queued'):
                    $this->mdb->inc('pn_queued', $val);
                    break;
                default:
                    $this->mdb->set($param, $val); 
                    break;
            }
        }
        
        //Do it!
        $this->mdb->update('batch', array('upsert' => false));
    }
    
    /**
     * Store file info
     */
    public function get() {
        
        //Set where to domain
        if($this->moe) $this->mdb->where('moe', $this->moe);
        
        //Date ranges
        if($this->start_date) $this->mdb->where_gte('date', new mongoDate($this->start_date->getTimestamp()));
        if($this->end_date) $this->mdb->where_lte('date', new mongoDate($this->end_date->getTimestamp()));
        
        //Results
        $result = $this->mdb->get('batch');
        
        //Get em
        return $result;
    }
    
    

    /**
     * Setters
     */
    public function batch($d) {
        $this->batch = $d;
        return $this;
    }
    public function moe($moe) {
        $this->moe = $moe;
        return $this;
    }
    public function data($data) {
        $this->data = $data;
        return $this;
    }
    public function start_date($data) {
        $this->start_date = $data;
        return $this;
    }
    public function end_date($data) {
        $this->end_date = $data;
        return $this;
    }
    
}


?>