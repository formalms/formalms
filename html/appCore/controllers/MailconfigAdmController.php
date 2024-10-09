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


class MailconfigAdmController extends AdmController
{
    /** @var MailconfigAdm */
    protected $model;

    protected $mailer;

    protected $requestObj;

    protected $title = ' _MAIL_CONFIG';
    public array $queryString;

    public function init()
    {
        parent::init();
        
        $this->model = new MailconfigAdm();

        $this->requestObj = $this->request->request->all();

        $this->queryString = $this->request->query->all();

        $this->mailer = new FormaLms\lib\Mailer\FormaMailer((int) $this->queryString['mailConfigId']);

    }


    public function show() {
        if (\FormaLms\lib\Forma::errorsExists()) {
            UIFeedback::error(\FormaLms\lib\Forma::getFormattedErrors(true));
        }

        if($this->checkConfigDatabase())
        {
            $params['title'] = $this->title;

            $params['settings'] = $this->model->get();
    
            $this->render('show', $params);

        }
   
    }


    public function insert() {
        if (\FormaLms\lib\Forma::errorsExists()) {
            UIFeedback::error(\FormaLms\lib\Forma::getFormattedErrors(true));
        }

        if($this->checkConfigDatabase()) {
            $params['title'] = Lang::t('_INSERT', 'standard');

            $params['settings'] = $this->model->getSettings();
    
            $params['required_fields'] = $this->model->getRequiredSettings();
            
            $this->render('view', $params);
        }
        
    }

    public function edit() {
        if (\FormaLms\lib\Forma::errorsExists()) {
            UIFeedback::error(\FormaLms\lib\Forma::getFormattedErrors(true));
        }
        
        if($this->checkConfigDatabase()) {
            $params['title'] = Lang::t('_MOD', 'standard');

            $params['settings'] = $this->model->getSettings();
    
            $params['item'] = $this->model->getConfigItem($this->queryString['id']);
    
            $params['required_fields'] = $this->model->getRequiredSettings();
    
            $params['id'] = $this->queryString['id'];
            $this->render('view', $params);
        }
        
    }

    public function upsert() {

        if($this->checkConfigDatabase()) {
            $validatedParams = $this->model->upsert($this->requestObj);
        
            if($validatedParams['error']) {
                $view = $validatedParams['view'];
               
               
            } else {
                $view = 'show';
            }
            return Util::jump_to('index.php?r=adm/mailconfig/'.$view);

        }
        
        
    }

    public function delete() {
        if($this->checkConfigDatabase()) {
            $validatedParams = $this->model->delete($this->queryString['id']);
        
            return Util::jump_to('index.php?r=adm/mailconfig/show');
        }
       
        
    }

    public function setSystem() {
        
        if($this->checkConfigDatabase()) {
            $this->model->toggleSystem($this->queryString['id']);

            echo json_encode(["result" => "ok"]);
        }

    }

    public function setActive() {
        
        if($this->checkConfigDatabase()) {
            $this->model->toggleActive($this->queryString['id']);

            echo json_encode(["result" => "ok"]);

        }


    }

    public function testMail() {
  
        $recipients = explode(";", $this->queryString['recipient']);
        $result = $this->mailer->testMail($recipients);

        echo json_encode(["result" => $result[$recipients[0]]]);

    }

    private function checkConfigDatabase()  {

        if($this->mailer && method_exists($this->mailer->getHandler(), 'isEnabledDatabase') && !$this->mailer->getHandler()::isEnabledDatabase()) {
            $this->render('disabled_config', []);
            return false;
        }

        return true;
    }



}