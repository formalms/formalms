<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace appCore\Events\Core\User;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class UsersManagementUnsuspendEvent.
 */
class UsersManagementUnsuspendEvent extends Event
{
    public const EVENT_NAME = 'core.usersmanagementunsuspend.event';

    /** @var array */
    protected $user;
    protected $users;

    /**
     * UsersManagementUnsuspendEvent constructor.
     */
    public function __construct()
    {
        $this->user = null;
        $this->users = [];
    }

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->user ?: $this->users;
    }
}
