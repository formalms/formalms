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
 * Class UserProfileShowEvent.
 */
class UsersManagementShowDetailsEvent extends Event
{
    public const EVENT_NAME = 'core.usersmanagementdetails.event';

    /** @var null */
    protected $profile;

    /**
     * UserProfileShowEvent constructor.
     */
    public function __construct()
    {
        $this->profile = null;
    }

    /**
     * @param $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return null
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->profile;
    }
}
