<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class YellowPagesBusinessSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.yellowpages.com business search - This will return only businesses, it will not return residential listings.";
    
    public $countries = array('us', 'ca');
    
    function get_curl()
    {
        return $this->curl_helper('https://www.yellowpages.com/phone/?phone_search_terms=' . urlencode(substr($this->thenumber,2)));
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
                    //for yellow pages, all results are company names, so company = name
                    $result->company = $result->name;
		    $result->address = $this->clean_scraped_html($this->get_address($body));
		    return $result;
	    }else{
	        return false;
	    }
	}

        function get_name($body){
            $patternName = '/<h3 class=\"business-name fn org\">(.+?)<\/h3>/sim';
            preg_match($patternName, $body, $name);
            if(isset($name[1])){
                return $name[1];
        }else{
            return null;
        }
        }

        function get_address($body){
            $patternName = '/<span class=\"listing-address adr\">(.+?)<a /si';
            preg_match($patternName, $body, $name);
            if(isset($name[1])){
                return $name[1];
        }else{
            return null;
        }
        }
}

