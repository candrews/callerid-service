<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class InfobelSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.infobel.com - This source includes business and residential data for Belgium, France, Luxembourg, Denmark, Austria, Italy, and Germany.";

    public $countries = array('be','fr','lu','dk','at','it','de');

	function get_curl()
	{
	    switch($this->country){
	        case 'be':
	            $long_country = 'belgium';
	            break;
	        case 'fr':
	            $long_country = 'france';
	            break;
	        case 'lu':
	            $long_country = 'luxembourg';
	            break;
	        case 'dk':
	            $long_country = 'denmark';
	            break;
	        case 'at':
	            $long_country = 'austria';
	            break;
	        case 'it':
	            $long_country = 'italy';
	            break;
	        case 'de':
	            $long_country = 'germany';
	            break;
            default:
                //this shouldn't happen...
	    }
	    return $this->curl_helper('http://www.infobel.com/en/' . $long_country . '/Inverse.aspx?qPhone=' . urlencode($this->thenumber));
	}

	function parse_response()
	{
        if($this->response->code == 200){
            $body = $this->response->body;
            
	        $pattern = '/<div class=\"result-item\">.*?<a.*?>(.+?)<\/a>.*?<div class=\"result-box-col\">(.*?)<\/div>/si';
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

