<?php
namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UserProfileShowEvent
 * @package appLms\Events\Lms
 */
class UserProfileShowEvent extends Event
{
    const EVENT_NAME = 'lms.profileshow.event';
    
    /** @var null  */
    protected $profile;

    /**
     * UserProfileShowEvent constructor.
     */
    public function __construct()
    {
        
        $this->profile = NULL;
    }

    /**
     * @param $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return null
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->profile;
    }

}