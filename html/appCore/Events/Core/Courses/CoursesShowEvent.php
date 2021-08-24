<?php

namespace appCore\Events\Core\Courses;

use Symfony\Component\EventDispatcher\Event;

class CoursesShowEvent extends Event
{
    const COURSE_SHOW_COLUMNS = 'core.course.show.columns';

    const COURSE_EDITION_SHOW_COLUMNS = 'core.course.edition.show.columns';

    /** @var array */
    protected $columns;
    /** @var array */
    protected $fields;

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
     * @return CoursesShowEvent
     */
    public function setColumns( $columns)
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
     * @return CoursesShowEvent
     */
    public function setFields($fields)
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
}