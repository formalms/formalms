<?php
namespace appCore\Events\Core;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementOrgChartAddEvent
 * @package appLms\Events\Core
 */
class UsersManagementOrgChartAddEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementorgchartadd.event';
    
    /** @var array */
    protected $user;
    protected $users;

    /**
     * UsersManagementOrgChartAddEvent constructor.
     */
    public function __construct()
    {
        $this->user = null;
        $this->users = array();
    }

    /**
     * @param $users
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->user ?: $this->users;
    }

}
