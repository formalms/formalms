<?php

namespace Forma\lib\Session\Handlers;

use Forma\lib\Session\Config;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

class RedisHandler extends RedisSessionHandler
{
    public function __construct(Config $config)
    {

        parent::__construct($redis, $options);
    }
}