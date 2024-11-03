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
 * Class UsersManagementShowEvent.
 */
class UsersManagementEditEvent extends Event
{
    public const EVENT_NAME = 'core.usersmanagementedit.event';

    /** @var array */
    protected $user;
    protected $old_user;
    protected $users;
    protected $type;

    /**
     * UsersManagementShowEvent constructor.
     */
    public function __construct()
    {
        $this->user = [];
        $this->users = [];
        $this->type = null;
    }

    /**
     * @param $users
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param $users
     */
    public function setOldUser($old_user)
    {
        $this->old_user = $old_user;
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
    public function getOldUser()
    {
        return $this->old_user;
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
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $fields = ['type', 'users', 'user', 'old_user'];
        $data = ['diff' => $this->userDiff()];

        foreach ($fields as $f) {
            if ($this->$f) {
                $data[$f] = $this->$f;
            }
        }

        return $data;
    }

    private function userDiff()
    {
        $result = [];

        foreach ($this->old_user as $k => $v) {
            if (isset($this->user->$k) && $this->user->$k != $v) {
                $result[$k] = $v . ' > ' . $this->user->$k;
            }
        }

        return $result ?: 'No data changed';
    }
}
