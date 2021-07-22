<?php

echo '{';
echo '"success":true,';
echo '"header":'.$json->encode(Lang::t('_ADD')).',';
echo '"body":';

$this->render('_editmask', array(
	'url' => 'ajax.adm_server.php?r=alms/timeperiods/addaction',
	'json' => $json
));

if (isset($GLOBALS['date_inputs']) && !empty($GLOBALS['date_inputs'])) {
  echo ',"__date_inputs":'.$json->encode($GLOBALS['date_inputs']);
}

echo '}';

?>