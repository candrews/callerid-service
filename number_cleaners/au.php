<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class AuNumberCleaner extends NumberCleaner
{
    function clean_number($number){
        switch(strlen($number)){
            case 10:
	            $pattern = '/^0(\d{9})$/';
                break;
            case 12:
	            $pattern = '/^\+61(\d{9})$/';
                break;
            default:
                return false;
        }
	    if(preg_match($pattern, $number, $matches)){
	        return array('number' => '+61' . $matches[1], 'country' => 'au');
	    }else{
	        return false;
        }
        
    }
}

