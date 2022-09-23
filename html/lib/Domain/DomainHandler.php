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
use \FormaLms\lib\Domain\DomainConfigEntity;
use \FormaLms\lib\Template\TemplateInfo;
use \FormaLms\lib\Mailer\FormaMailer;
defined('IN_FORMA') or exit('Direct access is forbidden.');

class DomainHandler 
{

    /** @var DomainHandler */
    private static $instance = null;

    protected $templateInfo = null;

    protected $domainInfo = null;

    protected $mailHandler = null;

    protected $entity;

    protected $request;

    protected $session;

    //the constructor
    public function __construct()
    {
        $this->request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();
        $httpHost = $this->request->server->get('HTTP_HOST');
        $notUseTemplate = $this->request->query->get('notuse_template');
        $this->entity = new DomainConfigEntity($httpHost);
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        if($notUseTemplate) {
            $this->entity->setTemplate('standard');
        }
        $this->setAttributes($this->entity);

        //salvare tutto in sessione
    }

     /**
     * @return DomainHandler|mixed
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new DomainHandler();
        }

        return self::$instance;
    }

    public function setTemplateInfo($templateName) {
        $this->templateInfo = new TemplateInfo($templateName);
        $this->session->set('template_info', $this->templateInfo);
        //retrocompatibilità
        $this->session->set('template', $templateName);
        return $this;
    }

    public function getTemplateInfo() {
        return $this->templateInfo;
    }

    public function setDomainInfo($title, $domain) {
        $this->domainInfo = new DomainInfo($title, $domain);
        $this->session->set('domain_info', $this->domainInfo);
        return $this;
    }

    public function getDomainInfo() {
        return $this->domainInfo;

    }

    public function attachDefaultMailer() {
        $templateName = $this->templateInfo ? $this->templateInfo->getName() : null;
        $this->mailHandler = new FormaMailer($this->entity->getMailConfigId(), $templateName);
        $this->session->set('mailer_info', $this->mailHandler);
        $this->session->save();
        return $this;
    }

    public function getMailer() {
        return $this->mailHandler;

    }

    private function setAttributes($entity) {
        $this->setDomainInfo($entity->getTitle(), $entity->getDomain());
        $this->setTemplateInfo($entity->getTemplate());
        $this->session->save();

        return $this;
    }


}
