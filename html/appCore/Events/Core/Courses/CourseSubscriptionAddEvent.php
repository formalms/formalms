<?php
namespace appCore\Events\Core\Courses;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CourseSubscriptionAddEvent
 * @package appLms\Events\Core
 */
class CourseSubscriptionAddEvent extends Event
{
    const EVENT_NAME = 'core.coursesubscriptionadd.event';
    
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