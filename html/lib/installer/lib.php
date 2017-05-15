<?php

$GLOBALS['debug'] ='';


function generateConfig($tpl_fn) {
	$config ='';


		if (file_exists($tpl_fn)) {
			$handle = fopen($tpl_fn, "r");
			$config = fread($handle, filesize($tpl_fn));
			fclose($handle);
		}
        $config=str_replace("[%-DB_TYPE-%]", $_SESSION['db_info']["db_type"], $config);
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
		'framework'=>'appCore',
		'lms'=>'appLms',
		'scs'=>'appScs',
	);
}


function importSqlFile($fn, $allowed_err_codes = array()) {
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

            $q=sql_query($qtxt);
            if (!$q) {
                if (!in_array(sql_errno(), $allowed_err_codes)) {
                    $res['log'].=sql_error()."\n";
                    $res['ok'] =FALSE;
                }
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

	// $to_upgrade_arr[]=getVersionIntNumber($GLOBALS['cfg']['endversion']);

	return $to_upgrade_arr;
}

//  3xxx : docebo ce versions series 3.x.x
//  4xxx : docebo ce versions series 4.x.x
// 1xxxx : forma     versions series 1.x  (formely 1.xx.xx )

function getVersionIntNumber($ver) {

	if ( version_compare($ver, '3.6.0','>=') && version_compare($ver, '4.0.5','<=') ) {
		// docebo ce versions series
		$res =str_pad(str_replace('.', '', $ver), 4, '0', STR_PAD_RIGHT);
	} else {
		// forma     versions series 1.x   (formely 1.xx.nn ) =>  1xxnn
		// forma versions series 1:  1.0 - 1.1 - 1.2 - .. - 1.9 .. => 10900
		$arr_v = explode(".",$ver) ;
		$res = "";
		$first = true;
		foreach( $arr_v as $key => $val ) {
			if ( $first ) {
				$res = $val;
				$first = false;
			} else {
				$res = $res . str_pad($val , 2, '0' , STR_PAD_LEFT );
			}
		}
		$res = str_pad($res, 5 , '0' , STR_PAD_RIGHT);

	}

	return $res;
}


?>