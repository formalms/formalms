<?php
namespace FormaLms\lib\Interfaces;

// Declare the interface 'DataSelector'
interface Accessible
{

    public function getAccessList($resourceId) : array;

    public function setAccessList($resourceId, array $selection) : bool;
}