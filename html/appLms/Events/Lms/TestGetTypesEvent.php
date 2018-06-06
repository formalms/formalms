<?php
namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class TestGetTypesEvent extends Event
{
    const EVENT_NAME = 'lms.test.get_types';
    protected $testTypes;

    public function __construct()
    {
        $this->testTypes = array();
    }

    /**
     * @return array
     */
    public function addTestType($testType)
    {
        $this->testTypes[] = $testType;
        $this->testTypes = array_unique($this->testTypes);
    }

    /**
     * @return array
     */
    public function getTestTypes()
    {
        return $this->testTypes;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->testTypes;
    }

}