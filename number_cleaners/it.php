<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class ItNumberCleaner extends NumberCleaner
{
    public $international_calling_prefix = '00';
    
    function clean_number($number){
	    if(preg_match('/^(\d{6,11})$/', $number, $matches)){
	        return array('number' => '+39' . $matches[1], 'country' => 'it');
	    }else if(preg_match('/^\+39(\d{6,11})$/', $number, $matches)){
	        return array('number' => '+39' . $matches[1], 'country' => 'it');
        }else{
	        return false;
        }
    }
}

