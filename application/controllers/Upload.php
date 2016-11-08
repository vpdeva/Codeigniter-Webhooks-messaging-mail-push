<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require REST; 

class Upload extends REST_Controller {

    /**
     * Index POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function index_post() {
        
        //Check for filename
        $headers = $this->input->request_headers();
        if(!array_key_exists('filename', $headers) && !array_key_exists('Filename', $headers)) $this->response(array("status" => "error",  "error" => "filename is not set."));
        
        //Try load domain
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));
        
        //Set filename
        $filename = (array_key_exists('filename', $headers) ? $headers['filename'] : $headers['Filename']);
        
        //Set moe
        $moe = $domain['moe'];
        
        //Get the file
        $file = file_get_contents('php://input');
        
        //generate md5 file name
        $file_md5 = md5($file);
        
        //Build the storage path
        $path = '/storage/attachments/' . $moe;
        
        //Check path exists
        if(!file_exists($path)) mkdir($path, 0777, true);
        
        //Make file path
        $file_path = $path . '/' . $file_md5;
        
        //Save the file
        file_put_contents($file_path, file_get_contents('php://input'));
		
		if(!file_exists($file_path)) $this->response(array("status" => "error", "error" => "Could not save attachment to storage"));  // In case /storage is not mounted - this will fail ?
		
        
        //Generate token
        $token = sha1(uniqid($file_md5.time(), true));
        
        //Store info in DB
        $id = $this->file_store->token($token)->filename($filename)->file_md5($file_md5)->path($file_path)->store();
        
        //Build the url
        $url = 'https://api.school.kiwi/MESSAGING/1.0/View/Attachment?f=' . $filename . '&t=' . $token;
        
        //Return the id
        $this->response(array("status" => "success",  "id" => $id, 'url' => $url));   
    }
}