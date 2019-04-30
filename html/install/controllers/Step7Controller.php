<?php

require_once(dirname(__FILE__) . '/StepController.php');

require_once(__DIR__ . '/../../vendor/autoload.php');

Class Step7Controller extends StepController
{

	var $step = 7;


	public function ajax_validate()
	{
		$err = 0;
		$res = array('success' => false, 'err' => array(), 'ok' => array(), 'msg' => '');


		$op = Get::pReq('op', DOTY_STRING);
		// ---

		$use_smtp_database = Get::pReq('use_smtp_database', DOTY_STRING);
		$use_smtp = Get::pReq('use_smtp', DOTY_STRING);
		$smtp_host = Get::pReq('smtp_host', DOTY_STRING);
		$smtp_port = Get::pReq('smtp_port', DOTY_STRING);
		$smtp_secure = Get::pReq('smtp_secure', DOTY_STRING);
		$smtp_auto_tls = Get::pReq('smtp_auto_tls', DOTY_STRING);
		$smtp_user = Get::pReq('smtp_user', DOTY_STRING);
		$smtp_pwd = Get::pReq('smtp_pwd', DOTY_STRING);


		if ($use_smtp === 'on') {

			if (!$this->checkConnection($smtp_host, $smtp_port, $smtp_secure, $smtp_auto_tls, $smtp_user, $smtp_pwd)) {
				$err++;
				array_push($res['err'], 'smtp_host', 'smtp_port', 'smtp_secure', 'smtp_auto_tls', 'smtp_user', 'smtp_pwd');
				array_push($res['ok'], 'use_smtp');
				$res['msg'] = Lang::t('_CANT_CONNECT_SMTP');
			}
		}

		$res['success'] = ($err > 0 ? false : true);

		$this->ajax_out($res);
	}

	function checkConnection($smtp_host, $smtp_port, $smtp_secure, $smtp_auto_tls, $smtp_user, $smtp_pwd)
	{

		$mail = new PHPMailer();

		$mail->Host = $smtp_host;
		$mail->Port = $smtp_port;

		if (!empty($smtp_user)) {
			$mail->SMTPAuth = true;     // turn on SMTP authentication
			$mail->Username = $smtp_user;  // SMTP username
			$mail->Password = $smtp_pwd; // SMTP password
		} else {
			$mail->SMTPAuth = false;     // turn on SMTP authentication
		}

		$mail->SMTPSecure = $smtp_secure;
		$mail->SMTPAutoTLS = $smtp_auto_tls === 'on';

		if ($mail->smtpConnect()) {
			$mail->smtpClose();
			return true;
		} else {
			return false;
		}
	}

	public function validate()
	{
		$smtp_info = Get::pReq('smtp_info', DOTY_MIXED);

		if ($smtp_info['use_smtp_database'] === 'on') {
			$this->saveSettingsToDatabase($smtp_info);
			$_SESSION['smtp_info']['use_smtp_database'] = 'on';

		} else {
			$_SESSION['smtp_info'] = $smtp_info;

		}

		$this->saveConfig();

		return true;
	}

	private function saveConfig()
	{
		// ----------- Generating config file -----------------------------
		$config = "";
		$fn = _installer_ . "/data/config_template.php";

		$config = generateConfig($fn);

		$save_fn = _base_ . "/config.php";
		$saved = FALSE;
		if (is_writeable($save_fn)) {

			$handle = fopen($save_fn, 'w');
			if (fwrite($handle, $config)) $saved = TRUE;
			fclose($handle);

			@chmod($save_fn, 0644);
		}


		$_SESSION["config_saved"] = $saved;
	}

	private function saveSettingsToDatabase($smtpInfo)
	{
		DbConn::getInstance(false,
			[
				'db_type' => $_SESSION['db_info']['db_type'],
				'db_host' => $_SESSION['db_info']['db_host'],
				'db_user' => $_SESSION['db_info']['db_user'],
				'db_pass' => $_SESSION['db_info']['db_pass'],
				'db_name' => $_SESSION['db_info']['db_name']
			]
		);

		$queries[] = "INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('use_smtp', '" . $smtpInfo['use_smtp'] . "', 'on_off', 255, 'Use Smtp', 14, 1, 1, 0, '')";

		$queries[] = "INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_host', '" . $smtpInfo['smtp_host'] . "', 'string', 255, 'Smtp Host', 14, 2, 1, 0, '')";

		$queries[] = "INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_port', '" . $smtpInfo['smtp_port'] . "', 'string', 255, 'Smtp Port', 14, 3, 1, 0, '')";


		$queries[] = "INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_secure', '" . $smtpInfo['smtp_secure'] . "', 'string', 255, 'Smtp Secure', 14, 4, 1, 0, '')";

		$queries[] = "INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_user', '" . $smtpInfo['smtp_user'] . "', 'string', 255, 'Smtp User', 14, 5, 1, 0, '')";

		$queries[] = "INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_pwd', '" . $smtpInfo['smtp_pwd'] . "', 'string', 255, 'Smtp Password', 14, 6, 1, 0, '')";

		$queries[] = "INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_auto_tls','" . $smtpInfo['smtp_auto_tls'] . "', 'on_off', 255, 'Smtp Auto TLS', 14, 7, 1, 0, '')";

		$queries[] = "INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_debug', '0' , 'string', 255, 'Smtp Debug', 14, 8, 1, 0, '')";

		foreach ($queries as $query) {
			sql_query($query);
		}
	}

}


?>