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

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * The system model class.
 *
 * This class can be used in order to retrive and manipulate all kind of
 * information about the system
 *
 * @since 4.0
 */

class SystemAdm extends Model
{

    public function __construct($lang) {
        if(!in_array($lang, array_keys($this->wholeLangs))) {
            $lang = 'en';
        }
      //  require_once(_lib_ . '/System/lang/' . $this->wholeLangs[$lang] . '.php');
    }

    private array $systemChecks = [
        BOOT_CONFIG => _CONFIG_OK,
        BOOT_PLATFORM => _DATABASE_OK,
        BOOT_PHP => _PHPVERSION_OK,
        BOOT_SESSION_CHECK => _SESSION_OK,
    ];


    private array $descriptions = [
        'title' => _STATUSCHECK_TITLE,
        'description' => _STATUSCHECK_DESCRIPTION,

    ];

    private array $wholeLangs = [
        'it' => 'italian',
        'en' => 'english',

    ];

    public function getChecks() {
        return $this->systemChecks;
    }

    public function getDescriptions() {
        return $this->descriptions;
    }

    public function decodeErrorStatus(string $status) : array{
        return explode("_", base64_decode($status));
    }

}