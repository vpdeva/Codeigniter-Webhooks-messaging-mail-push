<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class File_store extends CI_Model {

    /**
     * Private variables
     */
    private $id = false;
    private $token = false;
    private $filename = false;
    private $file_md5 = false;
    private $path = false;
    
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
        //User defaults
        $store = array('token' => $this->token, 'filename' => $this->filename, 'md5' => $this->file_md5, 'path' => $this->path, 'ts' => new MongoDate());
        
        //Insert
        $result = $this->mdb->insert('attachments', $store);
        
        //Clear
        $this->clear_();
        
        //Return ID
        return $result->{'$id'};
    }
    
    /**
     * Store file info
     */
    public function get() {
        
        //If id is set
        if($this->id) {
            $id = false;
            try {
                $id = new mongoid($this->id);
            }
            catch(Exception $e) {
                return false;
            }
        
            //Get by the id
            $this->mdb->where('_id', $id);
        }
        
        //If token is set
        if($this->token) {
            $this->mdb->where('token', $this->token);
        }
        
        //Results
        $result = $this->mdb->get('attachments');
        
        //If no results then return
        if(count($result) != 1) return false;
        
        //Get em
        return $result[0];
    }
    
    

    /**
     * Setters
     */
    public function id($id) {
        $this->id = $id;
        return $this;
    }
    public function token($token) {
        $this->token = $token;
        return $this;
    }
    public function filename($filename) {
        $this->filename = $filename;
        return $this;
    }
    public function file_md5($file_md5 = '') {
        $this->file_md5 = $file_md5;
        return $this;
    }
    public function path($path = '') {
        $this->path = $path;
        return $this;
    }

    /**
     * Reset er thing
     */
    private function clear_() {
        $this->id = false;
        $this->token = false;
        $this->filename = false;
        $this->file_md5 = false;
        $this->path = false;
    }
    
    
}


?>