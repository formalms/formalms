<?php
namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class CourseCompletedEvent extends Event
{
    const EVENT_NAME = 'lms.course.complete';

    /**
     * @var
     */
    protected $idCourse;

    /**
     * @var
     */
    protected $userId;

    /**
     * @var
     */
    protected $acl_man;

    public function __construct($idCourse,$user_id,$acl_man)
    {
        $this->idCourse = $idCourse;
        $this->userId = $user_id;
        $this->acl_man = $acl_man;
    }

    /**
     * @return mixed
     */
    public function getIdCourse()
    {
        return $this->idCourse;
    }

    /**
     * @param mixed $idCourse
     * @return CourseCompletedEvent
     */
    public function setIdCourse($idCourse)
    {
        $this->idCourse = $idCourse;
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
     * @return CourseCompletedEvent
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
     * @return CourseCompletedEvent
     */
    public function setAclMan($acl_man)
    {
        $this->acl_man = $acl_man;
        return $this;
    }
}