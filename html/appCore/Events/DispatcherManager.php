<?php
namespace appCore\Events;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;



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

    public static function dispatch($eventName, Event $event) {
        return self::getInstance()->dispatcher->dispatch($event,$eventName);
    }

    public static function addListener($eventName, $listener, $priority = 0) {
        return self::getInstance()->dispatcher->addListener($eventName, $listener, $priority);
    }
}
