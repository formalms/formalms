<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace FormaLms\lib\Session\Handlers;

use FormaLms\lib\Session\SessionConfig;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;

class MemcachedHandler extends MemcachedSessionHandler
{
    public function __construct(SessionConfig $config)
    {
        try {
            if (empty($config->getUrl())) {
                if ($config->isAuthentication()) {
                    $url = sprintf('memcached://%s:%s@%s:%u', $config->getUser(), $config->getPassword(), $config->getHost(), $config->getPort());
                } else {
                    $url = sprintf('memcached://%s:%u', $config->getHost(), $config->getPort());
                }
                $config->setUrl($url);
            }

            $options = [
                'prefix_key' => $config->getPrefix(),
                'connect_timeout' => $config->getTimeout(),
            ];

            $options = array_merge($options, $config->getOptions());

            $connection = MemcachedAdapter::createConnection($config->getUrl(), $options);
        } catch (\Exception $exception) {
            exit($exception->getMessage());
        }

        parent::__construct($connection);
    }
}
