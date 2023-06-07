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

namespace appLms\Events\Lms;

use Symfony\Contracts\EventDispatcher\Event;

class TestGetTypesEvent extends Event
{
    public const EVENT_NAME = 'lms.test.get_types';
    protected $testTypes;

    public function __construct()
    {
        $this->testTypes = [];
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
