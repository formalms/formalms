<?php

namespace FormaLms\lib\Serializer;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class FormaSerializer extends Serializer
{
    private static ?FormaSerializer $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function __construct()
    {

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
            new ObjectNormalizer(
               null,
                new CamelCaseToSnakeCaseNameConverter(),
                new PropertyAccessor(),
                new ReflectionExtractor()
            )];
        parent::__construct($normalizers, $encoders);
    }
}