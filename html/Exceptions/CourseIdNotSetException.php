<?php


class CourseIdNotSetException extends Exception
{

    public function __construct()
    {
        parent::__construct('Course id is not set', 404, null);
    }
}