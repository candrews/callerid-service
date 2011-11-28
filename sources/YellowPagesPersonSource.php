<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class YellowPagesPersonSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.yellowpages.com person search - This will return only people, it will not return business listings.";
    
    public $countries = array('us', 'ca');
    
    function get_curl()
    {
        return $this->curl_helper('http://www.yellowpages.com/reversephonelookup?fap_terms[phone]=' . urlencode(substr($this->thenumber,2)) . '&fap_terms[searchtype]=phone');
    }

    function parse_response()
    {
        if($this->response->code == 200){
            $body = $this->response->body;

		    $result = new Result();
		    $result->name = $this->clean_scraped_html($this->get_name($body));
		    if(empty($result->name)){
		        return false;
	        }
		    $result->address = $this->clean_scraped_html($this->get_address($body));
		    return $result;
	    }else{
	        return false;
	    }
	}

        function get_name($body){
            $patternName = '/\<address.+?<a .+?>(.+?)<\/a>/sim';
            preg_match($patternName, $body, $name);
            if(isset($name[1])){
                return $name[1];
        }else{
            return null;
        }
        }

        function get_address($body){
            $patternName = '/<span class=\"address\">(.+?)<\/span>/si';
            preg_match($patternName, $body, $name);
            if(isset($name[1])){
                return $name[1];
        }else{
            return null;
        }
        }
}
