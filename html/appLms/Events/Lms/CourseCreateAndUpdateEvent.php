<?php

namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class CourseCreateAndUpdateEvent extends Event
{
    const EVENT_NAME_MASK = 'lms.course.mask';
    const EVENT_NAME_INS = 'lms.course.ins';
    const EVENT_NAME_MOD = 'lms.course.mod';

    /**
     * @var bool
     */
    protected $idCourse;

    /**
     * @var
     */
    protected $postData;

    /** @var string $htmlData */
    protected $htmlData;

    /**
     * CourseCreateAndUpdateEvent constructor.
     * @param $idCourse
     */
    public function __construct($idCourse = false)
    {
        $this->htmlData = '';
        $this->idCourse = $idCourse;
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
     * @return CourseCreateAndUpdateEvent
     */
    public function setIdCourse($idCourse)
    {
        $this->idCourse = $idCourse;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * @param mixed $postData
     * @return CourseCreateAndUpdateEvent
     */
    public function setPostData($postData)
    {
        $this->postData = $postData;
        return $this;
    }

    /**
     * @return string
     */
    public function getHtmlData()
    {
        return $this->htmlData;
    }

    /**
     * @param string $htmlData
     * @return CourseCreateAndUpdateEvent
     */
    public function setHtmlData($htmlData)
    {
        $this->htmlData = $htmlData;
        return $this;
    }

    /**
     * @param $data
     * @return string
     */
    public function appendHtmlData($htmlData)
    {
        $this->htmlData .= $htmlData;

        return $this->htmlData;
    }
}
