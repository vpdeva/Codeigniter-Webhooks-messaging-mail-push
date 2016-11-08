<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require REST; 

class Monitor extends REST_Controller {

    /**
     * Results POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function index_get() {
        
        
        $this->db->select('COUNT(id) as requests');
        $this->db->where('from_unixtime(time) >= CURDATE()'); 
        $this->db->where("uri <> 'Monitor'"); 
        $request_query = $this->db->get('mail_logs');
        $request_result = $request_query->result()[0];
        
        $this->db->select('AVG(rtime) as ave');
        $this->db->where('from_unixtime(time) >= CURDATE()'); 
        $this->db->where("uri <> 'Monitor'"); 
        $response_query = $this->db->get('mail_logs');
        $response_result = $response_query->result()[0];
        
        $this->response(array("status" => "success",  "result" => array('requests' => $request_result->requests, 'average_response' => $response_result->ave)));
    }
}