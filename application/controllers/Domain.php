<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require REST; 

class Domain extends REST_Controller {

    /**
     * Register POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function register_post() {
        
        //Get the body of request
        $data = $this->request->body;
        
        //Check for valid json
        if(count($data) == 0) $this->response(array("status" => "error",  "error" => "Invalid JSON"));
        
        //Set domain register request
        if(!$this->domain_request->data($data)->validate()) {
            
            //Check if result is set
            if($domain = $this->domain_request->result()) {
                //Check if the domain moe number match
                if($domain['moe'] == $data['moe']) $this->response(array("status" => "success", "token" => $domain['token']));
            } 
            
            //If we havn't returned the domain then error!
            $this->response(array("status" => "error",  "error" => $this->domain_request->get_errors())); 
        } 
        
        //Build the domain for insert
        if(!$domain = $this->domain_request->build()) $this->response(array("status" => "error",  "error" => $this->domain_request->get_errors()));
        
        //Insert our domain and return the info
        if(!$this->domain_store->data($domain)->store()) {
            $this->response(array("status" => "error",  "error" => "Error storing the domain info."));
            //Invalidate the token...
        }
        
        //return info
        $this->response(array("status" => "success", "token" => $domain['token']));
    }
    
    /**
     * Header Text POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function header_text_post() {
        
        if(!$data = $this->request->body) $this->response(array("status" => "error", "error" => "invalid JSON."));
        
        //Check for text
        if(!array_key_exists('text', $data)) $this->response(array("status" => "error", "error" => "text - not set."));
        $text = $data['text'];
        
        //Try load domain
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));

        //Update the text
        $this->domain_store->id($domain['_id'])->data(array('header_text' => $text))->update();
        
        //Respond
        $this->response(array("status" => "success"));
    }
    
    /**
     * Footer Text POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function footer_text_post() {
        
        if(!$data = $this->request->body) $this->response(array("status" => "error", "error" => "invalid JSON."));
        
        //Check for text
        if(!array_key_exists('text', $data)) $this->response(array("status" => "error", "error" => "text - not set."));
        $text = $data['text'];
        
        //Try load domain
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));

        //Update the text
        $this->domain_store->id($domain['_id'])->data(array('footer_text' => $text))->update();
        
        //Respond
        $this->response(array("status" => "success"));
    }
    
    /**
     * Header Image POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function header_image_post() {
        
        //Try load domain
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));
        
        //Get the file
        $file = file_get_contents('php://input');
        
        //generate md5 file name
        $file_md5 = md5($file);
        
        //Build the storage path
        $path = '/storage/domains/' . $domain['moe'] . '/header';

        //Check path exists
        if(!file_exists($path)) mkdir($path, 0777, true);
        if(!file_exists($path)) $this->response(array("status" => "error", "error" => "Could not create storage folder"));
        
        //Make file path
        $file_path = $path . '/' . $file_md5;
        
        //DB store path
        $db_file_path = $domain['moe'] . '/header/' . $file_md5;
        
        //Save the file
        file_put_contents($file_path, file_get_contents('php://input'));
		
		if(!file_exists($file_path)) $this->response(array("status" => "error", "error" => "Could not save attachment to storage"));  // In case /storage is not mounted - this will fail ?

        //Update the text
        $this->domain_store->id($domain['_id'])->data(array('header_image' => $db_file_path))->update();
        
        //Respond
        $this->response(array("status" => "success"));
    }
    
    /**
     * Remove Header Image POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function remove_header_image_post() {
        
        //Try load domain
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));
        
        //Check for header image
        if($domain['header_image'] == null) $this->response(array("status" => "success"));
        
        //Get the path
        $path = '/storage/domains/' . $domain['header_image'];
        
        //Remove
        unlink($path);
        
        //Update domain record
        $this->domain_store->id($domain['_id'])->data(array('header_image' => null))->update();
        
        $this->response(array("status" => "success"));
    }
    
    /**
     * Footer Image POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function footer_image_post() {
        
        //Try load domain
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));
        
        //Get the file
        $file = file_get_contents('php://input');
        
        //generate md5 file name
        $file_md5 = md5($file);
        
        //Build the storage path
        $path = '/storage/domains/' . $domain['moe'] . '/footer';
        
        //Check path exists
        if(!file_exists($path)) mkdir($path, 0777, true);
        if(!file_exists($path)) $this->response(array("status" => "error", "error" => "Could not create storage folder"));
        
        //Make file path
        $file_path = $path . '/' . $file_md5;
        
        //DB store path
        $db_file_path = $domain['moe'] . '/footer/' . $file_md5;
        
        //Save the file
        file_put_contents($file_path, file_get_contents('php://input'));

		if(!file_exists($file_path)) $this->response(array("status" => "error", "error" => "Could not save attachment to storage"));  // In case /storage is not mounted - this will fail ?

        //Update the text
        $this->domain_store->id($domain['_id'])->data(array('footer_image' => $db_file_path))->update();
        
        //Respond
        $this->response(array("status" => "success"));
    }
    
    /**
     * Remove Footer Image POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function remove_footer_image_post() {
        
        //Try load domain
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));
        
        //Check for header image
        if($domain['footer_image'] == null) $this->response(array("status" => "success"));
        
        //Get the path
        $path = '/storage/domains/' . $domain['footer_image'];
        
        //Remove
        unlink($path);
        
        //Update domain record
        $this->domain_store->id($domain['_id'])->data(array('footer_image' => null))->update();
        
        $this->response(array("status" => "success"));
    }
    
    /**
     * Disclaimer POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function disclaimer_post() {
        
        if(!$data = $this->request->body) $this->response(array("status" => "error", "error" => "invalid JSON."));
        
        //Check for text
        if(!array_key_exists('text', $data)) $this->response(array("status" => "error", "error" => "text - not set."));
        $text = $data['text'];
        
        //Try load domain
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));

        //Update the text
        $this->domain_store->id($domain['_id'])->data(array('disclaimer' => $text))->update();
        
        //Respond
        $this->response(array("status" => "success"));
    }
    
    /**
     * Information POST Endpoint
     *
     * @access public
     *
     * @return
     */
    public function info_post() {
        
        //Get domain
        if(!$domain = get_domain($this->rest->key)) $this->response(array("status" => "error", "error" => "Could not find domain from token"));
        
        //Build a nice array to return
        $info = array(
            "moe" => $domain['moe'],
            "name" => $domain['name'],
            "domain" => $domain['domain'],
            "domain_url" => $domain['domain'].'.school.kiwi',
            "school_url" => $domain['url'],
            "header_text" => $domain['header_text'],
            "header_image_url" => ($domain['header_image'] == null ? null : 'https://api.school.kiwi/MESSAGING/1.0/view/file?p='.$domain['header_image']),
            "footer_text" => $domain['footer_text'],
            "footer_image_url" => ($domain['footer_image'] == null ? null : 'https://api.school.kiwi/MESSAGING/1.0/view/file?p='.$domain['footer_image'])
        );
        
        //Return it
        $this->response(array("status" => "success", "domain" => $info));
        
    }    
}