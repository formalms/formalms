<?php
namespace FormaLms\lib\Selectors\Multiuserselector\DataSelectors;

require_once _adm_ . '/models/UsermanagementAdm.php';
class RoleDataSelector extends DataSelector{ 


    public function __construct() {
     
        $this->builder = new \UsermanagementAdm();
        $this->name = 'RolerDataSelector';
    }

    public function getData($params = []) : string  {

    }

    
    protected function _selectAll($params = []){}

    protected function _getDynamicFilter($input){}

    protected function mapData($records){}

}