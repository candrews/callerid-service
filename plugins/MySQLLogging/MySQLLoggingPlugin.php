<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class MySQLLoggingPlugin extends Plugin
{
    public function __construct(){
        parent::__construct();
        $this->query_start_time = microtime(true);
    }

    public $SQL_Query = 'insert into log (agent_country, thenumber_orig, thenumber, country, winning_result_name, query_time, cacheable) values (?, ?, ?, ?, ?, ?, ?)';

    function onAfterLookup($thenumber_orig, $thenumber, $country, $agent_country, &$winning_result, &$cacheable){
        $link = mysqli_connect($this->DB_Host, $this->DB_User, $this->DB_Password, $this->DB_Name);
        if(mysqli_connect_error()){
            error_log('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
        }else{
            if($stmt = mysqli_prepare($link, $this->SQL_Query)){
                $query_time = (microtime(true) - $this->query_start_time) * 1000;
                if($winning_result === false){
                    $name = null;
                }else{
                    $name = $winning_result->name;
                }
                $ret = mysqli_stmt_bind_param($stmt, 'sssssii', $agent_country, $thenumber_orig, $thenumber, $country, $name, $query_time, $cacheable);
                assert($ret);
                $ret = mysqli_stmt_execute($stmt);
                assert($ret);
                assert(mysqli_stmt_affected_rows($stmt) > 0);
                $ret = mysqli_stmt_close($stmt);
                assert($ret);
                $ret = mysqli_close($link);
                assert($ret);
            }else{
                error_log('Failed to prepare statement: ' . $this->SQL_Query);
            }
        }
        return true;
    }

}
