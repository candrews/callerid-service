<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-Google.php
*/

class CanpagesSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.canpages.ca - These listings will return both residential and business Canadian listings as well as (at least some) American business and residential listings.";
    
    public $countries = array('us', 'ca');
	
	function get_curl(){
	    return $this->curl_helper('http://www.canpages.ca/rl/index.jsp?fi=Search&lang=0&val=' . urlencode(substr($this->thenumber,2)));
	}
	
	function parse_response(){
        if($this->response->code == 200){
	        $result = new Result();
	        
	        $body = $this->response->body;
	        
		    $notfound = strpos($body, "PHONE_USER_ERROR");
		    $notfound = ($notfound < 1) ? strpos($body, "PHONE_NO_RESULTS") : $notfound;
		    if($notfound)
		    {
			    return false;
		    }
		    
		    $patternAddress = '/class=\"listing_content(?:_ps)?\">.*?<br\s*?\/>(.+?)\s*<br\s*?\/>\s*<div/si';
		    $patternCompany = '/style=\"font-size: 13px\">(.+?)<\/a>/si';
		    $patternName = '/class=\"header_listing\">(.+?)<\/a>/si';
		    
            preg_match($patternCompany, $body, $company);
            if(isset($company[1])){
                $result->company = $this->clean_scraped_html($company[1]);
            }
		    
            preg_match($patternName, $body, $name);
            if(isset($name[1])){
                $result->name = $this->clean_scraped_html($name[1]);
            }
		    
            preg_match($patternAddress, $body, $address);
            if(isset($address[1])){
                $result->address = $this->clean_scraped_html($address[1]);
            }

	        if(empty($result->name)){
	            $result->name = $result->company;
            }
            if(empty($result->name)){
                //couldn't find a name... have to return failure
                return false;
            }else{
		        return $result;
		    }
	    }else{
	        return false;
	    }
    }
}

