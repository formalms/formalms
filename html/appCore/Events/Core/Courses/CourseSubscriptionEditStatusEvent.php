<?php
namespace appCore\Events\Core\Courses;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CourseSubscriptionEditStatusEvent
 * @package appLms\Events\Core
 */
class CourseSubscriptionEditStatusEvent extends Event
{
    const EVENT_NAME = 'core.coursesubscriptioneditstatus.event';
    
    /** @var array */
    protected $user;
    protected $status;
    protected $course;

    /**
     * CourseSubscriptionRemoveEvent constructor.
     */
    public function __construct()
    {        
        $this->user = null;
        $this->status = null;
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
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param $course
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
    public function getStatus()
    {
        return $this->status;
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
            'status' => $this->status,
            'course' => $this->course,
        ];
    }

}