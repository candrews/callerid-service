<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

//original code from lib/event.php in StatusNet.
class Event {

    /* Global array of hooks, mapping eventname => array of callables */

    protected static $_handlers = array();

    /**
     * Add an event handler
     *
     * To run some code at a particular point in processing.
     *
     * The arguments to the handler vary by the event. Handlers can return
     * two possible values: false means that the event has been replaced by
     * the handler completely, and no default processing should be done.
     * Non-false means successful handling, and that the default processing
     * should succeed. (Note that this only makes sense for some events.)
     *
     * Handlers can also abort processing by throwing an exception; these will
     * be caught by the closest code and displayed as errors.
     *
     * @param string   $name    Name of the event
     * @param callable $handler Code to run
     *
     * @return void
     */

    public static function addHandler($name, $handler) {
        if (array_key_exists($name, Event::$_handlers)) {
            Event::$_handlers[$name][] = $handler;
        } else {
            Event::$_handlers[$name] = array($handler);
        }
    }

    /**
     * Handle an event
     *
     * Events are any point in the code that we want to expose for admins
     * or third-party developers to use.
     *
     * We pass in an array of arguments (including references, for stuff
     * that can be changed), and each assigned handler gets run with those
     * arguments. Exceptions can be thrown to indicate an error.
     *
     * @param string $name Name of the event that's happening
     * @param array  $args Arguments for handlers
     *
     * @return boolean flag saying whether to continue processing, based
     *                 on results of handlers.
     */

    public static function handle($name, $args=array()) {
        $result = null;
        if (array_key_exists($name, Event::$_handlers)) {
            foreach (Event::$_handlers[$name] as $handler) {
                $result = call_user_func_array($handler, $args);
                if ($result === false) {
                    break;
                }
            }
        }
        return ($result !== false);
    }

    /**
     * Disables any and all handlers that have been set up so far;
     * use only if you know it's safe to reinitialize all plugins.
     */
    public static function clearHandlers() {
        Event::$_handlers = array();
    }
}
