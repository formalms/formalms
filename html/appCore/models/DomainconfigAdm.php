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


class DomainconfigAdm extends Model {


    protected $db;


    public function __construct() {
        $this->db = \FormaLms\db\DbConn::getInstance();
    }


    public function get($domainConfigId = null, $mailConfigs = [], $orgs = []) {
        $output = [];
        $query = 'SELECT ac.*, count(adc.parentId) as childrenNumber FROM %adm_domain_configs ac
                    LEFT JOIN %adm_domain_configs adc ON ac.id = adc.parentId';
       
        if($domainConfigId) {
            $query .= ' WHERE ac.parentId = "'.$domainConfigId.'"';
        } else {
            $query .= ' WHERE ac.parentId IS NULL';
        }
        $query .= ' GROUP BY ac.id';
        $queryResult = $this->db->query($query) ?? [];
        
        foreach($queryResult as $result) {
            if($mailConfigs) {
                $result['mailConfigName'] = array_key_exists($result['mailConfigId'], $mailConfigs) ? $mailConfigs[$result['mailConfigId']] : null;
            }

            if($orgs) {
                $result['orgName'] = array_key_exists($result['orgId'], $orgs) ?  $orgs[$result['orgId']] : null;
            }
            $output[] = $result;
        }

        return $output;
    }

    public function read($domainConfigId) {
        $output = [];
        $query = 'SELECT * FROM %adm_domain_configs WHERE id = "'.$domainConfigId.'"';
        

        $queryResult = $this->db->query($query) ?? [];
        
        foreach($queryResult as $result) {
            $output[] = $result;
        }

        return $output[0];
    }


    public function save($params = []) {
      
        $parentId = $params['parentId'] ?? 'NULL';

        if(array_key_exists('id', $params)) {
            $query = 'UPDATE %adm_domain_configs SET
                                title = "'.$params['title'].'",
                                domain = "'.$params['domain'].'",
                                template = "'.$params['template'].'",
                                orgId = "'.$params['orgId'].'",
                                mailConfigId =  "'.$params['mailConfigId'].'"
                                WHERE id = "'.$params['id'].'"';
        } else {
             $query = 'INSERT INTO %adm_domain_configs (title,
                                                    domain,
                                                    parentId,
                                                    template,
                                                    orgId,
                                                    mailConfigId) 
                                                    VALUES (
                                                        "'.$params['title'].'",
                                                         "'.$params['domain'].'",
                                                        '.$parentId.',
                                                        "'.$params['template'].'",
                                                        "'.$params['orgId'].'",
                                                        "'.$params['mailConfigId'].'"
                                                        )';
        }
       
       
        
        
                                                      
        $queryResult = $this->db->query($query);

        if(!array_key_exists('id', $params)) {
            $params['id'] = $this->db->insert_id();
        }

  
        \Events::trigger('core.domainconfig.save', $params);

        return $queryResult;
    }




    public function delete($id) {
       
        //controllo che non abbia sottonodi
        $query = 'DELETE FROM %adm_domain_configs WHERE id = "' . $id . '"';
        $queryResult = $this->db->query($query);
        
        return $queryResult;
    }

    public function checkChildren($id) {
       
        //controllo che non abbia sottonodi
        $query = 'SELECT * FROM %adm_domain_configs WHERE parentId = "' . $id . '"';
        $queryResult = $this->db->query($query) ?? [];
        foreach($queryResult as $result) {
            $output[] = $result;
        }
        return count($output);
    }


}