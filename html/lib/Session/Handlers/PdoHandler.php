<?php

namespace Forma\lib\Session\Handlers;

use Forma\lib\Session\Config;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\Cache\Adapter\DoctrineDbalAdapter;


class PdoHandler extends PdoSessionHandler
{
    public function __construct(Config $config){

        $options = [];
        if($config->getPrefix()) {
            $options['db_table'] = $config->getPrefix();
        }
        if($config->getLifetime()) {
            $options['db_lifetime_col'] = $config->getLifetime();
        }
        
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

     
        }
        catch (\Exception $exception){
            die($exception->getMessage());
        }

        parent::__construct($config->getUrl(), $options);

    }

}