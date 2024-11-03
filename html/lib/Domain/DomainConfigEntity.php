<?php
namespace FormaLms\lib\Domain;
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

class DomainConfigEntity
{
    protected $table = '%adm_domain_configs';

    protected $domain = null;

    protected $title = null;

    protected $template = null;

    protected $mailConfigId = null;

    protected $orgId = null;

    protected $id = null;

    protected $db;

    public function __construct($host) {
        
        $this->db =\FormaLms\db\DbConn::getInstance();

        return $this->getDomainConfigs($host);
    }

    private function getDomainConfigs($host) {

       
        //fa la query
        $query = 'SELECT id,title,domain,orgId,mailConfigId,template FROM ' . $this->table . ' WHERE domain = "' . $host . '" LIMIT 1' ;
        $res = $this->db->query($query);
        
        if($res) {
            $domainConfigs = $this->db->fetch_assoc($res);
            
            if($domainConfigs) {
                
                foreach($domainConfigs ?? [] as $key => $domainConfig) {
                 
                    $this->set($key, $domainConfig);
                }
            }

            return true;
        } else {
            return false;
        }
        
    }

    private function set($attribute, $value) {
        $this->$attribute = $value;
        return $this;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function getTemplate() {
        return $this->template;
    }

    public function setTemplate($templateName) {
        $this->template = $templateName;

        return $this;
    }

    public function getMailConfigId() {
        return $this->mailConfigId;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;

        return $this;
    }
}