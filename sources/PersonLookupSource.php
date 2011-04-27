<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-Personlookup_AU.php
*/

class PersonLookupSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://personlookup.com.au - These listings include residential data for AU.";

    public $countries = array('au');

	function get_curl()
	{
	    return $this->curl_helper('http://personlookup.com.au/browse.aspx?t=search&state=all&s=number&value=' . urlencode($this->thenumber));
	}

	function parse_response()
	{
        if($this->response->code == 200){
            $body = $this->response->body;
            
	        $pattern = '/<div class="result">.*?<div class=\"col\">(.+?)<\/div>\s*(?:<div class=\"col\">(.+?)<\/div>)?/si';
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
	    }else{
	        return false;
	    }
	}
}

