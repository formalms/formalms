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

namespace appCore\Events\Core\Courses;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class CourseSubscriptionEditEvent.
 */
class CourseSubscriptionEditEvent extends Event
{
    public const EVENT_NAME = 'core.coursesubscriptionedit.event';

    /** @var array */
    protected $users;
    protected $level;
    protected $status;

    /**
     * CourseSubscriptionEditEvent constructor.
     */
    public function __construct()
    {
        $this->users = [];
    }

    /**
     * @param $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @param $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'users' => $this->users,
            'level' => $this->level,
            'status' => $this->status,
        ];
    }
}
