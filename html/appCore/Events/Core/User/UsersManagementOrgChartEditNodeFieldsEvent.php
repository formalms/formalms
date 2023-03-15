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
 * Class UsersManagementOrgChartEditNodeFieldsEvent.
 */
class UsersManagementOrgChartEditNodeFieldsEvent extends Event
{
    public const EVENT_NAME = 'core.usersmanagementorgcharteditnodedields.event';

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
