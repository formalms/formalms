<?php
namespace FormaLms\lib\Domain;
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

class DomainInfo
{

    protected $domain;

    protected $title;


    public function __construct($title, $domain) {
        
        $this->title = $title;
        $this->domain = $domain;
        
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
}