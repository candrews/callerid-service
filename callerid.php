<?php
define('CALLERID',true);
define('INSTALLDIR', dirname(__FILE__));

$latestAndroidVersionCode = 4;

/**
 * Configure and instantiate a plugin into the current configuration.
 * Class definitions will be loaded from standard paths if necessary.
 * Note that initialization events won't be fired until later.
 *
 * @param string $name class name & plugin file/subdir name
 * @param array $attrs key/value pairs of public attributes to set on plugin instance
 *
 * @throws ServerException if plugin can't be found
 */
function addPlugin($name, $attrs = null)
{
    $name = ucfirst($name);
    $pluginclass = "{$name}Plugin";

    if (!class_exists($pluginclass)) {

        $files = array("plugins/{$pluginclass}.php",
                       "plugins/{$name}/{$pluginclass}.php");

        foreach ($files as $file) {
            $fullpath = INSTALLDIR.'/'.$file;
            if (@file_exists($fullpath)) {
                include_once($fullpath);
                break;
            }
        }
        if (!class_exists($pluginclass)) {
            die("Plugin $name not found.");
        }
    }

    $inst = new $pluginclass();
    if (!empty($attrs)) {
        foreach ($attrs as $aname => $avalue) {
            $inst->$aname = $avalue;
        }
    }

    return true;
}

function endswith($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, -$testlen) === 0;
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}


function __autoload($class_name) {
    if(endswith($class_name,'NumberCleaner') && $class_name!='NumberCleaner' && file_exists(INSTALLDIR . '/number_cleaners/' . strtolower(substr($class_name,0,-13)) .'.php')){
        require_once(INSTALLDIR . '/number_cleaners/' . strtolower(substr($class_name,0,-13)) .'.php');
    }else if(endswith($class_name,'Source') && file_exists(INSTALLDIR . "/sources/$class_name.php")){
        require_once(INSTALLDIR . "/sources/$class_name.php");
    }else if(file_exists(INSTALLDIR . "/lib/$class_name.php")){
        require_once(INSTALLDIR . "/lib/$class_name.php");
    }else{
        Event::handle('Autoload', array(&$class_name));
    }
}
$thenumber_orig = isset($_GET['num'])?$_GET['num']:null;

//The lookup agent can (and should, for best results) specifiy which country it is in.
$agent_country = isset($_GET['country'])?$_GET['country']:null;
if(!empty($agent_country)) $agent_country = strtolower($agent_country);

