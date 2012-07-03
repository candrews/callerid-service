<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class LibphonenumberSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "https://libphonenumber.appspot.com/";
    
    function get_curl()
    {
        return $this->curl_helper('http://libphonenumber.appspot.com/phonenumberparser', array('phoneNumber' => $this->thenumber, 'numberFile' => '@/dev/null'));
    }

    function parse_response()
    {
        $body = $this->response->body;

	    $result = new Result();
	    $result->name = $this->clean_scraped_html($this->get_name($body));
	    if(empty($result->name)){
	        return false;
        }
	    $result->address = $result->name;
	    return $result;
	}

        function get_name($body){
            $patternName = '/<TH>Location<\/TH><TD>(.+?)<\/TD>/sim';
            preg_match($patternName, $body, $name);
            if(isset($name[1])){
                return $name[1];
        }else{
            return null;
        }
        }

}

