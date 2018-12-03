<?php
namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class CourceCompletedEvent extends Event
{
    const EVENT_NAME = 'lms.course.complete';

    /**
     * @var
     */
    protected $courseId;

    /**
     * @var
     */
    protected $userId;

    /**
     * @var
     */
    protected $acl_man;

    public function __construct($course_id,$user_id,$acl_man)
    {
        $this->courseId = $course_id;
        $this->userId = $user_id;
        $this->acl_man = $acl_man;
    }

    /**
     * @return mixed
     */
    public function getCourseId()
    {
        return $this->courseId;
    }

    /**
     * @param mixed $courseId
     * @return CourceCompletedEvent
     */
    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     * @return CourceCompletedEvent
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAclMan()
    {
        return $this->acl_man;
    }

    /**
     * @param mixed $acl_man
     * @return CourceCompletedEvent
     */
    public function setAclMan($acl_man)
    {
        $this->acl_man = $acl_man;
        return $this;
    }
}