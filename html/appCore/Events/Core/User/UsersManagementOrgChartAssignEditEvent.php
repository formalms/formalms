<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace appCore\Events\Core\User;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class UsersManagementOrgChartAssignEditEvent.
 */
class UsersManagementOrgChartAssignEditEvent extends Event
{
    public const EVENT_NAME = 'core.usersmanagementorgchartadd.event';

    /** @var array */
    protected $user;
    protected $users;
    protected $node;

    /**
     * UsersManagementOrgChartAssignEditEvent constructor.
     */
    public function __construct()
    {
        $this->user = null;
        $this->users = [];
        $this->node = null;
    }

    /**
     * @param $users
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
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
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * @return array
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'users' => $this->user ?: $this->users,
            'node' => $this->node,
        ];
    }
}
