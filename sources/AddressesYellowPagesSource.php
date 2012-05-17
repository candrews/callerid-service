<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-Yellow_Pages.php
*/

class AddressesYellowPagesSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://yellowpages.addresses.com - This will return only business listings, it will not return residential listings.";
    
    public $countries = array('us', 'ca');
    
    function get_curl()
    {
        return $this->curl_helper('http://yellowpages.addresses.com/yellow-pages/phone:' . urlencode($this->thenumber) . '/listings.html');
    }

    function parse_response()
    {
        $body = $this->response->body;
        $pattern = '/<a\s+class="listing_name .*?>(.*?)<\/a>.*?<div\s+class="sml_txt".?>(.*?)<\/div>/sim';
        if(preg_match($pattern, $body, $match)){
	        $result = new Result();
	        $result->name = $this->clean_scraped_html($match[1]);
	        if(isset($match[2])) $result->address = $this->clean_scraped_html(str_replace('<br>',',',$match[2]));
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

