<?php
namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementOrgChartEditNodeFieldsEvent
 * @package appLms\Events\Core
 */
class UsersManagementOrgChartEditNodeFieldsEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementorgcharteditnodedields.event';
    
    /** @var array */
    protected $node;
    protected $fields;
    protected $old_fields;

    /**
     * UsersManagementOrgChartEditNodeFieldsEvent constructor.
     */
    public function __construct()
    {        
        $this->node = null;
    }

    /**
     * @param $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * @return array
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param $old_fields
     */
    public function setOldFields($old_fields)
    {
        $this->old_fields = $old_fields;
    }

    /**
     * @return array
     */
    public function getOldFields()
    {
        return $this->old_fields;
    }

    /**
     * @param $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'node' => $this->node,
            // 'old_fields' => $this->old_fields,
            'fields' => $this->fields,
        ];
    }

}