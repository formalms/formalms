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

namespace FormaLms\lib\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class FormaLogger
{
    private static ?FormaLogger $instance = null;

    private Logger $log;

    public static function getInstance()
    {
        if (self::$instance === null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->log = new Logger(self::class);
        if (isset($GLOBALS['cfg']) && isset($GLOBALS['cfg']['log_path']) && isset($GLOBALS['cfg']['logger_level'])) {
            $this->log->pushHandler(new StreamHandler($GLOBALS['cfg']['log_path'], $GLOBALS['cfg']['logger_level']));
        }
    }
}
