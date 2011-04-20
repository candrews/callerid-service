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
    public $source_desc = "http://www.google.com - These listing include data from the Google (Residential) Phone Book.";

    function is_applicable()
    {
	    $number_error = false;
	    //check for the correct 11 digits NAP phone numbers in international format.
	    // country code + number
	    if (strlen($this->thenumber) == 11)
	    {
		    if (substr($this->thenumber,0,1) == 1)
		    {
			    $this->thenumber = substr($this->thenumber,1);
		    }
		    else
		    {
			    $number_error = true;
		    }

	    }
	    // international dialing prefix + country code + number
	    if (strlen($this->thenumber) > 11)
	    {
		    if (substr($this->thenumber,0,3) == '001')
		    {
			    $this->thenumber = substr($this->thenumber, 3);
		    }
		    else
		    {
			    if (substr($this->thenumber,0,4) == '0111')
			    {
				    $this->thenumber = substr($this->thenumber,4);
			    }			
			    else
			    {
				    $number_error = true;
			    }
		    }

	    }	
	    // number
          if(strlen($this->thenumber) < 10)
	    {
		    $number_error = true;

	    }

	    if(!$number_error)
	    {
		    $npa = substr($this->thenumber,0,3);
		    $nxx = substr($this->thenumber,3,3);
		    $station = substr($this->thenumber,6,4);
		
		    // Check for Toll-Free numbers
		    $TFnpa = false;
		    if($npa=='800'||$npa=='866'||$npa=='877'||$npa=='888')
		    {
			    $TFnpa = true;
		    }
		
		    // Check for valid CAN NPA
		    $npalistCAN = array(
			    "204", "226", "249", "250", "289", "306", "343", "403", "416", "418", "438", "450",
			    "506", "514", "519", "365", "581", "587", "579", "604", "613", "647", "705", "709",
			    "778", "780", "807", "819", "867", "873", "902", "905",
			    "800", "866", "877", "888"
		      );
		
		    $validnpaCAN = false;
		    if(in_array($npa, $npalistCAN))
		    {
			    $validnpaCAN = true;
		    }
	
		    if(!$TFnpa && !$validnpaCAN)
		    {
			    $number_error = true;
		    }
	    }
	    return !$number_error;
    }
	
	function get_curl(){
	    return $this->curl_helper('http://www.canpages.ca/rl/index.jsp?fi=Search&lang=0&val=' . $this->thenumber);
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
die(print_r($result,1));
		        return $result;
		    }
	    }else{
	        return false;
	    }
    }
}

