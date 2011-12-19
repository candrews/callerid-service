<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-Google.php
*/

class PagineBiancheSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.paginebianche.it - These listings will return both residential and business Italian listings.";
    
    public $countries = array('it');
	
	function get_curl(){
	    return $this->curl_helper('http://www.paginebianche.it/execute.cgi?btt=1&qs=' . urlencode(substr($this->thenumber,3)));
	}
	
	function parse_response(){
        $result = new Result();
        
        $body = $this->response->body;
        
	    $notfound = strpos($body, "Nessun risultato trovato");
	    if($notfound)
	    {
		    return false;
	    }
	    
	    $patternAddress = '/<div id="addr_1".*?>(.+?)<\/span><\/div>/si';
	    $patternName = '/<div class=\"org fn\">(.+?)<\/div>/si';
	    $patternCompany = '/<div class=\"org fn\">(.+?)<\/div>/si';
	    
        preg_match($patternName, $body, $name);
        if(isset($name[1])){
            $patternFirstLast = '/<strong>(.+?)<\/strong>(.+)/sim';
            preg_match($patternFirstLast, $name[1], $firstLast);
            if(isset($firstLast[1]) && isset($firstLast[2])){
                $result->name = $this->clean_scraped_html($firstLast[2] . ' ' . $firstLast[1]);
            }else{
                $result->name = $this->clean_scraped_html($name[1]);
            }
        }else{
            preg_match($patternCompany, $body, $company);
            if(isset($company[1])){
                $result->company = $this->clean_scraped_html($company[1]);
            }
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
    }
}

