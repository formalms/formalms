<?php
//namespace appCore\Events\Core;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class DummyEvent
 * @package appLms\Events\Core
 */
class DummyEvent extends Event
{
    const EVENT_NAME = 'core.dummy.event';

    private $foo;

    /**
     * @return mixed
     */
    public function getFoo()
    {
        return $this->foo;
    }

    /**
     * @param mixed $foo
     */
    public function setFoo($foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->foo;
    }

}