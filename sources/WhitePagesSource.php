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

    function is_applicable()
    {
	    $number_error = false;
	    $validnpaCAN = false;
	    $TFnpa = false;
	    $validnpaUS = false;
	
	    //check for the correct 11 digits in US/CAN phone numbers in international format.
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
		    $this->thenumber = (substr($this->thenumber,0,1) == 1) ? substr($this->thenumber,1) : $this->thenumber;
		    $npa = substr($this->thenumber,0,3);
		
		    // Check for Toll-Free numbers
		    if($npa=='800'||$npa=='866'||$npa=='877'||$npa=='888')
		    {
			    $TFnpa = true;
		    }
		
		    // Check for valid US NPA
		    $npalistUS = array(
			    "201", "202", "203", "205", "206", "207", "208", "209", "210", "212",
			    "213", "214", "215", "216", "217", "218", "219", "224", "225", "228",
			    "229", "231", "234", "239", "240", "242", "246", "248", "251", "252",
			    "253", "254", "256", "260", "262", "264", "267", "268", "269", "270",
			    "276", "281", "284", "301", "302", "303", "304", "305", "307", "308",
			    "309", "310", "312", "313", "314", "315", "316", "317", "318", "319",
			    "320", "321", "323", "325", "330", "331", "334", "336", "337", "339",
			    "340", "345", "347", "351", "352", "360", "361", "386", "401", "402",
			    "404", "405", "406", "407", "408", "409", "410", "412", "413", "414",
			    "415", "417", "419", "423", "424", "425", "430", "432", "434", "435",
			    "440", "441", "443", "456", "469", "473", "478", "479", "480", "484",
			    "500", "501", "502", "503", "504", "505", "507", "508", "509", "510",
			    "512", "513", "515", "516", "517", "518", "520", "530", "540", "541",
			    "551", "559", "561", "562", "563", "567", "570", "571", "573", "574",
			    "575", "580", "585", "586", "600", "601", "602", "603", "605", "606",
			    "607", "608", "609", "610", "612", "614", "615", "616", "617", "618",
			    "619", "620", "623", "626", "630", "631", "636", "641", "646", "649",
			    "650", "651", "660", "661", "662", "664", "670", "671", "678", "682",
			    "684", "700", "701", "702", "703", "704", "706", "707", "708", "710",
			    "712", "713", "714", "715", "716", "717", "718", "719", "720", "724",
			    "727", "731", "732", "734", "740", "754", "757", "758", "760", "762",
			    "763", "765", "767", "769", "770", "772", "773", "774", "775", "779",
			    "781", "784", "785", "786", "787", "801", "802", "803", "804", "805",
			    "806", "808", "809", "810", "812", "813", "814", "815", "816", "817",
			    "818", "828", "829", "830", "831", "832", "843", "845", "847", "848",
			    "850", "856", "857", "858", "859", "860", "862", "863", "864", "865",
			    "868", "869", "870", "876", "878", "900", "901", "903", "904", "906",
			    "907", "908", "909", "910", "912", "913", "914", "915", "916", "917",
			    "918", "919", "920", "925", "928", "931", "936", "937", "939", "940",
			    "941", "947", "949", "951", "952", "954", "956", "970", "971", "972",
			    "973", "978", "979", "980", "985", "989",
			    "800", "866", "877", "888"
		    );
		
		    if(in_array($npa, $npalistUS))
		    {
			    $validnpaUS = true;
		    }
		
		    // Check for valid CAN NPA
		    $npalistCAN = array(
			    "204", "226", "250", "289", "306", "403", "416", "418", "438", "450",
			    "506", "514", "519", "581", "587", "604", "613", "647", "705", "709",
			    "778", "780", "807", "819", "867", "902", "905",
			    "800", "866", "877", "888"
		      );
		
		    if(in_array($npa, $npalistCAN))
		    {
			    $validnpaCAN = true;
		    }
	    }
	
	    if($TFnpa || (!$validnpaUS && !$validnpaCAN))
	    {
		    $number_error = true;
	    }
	    return !$number_error;
    }
	
	function get_curl()
	{
		return $this->curl_helper('http://www.whitepages.com/search/ReversePhone?full_phone=' . $this->thenumber);
	}
	
	function parse_response()
	{
        if($this->response->code == 200){
	        $body = $this->response->body;
	        
		    $notfound = strpos($body, "PHONE_USER_ERROR");
		    $notfound = ($notfound < 1) ? strpos($body, "PHONE_NO_RESULTS") : $notfound;
		    $patternFirst = "/FIRST.*?\"(.*?)\",/";
		    $patternLast = "/LAST.*?\"(.*?)\",/";
		    $patternCity = "/CITY.*?\"(.*?)\",/";
		    $patternState = "/STATE.*?\"(.*?)\",/";
		    $patternType = "/Type: *(.*?)<\/span>/";
		    $patternCompany = "/Company: <\/strong><span class=\"org\">(.*?)<\/span>/";
		    $patternName = "/<span class=\"name\">(.*?)<\/span>/";
		
		    // Look at named results first
		    preg_match($patternName, $body, $namespans);
		    $name = (isset($namespans[1])) ? trim(strip_tags($namespans[1])) : '';
		
		    if($name == '')
		    {
			    //look for company name
			    preg_match($patternCompany, $body, $company);
			    $name = (isset($company[1])) ? trim(strip_tags($company[1])) : '';
			    if($name == '')
			    {
				    //now look for a first and last name
				    preg_match($patternFirst, $body, $first);
				    preg_match($patternLast, $body, $last);
				    if(isset($first[1]) && isset($last[1])){
					    $name = $first[1]." ".$last[1];
				    }
				    if($name == " ")
				    {
					    $start= strpos($body, "</strong> based in <strong>");
					
					    if($start > 1)
					    {
						    $body = substr($body,$start);
						    $start= strpos($body, "<strong>");
						    $body = substr($body,$start+8);
						    $end= strpos($body, "</strong>");
						    $name = substr($body,0,$end);
						    $name = str_replace( chr(13), "", $name ); 
						    $name = str_replace( chr(10), "", $name ); 
						    $name = trim(str_replace( "&nbsp;", "", $name ));
					    }
				    }
			    }
		    }
		
		    if($notfound)
		    {
			    return false;
		    }
		
		    if(strlen($name) > 1)
		    {
		        $result = new Result();
		        $result->name = strip_tags($name);
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

