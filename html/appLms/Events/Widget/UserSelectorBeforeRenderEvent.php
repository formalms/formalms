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

namespace appLms\Events\Widget;

use Symfony\Contracts\EventDispatcher\Event;

class UserSelectorBeforeRenderEvent extends Event
{
    public const EVENT_NAME = 'widget.user_selector.before_render';
    protected $idOrg;
    protected $userSelectorId;
    protected $columns;
    protected $fields;

    public function __construct($idOrg, $userSelectorId, $columns = [], $fields = [])
    {
        $this->idOrg = $idOrg;
        $this->userSelectorId = $userSelectorId;
        $this->columns = $columns;
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param array $column
     */
    public function addColumn($column, $position = null)
    {
        if ($position == null) {
            $this->columns[] = $column;

            return;
        }
        $head = array_slice($this->columns, 0, $position);
        $tail = array_slice($this->columns, $position);
        $this->columns = array_merge($head, [$column]);
        $this->columns = array_merge($this->columns, $tail);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $field
     */
    public function addField($field, $position = null)
    {
        if ($position == null) {
            $this->fields[] = $field;

            return;
        }
        $head = array_slice($this->fields, 0, $position - 1);
        $tail = array_slice($this->fields, $position);
        $this->fields = array_merge($head, $field);
        $this->fields = array_merge($this->fields, $tail);
    }

    /**
     * @return mixed
     */
    public function getUserSelectorId()
    {
        return $this->userSelectorId;
    }

    /**
     * @return mixed
     */
    public function getIdOrg()
    {
        return $this->idOrg;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'idOrg' => $this->idOrg,
            'userSelectorId' => $this->userSelectorId,
            'columns' => $this->columns,
            'fields' => $this->fields,
        ];
    }
}
