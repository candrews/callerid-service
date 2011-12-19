<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class FonectaSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://en.fonecta.fi - These listings include residential and business data for Finland.";

    public $countries = array('fi');

	function get_curl()
	{
	    return $this->curl_helper('http://en.fonecta.fi/white-pages/-/q_' . urlencode($this->thenumber));
	}

	function parse_response()
	{
        $body = $this->response->body;
        
        $pattern = '/<a class=\'fn\'.*?>(.*?)<\/a>.*?<div class=\'adr\'>(.*?)<\/div>/si';
        
        if(preg_match($pattern, $body, $match)){
	        $result = new Result();
	        $result->name = $this->clean_scraped_html($match[1]);
	        if(isset($match[2])) $result->address = $this->clean_scraped_html($match[2]);
	        if(empty($result->name)){
	            return false;
            }else{
                return $result;
            }
        }else{
            return false;
        }
	}
}

