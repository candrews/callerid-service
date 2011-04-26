<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

abstract class NumberCleaner
{
    /*
     * Return a associative array with keys 'number' and 'country', or false if the number is not valid
     */
    abstract function clean_number($number);
}

