<?php

namespace appCore\Events\Core\User;

use Symfony\Component\EventDispatcher\Event;

class UsersShowEvent extends Event
{
    const USERS_SHOW_COLUMNS = 'core.users.show.columns';

    /** @var array */
    protected $columns;
    /** @var array */
    protected $fields;

    protected $hiddenValidity;

    public function __construct()
    {
        $this->columns = [];
        $this->fields = [];
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     * @return UsersShowEvent
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @param $column
     * @return $this
     */
    public function addColumnWithField($column, $field) {

        $this->columns[] = $column;
        $this->fields[] = $field;
        return $this;
    }

    public function insertColumnAtIndexWithField($index,$column, $field){

        array_splice( $this->columns, $index, 0, [$column] );
        $this->fields[] = $field;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     * @return UsersShowEvent
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function addField($field) {

        $this->fields[] = $field;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHiddenValidity()
    {
        return $this->hiddenValidity;
    }

    /**
     * @param mixed $hiddenValidity
     * @return UsersShowEvent
     */
    public function setHiddenValidity($hiddenValidity)
    {
        $this->hiddenValidity = $hiddenValidity;
        return $this;
    }
}