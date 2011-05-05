<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class FileLoggingPlugin extends Plugin
{
    public $path = 'callerid.log';

    function onAfterLookup($thenumber_orig, $thenumber, $country, &$winning_result){
        $fh = fopen($this->path, 'a');
        fwrite($fh, "$thenumber_orig $thenumber $winning_result->name" . "\n");
        fclose($fh);
        return true;
    }
}
