<?php

namespace Forma\lib\Session\Handlers;

use Forma\lib\Session\Config;
use Nyholm\Dsn\DsnParser;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;


class RedisHandler extends RedisSessionHandler
{
    public function __construct(Config $config)
    {
        try {
            $connection = RedisAdapter::createConnection($config->getHost());
        }
        catch (\Exception $exception){
            die($exception->getMessage());
        }

        parent::__construct($connection);
    }
}