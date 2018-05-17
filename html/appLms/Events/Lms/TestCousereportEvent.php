<?php
namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class TestCousereportEvent extends Event
{
    const EVENT_NAME = 'lms.test.coursereport.coursereport';

    /**
     * @var \Learning_Test
     */
    protected $object_test;
    /**
     * @var String
     */
    protected $overViewTestQuestionLink;

    /**
     * TestCreateEvent constructor.
     * @param \Learning_Test $object_test
     * @param $lang
     */
    public function __construct($object_test)
    {
        $this->object_test = $object_test;
    }

    /**
     * @param String $overViewTestQuestionLink
     */
    public function setOverViewTestQuestionLink($overViewTestQuestionLink)
    {
        $this->overViewTestQuestionLink = $overViewTestQuestionLink;
    }

    /**
     * @return String
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