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
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

class RedisHandler extends RedisSessionHandler
{
    public function __construct(SessionConfig $config)
    {
        try {
            if (empty($config->getUrl())) {
                if ($config->isAuthentication()) {
                    $url = sprintf('redis://%s@%s:%u?timeout=%d&prefix=%s', $config->getPassword(), $config->getHost(), $config->getPort(), $config->getTimeout(), $config->getPrefix());
                } else {
                    $url = sprintf('redis://%s:%u?timeout=%d&prefix=%s', $config->getHost(), $config->getPort(), $config->getTimeout(), $config->getPrefix());
                }
                $config->setUrl($url);
            }

            $options = [];

            $options = array_merge($options, $config->getOptions());


            $connection = RedisAdapter::createConnection($config->getUrl(), $options);
        } catch (\Exception $exception) {
            exit($exception->getMessage());
        }

        parent::__construct($connection);
    }
}
