<?php

namespace appCore\Events\Core\User;

use Symfony\Contracts\EventDispatcher\Event;

class LoggedOutEvent extends Event
{
    const EVENT_NAME = 'logged_out.event';

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
