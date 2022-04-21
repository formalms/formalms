<?php

namespace Forma\lib\Session\Handlers;

use Forma\lib\Session\Config;
use MongoDB\Client;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;

class MongoDbHandler extends MongoDbSessionHandler
{
    public function __construct(Config $config)
   {
       parent::__construct($mongo, $options);
   }
}