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
        if($this->response->code == 200){
	        $result = new Result();
	        
	        $body = $this->response->body;
	        
		    $notfound = strpos($body, "Nessun risultato trovato");
		    if($notfound)
		    {
			    return false;
		    }
		    
		    $patternPostalCode = '/<span class=\"postal-code\">(.+?)<\/span>/si';
		    $patternLocality = '/<span class=\"locality\">(.+?)<\/span>/si';
		    $patternRegion = '/<span class=\"region\">\((.+?)\)<\/span>/si';
		    $patternStreet = '/<span class=\"street-address\">.*?, (.+?)<\/span>/si';
		    $patternStreetNumber = '/<span class=\"street-address\">(.*?), .+?<\/span>/si';
		    
		    $patternCompany = '/<h3 class=\"org\">(.+?)<\/h3>/si';
		    $patternName = '/<h3 class=\"org\">(.+?)<\/h3>/si';
		    
            preg_match($patternCompany, $body, $company);
            if(isset($company[1])){
                $result->company = $this->clean_scraped_html($company[1]);
            }
		    
            preg_match($patternName, $body, $name);
            if(isset($name[1])){
                $result->name = $this->clean_scraped_html($name[1]);
            }
		    
            preg_match($patternPostalCode, $body, $postalCode);
            preg_match($patternLocality, $body, $locality);
            preg_match($patternRegion, $body, $region);
            preg_match($patternStreet, $body, $street);
            preg_match($patternStreetNumber, $body, $streetNumber);
            if(isset($postalCode[1]) && isset($locality[1]) && isset($region[1]) && isset($street[1]) && isset($streetNumber[1])){
                $result->address = $this->clean_scraped_html($street[1] . ' ' . $streetNumber[1] . ', ' . $postalCode[1] . ' ' . $locality[1] . ' (' . $region[1] . ')');
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

