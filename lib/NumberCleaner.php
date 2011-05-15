<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

abstract class NumberCleaner
{
    /*
     * the outgoing international call prefix (for example, 011 in the US).
     */
    public $international_calling_prefix = '00';
    
    /*
     * Return a associative array with keys 'number' and 'country', or false if the number is not valid
     * @param $number the number to clean
     */
    abstract function clean_number($number);
}

