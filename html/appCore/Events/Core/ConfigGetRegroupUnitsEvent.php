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

namespace appCore\Events\Core;

use Symfony\Contracts\EventDispatcher\Event;

class ConfigGetRegroupUnitsEvent extends Event
{
    public const EVENT_NAME = 'core.config.get_group_units';
    protected $groupUnits;

    public function __construct()
    {
        $this->groupUnits = [];
    }

    /**
     * @return array
     */
    public function addGroupUnit($key, $value)
    {
        $this->groupUnits[$key] = $value;
    }

    /**
     * @return array
     */
    public function getGroupUnits()
    {
        return $this->groupUnits;
    }

    /**
     * @param array $groupUnits
     */
    public function setGroupUnits($groupUnits)
    {
        $this->groupUnits = $groupUnits;
    }

    public function getData()
    {
        return $this->groupUnits;
    }
}
