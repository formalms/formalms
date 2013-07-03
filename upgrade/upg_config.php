<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

include('bootstrap.php');

if(isset($GLOBALS['cfg']['db_type'])) {

	$res = array('res'=>'ok');

} else {
	require_once(_base_.'/config.php');
	$_SESSION['db_info']["db_host"]=$GLOBALS['dbhost'];
	$_SESSION['db_info']["db_user"]=$GLOBALS['dbuname'];
	$_SESSION['db_info']["db_pass"]=$GLOBALS['dbpass'];
	$_SESSION['db_info']["db_name"]=$GLOBALS['dbname'];

	if ($GLOBALS['uploadType'] == 'fs') {
		$_SESSION['upload_method']='http';
	} else {
		$_SESSION['upload_method']='ftp';
	}
	$_SESSION['ul_info']["ftp_host"]=$GLOBALS['ftphost'];
	$_SESSION['ul_info']["ftp_port"]=$GLOBALS['ftpport'];
	$_SESSION['ul_info']["ftp_user"]=$GLOBALS['ftpuser'];
	$_SESSION['ul_info']["ftp_pass"]=$GLOBALS['ftppass'];
	$_SESSION['ul_info']["ftp_path"]=$GLOBALS['ftppath'];


	$fn = _upgrader_.'/data/config_template.php';
	$config =generateConfig($fn);
	$config_fn =_base_.'/config.php';
	$config_saved =file_put_contents($config_fn, $config);
	if ($config_saved) {
		$res =array('res'=>'ok');
	} else {
		$res =array('res'=>'not_saved');
	}
}

require_once(_base_.'/lib/lib.json.php');
$json = new Services_JSON();
echo $json->encode($res);
session_write_close();
die();

?>