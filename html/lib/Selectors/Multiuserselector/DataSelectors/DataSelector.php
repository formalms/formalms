<?php
namespace FormaLms\lib\Selectors\Multiuserselector\DataSelectors;

use FormaLms\lib\Selectors\DataSelectorInterface;

require_once _base_ . '/lib/lib.json.php';
require_once _base_ . '/lib/lib.docebo.php';
require_once _base_ . '/i18n/lib.format.php';
require_once _base_ . '/lib/layout/lib.layout.php';
abstract class DataSelector implements DataSelectorInterface { 

    protected $name;

    protected $builder;

    protected $json;

    public function __construct() {
        $this->json = new \Services_JSON(SERVICES_JSON_LOOSE_TYPE);
    }

    public function getName() : string  {
        return $this->name;
    }

    public function getData($params = []){
    }

    abstract protected function _selectAll($params = []);

    abstract protected function _getDynamicFilter($input);

    abstract protected function mapData($records);
}