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

require_once __DIR__ . '/StepController.php';

/**
 * Upgrade config file.
 */
class Step3Controller extends StepController
{
    public $step = 3;

    public $session = null;

    public function __construct()
    {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function validate()
    {
        return true;
    }

    public function getNextStep($current_step)
    {
        $version = $this->session->get('start_version');
        if (version_compare($version, '3600', '>=') &&
             version_compare($version, '4000', '<')) {
            //docebo ce v 3.x.x =>  step 4: specific 3.x db upgrade
            $next_step = $current_step + 1;
        } elseif (version_compare($version, '4000', '>=') &&
                  version_compare($version, '5000', '<')) {
            //docebo ce v 4.x.x => skip step 4
            $next_step = $current_step + 2;
        } else {
            // forma v1.x => skip step 4
            $next_step = $current_step + 2;
        }

        return $next_step;
    }
}
