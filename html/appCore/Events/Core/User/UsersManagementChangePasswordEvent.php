<?php
namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementShowEvent
 * @package appLms\Events\Core
 */
class UsersManagementChangePasswordEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementchangepassword.event';
    
    /** @var array */
    protected $user;
    protected $filledPwd;

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
     * @return void
     */
    public function setFilledPwd($filledPwd)
    {
        $this->filledPwd = $filledPwd;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getFilledPwd()
    {
        return $this->filledPwd;
    }

    public function getData()
    {
        return [
            'user' => $this->user,
            'filledPwd' => $this->filledPwd,
        ];
    }

}