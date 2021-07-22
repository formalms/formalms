<?php
namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementOrgChartAssignEditEvent
 * @package appLms\Events\Core
 */
class UsersManagementOrgChartAssignEditEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementorgchartadd.event';
    
    /** @var array */
    protected $user;
    protected $users;
    protected $node;

    /**
     * UsersManagementOrgChartAssignEditEvent constructor.
     */
    public function __construct()
    {
        $this->user = null;
        $this->users = array();
        $this->node = null;
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
     * @param $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * @return array
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'users' => $this->user ?: $this->users,
            'node' => $this->node,
        ];
    }

}