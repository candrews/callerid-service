<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class CitizensInfoSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.citizensinfo.com search";
    
    public $countries = array('us', 'ca');
    
    function get_curl()
    {
        return $this->curl_helper('http://citizensinfo.com/Result.aspx', array('p' => substr($this->thenumber,2)));
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
            $patternName = '/<span id=\"lblName\">(.*?)<\/span>/sim';
            preg_match($patternName, $body, $name);
            if(isset($name[1]) && $name[1]!='Unlisted'){
            	//names are returned in "last first" format - translate to "first last" format
            	$space = strpos($name[1], ' ');
            	if($space!==false){
            		return substr($name[1],$space) . ' ' . substr($name[1],0,$space);
            	}
                return $name[1];
        }else{
            return null;
        }
        }

        function get_address($body){
            $patternName = '/<span id=\"lblAddress\">(.*?)<\/span>.+?<span id=\"lblCity\">(.*?)<\/span>.+?<span id=\"lblState\">([A-Z]{2})<\/span>.+?<span id=\"lblZip\">(\d*)<\/span>/sim';
            preg_match($patternName, $body, $name);
            if(isset($name[1])){
                return $name[1] . ', ' . $name[2] . ', ' . $name[3] . ' ' . $name[4];
        }else{
            return null;
        }
        }
}

