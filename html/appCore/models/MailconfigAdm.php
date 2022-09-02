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


class MailconfigAdm extends Model {


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
        'noreply_name' => 'string',
        'noreply_mail' => 'string',
        'active' => 'boolean'
    ];

    public function __construct() {
        $this->db = DbConn::getInstance();
    }


    public function get($params = []) {
        $output = [];
        $query = 'SELECT id, title, system FROM %adm_mail_configs';
        $queryResult = $this->db->query($query);
        
        foreach($queryResult as $result) {
            $output[] = $result;
        }

        return $output;
    }


    public function upsert($params = []) {
      

        $result = $this->validate($params);

        if(!$result['error']) {
            if($params['id']) {
                //sto modificando deov prima cancellare tutto
                $this->deleteFieldsByMailConfigId((int) $params['id']);
                $this->update($params['title']);
                $id = $params['id'];
            } else {
                $id = $this->insert($params['title']);
            }

            unset($result['fields']['title']);
            $this->insertFields($result['fields'], $id);
        }
        

        if($params['id']) {
            $result['view'] = 'update';
        } else {
            $result['view'] = 'insert';
        }

        return $result;
    }


    public function getSettings() {
        return self::MINIMUM_SETTINGS;
    }

    private function validate($params) {

        $output['error'] = false;
        $params = array_intersect_key($params, $this->getSettings());

        if(count($params) < count($this->getSettings())) {
            $missingKeys = array_keys(array_diff_key($this->getSettings(), $params));
            foreach($missingKeys as $key) {
                $missingFields[] = Lang::t('_'.strtoupper($key), 'mailconfig');
            }
            $errorMessage = Lang::t('_MISSING_FIELDS', 'mailconfig', [
                '[fields]' => implode(',', $missingFields),
            ]);

            Forma::addError($errorMessage);
            $output['error'] = true;
        }

        foreach($params as $key=>$param) {
            if(in_array($key, array_keys($this->getSettings()))) {
               
                    if($param == '') {
                        $blankKeys[] = $key;
                    }
    
                    $output['fields'][$key] = (string) $param;
               
            }
            
        }

        if(count($blankKeys)) {
            foreach($blankKeys as $key) {
                $blankFields[] = Lang::t('_'.strtoupper($key), 'mailconfig');
            }
            $errorMessage = Lang::t('_BLANK_FIELDS', 'mailconfig', [
                '[fields]' => implode(',', $blankFields),
            ]);

            Forma::addError($errorMessage);
            $output['error'] = true;
        }

        return $output;
    }

    public function insertFields($params, $id) {

        foreach($params as $key=>$value) {
            $query = 'INSERT INTO %adm_mail_configs_fields (mailConfigId, type ,value) VALUES ("'.$id.'","'.$key.'","'.$value.'")';
            $queryResult = $this->db->query($query);
        }

        return $queryResult;
    }

    public function insert($title) {

      
        $query = 'INSERT INTO %adm_mail_configs (title) VALUES ("'.$title.'")';
        $queryResult = $this->db->query($query);

        return $this->db->insert_id();
    }

    public function getConfigItem($id) {

       
        $query = 'SELECT mcf.*, mc.title FROM %adm_mail_configs_fields mcf 
                        JOIN %adm_mail_configs mc ON mc.id = mcf.mailConfigId
                        WHERE mcf.mailConfigId = "'.$id.'"';
        $queryResult = $this->db->query($query);
        
        foreach($queryResult as $result) {
            $output[$result['type']] = $result['value'];
            $output['title'] = $result['title'];
        }
        return $output;
    }

}