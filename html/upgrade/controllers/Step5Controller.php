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

    public function render()
    {
        $_SESSION['upgrade_ok'] = true;
        $_SESSION['to_upgrade_arr'] = getToUpgradeArray();
        parent::render();
    }

    public function validate()
    {
        return true;
    }
}
