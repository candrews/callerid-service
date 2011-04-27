<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-White_Pages.php
*/

class WhitePagesCanadaSource extends WhitePagesSource
{

    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "http://www.whitepages.ca - These listings will return both residential and business Canadian listings as well as (at least some) American business and residential listings.";
    
	function get_curl()
	{
		return $this->curl_helper('http://www.whitepages.ca/search/ReversePhone?full_phone=' . urlencode($this->thenumber));
	}
}

