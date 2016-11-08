<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require REST; 

class View extends REST_Controller {

    /**
     * Attachment GET Endpoint
     *
     * @access public
     *
     * @return
     */
    public function attachment_get() {
        
        //Try get token
        if(!$token = $this->input->get('t')) die('Error');
        
        //Try get file name
        if(!$filename = $this->input->get('f')) die('Error');
        
        //Try and get the attachment from the DB
        if(!$file = $this->file_store->token($token)->get()) die('Error');
        
        //Check that the filename matches the token
        if($file['filename'] != $filename) die('Error');
        
        //Get info from the file name
        if(!$info = pathinfo($file['filename'])) die('Error');
        
        //Load the downloader class
        $this->load->helper('download');

        //Download the file
        force_download($file['filename'], file_get_contents($file['path']));   
    }
    
    /**
     * Email GET Endpoint
     *
     * @access public
     *
     * @return
     */
    public function email_get() {
        
        //Try get the mid
        if(!$lookup = $this->uri->segment(3)) die('Error');
        
        //Get the message
        if(!$message = $this->mail_store->lookup($lookup)->get()) die('Error');
        
        //Remove the view in broswer element
        $doc = new DOMDocument();
        $doc->loadHTML($message['html']);
        $element = $doc->getElementById('viewinbrowser');
        $element->parentNode->removeChild($element);
        $html = $doc->saveHTML();

        //Display the email
        echo $html;
    }
    
    /**
     * File GET Endpoint
     *
     * @access public
     *
     * @return
     */
    public function file_get() {
        
        //Get path
        if(!$path = $this->input->get('p')) die();
        
        //Build correct path
        $full_path = '/storage/domains/' . $path;
        
        //Check file exists
        if(!file_exists($full_path)) die("file not found");
        
        //Load the downloader class
        $this->load->helper('download');

        //Download the file
        force_download('file', file_get_contents($full_path));   
    }
}