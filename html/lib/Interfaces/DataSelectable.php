<?php
namespace FormaLms\lib\Interfaces;

// Declare the interface 'DataSelector'
interface DataSelectable
{

    public function getName();

    public function getData($params = []);

    public function getColumns();
}