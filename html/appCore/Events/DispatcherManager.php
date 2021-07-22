<?php
namespace appCore\Events;

use Symfony\Component\EventDispatcher\EventDispatcher;

/* ======================================================================== \
  |   FORMA - The E-Learning Suite                                            |
  |                                                                           |
  |   Copyright (c) 2013 (Forma)                                              |
  |   http://www.formalms.org                                                 |
  |   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
  |                                                                           |
  |   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
  |   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
  \ ======================================================================== */

class DispatcherManager {

    private static $instance = null;
    private $dispatcher = null;

    /**
     * Singleton class, the constructor is private
     */
    private function __construct() {
        $this->dispatcher = new EventDispatcher();
    }

    /**
     * Get the DispatcherManager instance
     * 
     * @param string $mvc_name
     * @return DispatcherManager
     * @throws Exception
     */
    public static function getInstance() {
        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    public static function dispatch($eventName, \Symfony\Component\EventDispatcher\Event $event = null) {
        return self::getInstance()->dispatcher->dispatch($eventName, $event);
    }

    public static function addListener($eventName, $listener, $priority = 0) {
        return self::getInstance()->dispatcher->addListener($eventName, $listener, $priority);
    }
}
