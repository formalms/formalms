<?php

Class Lang {

	static protected $_loaded = false;

	private function  __construct() {
		self::init();
	}

	public static function init() {
		require_once(_lib_.'/installer/lang/'.self::getSelLang().'.php');
	}

	public static function getSelLang() {
		return $_SESSION['sel_lang'];
	}

	public static function t($keyword) {
		if (!self::$_loaded) self::init();

		if (defined($keyword)) {
			return constant($keyword);
		}
		else {
			return '<span style="font-weight: bold;color:red;">'.$keyword.'</span>';
			//return ucfirst(trim(strtolower(str_replace('_', ' ', $keyword))));
		}
	}

	static public function direction($key="code") {

		return 'ltr';
	}

	static public function getLanguageList($key="code") {
		// key can be "code" or "language"
		$res=array();
		if ($key == "code") {

			$res["ar"] = "arabic";
			$res["bs"] = "bosnian";
			$res["bg"] = "bulgarian";
			$res["hr"] = "croatian";
			$res["cs"] = "czech";
			//$res["da"] = "danish";
			$res["nl"] = "dutch";
			$res["en"] = "english";
			$res["fa"] = "farsi";
			$res["fi"] = "finnish";
			$res["fr"] = "french";
			$res["de"] = "german";
			$res["el"] = "greek";
			//$res["hi"] = "hindi";
			$res["he"] = "hebrew";
			$res["hu"] = "hungarian";
			$res["id"] = "indonesian";
			$res["it"] = "italian";
			$res["ja"] = "japanese";
			//$res["ko"] = "korean";
			//$res["ms"] = "malay";
			//$res["no"] = "norwegian";
			//$res["nb"] = "norwegian";
			//$res["nn"] = "norwegian";
			$res["pl"] = "polish";
			$res["pt"] = "portuguese";
			//$res["pt-br"] = "portuguese-br";
			$res["ro"] = "romanian";
			$res["ru"] = "russian";
			$res["zh"] = "simplified_chinese";
			//$res["sk"] = "slovak";
			$res["es"] = "spanish";
			$res["sw"] = "swedish";
			//$res["th"] = "thai";
			$res["tr"] = "turkish";
			$res["uk"] = "ukrainian";

		} else if ($key == "language") {

			$res["arabic"] = "arabic";
			$res["bosnian"] = "bosnian";
			$res["bulgarian"] = "bulgarian";
			$res["croatian"] = "croatian";
			$res["czech"] = "czech";
			//$res["danish"] = "danish";
			$res["dutch"] = "dutch";
			$res["english"] = "english";
			$res["farsi"] = "farsi";
			$res["finnish"] = "finnish";
			$res["french"] = "french";
			$res["german"] = "german";
			$res["greek"] = "greek";
			//$res["hindi"] = "hindi";
			$res["hebrew"] = "hebrew";
			$res["hungarian"] = "hungarian";
			$res["indonesian"] = "indonesian";
			$res["italian"] = "italian";
			$res["japanese"] = "japanese";
			//$res["korean"] = "korean";
			//$res["malay"] = "malay";
			//$res["norwegian"] = "norwegian";
			$res["polish"] = "polish";
			$res["portuguese"] = "portuguese";
			//$res["portuguese-br"] = "portuguese-br";
			$res["romanian"] = "romanian";
			$res["russian"] = "russian";
			$res["simplified_chinese"] = "simplified_chinese";
			//$res["slovak"] = "slovak";
			$res["spanish"] = "spanish";
			$res["swedish"] = "swedish";
			//$res["thai"] = "thai";
			$res["turkish"] = "turkish";
			$res["ukrainian"] = "ukrainian";
		}
		return $res;
	}


	public static function setLanguage() {
		$lang =Get::gReq('set_lang', DOTY_STRING, '');
		if (!empty($lang)) {
			$_SESSION['sel_lang']=$lang;
			self::init();
			StepManager::loadCurrentStep();
			ob_clean();
			$res =array();
			$res['intro']=Lang::t('_INSTALLER_INTRO_TEXT');
			$res['title']=Lang::t('_INTRODUCTION');
			$res['btn']=Lang::t('_NEXT').' &raquo;';
			require_once(_base_.'/lib/lib.json.php');
			$json = new Services_JSON();
			echo $json->encode($res);
			session_write_close();
			die();
		}
		if (!isset($_SESSION['sel_lang'])) {
			$_SESSION['sel_lang']='english';
		}
	}

}

?>