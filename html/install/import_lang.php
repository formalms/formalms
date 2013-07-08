<?php

include('bootstrap.php');

require_once(_installer_.'/lib/lib.lang_import.php');


$db = mysql_connect($_SESSION['db_info']['db_host'], $_SESSION['db_info']['db_user'], $_SESSION['db_info']['db_pass']);
mysql_select_db($_SESSION['db_info']['db_name']);

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

$platform_code = Get::pReq('platform', DOTY_STRING);
$lang = Get::pReq('lang', DOTY_STRING);
$upgrade = Get::pReq('upgrade', DOTY_INT);

$lang_arr =array_keys($_SESSION["lang_install"]);
$pl_arr =array('framework');

$cur =each($pl_arr);
$prev = '';
while($cur && $prev != $platform_code) {
	$prev =$cur['value'];
	$cur =each($pl_arr);
}
$next_platform =($cur ? $cur['value'] : false);


if ($next_platform === false) {
	$cur =each($lang_arr);
	$prev ='';
	while($cur && $prev != $lang) {
		$prev =$cur['value'];
		$cur =each($lang_arr);
	}
	$next_lang =($cur ? $cur['value'] : false);
	$next_platform =$pl_arr[0];
}
else {
	$next_lang =$lang;
}


$fn=_base_.'/xml_language/lang['.$lang.'].xml';

/* $overwrite =true;
if ($upgrade == 1) {
	$overwrite =false;
} */
// we always overwrite, also on upgrade
// cause there are too many changes

if (file_exists($fn)) {
	$LangAdm = new LangAdm();
	$LangAdm->importTranslation($fn, true, false);
}

$res =array();
$res['current_lang']=$lang;
$res['current_platform']=$platform_code;
$res['next_lang']=$next_lang;
$res['next_platform']=$next_platform;

require_once(_base_.'/lib/lib.json.php');
$json =new Services_JSON();
ob_clean();
echo $json->encode($res);
mysql_close($db);
die();

