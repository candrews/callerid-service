<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class DeNumberCleaner extends NumberCleaner
{
    function clean_number($number){
	    if(preg_match('/^(\d{3,12})$/', $number, $matches)){
	        return array('number' => '+49' . $matches[1], 'country' => 'de');
	    }else if(preg_match('/^\+49(\d{3,12})$/', $number, $matches)){
	        return array('number' => '+49' . $matches[1], 'country' => 'de');
        }else{
	        return false;
        }
    }
}

