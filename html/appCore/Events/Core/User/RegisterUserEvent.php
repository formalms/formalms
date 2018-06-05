<?php
namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class RegisterUserEvent
 * @package appCore\Events\Core\User
 */
class RegisterUserEvent extends Event
{
    const EVENT_NAME = 'register_user.event';

    /** @var null */
    protected $id;

    /**
     * RegisterUserEvent constructor.
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