<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class MySQLiDBSource extends Source
{
    //The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
    public $source_desc = "Query a local or remote MySQL database.";
    
    function get_configuration(){
        //configuration / display parameters
        $source_param = array();
        $source_param['DB_Host']['desc'] = 'Host address of the database. (localhost if the database is on the same server as FreePBX)';
        $source_param['DB_Host']['type'] = 'text';
        $source_param['DB_Name']['desc'] = 'schema name of the database';
        $source_param['DB_Name']['type'] = 'text';
        $source_param['DB_User']['desc'] = 'Username used to connect to the database';
        $source_param['DB_User']['type'] = 'text';
        $source_param['DB_Password']['desc'] = 'Password used to connect to the database';
        $source_param['DB_Password']['type'] = 'password';
        $source_param['SQL_Query']['desc'] = 'SQL Query used to retrieve the Dialer Name. select result from table where telfield=?. ? will be replaced by the called number';
        $source_param['SQL_Query']['type'] = 'text';
        return $source_desc;
    }
    
    function lookup(){
	    $link = mysqli_connect($this->DB_Host, $this->DB_User, $this->DB_Password, $this->DB_Name);
	    if(mysqli_connect_error()){
            error_log('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
	    }else{
	        if($stmt = mysqli_prepare($link, $this->SQL_Query)){
	            mysqli_stmt_bind_param($stmt, 's', $this->thenumber);
	            mysqli_stmt_execute($stmt);
	            mysqli_stmt_bind_result($stmt, $name);
	            if(! mysqli_stmt_fetch($stmt)){
	                $name = null;
	            }
	            mysqli_stmt_close($stmt);
	            mysqli_close($link);
	            if($name == null){
	                return false;
	            }else{
	                $result = new Result();
	                $result->name = $name;
	                return $result;
	            }
            }else{
                error_log('Failed to prepare statement: ' . $this->SQL_Query);
            }
	    }
    }
}

