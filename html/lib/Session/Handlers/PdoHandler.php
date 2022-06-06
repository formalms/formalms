<?php

namespace Forma\lib\Session\Handlers;

use Forma\lib\Session\Config;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

class PdoHandler extends PdoSessionHandler
{
    public function __construct(Config $config){

        try {
            if (empty($config->getUrl())) {
                if ($config->isAuthentication()){
                    $url = sprintf('mysql://%s:%s@%s:%u/%s',$config->getUser(),$config->getPassword(),$config->getHost(),$config->getPort(),$config->getName());
                }
                else {
                    $url = sprintf('mysql://%s:%u/%s',$config->getHost(),$config->getPort(),$config->getName());
                }
                $config->setUrl($url);
            }

            $connection = new PdoAdapter(
                $config->getUrl(),
                // the string prefixed to the keys of the items stored in this cache
                $config->getPrefix(),
                // the default lifetime (in seconds) for cache items that do not define their
                // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
                // until the database table is truncated or its rows are otherwise deleted)
                $config->getLifetime(),
                // an array of options for configuring the database table and connection
                []
            );
        }
        catch (\Exception $exception){
            die($exception->getMessage());
        }

        parent::__construct($connection);

    }

}