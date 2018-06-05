<?php
namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementOrgChartDeleteNodeEvent
 * @package appLms\Events\Core
 */
class UsersManagementOrgChartDeleteNodeEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementorgchartdeletenode.event';
    
    /** @var array */
    protected $node;

    /**
     * UsersManagementOrgChartDeleteNodeEvent constructor.
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
     * @return array
     */
    public function getData()
    {
        return $this->node;
    }

}