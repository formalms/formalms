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

class SystemAdmController extends AdmController
{

    protected InstallAdm $installModel;

    protected SystemAdm $systemModel;
  
    public function init()
    {
        $debug =  $this->request->query->has('debug') ? (int) $this->request->query->get('debug') : 0;

        $this->installModel = new InstallAdm($debug);
        $this->systemModel = new SystemAdm();
    }

    public function install()
    {
      
        $params = $this->installModel->getData($this->request);

        $params['steps'] = $this->installModel->getSteps();
        $params['languages'] = Lang::getFileSystemCoreLanguages('language');
        $params['setLang'] = Lang::getSelLang();

        $this->render('install', $params);

    }

    public function set() {

        $result = json_encode(array('success' => $this->installModel->setValue($this->request)));
        echo $result;
      
        exit;
    }

    public function checkDbData() {

        echo $this->installModel->checkDbData($this->request);
        exit;
    }

    public function testMigrations() {

        $params = $this->request->request->all();

        if((int) $params['upgrade']) {
            $this->installModel->installMigrationsTable();
        }
        $writeSqlFile = dirname(__DIR__, 2) . "/files/migration" . floor(microtime(true) * 1000) .".sql";

        $testLine = '--dry-run --write-sql=' . $writeSqlFile;
        $messages[] = $this->installModel->migrate((bool) $params['debug'], $testLine);
        $messages[] = 'CHECK: ' . $writeSqlFile;
       
        echo $this->installModel->setResponse(true, $messages)->wrapResponse();
      
    }

    public function getErrorMessages() {

        $result = json_encode(array('messages' =>  $this->installModel->getErrorMessages($this->request)));
        echo $result;
        exit;
       
    }

    public function checkFtp() {

        echo $this->installModel->checkFtp($this->request);
       
        exit;
       
    }


    public function checkAdminData() {
        echo $this->installModel->checkAdminData($this->request);
       
        exit;
    }

    public function checkSmtpData() {
        echo $this->installModel->checkSmtpData($this->request);
       
        exit;
    }

    public function finalize() {
        echo $this->installModel->finalize($this->request);
       
        exit;
    }

    public function formSave() {
        echo $this->installModel->saveFields($this->request);
       
        exit;
    }


    public function checkSystemStatus()
    {
    
        $params['checks'] = $this->systemModel->getChecks();
        $errorStatus = $this->request->get('errorStatus');

        if($errorStatus) {
            $params['errorChecks'] = $this->systemModel->decodeErrorStatus($errorStatus);
        }

        $this->render('checkstatus', $params);

    }

 
}
