<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

require_once(dirname(__FILE__).'/StepController.php');

Class Step6Controller extends StepController {

	var $step=6;


	public function render() {

		$platform_arr=getPlatformArray();
		$_SESSION['platform_arr'] =$platform_arr;

		$db = mysql_connect($_SESSION['db_info']['db_host'], $_SESSION['db_info']['db_user'], $_SESSION['db_info']['db_pass']);
		mysql_select_db($_SESSION['db_info']['db_name']);
		
		$qtxt ="SELECT lang_code FROM core_lang_language WHERE 1";
		$q =mysql_query($qtxt);

		if ($q) {
			while($row=mysql_fetch_assoc($q)) {
				$lang_code =$row["lang_code"];
				$_SESSION["lang_install"][$lang_code]=1;
			}
		}

		mysql_close($db);
		parent::render();
	}


	public function validate() {
		return true;
	}

}

?>