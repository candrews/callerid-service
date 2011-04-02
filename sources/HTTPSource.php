<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class HTTPResponse
{
    public $code;
    public $body;
}

abstract class HTTPSource extends Source
{
    function lookup(){
        $crl = $this->get_curl();
        
    	$ret = trim(curl_exec($crl));
	    if(curl_error($crl))
	    {
		    error_log(curl_error($crl));
		    return false;
	    }
        
        $response = new HTTPResponse();
        $response->code = curl_getinfo($crl, CURLINFO_HTTP_CODE);
        $response->body = curl_exec($crl);
        $this->response = $response;
        return $this->parse_response();
    }
    
    abstract function get_curl();
    
    abstract function parse_response();
    
    function curl_helper($url,$post_data=false,$referrer=false,$cookie_file=false,$useragent=false)
    {
    	$crl = curl_init();
	    if(!$useragent){
		    // Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)
		    $useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
	    }
	    if($referrer){
		    curl_setopt ($crl, CURLOPT_REFERER, $referrer);
	    }
	    curl_setopt($crl,CURLOPT_USERAGENT,$useragent);
	    curl_setopt($crl,CURLOPT_URL,$url);
	    curl_setopt($crl,CURLOPT_RETURNTRANSFER,true);
	    //curl_setopt($crl,CURLOPT_CONNECTTIMEOUT,$curl_timeout);
	    curl_setopt($crl,CURLOPT_FAILONERROR,true);
	    //curl_setopt($crl,CURLOPT_TIMEOUT,$curl_timeout);
	    if($cookie_file){
		    curl_setopt($crl, CURLOPT_COOKIEJAR, $cookie_file);
		    curl_setopt($crl, CURLOPT_COOKIEFILE, $cookie_file);
	    }
	    if($post_data){
		    curl_setopt($crl, CURLOPT_POST, 1); // set POST method
		    curl_setopt($crl, CURLOPT_POSTFIELDS, cisf_url_encode_array($post_data)); // add POST fields
	    }
	    return $crl;
    }
    
}

