<?php

namespace appLms\Events\Lms;

use Behat\Mink\Exception\Exception;
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
    protected $courseArrayData;

    protected $maskData;

    /**
     * CourseCreateAndUpdateEvent constructor.
     * @param $idCourse
     */
    public function __construct ($idCourse = false)
    {
        $this->maskData = '';
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
    public function getCourseArrayData()
    {
        return $this->courseArrayData;
    }

    /**
     * @param mixed $courseArrayData
     * @return CourseCreateAndUpdateEvent
     */
    public function setCourseArrayData($courseArrayData)
    {
        $this->courseArrayData = $courseArrayData;
        return $this;
    }

    /**
     * @return array
     */
    public function getMaskData()
    {
        return $this->maskData;
    }

    /**
     * @param $maskData
     * @return CourseCreateAndUpdateEvent
     */
    public function setMaskData($maskData)
    {
        $this->maskData = $maskData;
        return $this;
    }

    /**
     * @param $data
     * @return string
     */
    public function appendData($data){

        $this->maskData .=$data;

        return $this->maskData;
    }
}
