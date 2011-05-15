<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class CaNumberCleaner extends NanpNumberCleaner
{
    protected $country = 'ca';
    
    protected $npas = array(
        '204', '226', '249', '250', '289', '306', '343', '365', '403', '416', '418', '438', '450',
        '506', '514', '519', '579', '581', '587', '604', '613', '647', '705', '709',
        '778', '780', '807', '819', '867', '873', '902', '905',
        '800', '866', '877', '888');
}
