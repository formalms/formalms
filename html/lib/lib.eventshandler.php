<?php

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

use Symfony\Component\EventDispatcher\EventDispatcher;

defined('IN_FORMA') or die('Direct access is forbidden.');

/**
 * Events handler class.
 * 
 * @internal used by Events class.
 */
final class EventsHandler
{
    const PRIORITY_CORE = 0;
    const PRIORITY_DEFAULT = 100;

    /**
     * Events dispatcher.
     *
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * Initialize events handler.
     */
    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }

    /**
     * Trigger an event.
     *
     * @param string $eventName
     * @param array $arguments
     * @return array
     */
    public function trigger($eventName, $arguments = [])
    {
        $event = $this->dispatcher->dispatch(new FormaEvent(null, $arguments),$eventName);
        return $event->getArguments();
    }

    /**
     * Trigger an event and send a deprecated error if any listener is attached.
     *
     * @param string $eventName
     * @param array $arguments
     * @return array
     */
    public function triggerDeprecated($eventName, $arguments = [])
    {
        if ($this->dispatcher->hasListeners($eventName)) {
            trigger_error("Event {$eventName} is deprecated and will be removed in a future release.", E_USER_DEPRECATED);
        }
        return $this->trigger($eventName, $arguments);
    }

    /**
     * Add a new listener for the event.
     *
     * @param string $eventName
     * @param callback $listener
     * @param int $priority
     * @return void
     */
    public function listen($eventName, $listener, $priority = self::PRIORITY_DEFAULT)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }
}
