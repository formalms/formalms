<?php

namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class CourseDeletedEvent extends Event
{
    const EVENT_NAME = 'lms.course.deleted';

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
