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

class MailconfigAdmController extends AdmController
{

    protected $model;

    protected $requestObj;

    protected $title = ' _MAIL_CONFIG';

    public function init()
    {
        parent::init();
        
        $this->model = new MailconfigAdm();

        $this->requestObj = $this->request->request->all();

        $this->queryString = $this->request->query->all();

    }


    public function show() {
        if (Forma::errorsExists()) {
            UIFeedback::error(Forma::getFormattedErrors(true));
        }

        $params['title'] = $this->title;

        $params['settings'] = $this->model->get();

        $this->render('show', $params);
    }


    public function insert() {
        if (Forma::errorsExists()) {
            UIFeedback::error(Forma::getFormattedErrors(true));
        }

        $params['title'] = Lang::t('_INSERT', 'standard');

        $params['settings'] = $this->model->getSettings();
        
        $this->render('view', $params);
    }

    public function edit() {
        if (Forma::errorsExists()) {
            UIFeedback::error(Forma::getFormattedErrors(true));
        }

        $params['title'] = Lang::t('_MOD', 'standard');

        $params['settings'] = $this->model->getSettings();

        $params['item'] = $this->model->getConfigItem($this->queryString['id']);

        $params['id'] = $this->queryString['id'];
        $this->render('view', $params);
    }

    public function upsert() {

        $validatedParams = $this->model->upsert($this->requestObj);
        
        if($validatedParams['error']) {
            $view = $validatedParams['view'];
           
           
        } else {
            $view = 'show';
        }
        return Util::jump_to('index.php?r=adm/mailconfig/'.$view);
        
    }

    

}