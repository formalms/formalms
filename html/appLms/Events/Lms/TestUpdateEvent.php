<?php
namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class TestUpdateEvent extends Event
{
    const EVENT_NAME = 'lms.test.update';

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
     * @param \Learning_Test $object_test
     * @param $lang
     */
    public function __construct($object_test, $lang)
    {
        $this->object_test = $object_test;
        $this->lang = $lang;

        $this->formElements = array();

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
        $formString = "";

        foreach ($this->formElements as $formElement) {
            $formString .= $formElement;
        }

        return $formString;
    }

}