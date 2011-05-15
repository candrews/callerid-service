<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class ChNumberCleaner extends NumberCleaner
{
    public $international_calling_prefix = '00';
    
    function clean_number($number){
        switch(strlen($number)){
            case 10:
	            $pattern = '/^0(\d{9})$/';
                break;
            case 12:
	            $pattern = '/^\+41(\d{9})$/';
                break;
            default:
                return false;
        }
	    if(preg_match($pattern, $number, $matches)){
	        return array('number' => '+41' . $matches[1], 'country' => 'ch');
	    }else{
	        return false;
        }
        
    }
}

