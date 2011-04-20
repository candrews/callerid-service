<?php
define('CALLERID',true);
$thenumber_orig = $_GET['num'];

function endswith($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, -$testlen) === 0;
}

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

    switch($format){
        case 'json':
            header('Content-type: application/javascript');
            break;
        case 'basic':
            header('Content-type: text/plain');
            break;
    }

    //remove all punctuation
    $thenumber = preg_replace('/[^0-9]+/', '', $thenumber_orig);
    //if the number has an initial 1, remove it
    if(substr($thenumber,0,1)=='1') $thenumber = substr($thenumber,1);
/*    if($thenumber_orig != $thenumber || $format!=$format_orig){
        // redirect to the canonical url
        header('HTTP/1.1 302 Moved Permanently');
        header('Location: callerid.php?format=' . urlencode($format) . '&num=' . urlencode($thenumber));
        exit;
    }
*/

    function __autoload($class_name) {
        if(endswith($class_name,'Source')){
            require_once("sources/$class_name.php");
        }else{
            require_once("lib/$class_name.php");
        }
    }
    
    $config = array();
    //set config defaults
    $config['include_source_in_result']=true;
    
    require_once('config.php');
    
    $winning_result = false;
    $non_http_winning_result = false;
    
    $http_sources = array();
    
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
        if($source->prepare()){
            if($source instanceof HTTP_Source){
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
        //there are HTTP_Sources to be considered
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
    
    if($winning_result === false){
        header("HTTP/1.0 404 Not Found");
        if($format == 'basic') {
            echo 'Unknown number';
        } else if($format == 'json') {
            $result = new stdClass();
            $result->error = 'Unknown number';
            echo json_encode($result);
        }
    }else{
        $winning_result->phoneNumber = $thenumber;
        if($format == 'basic') {
            echo $winning_result->name;
        } else if($format == 'json') {
             echo json_encode($winning_result);
        }
    }
}
