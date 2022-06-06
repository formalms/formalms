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
            if (empty($config->getUrl())) {
                if ($config->isAuthentication()) {
                    $url = sprintf('redis://%s@%s:%u?timeout=%d&prefix=%s', $config->getPassword(), $config->getHost(),$config->getPort(), $config->getTimeout(), $config->getPrefix());
                }
                else {
                    $url = sprintf('redis://%s:%u?timeout=%d&prefix=%s', $config->getHost(),$config->getPort(), $config->getTimeout(), $config->getPrefix());
                }
                $config->setUrl($url);
            }
            $connection = RedisAdapter::createConnection($config->getUrl());
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }

        parent::__construct($connection);
    }
}