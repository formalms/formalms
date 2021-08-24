<?php

namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

class UsersListEvent extends Event
{
    const EVENT_NAME_LIST = 'core.users.list';

    /** @var array */
    protected $usersList;

    /** @var string */
    protected $link;

    protected $courseId;

    protected $editionId;

    protected $dateId;

    /**
     * CoursesListEvent constructor.
     * @param $coursesList
     */
    public function __construct($usersList, $link, $courseId, $editionId, $dateId)
    {
        $this->usersList = $usersList;
        $this->courseId = $courseId;
        $this->link = $link;
        $this->editionId = $editionId;
        $this->dateId = $dateId;
    }

    /**
     * @return array
     */
    public function getUsersList()
    {
        return $this->usersList;
    }

    /**
     * @param array $usersList
     * @return UsersListEvent
     */
    public function setUsersList(array $usersList)
    {
        $this->usersList = $usersList;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return UsersListEvent
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
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
     * @return UsersListEvent
     */
    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEditionId()
    {
        return $this->editionId;
    }

    /**
     * @param mixed $editionId
     * @return UsersListEvent
     */
    public function setEditionId($editionId)
    {
        $this->editionId = $editionId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateId()
    {
        return $this->dateId;
    }

    /**
     * @param mixed $dateId
     * @return UsersListEvent
     */
    public function setDateId($dateId)
    {
        $this->dateId = $dateId;
        return $this;
    }
}