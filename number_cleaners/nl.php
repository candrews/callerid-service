<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class NlNumberCleaner extends NumberCleaner
{
    public $international_calling_prefix = '00';
    
    function clean_number($number){
	    if(preg_match('/^0(\d{7,10})$/', $number, $matches)){
	        return array('number' => '+31' . $matches[1], 'country' => 'nl');
	    }else if(preg_match('/^\+31(\d{7,10})$/', $number, $matches)){
	        return array('number' => '+31' . $matches[1], 'country' => 'nl');
        }else{
	        return false;
        }
    }
}

