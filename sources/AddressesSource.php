<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-Addresses.php
*/

class AddressesSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://phonenumbers.addresses.com - This will return only residential listings, business listings will not be returned.";
    
    function prepare(){
	    $this->thenumber = $this->clean_uscan_number($this->thenumber);
	    return $this->thenumber !== false;
    }
	
	function get_curl()
	{
	    return $this->curl_helper('http://phonenumbers.addresses.com/results.php?ReportType=33&qfilter[pro]=on&qi=0&qk=10&qnpa=' . substr($this->thenumber,0,3) . '&qp='.substr($this->thenumber,3));
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
	    $patternName = '/<div class=["\']phone_detail_name["\']>.*?<a .*?>(.*?)<\/a>/si';
	    preg_match($patternName, $body, $name);
	    if(isset($name[1])){
	        return $name[1];
        }else{
            return false;
        }
	}

	function get_address($body){
	    $patternAddress = '/<div class=["\']phone_detail_addr["\']>(.*?)(?:<br>(.*?))?(?:<br>(.*?))?<\/div>/si';
	    preg_match($patternAddress, $body, $address);
	    //1 should be the street address
	    //2 should be the city
	    //3 should be phone number (which we'll ignore)
	    if(isset($address[1])){
	        $ret = $address[1];
	        if(isset($address[1])) $ret.=', '.$address[2];
	        return $ret;
        }else{
            return false;
        }
	}
}

