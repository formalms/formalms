<?php
namespace FormaLms\lib\Selectors\Multiuserselector\DataSelectors;

use FormaLms\lib\Interfaces\DataSelectable;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;


require_once _base_ . '/lib/lib.json.php';
require_once _base_ . '/i18n/lib.format.php';
require_once _base_ . '/i18n/lib.lang.php';
require_once _base_ . '/lib/layout/lib.layout.php';
abstract class DataSelector implements DataSelectable { 

    protected $name;

    protected $builder;

    protected $json;

    protected Serializer $serializer;

    public function __construct() {
        $this->json = new \Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $encoders = [new JsonEncoder()];
        $normalizers = [
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
            new ObjectNormalizer(
                null,
                null,
                null,
                new ReflectionExtractor()
            )];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function getName() : string  {
        return $this->name;
    }

    public function getData($params = []){
    }

    public function getColumns(){
    }

    public function getHiddenColumns(){
    }

    abstract protected function _selectAll($params = [], $columnsFilter = []);

    abstract protected function _getDynamicFilter($input);

    abstract protected function mapData($records, $filter = '');
}