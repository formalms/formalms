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

namespace appLms\Events\Lms;

use Symfony\Contracts\EventDispatcher\Event;

class CourseDeletedEvent extends Event
{
    public const EVENT_NAME = 'lms.course.deleted';

    protected $course;

    public function __construct($course)
    {
        $this->course = $course;
    }

    public function getCourse()
    {
        return $this->course;
    }
}
