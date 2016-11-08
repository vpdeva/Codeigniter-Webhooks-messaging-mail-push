<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email extends CI_Controller {

    public function batch() {
        
        if(!$batch = $this->uri->segment(3)) die('Error');

        $messages = $this->outbox_store->batch($batch)->getEmail();
        
        $this->load->view('header');
        $this->load->view('email_table', array('messages' => $messages));
        $this->load->view('footer');
    }
    
    public function all() {

        $messages = $this->outbox_store->getEmail();
        
        $this->load->view('header');
        $this->load->view('email_table', array('messages' => $messages));
        $this->load->view('footer');
    }
}
