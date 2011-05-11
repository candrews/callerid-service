<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class FiNumberCleaner extends NumberCleaner
{
    function clean_number($number){
	    if(preg_match('/^0(\d{5,11})$/', $number, $matches)){
	        return array('number' => '+358' . $matches[1], 'country' => 'fi');
	    }else if(preg_match('/^\+358(\d{5,11})$/', $number, $matches)){
	        return array('number' => '+358' . $matches[1], 'country' => 'fi');
        }else{
	        return false;
        }
    }
}

