<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Domain_Usage {
    
    /**
     * Private variables
     */
    private $data = false;
    private $total = array(
        'email_requested' => 0,
        'sms_requested' => 0,
        'pn_requested' => 0,
        'email_sent' => 0,
        'sms_sent' => 0,
        'pn_sent' => 0,
        'total' => 0
    );
    private $dept = array();
    private $user = array();
        
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
        if(!$this->data) return false;
        
        foreach($this->data as $batch) {
            
            //Sum up totals
            $this->total['email_requested'] += $batch['email_total'];
            $this->total['sms_requested'] += $batch['sms_total'];
            $this->total['pn_requested'] += $batch['pn_total'];
            $this->total['email_sent'] += $batch['email_queued'];
            $this->total['sms_sent'] += $batch['sms_queued'];
            $this->total['pn_sent'] += $batch['pn_queued'];
            
            $this->total['total'] += $batch['email_queued'];
            $this->total['total'] += $batch['sms_queued'];
            $this->total['total'] += $batch['pn_queued'];
            
//            //Do le Math
//            $result['batch'][$batch['batch']]['email_requested'] += $batch['email_total'];
//            $result['batch'][$batch['batch']]['sms_requested'] += $batch['sms_total'];
//            $result['batch'][$batch['batch']]['pn_requested'] += $batch['pn_total'];
//            $result['batch'][$batch['batch']]['email_sent'] += $batch['email_queued'];
//            $result['batch'][$batch['batch']]['sms_sent'] += $batch['sms_queued'];
//            $result['batch'][$batch['batch']]['pn_sent'] += $batch['pn_queued'];
//            
//            
            //Check if department is defined
            if(!array_key_exists($batch['dept'], $this->dept)) {
                $this->dept[$batch['dept']] = array(
                    'email_requested' => 0,
                    'sms_requested' => 0,
                    'pn_requested' => 0,
                    'email_sent' => 0,
                    'sms_sent' => 0,
                    'pn_sent' => 0,
                    'total' => 0,
                    'users' => array()
                );
            }
            
            //Check if user is defined in department
            if(!array_key_exists($batch['user'], $this->dept[$batch['dept']]['users'])) {
                $this->dept[$batch['dept']]['users'][$batch['user']] = array(
                    'email_requested' => 0,
                    'sms_requested' => 0,
                    'pn_requested' => 0,
                    'email_sent' => 0,
                    'sms_sent' => 0,
                    'pn_sent' => 0,
                    'total' => 0
                );
            }
            
            //Do le Math
            $this->dept[$batch['dept']]['email_requested'] += $batch['email_total'];
            $this->dept[$batch['dept']]['sms_requested'] += $batch['sms_total'];
            $this->dept[$batch['dept']]['pn_requested'] += $batch['pn_total'];
            $this->dept[$batch['dept']]['email_sent'] += $batch['email_queued'];
            $this->dept[$batch['dept']]['sms_sent'] += $batch['sms_queued'];
            $this->dept[$batch['dept']]['pn_sent'] += $batch['pn_queued'];
            
            $this->dept[$batch['dept']]['total'] += $batch['email_queued'];
            $this->dept[$batch['dept']]['total'] += $batch['sms_queued'];
            $this->dept[$batch['dept']]['total'] += $batch['pn_queued'];
            
            //Dept user break down
            $this->dept[$batch['dept']]['users'][$batch['user']]['email_requested'] += $batch['email_total'];
            $this->dept[$batch['dept']]['users'][$batch['user']]['sms_requested'] += $batch['sms_total'];
            $this->dept[$batch['dept']]['users'][$batch['user']]['pn_requested'] += $batch['pn_total'];
            $this->dept[$batch['dept']]['users'][$batch['user']]['email_sent'] += $batch['email_queued'];
            $this->dept[$batch['dept']]['users'][$batch['user']]['sms_sent'] += $batch['sms_queued'];
            $this->dept[$batch['dept']]['users'][$batch['user']]['pn_sent'] += $batch['pn_queued'];
            
            $this->dept[$batch['dept']]['users'][$batch['user']]['total'] += $batch['email_queued'];
            $this->dept[$batch['dept']]['users'][$batch['user']]['total'] += $batch['sms_queued'];
            $this->dept[$batch['dept']]['users'][$batch['user']]['total'] += $batch['pn_queued'];
            
            //Check if user is defined
            if(!array_key_exists($batch['user'], $this->user)) {
                $this->user[$batch['user']] = array(
                    'email_requested' => 0,
                    'sms_requested' => 0,
                    'pn_requested' => 0,
                    'email_sent' => 0,
                    'sms_sent' => 0,
                    'pn_sent' => 0,
                    'total' => 0
                );
            }
            
            //Do le Math
            $this->user[$batch['user']]['email_requested'] += $batch['email_total'];
            $this->user[$batch['user']]['sms_requested'] += $batch['sms_total'];
            $this->user[$batch['user']]['pn_requested'] += $batch['pn_total'];
            $this->user[$batch['user']]['email_sent'] += $batch['email_queued'];
            $this->user[$batch['user']]['sms_sent'] += $batch['sms_queued'];
            $this->user[$batch['user']]['pn_sent'] += $batch['pn_queued'];
            
            $this->user[$batch['user']]['total'] += $batch['email_queued'];
            $this->user[$batch['user']]['total'] += $batch['sms_queued'];
            $this->user[$batch['user']]['total'] += $batch['pn_queued'];

        }
        return true;
    }
    
    /**
     * Build totals
     *
     * @access private
     *
     * @return array
     */
    private function build_totals() {
        
        $result = array(
            'email' => 0,
            'sms' => 0,
            'apn' => 0
        );
        
        $this->total = $result;
    }
    
    /**
     * Build dept totals
     *
     * @access private
     *
     * @return array
     */
    private function build_dept() {
        
        $result = array();
        
        $this->total = $result;
    }
    
    /**
     * Build user totals
     *
     * @access private
     *
     * @return array
     */
    private function build_users() {
        
        $result = array();
        
        $this->total = $result;
    }
  
    /**
     * Setters
     */
    public function data($data) {
        $this->data = $data;
        return $this;
    }

    
    /**
     * Getters
     */
    public function total() {
        return $this->total;
    }
    public function dept() {
        return $this->dept;
    }
    public function user() {
        return $this->user;
    }
}
