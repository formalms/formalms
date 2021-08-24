<?php

namespace appCore\Events\Core\Courses;

use Symfony\Component\EventDispatcher\Event;

class CoursesEditionListEvent extends Event
{
    const EVENT_NAME_LIST = 'core.course.edition.list';

    /** @var array */
    protected $coursesEditions;

    protected $idCourse;

    /** @var string */
    protected $base_link_course;

    /** @var string */
    protected $base_link_classroom;

    /** @var string */
    protected $base_link_edition;

    /** @var string */
    protected $base_link_subscription;

    /** @var string */
    protected $base_link_competence;

    /**
     * CoursesListEvent constructor.
     * @param $coursesList
     */
    public function __construct($coursesEditions, $idCourse)
    {
        $this->coursesEditions = $coursesEditions;
        $this->idCourse = $idCourse;


        $this->base_link_course = 'alms/course';
        $this->base_link_classroom = 'alms/classroom';
        $this->base_link_edition = 'alms/edition';
        $this->base_link_subscription = 'alms/subscription';
        $this->base_link_competence = 'adm/competences';
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
     * @return CoursesEditionListEvent
     */
    public function setIdCourse($idCourse)
    {
        $this->idCourse = $idCourse;
        return $this;
    }

    /**
     * @return array
     */
    public function getCoursesEditions()
    {
        return $this->coursesEditions;
    }

    /**
     * @param array $coursesEditions
     * @return CoursesEditionListEvent
     */
    public function setCoursesEditions( $coursesEditions)
    {
        $this->coursesEditions = $coursesEditions;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseLinkCourse()
    {
        return $this->base_link_course;
    }

    /**
     * @return string
     */
    public function getBaseLinkClassroom()
    {
        return $this->base_link_classroom;
    }

    /**
     * @return string
     */
    public function getBaseLinkEdition()
    {
        return $this->base_link_edition;
    }

    /**
     * @return string
     */
    public function getBaseLinkSubscription()
    {
        return $this->base_link_subscription;
    }

    /**
     * @return string
     */
    public function getBaseLinkCompetence()
    {
        return $this->base_link_competence;
    }


}