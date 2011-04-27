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
}

