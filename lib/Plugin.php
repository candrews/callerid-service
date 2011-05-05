<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

//original code from lib/plugin.php in StatusNet.
class Plugin
{
    function __construct()
    {
        Event::addHandler('InitializePlugin', array($this, 'initialize'));
        Event::addHandler('CleanupPlugin', array($this, 'cleanup'));

        foreach (get_class_methods($this) as $method) {
            if (mb_substr($method, 0, 2) == 'on') {
                Event::addHandler(mb_substr($method, 2), array($this, $method));
            }
        }
    }

    function initialize()
    {
        return true;
    }

    function cleanup()
    {
        return true;
    }

    function name()
    {
        $cls = get_class($this);
        return mb_substr($cls, 0, -6);
    }

}

