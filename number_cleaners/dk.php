<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class DkNumberCleaner extends NumberCleaner
{
    function clean_number($number){
        switch(strlen($number)){
            case 10:
	            $pattern = '/^([2-9]\d{7})$/';
                break;
            case 11:
	            $pattern = '/^\+45([2-9]\d{7})$/';
                break;
            default:
                return false;
        }
	    if(preg_match($pattern, $number, $matches)){
	        return array('number' => '+45' . $matches[1], 'country' => 'dk');
	    }else{
	        return false;
        }
        
    }
}

