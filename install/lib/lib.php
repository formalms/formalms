<?php

$GLOBALS['debug'] ='';


function generateConfig($tpl_fn) {
	$config ='';


		if (file_exists($tpl_fn)) {
			$handle = fopen($tpl_fn, "r");
			$config = fread($handle, filesize($tpl_fn));
			fclose($handle);
		}
		
		$config=str_replace("[%-DB_HOST-%]", $_SESSION['db_info']["db_host"], $config);
		$config=str_replace("[%-DB_USER-%]", $_SESSION['db_info']["db_user"], $config);
		$config=str_replace("[%-DB_PASS-%]", $_SESSION['db_info']["db_pass"], $config);
		$config=str_replace("[%-DB_NAME-%]", $_SESSION['db_info']["db_name"], $config);

		if ($_SESSION['upload_method'] == "http") {
			$upload_method="fs";

			$config=str_replace("[%-FTP_HOST-%]", "localhost", $config);
			$config=str_replace("[%-FTP_PORT-%]", "21", $config);
			$config=str_replace("[%-FTP_USER-%]", "", $config);
			$config=str_replace("[%-FTP_PASS-%]", "", $config);
			$config=str_replace("[%-FTP_PATH-%]", "/", $config);
		}
		else if ($_SESSION["upload_method"] == "ftp") {
			$upload_method="ftp";

			$config=str_replace("[%-FTP_HOST-%]", $_SESSION['ul_info']["ftp_host"], $config);
			$config=str_replace("[%-FTP_PORT-%]", $_SESSION['ul_info']["ftp_port"], $config);
			$config=str_replace("[%-FTP_USER-%]", $_SESSION['ul_info']["ftp_user"], $config);
			$config=str_replace("[%-FTP_PASS-%]", $_SESSION['ul_info']["ftp_pass"], $config);
			$config=str_replace("[%-FTP_PATH-%]", $_SESSION['ul_info']["ftp_path"], $config);
		}

		$config=str_replace("[%-UPLOAD_METHOD-%]", $upload_method, $config);

	return $config;
}


function getPlatformArray() {
	return array(
		'framework'=>'doceboCore',
		'lms'=>'doceboLms',
		'scs'=>'doceboScs',
	);
}


function importSqlFile($fn) {
	$res =array('ok'=>true, 'log'=>'');

	$handle = fopen($fn, "r");
	$content = fread($handle, filesize($fn));
	fclose($handle);

	// This two regexp works fine; don't edit them! :)
	$content=preg_replace("/--(.*)[^\$]/", "", $content);
	$sql_arr=preg_split("/;([\s]*)[\n\r]/", $content);

	foreach ($sql_arr as $sql) {
		$qtxt=trim($sql);

		if (!empty($qtxt)) {

			$q=mysql_query($qtxt);
			if (!$q) {
				$res['log'].=mysql_error()."\n";
				$res['ok'] =FALSE;
			}
		}
	}

	$GLOBALS['debug'].=$res['log'];

	return $res;
}


function getToUpgradeArray() {
	$to_upgrade_arr =array();

	foreach($GLOBALS['cfg']['versions'] as $ver=>$label) {
		if ($ver > $_SESSION['start_version']) {
			$to_upgrade_arr[]=$ver;
		}
	}
	
	$to_upgrade_arr[]=getVersionIntNumber($GLOBALS['cfg']['endversion']);
	
	return $to_upgrade_arr;
}


function getVersionIntNumber($ver) {
	$res =str_pad(str_replace('.', '', $ver), 4, '0', STR_PAD_RIGHT);
	return $res;
}


?>