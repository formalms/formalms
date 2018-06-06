<?php
namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementUnsuspendEvent
 * @package appLms\Events\Core
 */
class UsersManagementUnsuspendEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementunsuspend.event';
    
    /** @var array */
    protected $user;
    protected $users;

    /**
     * UsersManagementUnsuspendEvent constructor.
     */
    public function __construct()
    {
        $this->user = null;
        $this->users = array();
    }

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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
    public function getUser()
    {
        return $this->user;
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