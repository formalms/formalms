<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * The system model class.
 *
 * This class can be used in order to retrive and manipulate all kind of
 * information about the system
 *
 * @since 4.0
 */
require_once(_lib_ . '/Helpers/HelperTool.php');

class SystemAdm extends Model
{

    private array $systemChecks = [
        BOOT_CONFIG => 'config',
        BOOT_PLATFORM => 'database',
        BOOT_PHP => 'phpversion',

    ];

    public function checkSystem() {
        
    }

    public function getChecks() {
        return $this->systemChecks;
    }

    public function decodeErrorStatus(string $status) : array{
        return explode("_", base64_decode($status));
    }

}