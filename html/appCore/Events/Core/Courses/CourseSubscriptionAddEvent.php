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
 * Class CourseSubscriptionAddEvent.
 */
class CourseSubscriptionAddEvent extends Event
{
    public const EVENT_NAME = 'core.coursesubscriptionadd.event';

    /** @var array */
    protected $data;
    protected $user;
    protected $level;
    protected $type;

    /**
     * CourseSubscriptionAddEvent constructor.
     */
    public function __construct()
    {
        $this->data = null;
    }

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return $level
     */
    public function getLevel()
    {
        return $this->level;
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
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $fields = ['type', 'data', 'level', 'user'];
        $data = [];

        foreach ($fields as $f) {
            if ($this->$f) {
                $data[$f] = $this->$f;
            }
        }

        return $data;
    }
}
