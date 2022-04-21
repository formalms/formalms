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
    public $step = 5;

    public function validate()
    {
        $_SESSION['adm_info'] = Forma\lib\Get::pReq('adm_info');
        $_SESSION['lang_install'] = Forma\lib\Get::pReq('lang_install');

        $this->saveConfig();

        return true;
    }

    private function saveConfig()
    {
        // ----------- Generating config file -----------------------------
        $config = '';
        $fn = _installer_ . '/data/config_template.php';

        $config = generateConfig($fn);

        $save_fn = _base_ . '/config.php';
        $saved = false;
        if (is_writeable($save_fn)) {
            $handle = fopen($save_fn, 'w');
            if (fwrite($handle, $config)) {
                $saved = true;
            }
            fclose($handle);

            @chmod($save_fn, 0644);
        }

        $_SESSION['config_saved'] = $saved;
    }
}
