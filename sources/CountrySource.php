<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/*
Parser courtesy the CallerID Superfecta project, from source-Telco_Data.php
*/

class CountrySource extends Source
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "Returns just the country the call is from.<br>Because the data provided is less specific than other sources, this data source is usualy configured near the bottom of the list of active data sources.";

    function prepare(){
        return $this->country!==false;
    }
    
    function get_country_name($country){
	    switch($country){
	        case 'at':
	            $long_country = 'Austria';
	            break;
	        case 'au':
	            $long_country = 'Australia';
	            break;
	        case 'be':
	            $long_country = 'Belgium';
	            break;
	        case 'ca':
	            $long_country = 'Canada';
	            break;
	        case 'ch':
	            $long_country = 'Switzerland';
	            break;
	        case 'de':
	            $long_country = 'Germany';
	            break;
	        case 'dk':
	            $long_country = 'Denmark';
	            break;
	        case 'fi':
	            $long_country = 'Finland';
	            break;
	        case 'fr':
	            $long_country = 'France';
	            break;
	        case 'it':
	            $long_country = 'Italy';
	            break;
	        case 'lu':
	            $long_country = 'Luxembourg';
	            break;
	        case 'pt':
	            $long_country = 'Portugal';
	            break;
	        case 'se':
	            $long_country = 'Sweden';
	            break;
	        case 'uk':
	            $long_country = 'United Kingdom';
	            break;
	        case 'us':
	            $long_country = 'United States';
	            break;
            default:
                $long_country = $country;
	    }
	    return $long_country;
    }

    function lookup(){
        $result = new Result();
	    $result->name = 'Unknown (' . $this->get_country_name($this->country) . ')';
	    return $result;
    }
}

