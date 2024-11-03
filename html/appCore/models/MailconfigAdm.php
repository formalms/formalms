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


class MailconfigAdm extends Model
{


    protected $db;

    const SETTINGS = [
        'title' => 'string',
        'auto_tls' => 'boolean',
        'host' => 'string',
        'port' => 'string',
        'user' => 'string',
        'password' => 'string',
        'debug' => 'boolean',
        'secure' => ['ssl' => 'ssl', 'tls' => 'tls'],
        'sender_mail_notification' => 'string',
        'sender_name_notification' => 'string',
        'sender_mail_system' => 'string',
        'sender_name_system' => 'string',
        'sender_cc_emails' => 'string',
        'sender_ccn_emails' => 'string',
        'helper_desk_mail' => 'string',
        'helper_desk_subject' => 'string',
        'helper_desk_name' => 'string',
        'replyto_name' => 'string',
        'replyto_mail' => 'string',
        'active' => 'boolean'
    ];

    const MINIMUM_SETTINGS = [
        'title' => 'string',
        'auto_tls' => 'boolean',
        'host' => 'string',
        'port' => 'string',
        'user' => 'string',
        'password' => 'string',
        'debug' => 'boolean',
        'secure' => 'string',
        'sender_mail_notification' => 'string',
        'sender_mail_system' => 'string',
        'active' => 'boolean'
    ];

    const OPTIONAL_FIELDS = [
        'sender_name_notification' => 'string',
        'sender_name_system' => 'string',
        'helper_desk_mail' => 'string',
        'helper_desk_subject' => 'string',
        'helper_desk_name' => 'string',
        'replyto_name' => 'string',
        'replyto_mail' => 'string',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->db = \FormaLms\db\DbConn::getInstance();
    }


    public function get($params = [])
    {
        $output = [];
        $query = 'SELECT mc.id, mc.system, mcf.value as active, mc.title FROM %adm_mail_configs_fields mcf 
                RIGHT JOIN %adm_mail_configs mc ON mc.id = mcf.mailConfigId AND mcf.type = "active"';
        $queryResult = $this->db->query($query);

        foreach ($queryResult as $result) {
            $output[] = $result;
        }

        return $output;
    }

    public function getList($params = [])
    {
        $output = [];
        $query = 'SELECT id, title FROM %adm_mail_configs';
        $queryResult = $this->db->query($query);

        foreach ($queryResult as $result) {
            $output[$result['id']] = $result['title'];
        }

        return $output;
    }


    public function upsert($params = [])
    {
        $result = $this->validate($params);

        if (!$result['error']) {
            if ($params['id']) {
                //sto modificando deov prima cancellare tutto
                if ($this->deleteFieldsByMailConfigId((int)$params['id'])) {
                    $this->update($params['title'], (int)$params['id']);
                }

                $id = $params['id'];
            } else {
                $id = $this->insert($params['title']);
            }

            unset($result['fields']['title']);
            $this->insertFields($result['fields'], $id);
        }


        if ($params['id']) {
            $result['view'] = 'update';
        } else {
            $result['view'] = 'insert';
        }

        return $result;
    }


    public function getRequiredSettings()
    {
        return self::MINIMUM_SETTINGS;
    }


    public function getSettings()
    {
        return self::SETTINGS;
    }

    private function validate($params)
    {

        $output['error'] = false;
        $params = array_intersect_key($params, $this->getSettings());

        if (count($params) < count($this->getRequiredSettings())) {
            $missingKeys = array_keys(array_diff_key($this->getRequiredSettings(), $params));
            foreach ($missingKeys as $key) {
                $missingFields[] = Lang::t('_' . strtoupper($key), 'mailconfig');
            }
            $errorMessage = Lang::t('_MISSING_FIELDS', 'mailconfig', [
                '[fields]' => implode(',', $missingFields),
            ]);

            \FormaLms\lib\Forma::addError($errorMessage);
            $output['error'] = true;
        }
        $blankKeys = [];
        foreach ($params as $key => $param) {
            if (in_array($key, array_keys($this->getRequiredSettings()))) {
                if ($param == '') {
                    $blankKeys[] = $key;
                }
               
            }
            $output['fields'][$key] = (string)$param;
        }

        if (count($blankKeys)) {
            foreach ($blankKeys as $key) {
                $blankFields[] = Lang::t('_' . strtoupper($key), 'mailconfig');
            }
            $errorMessage = Lang::t('_BLANK_FIELDS', 'mailconfig', [
                '[fields]' => implode(',', $blankFields),
            ]);

            \FormaLms\lib\Forma::addError($errorMessage);
            $output['error'] = true;
        }

        return $output;
    }

    public function insertFields($params, $id)
    {

        foreach ($params as $key => $value) {
            $query = 'INSERT INTO %adm_mail_configs_fields (mailConfigId, type ,value) VALUES ("' . $id . '","' . $key . '","' . $value . '")';
            $queryResult = $this->db->query($query);
        }

        return $queryResult;
    }

    public function insert($title)
    {


        $query = 'INSERT INTO %adm_mail_configs (title) VALUES ("' . $title . '")';
        $queryResult = $this->db->query($query);

        $insertId = $this->db->insert_id();

        $query = 'SELECT id FROM %adm_mail_configs';

        $queryResult = $this->db->query($query);

        foreach ($queryResult as $result) {
            $inserted[] = $result;
        }

        if (count($inserted) <= 1) {
            $this->toggleSystem($insertId);
        }

        return $insertId;
    }

    public function update($title, $id)
    {


        $query = 'UPDATE %adm_mail_configs SET title = "' . $title . '" WHERE id="' . $id . '"';
        $queryResult = $this->db->query($query);

        return $queryResult;
    }

    public function delete($id)
    {


        $queryResult = $this->deleteFieldsByMailConfigId($id);
        if ($queryResult) {
            $query = 'DELETE FROM %adm_mail_configs WHERE id = "' . $id . '"';
            $queryResult = $this->db->query($query);
        }
        return $queryResult;
    }

    public function getConfigItem($id)
    {


        $query = 'SELECT mcf.*, mc.title FROM %adm_mail_configs_fields mcf 
                        JOIN %adm_mail_configs mc ON mc.id = mcf.mailConfigId
                        WHERE mcf.mailConfigId = "' . $id . '"';
        $queryResult = $this->db->query($query);

        foreach ($queryResult as $result) {
            $output[$result['type']] = $result['value'];
            $output['title'] = $result['title'];
        }
        return $output;
    }

    public function deleteFieldsByMailConfigId($mailConfigId)
    {
        $query = 'DELETE FROM %adm_mail_configs_fields WHERE mailConfigId = "' . $mailConfigId . '"';
        $queryResult = $this->db->query($query);

        return $queryResult;
    }

    public function toggleSystem($id)
    {

        $query = 'UPDATE %adm_mail_configs SET `system` = 0';
        $queryResult = $this->db->query($query);
        if ($queryResult) {
            $query = 'UPDATE %adm_mail_configs SET `system` = 1 WHERE id = "' . $id . '"';
            $queryResult = $this->db->query($query);
        }

        return $queryResult;

    }

    public function toggleActive($id)
    {
        $query = 'UPDATE %adm_mail_configs_fields SET value = !value WHERE type="active" AND mailConfigId=' . $id;
        $queryResult = $this->db->query($query);

        return $queryResult;
    }


}