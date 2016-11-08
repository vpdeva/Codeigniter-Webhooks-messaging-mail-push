<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require REST; 

class Notification extends REST_Controller {

    /**
     * All notifications GET Endpoint
     *
     * @access public
     *
     * @return
     */
    public function all_get() {
        
        //Check for pnid
        if(!$pnid = $this->uri->segment(3)) $this->response(array("status" => "error", "error" => "pnid - Parameter not found"));

        $notifications = $this->pn_store->pnid($pnid)->get();
        
        //Result
        $result = array();
        
        foreach($notifications as $notification) {
            //Get removed status
            $removed = (array_key_exists('removed', $notification) ? $notification['removed'] : false);
            
            //If not removed then add it!
            if(!$removed) {
                $notification_ = array(
                    'batch' => $notification['batch'],
                    'mid' => $notification['mid'],
                    'message' => $notification['message'],
                    'opened' => $notification['opened'],
                    'date' => $notification['lastUpdate']->sec,
                    'replied' => $notification['replied']
                );
                $result[] = $notification_;
            }
        }
        
        //return info
        $this->response(array("status" => "success", "notification" => $result));
    }
}