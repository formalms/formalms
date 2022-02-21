<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace appCore\Events\Core\User;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class UsersManagementOrgChartEditNodeEvent.
 */
class UsersManagementOrgChartEditNodeEvent extends Event
{
    public const EVENT_NAME = 'core.usersmanagementorgcharteditnode.event';

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
