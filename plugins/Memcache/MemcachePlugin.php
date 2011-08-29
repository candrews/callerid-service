<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class MemcachePlugin extends Plugin
{
    private $_conn  = null;
    public $servers = array('127.0.0.1;11211');

    public $compressThreshold = 20480;
    public $compressMinSaving = 0.2;

    public $persistent = null;

    public $defaultExpiry = 86400; // 24h
    
    function onPerformLookup($thenumber_orig, $thenumber, $country, $agent_country, &$winning_result){
        $this->_ensureConn();
        $value = $this->_conn->get($thenumber);
        if($value===false){
            return true;
        }else{
            $winning_result = $value;
            return false;
        }
    }

    function onAfterLookup($thenumber_orig, $thenumber, $country, $agent_country, &$winning_result){
        $this->_ensureConn();
        $this->_conn->set($thenumber, $winning_result);
        return true;
    }

    /**
     * Ensure that a connection exists
     *
     * Checks the instance $_conn variable and connects
     * if it is empty.
     *
     * @return void
     */
    private function _ensureConn()
    {
        if (empty($this->_conn)) {
            $this->_conn = new Memcache();

            if (is_array($this->servers)) {
                $servers = $this->servers;
            } else {
                $servers = array($this->servers);
            }
            foreach ($servers as $server) {
                if (strpos($server, ';') !== false) {
                    list($host, $port) = explode(';', $server);
                } else {
                    $host = $server;
                    $port = 11211;
                }

                $this->_conn->addServer($host, $port, $this->persistent);
            }

            // Compress items stored in the cache if they're over threshold in size
            // (default 2KiB) and the compression would save more than min savings
            // ratio (default 0.2).

            // Allows the cache to store objects larger than 1MB (if they
            // compress to less than 1MB), and improves cache memory efficiency.

            $this->_conn->setCompressThreshold($this->compressThreshold,
                                               $this->compressMinSaving);
        }
    }
}
