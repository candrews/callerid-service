<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class InNumberCleaner extends NumberCleaner
{
    public $international_calling_prefix = '00';
    
    function clean_number($number){
        switch(strlen($number)){
            case 11:
	            $pattern = '/^0(\d{10})$/';
                break;
            case 13:
	            $pattern = '/^\+91(\d{10})$/';
                break;
            default:
                return false;
        }
	    if(preg_match($pattern, $number, $matches)){
	        return array('number' => '+91' . $matches[1], 'country' => 'in');
	    }else{
	        return false;
        }
        
    }
}

