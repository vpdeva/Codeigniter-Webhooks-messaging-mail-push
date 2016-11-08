<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
  
if(!function_exists('get_domain')) {
    function get_domain($token) {
        if(!$token) return false;
        $ci =& get_instance();
        if(!$domain = $ci->domain_store->token($token)->get()) return false;
        return $domain;
    } 
}

if(!function_exists('tidy_domain')) {
    function tidy_domain_for_email($domain) {
        unset($domain['_id']);
        unset($domain['token']);
        
        if($domain['header_image']) unset($domain['header_text']);
        else if($domain['header_text']) unset($domain['header_image']);
        else {
            unset($domain['header_image']);
            unset($domain['header_text']);
        }

        if($domain['footer_image']) unset($domain['footer_text']);
        else if($domain['footer_text']) unset($domain['footer_image']);
        else {
            unset($domain['footer_image']);
            unset($domain['footer_text']);
        }
        return $domain;
    } 
}

?> 