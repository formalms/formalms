<?php

namespace FormaLms\lib\Session\Handlers;

use FormaLms\lib\Session\SessionConfig;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\Cache\Adapter\DoctrineDbalAdapter;


class PdoHandler extends PdoSessionHandler
{
    public function __construct(SessionConfig $config){

        $options = [];
        if($config->getPrefix()) {
            $options['db_table'] = $config->getPrefix();
        }
        if($config->getLifetime()) {
            $options['db_lifetime_col'] = $config->getLifetime();
        }

        $options = array_merge($options,$config->getOptions());
        
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