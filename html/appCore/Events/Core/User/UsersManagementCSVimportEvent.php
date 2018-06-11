<?php
namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementCSVimportEvent
 * @package appLms\Events\Core
 */
class UsersManagementCSVimportEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementcsvimport.event';
    
    /** @var array */
    protected $users;

    /**
     * UsersManagementCSVimportEvent constructor.
     */
    public function __construct()
    {
        $this->users = array();
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
        return $this->users;
    }

}