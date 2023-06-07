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

class TestCreateEvent extends Event
{
    public const EVENT_NAME = 'lms.test.create';

    protected $formElements;
    /**
     * @var \Learning_Test
     */
    protected $object_test = null;
    /**
     * @var null
     */
    protected $lang = null;

    /**
     * TestCreateEvent constructor.
     *
     * @param \Learning_Test $object_test
     * @param $lang
     */
    public function __construct($object_test, $lang)
    {
        $this->object_test = $object_test;
        $this->lang = $lang;

        $this->formElements = [];
    }

    /**
     * @return null
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return \Learning_Test
     */
    public function getObjectTest()
    {
        return $this->object_test;
    }

    /**
     * @param array $formElements
     */
    public function setFormElements($formElements)
    {
        $this->formElements = $formElements;
    }

    /**
     * @return array
     */
    public function getFormElements()
    {
        return $this->formElements;
    }

    public function addFormElement($formElements)
    {
        $this->formElements = $formElements;
    }

    public function getElementString()
    {
        $formString = '';

        foreach ($this->formElements as $formElement) {
            $formString .= $formElement;
        }

        return $formString;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'formElements' => $this->formElements,
            'object_test' => $this->object_test,
            'lang' => $this->lang,
        ];
    }
}
