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

namespace appCore\Events\Core\User;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class UsersManagementOrgChartCreateNodeEvent.
 */
class UsersManagementOrgChartCreateNodeEvent extends Event
{
    public const EVENT_NAME = 'core.usersmanagementorgchartcreatenode.event';

    /** @var array */
    protected $node;

    /**
     * UsersManagementOrgChartCreateNodeEvent constructor.
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
