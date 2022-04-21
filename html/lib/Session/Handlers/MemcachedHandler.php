<?php

namespace Forma\lib\Session\Handlers;

use Forma\lib\Session\Config;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;

class MemcachedHandler extends MemcachedSessionHandler
{

    public function __construct(Config $config)
    {
        parent::__construct($memcached, $options);
    }
}