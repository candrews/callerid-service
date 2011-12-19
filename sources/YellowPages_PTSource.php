<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class YellowPages_PTSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://yellowpages.pt - These listings include residential and business data for PT.";

    public $countries = array('pt');

	function get_curl()
	{
	    return $this->curl_helper('http://www.white.yellowpages.pt/q/name/who/' . substr($this->thenumber,4) . '/1/');
	}

	function parse_response()
	{
        $body = $this->response->body;
        
        $pattern = '/<div class="result">.*?<div class=\"col\">(.+?)<\/div>\s*(?:<div class=\"col\">(.+?)<\/div>)?/si';
        
        $pattern = '/<span id=\"listingbase1\" class=\"result-title-link result-bn\">(.+?)<\/span>.*?<div class=\"result-address\">(.+?)<\/div>/si';
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

