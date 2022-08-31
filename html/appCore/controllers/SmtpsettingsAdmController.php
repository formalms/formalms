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

class SmtpsettingsAdmController extends AdmController
{

    protected $model;

    protected $title = ' _SMTP_SETTINGS';

    public function init()
    {
        parent::init();
        
        $this->model = new SmtpsettingsAdm();

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

}