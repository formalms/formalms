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

class Step3Controller extends StepController
{
    public $step = 3;

    public function validate()
    {
        $agree = FormaLms\lib\Get::pReq('agree', DOTY_INT, 0);
        if ($agree != 1 && $this->session->get('license_accepted')) {
            return false;
        } else {
            $this->session->set('license_accepted', 1);
            $this->session->save();

            return true;
        }
    }
}
