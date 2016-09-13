<?php
namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UserProfileShowEvent
 * @package appLms\Events\Lms
 */
class CourseTestReportEvent extends Event
{
    const EVENT_NAME = 'lms.coursetestreport.event';

    const EVENT_NAME_ACTION_HEADER = 'lms.coursetestreport.event.header';

    const EVENT_NAME_ACTION_BODY = 'lms.coursetestreport.event.body';

    /**
     * @var null
     */
    protected $tableHeaderArray;

    /**
     * @var null
     */
    protected $tableBodyArray;

    /**
     * CourseTestReportEvent constructor.
     */
    public function __construct()
    {
        
        $this->tableHeaderArray = NULL;
        $this->tableBodyArray = NULL;
    }

    public function addDeleteElementToTableHeader() {

    }

    /**
     * @param null $tableHeaderArray
     */
    public function setTableHeaderArray($tableHeaderArray)
    {
        $this->tableHeaderArray = $tableHeaderArray;
    }

    /**
     * @return null
     */
    public function getTableHeaderArray()
    {
        return $this->tableHeaderArray;
    }

    /**
     * @param null $tableBodyArray
     */
    public function setTableBodyArray($tableBodyArray)
    {
        $this->tableBodyArray = $tableBodyArray;
    }

    /**
     * @return null
     */
    public function getTableBodyArray()
    {
        return $this->tableBodyArray;
    }


}