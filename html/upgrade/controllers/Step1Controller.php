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

/**
 * The first step is only for language selection and info display.
 */
class Step1Controller extends StepController
{
    public $step = 1;

    public function validate()
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $platform_arr = getPlatformArray();
        $session->set('platform_arr', $platform_arr);
        $session->save();

        return true;
    }
}
