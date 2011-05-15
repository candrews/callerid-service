<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class LuNumberCleaner extends NumberCleaner
{
    public $international_calling_prefix = '00';
    
    function clean_number($number){
	    if(preg_match('/^(\d{5,11})$/', $number, $matches)){
	        return array('number' => '+352' . $matches[1], 'country' => 'lu');
	    }else if(preg_match('/^\+352(\d{5,11})$/', $number, $matches)){
	        return array('number' => '+352' . $matches[1], 'country' => 'lu');
        }else{
	        return false;
        }
    }
}

