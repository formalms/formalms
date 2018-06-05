<?php
namespace appCore\Events\Core\Courses;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CourseSubscriptionAddFastEvent
 * @package appLms\Events\Core
 */
class CourseSubscriptionAddFastEvent extends Event
{
    const EVENT_NAME = 'core.coursesubscriptionaddfast.event';
    
    /** @var array */
    protected $user;
    protected $level;

    /**
     * CourseSubscriptionAddFastEvent constructor.
     */
    public function __construct()
    {        
        $this->user = null;
        $this->level = null;
    }

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param void
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
     * @param void
     */
    public function getLevel()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'user' => $this->user,
            'level' => $this->level,
        ];
    }

}