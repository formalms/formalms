<?php
namespace appCore\Events\Core\Courses;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CourseSubscriptionEditEvent
 * @package appLms\Events\Core
 */
class CourseSubscriptionEditEvent extends Event
{
    const EVENT_NAME = 'core.coursesubscriptionedit.event';
    
    /** @var array */
    protected $users;
    protected $level;
    protected $status;

    /**
     * CourseSubscriptionEditEvent constructor.
     */
    public function __construct()
    {
        $this->users = array();
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