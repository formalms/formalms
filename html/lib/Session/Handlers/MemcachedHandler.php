<?php

namespace Forma\lib\Session\Handlers;

use Forma\lib\Session\Config;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;

class MemcachedHandler extends MemcachedSessionHandler
{

    public function __construct(Config $config)
    {
        try {
            $connection = MemcachedAdapter::createConnection($config->getHost());
        }
        catch (\Exception $exception){
            die($exception->getMessage());
        }

        parent::__construct($connection);
    }
}