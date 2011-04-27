<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-Addresses.php
Original contribution by pbxinaflash.com forum's user Nixi.
*/

class HittaSource extends HTTPSource
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.hitta.se - This listing includes data from the Swedish Hitta.se directory.";

    public $countries = array('se');
    
    function prepare()
    {
        if(parent::prepare()){
            //hitta.se wants the number in local format, not in international format
            $this->thenumber = '0'.substr($this->thenumber,3);
            return true;
        }else{
            return false;
        }
    }

	function get_curl()
	{
	    return $this->curl_helper('http://wap.hitta.se/default.aspx?Who=' . urlencode($this->thenumber));
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
	    $patternName = '/<p><b>(.+)<\/b>/si';
	    preg_match($patternName, $body, $name);
	    if(isset($name[1])){
	        return $name[1];
        }else{
            return null;
        }
	}

	function get_address($body){
	    $patternAddress = '/href="wtai.*?title="Link".*?<br\/>(.*?)<a/si';
	    preg_match($patternAddress, $body, $address);
	    if(isset($address[1])){
	        return $address[1];
        }else{
            return null;
        }
	}
}
