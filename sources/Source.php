<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

abstract class Source
{
    public $run_param;
    
    function get_configuration()
    {
        return array();
    }
    
    abstract function lookup();
    
    function is_applicable(){
        return true;
    }
}