if(empty($thenumber_orig)){
    header("HTTP/1.0 500 Internal server error");
    echo '"num" parameter is required';
}else{
    $format_orig = $_GET['format'];
    if(!empty($format_orig) && $format_orig != 'json' && $format_orig != 'basic'){
        header("HTTP/1.0 500 Internal server error");
        echo '"format" parameter must be "json" or "basic" (default to "basic" is not specified)';
        exit;
    }
    if(empty($format_orig)){
        $format = 'basic';
    }else{
        $format = $format_orig;
    }

    //cache for 1 day
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+24*60*60) . ' GMT');

    switch($format){
        case 'json':
            header('Content-type: application/javascript');
            break;
        case 'basic':
            header('Content-type: text/plain');
            break;
    }

    //if the first character is space, make it a plus
    //some clients will not URL escape the number,
    //since space becomes + when url escaped, this substitution will fix that error
    if(substr($thenumber_orig,0,1) == ' '){
        $thenumber = '+' . substr($thenumber_orig,1);
    }else{
        $thenumber = $thenumber_orig;
    }
    //remove all punctuation, except leading +
    $hasLeadingPlus = substr($thenumber,0,1) == '+';
    $thenumber = preg_replace('/\D/', '', $thenumber_orig);
    if($hasLeadingPlus){
        $thenumber='+'.$thenumber;
    }else{
        if(substr($thenumber,0,2)=='00'){
            //the ITU recommended prefix is unambiguous so safe to convert to a +
            //other prefixes are not safe to convert to a +, as they could be ambiguous.
            $thenumber = '+'.substr($thenumber,2);
        }
    }

    $config = array();
    //set config defaults
    $config['include_source_in_result']=true;
    $config['number_cleaners'] = array(
        'us', 'au', 'at', 'be', 'ca', 'ch', 'de', 'dk', 'fi', 'fr', 'in', 'it', 'nl', 'pt', 'se', 'uk', 'lu'
    );

    require_once('config.php');
    
    //check if the agent_country is one of the countries we wish to support
    if(!empty($agent_country) && in_array($agent_country,$config['number_cleaners'])){
        //add the agent_country to the beginning of the list so it is checked first
        array_unshift($config['number_cleaners'],$agent_country);
    }
    
    $winning_result = false;
    $non_http_winning_result = false;
    
    $http_sources = array();
    
    $country = false;
    if(Event::handle('CleanNumber', array($thenumber_orig, $thenumber, $agent_country, &$number_cleaner_result))){
        foreach($config['number_cleaners'] as $number_cleaner_name){
            $class_name = strtoupper(substr($number_cleaner_name,0,1)) . substr($number_cleaner_name,1) . 'NumberCleaner';
            $number_cleaner = new $class_name;
            if($agent_country == $number_cleaner_name){
                if(startsWith($thenumber,$number_cleaner->international_calling_prefix)){
                    //The number starts with the agent country's international dialing prefix,
                    //so this number must be in the international dialing format for that country.
                    //Convert the number to the international "+country" format so our souces can use it
                    $thenumber = '+' . substr($thenumber,strlen($number_cleaner->international_calling_prefix));
                }
            }
            $number_cleaner_result = $number_cleaner->clean_number($thenumber);
            if($number_cleaner_result !== false){
                $thenumber = $number_cleaner_result['number'];
                $country = $number_cleaner_result['country'];
            }
        }
    }
    if(Event::handle('PerformLookup', array($thenumber_orig, $thenumber, $country, $agent_country, &$winning_result))){
        if($country !== false){
            foreach($config['sources'] as $source_index => $source_configuration)
            {
                if(is_string($source_configuration)){
                    $source_configuration = array(
                        'name'=>$source_configuration,
                        'params'=>array()
                    );
                }
                $source_name = $source_configuration['name'];
                $source_class = $source_name . 'Source';
                $source = new $source_class();
                foreach($source_configuration['params'] as $key=>$value)
                {
                    $source->$key = $value;
                }
                $source->thenumber = $thenumber;
                $source->country = $country;
                if($source->prepare()){
                    if($source instanceof HTTPSource){
                        $http_sources[$source_index]=$source;
                    }else{
                        $result = $source->lookup();
                        if($result !== false){
                            $non_http_winning_result = $result;
                            $result->source = get_class($source);
                            break;
                        }
                    }
                }
            }
            if($http_sources){
                //there are HTTPSource's to be considered
                $mh = curl_multi_init();
                $ch = array();
                foreach($http_sources as $i=>$source){
                    $ch[$i]=$source->get_curl();
                    curl_multi_add_handle($mh, $ch[$i]);
                }
                // Start performing the requests
                $winning_index = null;
                do{
                    $execReturnValue = curl_multi_exec($mh, $runningHandles);
                }while($execReturnValue == CURLM_CALL_MULTI_PERFORM);
                // Loop and continue processing the request
                while ($runningHandles && $execReturnValue == CURLM_OK) {
                    // Wait forever for network
                    $numberReady = curl_multi_select($mh);
                    if ($numberReady != -1) {
                        // Pull in any new data, or at least handle timeouts
                        do {
                            $execReturnValue = curl_multi_exec($mh, $runningHandles);
                        } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
                    }
                    while($info = curl_multi_info_read($mh)){
                        $found_handle = false;
                        foreach($http_sources as $i=>$source){
                            if($ch[$i] === $info['handle']){
                                //found the source for current handle
                                if($winning_index == null || $winning_index>$i){
                                    //this result may become the winner, as it's a better priority than the current winner
                                    // Check for errors
                                    $curlError = curl_error($ch[$i]);
                                    if($curlError == "") {
                                        $response = new HTTPResponse();
                                        $response->code = curl_getinfo($ch[$i], CURLINFO_HTTP_CODE);
                                        $response->body = curl_multi_getcontent($ch[$i]);
                                        $source->response = $response;
                                        $result = $source->parse_response();
                                        if($result !== false){
                                            $result->source = get_class($source);
                                            $winning_result = $result;
                                            $winning_index = $i;
                                        }
                                    } else {
                                        error_log("Curl error on handle $i: $curlError");
                                    }
                                }
                                // Remove and close the handle
                                curl_multi_remove_handle($mh, $ch[$i]);
                                curl_close($ch[$i]);
                                $ch[$i] = null;
                                $found_handle = true;
                                break;
                            }
                        }
                        if($found_handle == false) error_log("handle not found! this shouldn't happen");
                        
                        //make sure there are still active curl handles for better priority requests
                        if($winning_index!==null){
                            $keep_going = false;
                            foreach($ch as $i=>$handle){
                                if($i<$winning_index && $handle!==null){
                                    $keep_going = true;
                                    break;
                                }
                            }
                            if(!$keep_going){
                                foreach($ch as $i=>$handle){
                                    if($handle!==null){
                                        // Remove and close the handle
                                        curl_multi_remove_handle($mh, $ch[$i]);
                                        curl_close($ch[$i]);
                                        $ch[$i] = null;
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
                
                // Check for any errors
                if ($execReturnValue != CURLM_OK) {
                  trigger_error("Curl multi read error $execReturnValue\n", E_USER_WARNING);
                }
                // Clean up the curl_multi handle
                curl_multi_close($mh);
            }
            
            if($winning_result === false){
                //HTTP_Sources didn't return any results, so return the non-HTTP result as the result
                $winning_result = $non_http_winning_result;
            }
        }
    }
    Event::handle('AfterLookup', array($thenumber_orig, $thenumber, $country, $agent_country, &$winning_result));

    if($winning_result === false){
        header("HTTP/1.0 404 Not Found");
        if($format == 'basic') {
            echo 'Unknown number';
        } else if($format == 'json') {
            $result = new stdClass();
            if(isset($latestAndroidVersionCode)) $result->latestAndroidVersionCode = $latestAndroidVersionCode;
            $result->error = 'Unknown number';
            echo json_encode($result);
        }
    }else{
        $winning_result->phoneNumber = $thenumber;
        if($format == 'basic') {
            echo $winning_result->name;
        } else if($format == 'json') {
            if(isset($latestAndroidVersionCode)) $winning_result->latestAndroidVersionCode = $latestAndroidVersionCode;
            echo json_encode($winning_result);
        }
    }
}

Event::handle('CleanupPlugin');
