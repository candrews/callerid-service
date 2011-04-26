<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class SeNumberCleaner extends NumberCleaner
{
    function clean_number($number){
	    if(preg_match('/^0([1-9]\d{6,8})$/', $number, $matches)){
	        return array('number' => '+46' . $matches[1], 'country' => 'se');
	    }else if(preg_match('/^\+46([1-9]\d{6,8})$/', $number, $matches)){
	        return array('number' => '+46' . $matches[1], 'country' => 'se');
        }else{
	        return false;
        }
    }
}

