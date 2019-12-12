<?php

use Symfony\Component\EventDispatcher\Event as SymphonyEvent;

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

/**
 * Forma event class.
 */
final class FormaEvent extends SymphonyEvent
{
    /**
     * Event name.
     *
     * @var string
     */
    private $_name;

    /**
     * Event arguments.
     *
     * @var array
     */
    private $_arguments;

    /**
     * Deprecated flag.
     *
     * @var bool
     */
    private $_deprecated = false;

    /**
     * Define an event.
     *
     * @param string $name
     * @param array $arguments
     */
    public function __construct($name, $arguments = [])
    {
        $this->_name = $name;
        $this->_arguments = $arguments;
    }

    /**
     * Getter.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_arguments[$name];
    }

    /**
     * Setter.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->_arguments[$name] = $value;
    }

    /**
     * Check if is set.
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_arguments[$name]);
    }

    /**
     * Set event as deprecated.
     *
     * @return self
     */
    public function deprecate()
    {
        $this->_deprecated = true;

        return $this;
    }

    /**
     * Call event dispatcher for this event.
     *
     * @return void
     */
    public function trigger()
    {
        if ($this->_deprecated && FormaEventDispatcher::getInstance()->hasListeners()) {
            trigger_error("Event {$this->_name} is deprecated and will be removed in a future release.", E_USER_DEPRECATED);
        }

        FormaEventDispatcher::getInstance()->dispatch($this->_name, $this);
    }

    /**
     * Call constructor statically.
     *
     * @param string $name
     * @param array $arguments
     * @return self
     */
    public static function make($name, $arguments = [])
    {
        return new self($name, $arguments);
    }
}
