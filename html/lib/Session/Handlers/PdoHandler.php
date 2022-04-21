<?php

namespace Forma\lib\Session\Handlers;

use Forma\lib\Session\Config;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

class PdoHandler extends PdoSessionHandler
{
    public function __construct(Config $config){

        parent::__construct($pdoOrDsn, $options);
    }

}