<?php
namespace appLms\Events\Api;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ApiUserRegistrationEvent
 * @package appLms\Events\Api
 */
class ApiUserRegistrationEvent extends Event
{
    const EVENT_NAME = 'api.registration.event';
    
    /** @var null */
    protected $id;

    /**
     * ApiUserRegistrationEvent constructor.
     */
    public function __construct()
    {
        $this->id = NULL;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'id' => $this->id,
        ];
    }

}