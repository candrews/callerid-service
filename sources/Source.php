<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

abstract class Source
{
    public $countries = null;
    public $run_param;
    
    function get_configuration()
    {
        return array();
    }
    
    abstract function lookup();
    
    function prepare(){
        if($this->countries == null){
            return true;
        }else{
            //make sure that the phone number's country is supported by this source
            return in_array($this->country, $this->countries);
        }
    }
    
    /**
     * given a block of messy, scraped html contains tags, escape sequences, etc, return a cleaned up string ready to be displayed to the user
     * 
     * if the parameter is empty, return null
     * removes multiple white spaces
     * replaces new line with spaces, 
     * convert html entities to unicode characters
     * strips html tags
     * trim leading/trailing white space
     */
    function clean_scraped_html($html){
        if(empty($html))
            return null;
        else
            $ret = trim(preg_replace('/\s+/',' ',html_entity_decode(strip_tags($html),ENT_QUOTES,'UTF-8')));
            if(empty($ret)){
                return null;
            }else{
                return $ret;
            }
    }
    
    /**
     * given a phone number in any format (with any punctuation), return the 10 digit phone number (or false if the number is not valid)
     */
    function clean_us_number($number){
        return clean_uscan_number($number, 'US');
    }
    
    /**
     * given a phone number in any format (with any punctuation), return the 10 digit phone number (or false if the number is not valid)
     */
    function clean_can_number($number){
        return clean_uscan_number($number, 'CAN');
    }
    
    /**
     * given a phone number in any format (with any punctuation), return the 10 digit phone number (or false if the number is not valid)
     */
    function clean_uscan_number($number, $country='USCAN'){
        $number = preg_replace('/[\D]/','',$number);
        switch(strlen($number)){
            case 10:
	            $pattern = '/(\d{3})(\d{3})(\d{4})/';
                break;
            case 11:
	            $pattern = '/1(\d{3})(\d{3})(\d{4})/';
                break;
            case 13:
	            $pattern = '/001(\d{3})(\d{3})(\d{4})/';
                break;
            case 14:
	            $pattern = '/0111(\d{3})(\d{3})(\d{4})/';
                break;
            default:
                return false;
        }
	    if(preg_match($pattern, $number, $matches)){
	        $npa = $matches[1];
	        $nxx = $matches[2];
	        $station = $matches[3];
	        switch($country){
	            case 'US':
	                if(in_array($npa, self::$npalistUS)){
	                    return $npa.$nxx.$station;
                    }else{
                        return false;
                    }
	                break;
	            case 'CAN':
	                if(in_array($npa, self::$npalistCAN)){
	                    return $npa.$nxx.$station;
                    }else{
                        return false;
                    }
	                break;
	            case 'USCAN':
	                if(in_array($npa, array_merge(self::$npalistUS,self::$npalistCAN))){
	                    return $npa.$nxx.$station;
                    }else{
                        return false;
                    }
	                break;
                default:
                    return false;
	        }
	    }else{
	        return false;
        }
    }
    
    /**
     * given a phone number in any format (with any punctuation), return the phone number, with leading 0 as dialed in Sweden (or false if the number is not valid)
     */
    function clean_se_number($number){
        $number = preg_replace('/[\D]/','',$number);
	    //check for the correct digits for Sweden in international format.
	    // international dialing prefix + country code + number
	    if (strlen($number) > 8)
	    {
		    if (substr($number,0,2) == '46')
		    {
			    $number = '0'.substr($number, 2);
		    }
		    else
		    {
			    if (substr($number,0,4) == '0046')
			    {
				    $number = '0'.substr($number, 4);
			    }
			    else
			    {
				    if (substr($number,0,5) == '01146')
				    {
					    $number = '0'.substr($number,5);
				    }			
				    else
				    {
					    return false;
				    }
			    }
		    }
	    }	
	    // number
        if(strlen($number) < 11 && substr($number,0,1) == '0')
	    {
		    return $number;
	    }
	    else
	    {
		    return false;
	    }
    }
    
    /**
     * given a phone number in any format (with any punctuation), return the phone number, with leading 0 as dialed in Australia (or false if the number is not valid)
     */
    function clean_au_number($number){
        $number = preg_replace('/[\D]/','',$number);
        switch(strlen($number)){
            case 10:
	            $pattern = '/(0\d{9})/';
	            $addtrunk = false;
                break;
            case 13:
	            $pattern = '/0061(\d{9})/';
	            $addtrunk = true;
                break;
            case 14:
	            $pattern = '/01161(\d{9})/';
	            $addtrunk = true;
                break;
            default:
                return false;
        }
	    if(preg_match($pattern, $number, $matches)){
	        return ($addtrunk?'0':'') . $matches[1];
	    }else{
	        return false;
        }
    }

    /**
     * given a phone number in any format (with any punctuation), return the phone number, with leading 0 as dialed in Switzerland (or false if the number is not valid)
     */
    function clean_ch_number($number){
        $number = preg_replace('/[\D]/','',$number);
        switch(strlen($number)){
            case 10:
	            $pattern = '/(0\d{9})/';
	            $addtrunk = false;
                break;
            case 13:
	            $pattern = '/0041(\d{9})/';
	            $addtrunk = true;
                break;
            case 14:
	            $pattern = '/01141(\d{9})/';
	            $addtrunk = true;
                break;
            default:
                return false;
        }
	    if(preg_match($pattern, $number, $matches)){
	        return ($addtrunk?'0':'') . $matches[1];
	    }else{
	        return false;
        }
    }
}

