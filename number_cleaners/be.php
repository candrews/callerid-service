<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class BeNumberCleaner extends NumberCleaner
{
    function clean_number($number){
	    if(preg_match('/^0([1-9]\d{7,8})$/', $number, $matches)){
	        return array('number' => '+32' . $matches[1], 'country' => 'be');
	    }else if(preg_match('/^\+32([1-9]\d{7,8})$/', $number, $matches)){
	        return array('number' => '+32' . $matches[1], 'country' => 'be');
        }else{
	        return false;
        }
    }
}

