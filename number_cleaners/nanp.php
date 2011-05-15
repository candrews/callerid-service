<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

abstract class NanpNumberCleaner extends NumberCleaner
{
    public $international_calling_prefix = '011';
    
    function clean_number($number){
        switch(strlen($number)){
            case 10:
	            $pattern = '/^([2-9][\d]{2})([2-9][\d]{2})(\d{4})$/';
                break;
            case 11:
	            $pattern = '/^1([2-9][\d]{2})([2-9][\d]{2})(\d{4})$/';
                break;
            case 12:
	            $pattern = '/^\+1([2-9][\d]{2})([2-9][\d]{2})(\d{4})$/';
                break;
            default:
                return false;
        }
	    if(preg_match($pattern, $number, $matches)){
	        $npa = $matches[1];
	        $nxx = $matches[2];
	        if(preg_match('/\d11/',$nxx)) return false; //the last 2 digits of nxx cannot both be 1
	        $station = $matches[3];
	        $ret =  '+1'.$npa.$nxx.$station;
            if(in_array($npa, $this->npas)){
                return array('number'=>$ret, 'country'=>$this->country);
            }
            return false;
	    }else{
	        return false;
        }
    }
}

