<?php
namespace appCore\Events\Core;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementShowEvent
 * @package appLms\Events\Core
 */
class UsersManagementEditEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementedit.event';
    
    /** @var array */
    protected $user;
    protected $old_user;

    /**
     * UsersManagementShowEvent constructor.
     */
    public function __construct()
    {
        
        $this->user = array();
    }

    /**
     * @param $users
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param $users
     */
    public function setOldUser($old_user)
    {
        $this->old_user = $old_user;
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
    public function getOldUser()
    {
        return $this->old_user;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->userDiff();
    }

    private function userDiff()
    {
        $result = [];

        foreach ($this->old_user as $k => $v) {
            if (isset($this->user->$k) && $this->user->$k != $v) {
                $result[$k] = $v . ' > ' . $this->user->$k;
            }
        }
        return $result ?: 'No data changed';
    }

}