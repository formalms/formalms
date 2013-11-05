<?php

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

include('bootstrap.php');

$fn = _base_.'/config.php';

$config ='';
if (file_exists($fn)) {
	$handle = fopen($fn, "r");
	$config = fread($handle, filesize($fn));
	fclose($handle);
}

if ( strpos($config, 'IN_FORMA') !== false ) {
	// config already upgraded
	$config_saved = true;
} else {
	// generate new configuration
	$config =upgradeConfig400($config);

	$dwnl = Get::req('dwnl', DOTY_ALPHANUM, '0');

	if ( $dwnl == 1 ) {
		// download new configuration
		downloadConfig400($config);
	} else {
		// save consiguration
		$fn_new =_base_.'/config.php';
		$config_saved =saveConfig400($fn_new, $config);
	}

}

if ($config_saved) {
	$res =array('res'=>'ok');
} else {
	$res =array('res'=>'not_saved');
}

require_once(_base_.'/lib/lib.json.php');
$json = new Services_JSON();
echo $json->encode($res);
session_write_close();
die();


function upgradeConfig400($config) {

	// change the definition of "IN_XXX" behavior
	$config=str_replace('defined("IN_DOCEBO")', 'defined("IN_FORMA")', $config);

	// change the license
	$old_license='!/\*.*\s.*\sDOCEBO.*\s.*\s.*\s.*\s.*\s.*= \*/!';

	$new_license='
/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */
';

	$config=preg_replace($old_license, $new_license, $config);

	return $config;
}

function saveConfig400($fn, $config) {
	$saved =file_put_contents($fn, $config);
	return($saved);
}

function downloadConfig400($config) {

	header('Content-type: text/plain');
	header('Content-Disposition: attachment; filename="config.php"');

	echo $config;
	die();
}

?>