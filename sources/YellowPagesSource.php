<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-Yellow_Pages.php
*/

class YellowPagesSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://yellowpages.addresses.com - This will return only business listings, it will not return residential listings.";
    
    function prepare(){
	    $this->thenumber = $this->clean_uscan_number($this->thenumber);
	    return $this->thenumber !== false;
    }
    
    function get_curl()
    {
        return $this->curl_helper('http://yellowpages.addresses.com/yellow-pages/phone:' . $this->thenumber . '/listings.html');
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
		    //for yellow pages, all results are company names, so company = name
		    $result->company = $result->name;
		    return $result;
	    }else{
	        return false;
	    }
	}

	function get_name($body){
	    $start= strpos($body, "listing_name");
	    if($start > 1){
	        $body = substr($body,$start);
	        $start= strpos($body, ">");
	        $body = substr($body,$start+1);
	        $end= strpos($body, "</a>");
	        $name = substr($body,0,$end);
	        return $name;
        }else{
            return null;
        }
	}

	function get_address($body){
	    $start= strpos($body, "sml_txt");
	    if($start > 1){
	        $body = substr($body,$start);
	        $start= strpos($body, ">");
	        $body = substr($body,$start+1);
	        $end= strpos($body, "</div>");
	        $name = substr($body,0,$end);
	        return $name;
        }else{
            return null;
        }
	}
}

