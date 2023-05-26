<?php
namespace FormaLms\lib\Interfaces;

// Declare the interface 'DataSelector'
interface Accessible
{

    public function getAccessList(int $resourceId) : array;

    public function setAccessList(int $resourceId, array $selection) : bool;
}