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
echo '"header":' . \FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode(Lang::t('_MOD'), \FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON) . ',';
echo '"body":';

$this->render('_editmask', [
    'url' => 'ajax.adm_server.php?r=alms/timeperiods/modaction',
    'id' => $id,
    'title' => $title,
    'start_date' => $start_date,
    'end_date' => $end_date,
    'json' => $json,
]);

if (isset($GLOBALS['date_inputs']) && !empty($GLOBALS['date_inputs'])) {
    echo ',"__date_inputs":' . \FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($GLOBALS['date_inputs'], \FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON);
}

echo '}';
