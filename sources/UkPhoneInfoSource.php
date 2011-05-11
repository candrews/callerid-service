<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class UkPhoneInfoSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.telcodata.us/ - Provides data for the UK.<br>These listings generally return only the geographic location of the caller, not a name.<br>Because the data provided is less specific than other sources, this data source is usualy configured near the bottom of the list of active data sources.";

    public $countries = array('uk');

	function get_curl()
	{
	    return $this->curl_helper('http://www.ukphoneinfo.com/search.php?d=nl&GNG=' . '0' . substr($this->thenumber,3));
	}

	function parse_response()
	{
        if($this->response->code == 200){
            $body = $this->response->body;
            
	        $pattern = '/<h2>(.*?)<\/h2>/si';
	        
	        if(preg_match($pattern, $body, $match)){
		        $result = new Result();
		        $result->name = $this->clean_scraped_html($match[1]);
		        if(empty($result->name)){
		            return false;
	            }else{
	                $result->name.=', UK';
	                return $result;
                }
            }else{
                return false;
            }
	    }else{
	        return false;
	    }
	}
}

