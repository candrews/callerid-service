<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class AtNumberCleaner extends NumberCleaner
{
    function clean_number($number){
	    if(preg_match('/^0(\d{4,16})$/', $number, $matches)){
	        return array('number' => '+43' . $matches[1], 'country' => 'at');
	    }else if(preg_match('/^\+43(\d{4,16})$/', $number, $matches)){
	        return array('number' => '+43' . $matches[1], 'country' => 'at');
        }else{
	        return false;
        }
    }
}

