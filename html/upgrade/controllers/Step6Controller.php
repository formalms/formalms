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
        $this->session = \Forma\lib\Session\SessionManager::getInstance()->getSession();
    }
    public function render()
    {
        $platform_arr = getPlatformArray();
        $this->session->set('platform_arr', $platform_arr);
        $this->session->save();
        $qtxt = 'SELECT lang_code FROM core_lang_language WHERE 1';
        // $q =sql_query($qtxt);
        require_once _base_ . '/config.php';
        require_once _base_ . '/db/lib.docebodb.php';

        $q = sql_query($qtxt);

        if ($q) {
            while ($row = sql_fetch_assoc($q)) {
                $lang_code = $row['lang_code'];
                $session->get('lang_install')[$lang_code] = 1;
            }
        }

        parent::render();
    }

    public function validate()
    {
        return true;
    }
}
