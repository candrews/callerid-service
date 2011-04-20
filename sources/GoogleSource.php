<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-Google.php
*/

class GoogleSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.google.com - These listing include data from the Google (Residential) Phone Book.";
    
    function prepare(){
	    $this->thenumber = $this->clean_uscan_number($this->thenumber);
	    return $this->thenumber !== false;
    }
	
	function get_curl(){
	    return $this->curl_helper('http://www.google.com/search?rls=en&q=phonebook:' . $this->thenumber . '&ie=UTF-8&oe=UTF-8');
	}
	
	function parse_response(){
        if($this->response->code == 200){
	        $body = $this->response->body;
		    $start = strpos($body, "Phonebook</b>");
		    $body = substr($body,$start+13);
		    $start = strpos($body, "<tr bgcolor=#e5ecf9><td>");
		    $body = substr($body,$start+24);
		    $end = strpos($body, "<td>(");
		    $body = substr($body,0,$end);
		    if (strlen($body) > 1)
		    {
			    $result = new Result();
			    $result->name = $this->clean_scraped_html($body);
			    return $result;
		    }
		    else
		    {
			    return false;
		    }
	    }else{
	        return false;
	    }
	}
}

