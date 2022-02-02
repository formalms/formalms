<?php


class PathNotFoundException extends Exception
{

    public function __construct()
    {
        parent::__construct('Include Path Not Found', 400, null);
    }
}