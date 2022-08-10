<?php
namespace FormaLms\lib\Selectors;

// Declare the interface 'DataSelector'
interface DataSelectorInterface
{

    public function getName();

    public function getData($params = []);

    public function getColumns();
}