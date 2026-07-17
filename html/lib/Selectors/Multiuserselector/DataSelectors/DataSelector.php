<?php

namespace FormaLms\lib\Selectors\Multiuserselector\DataSelectors;

use FormaLms\lib\Interfaces\DataSelectable;
use FormaLms\lib\Serializer\FormaSerializer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;


require_once _base_ . '/i18n/lib.format.php';
require_once _base_ . '/i18n/lib.lang.php';
require_once _base_ . '/lib/layout/lib.layout.php';

abstract class DataSelector implements DataSelectable
{

    const DYNAMIC_COLUMNS = false;

    protected $name;

    protected $builder;

    protected FormaSerializer $serializer;

    public function __construct()
    {
        $this->serializer = FormaSerializer::getInstance();
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getData($params = [])
    {
    }

    public function getColumns()
    {
    }

    public function getHiddenColumns()
    {
    }

    public function getDynamicColumns(array $columns): array
    {
        return [];
    }

    abstract protected function _selectAll($params = [], $columnsFilter = []);

    abstract protected function _getDynamicFilter($input);

    abstract protected function mapData($records, $filter = '');
}