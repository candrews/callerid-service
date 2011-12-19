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
        return $this->curl_helper('http://www.yellowpages.com/reversephonelookup?fap_terms%5Bphone%5D=' . urlencode(substr($this->thenumber,2)) . '&fap_terms%5Bsearchtype%5D=phone');
    }

    function parse_response()
    {
        $body = $this->response->body;

	    $result = new Result();
	    $result->name = $this->clean_scraped_html($this->get_name($body));
	    if(empty($result->name)){
	        return false;
        }
	    $result->address = $this->clean_scraped_html($this->get_address($body));
	    return $result;
	}

        function get_name($body){
            //if there is just 1 result, this pattern matches
            $patternName = '/\<h1 class=\"heading-greybox\">(.+?)<\/h1>/sim';
            preg_match($patternName, $body, $name);
            if(isset($name[1])){
                return $name[1];
            }else{
                //if there are multiple results, this pattern matches the first one
                $patternName = '/\<address.+?<a .+?>(.+?)<\/a>/sim';
                preg_match($patternName, $body, $name);
                if(isset($name[1])){
                    return $name[1];
                }else{
                    return null;
                }
            }
        }

        function get_address($body){
            //if there are multiple results, this pattern matches the first one
            $patternName = '/<span class=\"address\">(.+?)<\/span>/si';
            preg_match($patternName, $body, $name);
            if(isset($name[1])){
                return $name[1];
            }else{
                //if there is just 1 result, this pattern matches
                $patternName = '/<address.*?>(.+?)<\/address>/si';
                preg_match($patternName, $body, $name);
                if(isset($name[1])){
                    return $name[1];
                }else{
                    
                    return null;
                }
            }
        }
}

