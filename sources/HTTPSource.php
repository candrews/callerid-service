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
        
    	$ret = curl_exec($crl);
	    if(curl_errno($crl)!=0)
	    {
		    curl_close($crl);
		    throw new TemporaryFailureException("Curl error: " . curl_error($ch[$i]) . " effective url: " . curl_getinfo($crl, CURLINFO_EFFECTIVE_URL));
	    }
        
        $response = new HTTPResponse();
        $response->code = curl_getinfo($crl, CURLINFO_HTTP_CODE);
        $response->body = $ret;
        $this->response = $response;
        $parsed_response = $this->check_and_parse_response();
	    curl_close($crl);
        return $parsed_response;
    }
    
    abstract function get_curl();
    
    abstract function parse_response();
    
    function check_and_parse_response(){
        if($this->response->code == 404){
            return false;
        }elseif($this->response->code == 200){
            return $this->parse_response();
        }else{
            throw new TemporaryFailureException("HTTP error: " . $this->response->code);
        }
    }
    
    function curl_helper($url,$post_data=false,$referrer=false,$cookie_file=false,$useragent=false)
    {
    	$crl = curl_init();
    	    if(isset($this->proxy)){
    	            curl_setopt($crl, CURLOPT_HTTPPROXYTUNNEL, 1);
    	            curl_setopt($crl,CURLOPT_PROXY, $this->proxy);
    	    }
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
	    curl_setopt($crl,CURLOPT_FOLLOWLOCATION,true);
	    //curl_setopt($crl,CURLOPT_CONNECTTIMEOUT,$curl_timeout);
	    if(isset($this->timeout)){
	        curl_setopt($crl,CURLOPT_TIMEOUT,$this->timeout);
	    }
	    if($cookie_file){
		    curl_setopt($crl, CURLOPT_COOKIEJAR, $cookie_file);
		    curl_setopt($crl, CURLOPT_COOKIEFILE, $cookie_file);
	    }
	    if($post_data){
		    curl_setopt($crl, CURLOPT_POST, 1); // set POST method
		    curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data); // add POST fields
	    }
	    return $crl;
    }
    
}

