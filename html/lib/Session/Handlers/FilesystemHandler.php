<?php

namespace FormaLms\lib\Session\Handlers;

use FormaLms\lib\Session\SessionConfig;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

class FilesystemHandler extends NativeFileSessionHandler
{
    public function __construct(SessionConfig $config)
    {
        parent::__construct($config->getUrl());
    }
}