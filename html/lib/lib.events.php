<?php


defined('IN_FORMA') or die('Direct access is forbidden.');

/**
 * Events class.
 */
final class Events
{
    const PRIORITY_CORE = EventsHandler::PRIORITY_CORE;
    const PRIORITY_DEFAULT = EventsHandler::PRIORITY_DEFAULT;

    /**
     * Events handler.
     *
     * @var EventsHandler
     */
    private static $handler;

    /**
     * Get the handler instance.
     *
     * @return EventsHandler
     */
    private static function getHandler()
    {
        if (!isset(self::$handler)) {
            self::$handler = new EventsHandler();
        }
        return self::$handler;
    }

    /**
     * Trigger an event.
     *
     * @param string $eventName
     * @param array $arguments
     * @return array
     */
    public static function trigger($eventName, $arguments = [])
    {
        return self::getHandler()->trigger($eventName, $arguments);
    }

    /**
     * Trigger an event and send a deprecated error if any listener is attached.
     *
     * @param string $eventName
     * @param array $arguments
     * @return array
     */
    public static function triggerDeprecated($eventName, $arguments = [])
    {
        return self::getHandler()->triggerDeprecated($eventName, $arguments);
    }

    /**
     * Add a new listener for the event.
     *
     * @param string $eventName
     * @param callback $listener
     * @param int $priority
     * @return void
     */
    public static function listen($eventName, $listener, $priority = self::PRIORITY_DEFAULT)
    {   
        return self::getHandler()->listen($eventName, $listener, $priority);
    }
}
