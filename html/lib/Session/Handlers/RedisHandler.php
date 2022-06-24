<?php

namespace FormaLms\lib\Session\Handlers;

use FormaLms\lib\Session\SessionConfig;
use Nyholm\Dsn\DsnParser;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;


class RedisHandler extends RedisSessionHandler
{
    public function __construct(SessionConfig $config)
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
            $connection = RedisAdapter::createConnection($config->getUrl(), $config->getOptions());
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }

        parent::__construct($connection);
    }
}