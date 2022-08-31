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


class SmtpsettingsAdm extends Model {


    protected $db;

    const MINIMUM_SETTINGS =[
        'title' => 'string',
        'auto_tls' => 'boolean',
        'host' => 'string',
        'port' => 'string',
        'user' => 'string',
        'password' => 'string',
        'debug' => 'boolean',
        'secure' => 'string',
        'sender_mail_notification' => 'string',
        'sender_name_notification' => 'string',
        'sender_mail_system' => 'string',
        'sender_name_system' => 'string',
        'helper_desk_mail' => 'string',
        'helper_desk_subject' => 'string',
        'helper_desk_name' => 'string',
        'active' => 'boolean'
    ];

    public function __construct() {
        $this->db = DbConn::getInstance();
    }


    public function get($params = []) {
        $output = [];
        $query = 'SELECT id, title, system FROM %adm_smtp_settings';
        $queryResult = $this->db->query($query);
        
        foreach($queryResult as $result) {
            $output[] = $result;
        }

        return $output;
    }


    public function upsert($params = []) {
        $output = [];
        $query = 'SELECT id, title, system FROM %adm_smtp_settings';
        $queryResult = $this->db->query($query);
        
        foreach($queryResult as $result) {
            $output[] = $result;
        }

        return $output;
    }


    public function getSettings() {
        return self::MINIMUM_SETTINGS;
    }

}