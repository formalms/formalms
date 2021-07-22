<?php
namespace appCore\Events\Core\Courses;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CourseSubscriptionRemoveEvent
 * @package appLms\Events\Core
 */
class CourseSubscriptionRemoveEvent extends Event
{
    const EVENT_NAME = 'core.coursesubscriptionremove.event';
    
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