<?php
namespace appCore\Events\Core;

use Symfony\Component\EventDispatcher\Event;

class ConfigGetRegroupUnitsEvent extends Event
{
    const EVENT_NAME = 'core.config.get_group_units';
    protected $groupUnits;

    public function __construct()
    {
        $this->groupUnits = array();
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