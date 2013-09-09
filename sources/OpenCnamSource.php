<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class OpenCnamSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.opencnam.com search";
    
    public $countries = array('us', 'ca');
    
    function get_curl()
    {
        return $this->curl_helper('https://api.opencnam.com/v2/phone/' . $this->thenumber . '?format=json');
    }

    function parse_response()
    {
        $body = $this->response->body;
        $obj = json_decode($body);

        $result = new Result();
        $result->name = $obj->name;
        if(empty($result->name)){
            return false;
        }
        return $result;
    }

}

