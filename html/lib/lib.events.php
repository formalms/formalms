<?php

// Importa EventDispatcher
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * Events class.
 */
final class Events
{
    public const PRIORITY_CORE = EventsHandler::PRIORITY_CORE;
    public const PRIORITY_DEFAULT = EventsHandler::PRIORITY_DEFAULT;

    /**
     * Events handler.
     *
     * @var EventsHandler
     */
    private static $handler;

    /**
     * Symfony Event Dispatcher.
     *
     * @var EventDispatcher
     */
    private static $dispatcher;

    /**
     * Get the Symfony EventDispatcher instance.
     *
     * @return EventDispatcher
     */
    private static function getDispatcher(): EventDispatcher
    {
        if (!self::$dispatcher) {
            self::$dispatcher = new EventDispatcher();
        }

        return self::$dispatcher;
    }

    /**
     * Get the handler instance (EventsHandler).
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
     * Trigger an event using the Symfony EventDispatcher.
     *
     * @param string $eventName
     * @param array  $arguments
     *
     * @return void
     */
    public static function trigger($eventName, $arguments = [])
    {
        // Crea un nuovo evento con il payload
        // Se l'argomento è già un Event Symfony, lo passo diretto
        if (is_object($arguments)) {
            return self::getHandler()->trigger($eventName, $arguments);
        }

        // Altrimenti wrappo in una classe che si comporta come un array
        return self::getHandler()->trigger($eventName, new class($arguments) extends Symfony\Contracts\EventDispatcher\Event implements ArrayAccess {
            private $arguments = [];

            public function __construct(array $arguments = [])
            {
                $this->arguments = $arguments;
            }

            public function offsetExists($offset): bool
            {
                return isset($this->arguments[$offset]);
            }

            public function offsetGet($offset) : mixed
            {
                return $this->arguments[$offset] ?? null;
            }

            public function offsetSet($offset, $value): void
            {
                $this->arguments[$offset] = $value;
            }

            public function offsetUnset($offset): void
            {
                unset($this->arguments[$offset]);
            }

            public function getArguments(): array
            {
                return $this->arguments;
            }
        });
    }

    /**
     * Trigger an event and send a deprecated error if any listener is attached.
     *
     * @param string $eventName
     * @param array  $arguments
     *
     * @return array
     */
    public static function triggerDeprecated($eventName, $arguments = [])
    {
        return self::getHandler()->triggerDeprecated($eventName, $arguments);
    }

    /**
     * Add a new listener for the event.
     *
     * @param string   $eventName
     * @param callable $listener
     * @param int      $priority
     *
     * @return void
     */
    public static function listen($eventName, $listener, $priority = self::PRIORITY_DEFAULT)
    {
        // Aggiungi il listener al dispatcher Symfony
        self::getDispatcher()->addListener($eventName, $listener);

        // Aggiungi anche al handler legacy se necessario
        self::getHandler()->listen($eventName, $listener, $priority);
    }
}
