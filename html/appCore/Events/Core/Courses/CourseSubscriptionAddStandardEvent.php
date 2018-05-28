<?php
namespace appCore\Events\Core\Courses;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CourseSubscriptionAddStandardEvent
 * @package appLms\Events\Core
 */
class CourseSubscriptionAddStandardEvent extends Event
{
    const EVENT_NAME = 'core.coursesubscriptionaddstandard.event';
    
    /** @var array */
    protected $data;

    /**
     * CourseSubscriptionAddStandardEvent constructor.
     */
    public function __construct()
    {
        $this->data = null;
    }

    /**
     * @param $users
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
        return $this->data;
    }

}