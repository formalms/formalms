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
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class PdoHandler extends PdoSessionHandler
{
    public function __construct(SessionConfig $config)
    {
        $options = [];
        if ($config->getPrefix()) {
            $options['db_table'] = $config->getPrefix();
        }
        if ($config->getLifetime()) {
            $options['db_lifetime_col'] = $config->getLifetime();
        }

        $options = array_merge($options, $config->getOptions());

        try {
            if (empty($config->getUrl())) {
                if ($config->isAuthentication()) {
                    $url = sprintf('mysql://%s:%s@%s:%u/%s', $config->getUser(), $config->getPassword(), $config->getHost(), $config->getPort(), $config->getName());
                } else {
                    $url = sprintf('mysql://%s:%u/%s', $config->getHost(), $config->getPort(), $config->getName());
                }
                $config->setUrl($url);
            }
        } catch (\Exception $exception) {
            exit($exception->getMessage());
        }

        parent::__construct($config->getUrl(), $options);
    }
}
