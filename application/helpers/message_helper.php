<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
  
    if(!function_exists('mobile_all_versions')) {
        function mobile_all_versions($mobile) {
            
            $mobile = str_replace(' ', '', $mobile);
            $result = array();
            $_char1 = substr($mobile,0,1);

            if($_char1 == '+') {
                //+64..
                $result[] = $mobile;
                //64...
                $result[] = str_replace_first('+', '', $mobile);
                //02..
                $result[] = str_replace_first('+64', '0', $mobile);
            }
            else if($_char1 == '6') {
                //+64..
                $result[] = '+' . $mobile;
                //64...
                $result[] = $mobile;
                //02..
                $result[] = str_replace_first('64', '0', $mobile);
            }
            else if($_char1 == '0') {
                //+64..
                $result[] = '+' . str_replace_first('0', '64', $mobile);
                //64...
                $result[] = str_replace_first('0', '64', $mobile);
                //02..
                $result[] = $mobile;
            }
            else $result[] = $mobile;

            return $result;
        } 
    }

    if(!function_exists('str_replace_first')) {
        function str_replace_first($from, $to, $subject) {
            $from = '/'.preg_quote($from, '/').'/';

            return preg_replace($from, $to, $subject, 1);
        }
    }

    if(!function_exists('message_type_tostr')) {
        function message_type_tostr($type) {
            switch($type) {
                case 1:
                    return 'Email';
                case 2:
                    return 'SMS';
                case 3:
                    return 'PN';
                default:
                    return '?';
            }
        }
    }



    if(!function_exists('queued_send_time')) {
        function queued_send_time($queue_date, $delay) {
            
            $time = new DateTime();
            $time->setTimestamp($queue_date->sec);
            $time->add(new DateInterval('PT' . $delay . 'M'));
            $send_date = $time->format('d/m/Y H:i:s');
            $ttl = ceil(($time->getTimestamp() - time()) / 60);
            
            return $send_date;
        }
    }

    if(!function_exists('message_status_tostr')) {
        function message_status_tostr($data) {
            $sent = (array_key_exists('sent', $data) ? $data['sent'] : false);
            $delivered = (array_key_exists('delivered', $data) ? $data['delivered'] : false);
            $opened = (array_key_exists('opened', $data) ? $data['opened'] : false);
            $bounced = (array_key_exists('bounced', $data) ? $data['bounced'] : false);
            
            $result = false;
            if($sent) $result = 'Sent';
            if($delivered) $result = 'Delivered';
            if($opened) $result = 'Opened';
            if($bounced) $result = 'Bounced';
            return $result;
        }
    }





?> 