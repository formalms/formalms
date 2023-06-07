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
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

class FilesystemHandler extends NativeFileSessionHandler
{
    public function __construct(SessionConfig $config)
    {
        parent::__construct($config->getUrl());
    }
}
