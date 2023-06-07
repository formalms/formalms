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
class UsersManagementChangePasswordEvent extends Event
{
    public const EVENT_NAME = 'core.usersmanagementchangepassword.event';

    /** @var array */
    protected $user;
    protected $filledPwd;

    /**
     * UsersManagementShowEvent constructor.
     */
    public function __construct()
    {
        $this->user = [];
    }

    /**
     * @param $users
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return void
     */
    public function setFilledPwd($filledPwd)
    {
        $this->filledPwd = $filledPwd;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getFilledPwd()
    {
        return $this->filledPwd;
    }

    public function getData()
    {
        return [
            'user' => $this->user,
            'filledPwd' => $this->filledPwd,
        ];
    }
}
