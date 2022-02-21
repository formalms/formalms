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

include_once _base_ . '/db/lib.docebodb.php';
class StepController
{
    public $step = 0;
    public $err = [];

    public function render()
    {
        include_once _upgrader_ . '/views/Step' . (int) $this->step . '.php';
    }

    public function ajax_validate()
    {
        $this->ajax_out(['success' => false, 'err' => [], 'ok' => []]);
    }

    protected function ajax_out($res_arr)
    {
        require_once _base_ . '/lib/lib.json.php';
        $json = new Services_JSON();
        $array_j = $json->encode($res_arr);

        ob_clean();
        echo $array_j;
    }

    public function getNextStep($current_step)
    {
        return $current_step + 1;
    }

    public function validate()
    {
        return false;
    }
}
