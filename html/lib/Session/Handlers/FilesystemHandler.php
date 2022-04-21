<?php

namespace Forma\lib\Session\Handlers;

use Forma\lib\Session\Config;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

class FilesystemHandler extends NativeFileSessionHandler
{
    public function __construct(Config $config)
    {
        parent::__construct($config->getHost());
    }
}