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

class Step5Controller extends StepController
{
    public $step = 5; // Upgrade from version > 4040
    public $session = null;
    public function __construct() {
        $this->session = \Forma\lib\Session\SessionManager::getInstance()->getSession();
    }
    public function render()
    {
        $this->session->set('upgrade_ok', true);
        $this->session->set('to_upgrade_arr', getToUpgradeArray());
        $this->session->save();
        parent::render();
    }

    public function validate()
    {
        return true;
    }
}
