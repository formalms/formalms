<?php

namespace FormaLms\lib\Session\Handlers;

use FormaLms\lib\Session\SessionConfig;
use MongoDB\Client;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;

class MongoDbHandler extends MongoDbSessionHandler
{
    public function __construct(SessionConfig $config)
   {
       parent::__construct($mongo, $options);
   }
}