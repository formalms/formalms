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

class InstallAdmController extends AdmController
{

    protected $model;
  
    public function init()
    {
        $this->model = new InstallAdm();
    }

    public function show()
    {

        
      
        $params = $this->model->getData($this->request);
        $params['steps'] = $this->model->getSteps();
        $params['languages'] = Lang::getFileSystemCoreLanguages('language');
        $params['setLang'] = Lang::getSelLang();
       

        $this->render('show', $params);

    }

 
}
