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

echo '{';
echo '"success":true,';
echo '"header":' . $json->encode(Lang::t('_ADD')) . ',';
echo '"body":';

$this->render('_editmask', [
    'url' => 'ajax.adm_server.php?r=alms/timeperiods/addaction',
    'json' => $json,
]);

if (isset($GLOBALS['date_inputs']) && !empty($GLOBALS['date_inputs'])) {
    echo ',"__date_inputs":' . $json->encode($GLOBALS['date_inputs']);
}

echo '}';
