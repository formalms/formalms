<?php
namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementOrgChartRemoveEvent
 * @package appLms\Events\Core
 */
class UsersManagementOrgChartRemoveEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementorgchartremove.event';
    
    /** @var array */
    protected $user;
    protected $users;

    /**
     * UsersManagementOrgChartAssignEditEvent constructor.
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