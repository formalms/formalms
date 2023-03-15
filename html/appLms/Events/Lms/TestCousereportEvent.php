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

class TestCousereportEvent extends Event
{
    public const EVENT_NAME = 'lms.test.coursereport.coursereport';

    /**
     * @var \Learning_Test
     */
    protected $object_test;
    /**
     * @var string
     */
    protected $overViewTestQuestionLink;

    /**
     * TestCreateEvent constructor.
     *
     * @param \Learning_Test $object_test
     * @param $lang
     */
    public function __construct($object_test)
    {
        $this->object_test = $object_test;
    }

    /**
     * @param string $overViewTestQuestionLink
     */
    public function setOverViewTestQuestionLink($overViewTestQuestionLink)
    {
        $this->overViewTestQuestionLink = $overViewTestQuestionLink;
    }

    /**
     * @return string
     */
    public function getOverViewTestQuestionLink()
    {
        return $this->overViewTestQuestionLink;
    }

    /**
     * @return \Learning_Test
     */
    public function getObjectTest()
    {
        return $this->object_test;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'object_test' => $this->object_test,
            'overViewTestQuestionLink' => $this->overViewTestQuestionLink,
        ];
    }
}
