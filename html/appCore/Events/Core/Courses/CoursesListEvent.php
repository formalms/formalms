<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace appCore\Events\Core\Courses;

use Symfony\Contracts\EventDispatcher\Event;

class CoursesListEvent extends Event
{
    public const EVENT_NAME_LIST = 'core.course.list';

    /** @var array */
    protected $courses;

    /** @var array */
    protected $coursesWithCertificates;

    /** @var array */
    protected $coursesWithCompetences;

    /** @var array */
    protected $coursesList;

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
     *
     * @param $coursesList
     */
    public function __construct($coursesList, $courses, $coursesWithCertificates, $coursesWithCompetences)
    {
        $this->coursesList = $coursesList;
        $this->courses = $courses;
        $this->coursesWithCertificates = $coursesWithCertificates;
        $this->coursesWithCompetences = $coursesWithCompetences;

        $this->base_link_course = 'alms/course';
        $this->base_link_classroom = 'alms/classroom';
        $this->base_link_edition = 'alms/edition';
        $this->base_link_subscription = 'alms/subscription';
        $this->base_link_competence = 'adm/competences';
    }

    /**
     * @return array
     */
    public function getCoursesList()
    {
        return $this->coursesList;
    }

    /**
     * @param array $coursesList
     */
    public function setCoursesList($coursesList)
    {
        $this->coursesList = $coursesList;
    }

    /**
     * @return array
     */
    public function getCourses()
    {
        return $this->courses;
    }

    /**
     * @return array
     */
    public function getCoursesWithCertificates()
    {
        return $this->coursesWithCertificates;
    }

    /**
     * @return array
     */
    public function getCoursesWithCompetences()
    {
        return $this->coursesWithCompetences;
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
