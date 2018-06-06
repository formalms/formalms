<?php
namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementOrgChartEditNodeEvent
 * @package appLms\Events\Core
 */
class UsersManagementOrgChartEditNodeEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementorgcharteditnode.event';
    
    /** @var array */
    protected $old_node;
    protected $node;

    /**
     * UsersManagementOrgChartEditNodeEvent constructor.
     */
    public function __construct()
    {
        $this->old_node = null;
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
     * @param $old_node
     */
    public function setOldNode($old_node)
    {
        $this->old_node = $old_node;
    }

    /**
     * @return array
     */
    public function getOldNode()
    {
        return $this->old_node;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'old' => $this->old_node,
            'new' => $this->node,
        ];
    }

}