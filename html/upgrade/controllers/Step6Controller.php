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

require_once dirname(__FILE__) . '/StepController.php';

class Step6Controller extends StepController
{
    public $step = 6;
    public $session = null;
    public function __construct() {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }
    public function render()
    {
        $platform_arr = getPlatformArray();
        $this->session->set('platform_arr', $platform_arr);
      
        $qtxt = 'SELECT lang_code FROM core_lang_language WHERE 1';
        // $q =sql_query($qtxt);
        require_once _base_ . '/config.php';
        require_once _base_ . '/db/lib.docebodb.php';

        $queryResult = sql_query($qtxt);

        if ($queryResult) {
            $langCodes = [];
            foreach ($queryResult as $row) {
                $lang_code = $row['lang_code'];
                $langCodes[$lang_code] = 1;
            }
            $this->session->set('lang_install', $langCodes);

        }
        $this->session->save();
        parent::render();
    }

    public function validate()
    {
        return true;
    }
}
