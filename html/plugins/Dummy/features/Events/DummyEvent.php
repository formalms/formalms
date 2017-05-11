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

}