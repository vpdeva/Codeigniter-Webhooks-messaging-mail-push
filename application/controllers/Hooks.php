<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require REST;

class Hooks extends REST_Controller {

    //Test again!

     /**
     * Delivered POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function delivered_post() {

        //Dev
        //$_POST = $this->request->body;

        //Get the message id
        if(!$mid = $this->input->post('Message-Id')) $this->response(array("status" => "error"));

        //Remove <> wrapping
        preg_match('~<(.*?)>~', $mid, $output);
        $mid = $output[1];

        //Parse message ID
        $mid = explode("@", $mid, 2)[0];

        //Update the record
        $this->mail_store->mid($mid)->data(array('delivered' => true))->update();

        $this->response(array("status" => "success"));
    }

    /**
     * Opened POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function opened_post() {

        //Dev
//        $_POST = $this->request->body;

        //Get the message id
        if(!$mid = $this->input->post('message-id')) $this->response(array("status" => true));

        //Parse message ID
        $mid = explode("@", $mid, 2)[0];

        //Update the record
        $this->mail_store->mid($mid)->data(array('opened' => true))->update();

        $this->response(array("status" => "success"));
    }

    /**
     * Bounced POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function bounced_post() {

        //Dev
        //$_POST = $this->request->body;

        //Get the message id
        if(!$mid = $this->input->post('Message-Id')) $this->response(array("status" => true));

        //Remove <> wrapping
        preg_match('~<(.*?)>~', $mid, $output);
        $mid = $output[1];

        //Parse message ID
        $mid = explode("@", $mid, 2)[0];

        //Update the record
        $this->mail_store->mid($mid)->data(array('bounced' => true))->update();

        $this->response(array("status" => "success"));
    }

    /**
     * Email Reply POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function email_reply_post() {

        //Dev
        //$_POST = $this->request->body;

        //Check for data
        if(!$data = $this->input->post()) die('error');

        //Build reply
        if(!$message = $this->reply_request->data($data)->build()) die('error');

        //Store
        $this->inbox_store->message($message)->store();

        $this->response(array("status" => "success"));
    }

    /**
     * Email Reply POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function email_absence_reply_post() {
        //Dev
        //$_POST = $this->request->body;

        //Check for data
        if(!$data = $this->input->post()) die('error');

        //Build reply
        if(!$message = $this->reply_request->type(1)->data($data)->build()) die('error');

        //Store
        $this->inbox_store->message($message)->store();

        //Remove from the queue any other waiting..
        if($removed = $this->outbox_store->batch($message['reply']['batch'])->remove_from_queue()) {
            //Update the batch info
            $this->batch_store->batch($message['reply']['batch'])->data(array(
                'email_queued' => (0 - $removed['email']),
                'sms_queued' => (0 - $removed['sms']),
                'pn_queued' => (0 - $removed['pn'])
            ))->update();
        }

        $this->response(array("status" => "success"));
    }

    /**
     * SMS Reply POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function sms_reply_post() {

        //Check for data
        if(!$data = $this->request->body) die('error');

        //Build reply
        if(!$message = $this->reply_request->type(2)->data($data)->build()) die('error');

        //Store
        $this->inbox_store->message($message)->store();

        //If reply then flag that message as replied & remove future queue shit!. Hack for SMS as we dont know what they are replying to! :) got yo back!!
        if($message['reply']) {
            //Update the orginal
            $this->outbox_store->mid($message['reply']['mid'])->data(array('replied' => 1))->update();

            //Remove from the queue any other waiting..
            if($removed = $this->outbox_store->batch($message['reply']['batch'])->remove_from_queue()) {
                //Update the batch info
                $this->batch_store->batch($message['reply']['batch'])->data(array(
                    'email_queued' => (0 - $removed['email']),
                    'sms_queued' => (0 - $removed['sms']),
                    'pn_queued' => (0 - $removed['pn'])
                ))->update();
            }
        }

        //Respond
        $this->response(array("status" => "success"));
    }

    /**
     * PN Absence Reply POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function pn_reply_post() {

        //Validate the request
        if($errors = $this->rval->data($this->input->post())->required(array('reply_to', 'message', 'pnid'))->validate())
            $this->response(array("status" => "error", "error" => $errors));

        //Build reply
        if(!$message = $this->reply_request->type(3)->data($this->input->post())->build()) die('error');

        //Store message
        $this->inbox_store->message($message)->store();

        //Mark message as replyed & remove anything in the queue
        if($message['reply']) {
            //Update the orginal
            $this->outbox_store->mid($message['reply']['mid'])->data(array('replied' => true))->update();

            //Remove from the queue any other waiting..
            if($removed = $this->outbox_store->batch($message['reply']['batch'])->remove_from_queue()) {
                //Update the batch info
                $this->batch_store->batch($message['reply']['batch'])->data(array(
                    'email_queued' => (0 - $removed['email']),
                    'sms_queued' => (0 - $removed['sms']),
                    'pn_queued' => (0 - $removed['pn'])
                ))->update();
            }
        }

        //Ok
        $this->response(array("status" => "success"));
    }

    /**
     * PN Opened POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function pn_opened_post() {

        //Validate the request
        if($errors = $this->rval->data($this->input->post())->required(array('batch', 'mid', 'pnid'))->validate())
            $this->response(array("status" => "error", "error" => $errors));

        //Get the variables. I like this! Tidy things up ya know! Shorter code! Let me know if you have a better way!
        extract($this->rval->getArgs());

        //Saftey first only if the pnid is legit
        if($pnid = $this->pnid_store->pnid($pnid)->get()) {
            //Clear the queue
            if($removed = $this->outbox_store->batch($batch)->mobile(mobile_all_versions($pnid[0]['mobile']))->remove_from_queue()) {
                //Update the batch info
                $this->batch_store->batch($batch)->data(array(
                    'email_queued' => (0 - $removed['email']),
                    'sms_queued' => (0 - $removed['sms']),
                    'pn_queued' => (0 - $removed['pn'])
                ))->update();
            }
        }

        //Update the message
        $this->outbox_store->mid($mid)->data(array('opened' => true))->update();

        //Ok
        $this->response(array("status" => "success"));

    }

    /**
     * PN Opened POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function pn_removed_post() {

        //Validate the request
        if($errors = $this->rval->data($this->input->post())->required(array('mid', 'pnid', 'batch'))->validate())
            $this->response(array("status" => "error", "error" => $errors));

        //Get the variables. I like this! Tidy things up ya know! Shorter code! Let me know if you have a better way!
        extract($this->rval->getArgs());

        //Saftey first only if the pnid is legit
        if($pnid = $this->pnid_store->pnid($pnid)->get()) {
            //Clear the queue
            if($removed = $this->outbox_store->batch($batch)->mobile(mobile_all_versions($pnid[0]['mobile']))->remove_from_queue()) {
                //Update the batch info
                $this->batch_store->batch($batch)->data(array(
                    'email_queued' => (0 - $removed['email']),
                    'sms_queued' => (0 - $removed['sms']),
                    'pn_queued' => (0 - $removed['pn'])
                ))->update();
            }
        }

        //Update the message
        $this->outbox_store->mid($mid)->data(array('removed' => true))->update();

        //Ok
        $this->response(array("status" => "success"));

    }
}
