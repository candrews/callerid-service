<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-Telco_Data.php
*/

class TelcoDataSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.telcodata.us/ - Provides data for the US and some Canadian locations.<br>These listings generally return only the geographic location of the caller, not a name.<br>Because the data provided is less specific than other sources, this data source is usualy configured near the bottom of the list of active data sources.";

    public $countries = array('us', 'ca');
	
	function get_curl()
	{
	    return $this->curl_helper('http://www.telcodata.us/query/queryexchangexml.html?npa=' . substr($this->thenumber,2,3) . '&nxx='.substr($this->thenumber,5,3));
	}
	
	function parse_response()
	{
        if($this->response->code == 200){
            $body = $this->response->body;
		    
		    $start = strpos($body, "<englishname>");
		    $englishname = substr($body,$start+13);
		    $end = strpos($englishname, "</englishname>");
		    $englishname = substr($englishname,0,$end);
		    
		    //$start2 = strpos($body, "<company>");
		    //$result3 = substr($body,$start2+9);
		    //$end2 = strpos($result3, "</company>");
		    //$result3 = substr($result3,0,$end2);
		    
		    $start = strpos($body, "<state>");
		    $state = substr($body,$start+7);
		    $end = strpos($state, "</state>");
		    $state = substr($state,0,$end);
		    
		    $body = $englishname.", ".$state ;
		    
		    if(strlen($englishname) > 1)
		    {
			    $result = new Result();
			    $result->name = $this->clean_scraped_html($body);
			    //since this source only returns a location, address and name are the same
			    $result->address = $result->name;
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

