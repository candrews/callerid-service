<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-LocalTel_CH.php
Original contribution by  PiaF forum user harryhirch
*/

class LocalTelSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://local.ch - These listings include business and residential data for Switzerland.";

    public $countries = array('ch');

	function get_curl()
	{
	    return $this->curl_helper('http://tel.search.ch/index.en.html?was=' . $this->thenumber);
	}

	function parse_response()
	{
        if($this->response->code == 200){
            $body = $this->response->body;

	        $pattern = '/<div class=\"telrecord vcard\">.*?<a [^>]*?class=\"fn org\">(.+?)<\/a>.*?<div class=\"adr\">(.+?)<\/div>/si';
	        if(preg_match($pattern, $body, $match)){
		        $result = new Result();
		        $result->name = $this->clean_scraped_html($match[1]);
		        $result->address = $this->clean_scraped_html($match[2]);
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

	function get_name($body){
	    $patternName = '/<p><b>(.+)<\/b>/si';
	    preg_match($patternName, $body, $name);
	    if(isset($name[1])){
	        return $name[1];
        }else{
            return null;
        }
	}

	function get_address($body){
	    $patternAddress = '/href="wtai.*?title="Link".*?<br\/>(.*?)<a/si';
	    preg_match($patternAddress, $body, $address);
	    if(isset($address[1])){
	        return $address[1];
        }else{
            return null;
        }
	}
}
