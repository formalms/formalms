<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace appLms\Events\Api;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ApiUserRegistrationEvent.
 */
class ApiUserRegistrationEvent extends Event
{
    public const EVENT_NAME = 'api.registration.event';

    /** @var null */
    protected $id;

    /**
     * ApiUserRegistrationEvent constructor.
     */
    public function __construct()
    {
        $this->id = null;
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
