<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-White_Pages.php
*/

class WhitePagesSource extends HTTPSource
{

    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.whitepages.com - These listings will return both residential and business listings. Some Canada data available.";
    
    public $countries = array('us', 'ca');
	
	function get_curl()
	{
		return $this->curl_helper('http://www.whitepages.com/search/ReversePhone?full_phone=' . $this->thenumber);
	}
	function parse_response()
	{
        if($this->response->code == 200){
	        $result = new Result();
	        
	        $body = $this->response->body;
	        
		    $notfound = strpos($body, "PHONE_USER_ERROR");
		    $notfound = ($notfound < 1) ? strpos($body, "PHONE_NO_RESULTS") : $notfound;
		    if($notfound)
		    {
			    return false;
		    }
		    
		    $patternFirst = "/FIRST.*?\"(.*?)\",/";
		    $patternLast = "/LAST.*?\"(.*?)\",/";
		    $patternAddress = "/ADDRESS_ESC.*?\"(.*?)\",/";
		    $patternCity = "/CITY.*?\"(.*?)\",/";
		    $patternState = "/STATE.*?\"(.*?)\",/";
		    $patternType = "/Type: *(.*?)<\/span>/";
		    $patternCompany = "/Company: <\/strong><span class=\"org\">(.*?)<\/span>/";
		    $patternName = "/<span class=\"name\">(.*?)<\/span>/";
		    
            preg_match($patternCompany, $body, $company);
            if(isset($company[1])){
                $result->company = $this->clean_scraped_html($company[1]);
            }
		
		    // Look at named results first
		    preg_match($patternName, $body, $namespans);
		    if(isset($namespans[1])){
		        $result->name = $this->clean_scraped_html($namespans[1]);
	        }else{
	            //now look for a first and last name
			    preg_match($patternFirst, $body, $first);
			    preg_match($patternLast, $body, $last);
			    if(isset($first[1]) && isset($last[1])){
				    $result->name = $this->clean_scraped_html($first[1].' '.$last[1]);
			    }else{
				    $start= strpos($body, '</strong> based in <strong>');
				
				    if($start > 1)
				    {
					    $body = substr($body,$start);
					    $start= strpos($body, '<strong>');
					    $body = substr($body,$start+8);
					    $end= strpos($body, '</strong>');
					    $name = substr($body,0,$end);
				        $result->name = $this->clean_scraped_html($name);
				    }
			    }
	        }
	        if(empty($result->name)){
	            $result->name = $result->company;
            }
            if(empty($result->name)){
                //couldn't find a name... have to return failure
                return false;
            }else{
		        preg_match($patternAddress, $body, $address);
		        if(isset($address[1])){
		            $result->address = $address[1];
	            }
		        preg_match($patternCity, $body, $city);
		        if(isset($city[1])){
		            if(empty($result->address)){
		                $result->address = $city[1];
	                }else{
		                $result->address .= ', ' . $city[1];
	                }
	            }
		        preg_match($patternState, $body, $state);
		        if(isset($state[1])){
		            if(empty($result->address)){
		                $result->address = $state[1];
	                }else{
		                $result->address .= ', ' . $state[1];
	                }
	            }
	            if(!empty($result->address)){
	                $result->address = $this->clean_scraped_html($result->address);
                }
		        return $result;
		    }
	    }else{
	        return false;
	    }
	}
}

