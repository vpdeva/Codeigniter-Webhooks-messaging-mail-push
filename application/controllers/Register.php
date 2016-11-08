<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require REST;

class Register extends REST_Controller {

    /**
     * Legacy when only apple devices POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function apn_post() {
        $this->pn_post();
    }

    /**
     * Register for PN POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function pn_post() {

        //Check for Values
        if(!$pnid = $this->input->post('pnid')) $this->response(array("status" => "error", "error" => "pnid - Parameter is not set"));
        if(!$type = $this->input->post('type')) $this->response(array("status" => "error", "error" => "type - Parameter is not set"));
        if(!$mobile = $this->input->post('mobile')) $this->response(array("status" => "error", "error" => "mobile - Parameter is not set"));

        //Check if real mobile Number
        if(!is_numeric($mobile)) $this->response(array("status" => "error", "error" => "Invalid mobile number"));

        //Check if already registered for student
        $pn = $this->pnid_store->pnid($pnid)->get();

        if(!empty($pn)) {

            //Create message
            $message = ($type == 1 ? $pn[0]['code'] . ' - KAMAR Push Notifications registration code. Click --> KAMAR://'.$pn[0]['code'] : $pn[0]['code'] . ' - KAMAR Push Notifications registration code.');

            //SMS Code message
            $message = array(
                'uuid' => null,
                'to' => $mobile,
                'message' => $message,
                'delay' => 0
            );

            //Update
            $this->pnid_store->id($pn[0]['_id'])->data(array('mobile' => $mobile, 'enabled' => false))->update();

            //Send sms
            $this->sms_store->batch('SMSCODE')->moe('9999')->message($message)->store();
        }
        else {
            //Generate code
            $code = sms_register_code();

            //Store it!
            $this->pnid_store->level(1)->type($type)->code($code)->mobile($mobile)->store();

            //Create message
            $message = ($type == 1 ? $code . ' - KAMAR Push Notifications registration code. Click --> KAMAR://'.$code : $code . ' - KAMAR Push Notifications registration code.');

            //SMS Code message
            $message = array(
                'uuid' => null,
                'to' => $mobile,
                'message' => $message,
                'delay' => 0
            );

            //Send sms
            $this->sms_store->batch('SMSCODE')->moe('9999')->message($message)->store();
        }

        //return info
        $this->response(array("status" => "success"));
    }

    /**
     * Code POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function confirm_pn_post() {
        //Check for Values
        if(!$pnid = $this->input->post('pnid')) $this->response(array("status" => "error", "error" => "pnid - Parameter is not set"));
        if(!$code = $this->input->post('code')) $this->response(array("status" => "error", "error" => "code - Parameter is not set"));

        //Always upper case
        $code = strtoupper($code);

        $pn = $this->pnid_store->pnid($pnid)->code($code)->get();
        if(!empty($pn)) $this->pnid_store->enabled()->update();
        else $this->response(array("status" => "failed", "message" => "Code does not match"));

        $this->response(array("status" => "success"));
    }

    /**
     * Remove POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function remove_pn_post() {
        //Check for Values
        if(!$pnid = $this->input->post('pnid')) $this->response(array("status" => "error", "error" => "pnid - Parameter is not set"));

        $this->pnid_store->pnid($pnid)->data(array('enabled' => false))->update();
    }
}
