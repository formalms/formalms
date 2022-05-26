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
            if (empty($config->getUrl())) {
                if ($config->isAuthentication()){
                    $url = sprintf('memcached://%s:%s@%s:%f',$config->getUser(),$config->getPassword(),$config->getHost(),$config->getPort());
                }
                else {
                    $url = sprintf('memcached://%s:%f',$config->getHost(),$config->getPort());
                }
                $config->setUrl($url);
            }

            $connection = MemcachedAdapter::createConnection($config->getUrl(),[
                'prefix_key' => $config->getPrefix(),
                'connect_timeout' => $config->getTimeout()
            ]);
        }
        catch (\Exception $exception){
            die($exception->getMessage());
        }

        parent::__construct($connection);
    }
}