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

namespace appCore\Events\Core\Courses;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class CourseSubscriptionEditLevelEvent.
 */
class CourseSubscriptionEditLevelEvent extends Event
{
    public const EVENT_NAME = 'core.coursesubscriptioneditlevel.event';

    /** @var array */
    protected $user;
    protected $level;
    protected $course;

    /**
     * CourseSubscriptionRemoveEvent constructor.
     */
    public function __construct()
    {
        $this->user = null;
        $this->level = null;
        $this->course = null;
    }

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @param $level
     */
    public function setCourse($course)
    {
        $this->course = $course;
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
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return array
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'user' => $this->user,
            'level' => $this->level,
            'course' => $this->course,
        ];
    }
}
