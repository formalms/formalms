<?php

require_once(dirname(__FILE__).'/StepController.php');

Class Step4Controller extends StepController {

	var $step=4;
	
	public function ajax_validate() {
		$err =0;
		$res =array('success'=>false, 'err'=>array(), 'ok'=>array(), 'msg'=>'');
		$op =Get::pReq('op', DOTY_STRING);
		// ---
		$site_url =Get::pReq('site_url', DOTY_STRING);
		// ---
		$db_type =Get::pReq('db_type', DOTY_STRING);
        $db_host =Get::pReq('db_host', DOTY_STRING);
		$db_name =Get::pReq('db_name', DOTY_STRING);
		$db_user =Get::pReq('db_user', DOTY_STRING);
		$db_pass =Get::pReq('db_pass', DOTY_STRING);
		if($db_pass==="_fromconfig"){
			if (file_exists(_base_.'/config.php')){
				define('IN_FORMA',true);
				include _base_.'/config.php';
				$db_pass = $cfg['db_pass'];
			}
		}
		// ---
		$upload_method =Get::pReq('upload_method', DOTY_STRING);
		// ---
		$ftp_host =Get::pReq('ftp_host', DOTY_STRING);
		$ftp_port =Get::pReq('ftp_port', DOTY_STRING);
		$ftp_user =Get::pReq('ftp_user', DOTY_STRING);
		$ftp_pass =Get::pReq('ftp_pass', DOTY_STRING);
		// ---

		if (empty($site_url)) { $res['err'][]='site_url'; $err++; }
		else $res['ok'][]='site_url';


		if (!empty($db_user)) {
			$err++;
			switch ($this->checkConnection($db_type, $db_host, $db_name, $db_user, $db_pass)) {
                case 'create_db': {
                    if ($this->checkStrictMode()) {
                        $err--;
                        array_push($res['ok'], 'db_host', 'db_name', 'db_user', 'db_pass', 'db_type');
                        $res['msg']=Lang::t('_DB_WILL_BE_CREATED');
                    }
                    else {
                        array_push($res['err'], 'db_host');
                        array_push($res['ok'], 'db_name', 'db_user', 'db_pass', 'db_type');
                        $res['msg']=Lang::t('_SQL_STRICT_MODE_WARN')." ".Lang::t('_DB_WILL_BE_CREATED');
                    }
                } break;
				case "ok": {
				    if ($this->checkDBEmpty($db_name)) {
				        if ($this->checkDBCharset()){
                            if ($this->checkStrictMode()) {
                                $err--;
                                array_push($res['ok'], 'db_host', 'db_name', 'db_user', 'db_pass', 'db_type');
                            } else {
                                array_push($res['err'], 'db_host');
                                array_push($res['ok'], 'db_name', 'db_user', 'db_pass', 'db_type');
                                $res['msg'] = Lang::t('_SQL_STRICT_MODE_WARN');
                            }
                        } else {
                            array_push($res['err'], 'db_name');
                            array_push($res['ok'], 'db_host', 'db_user', 'db_pass', 'db_type');
                            $res['msg']=Lang::t('_DB_NOT_UTF8');
                        }
                    } else {
                        array_push($res['err'], 'db_name');
                        array_push($res['ok'], 'db_host', 'db_user', 'db_pass', 'db_type');
                        $res['msg']=Lang::t('_DB_NOT_EMPTY');
                    }
				} break;
				case "err_connect": {
					array_push($res['err'], 'db_host', 'db_user', 'db_pass', 'db_type');
					array_push($res['ok'], 'db_name');
					$res['msg']=Lang::t('_CANT_CONNECT_WITH_DB');
				} break;
				case "err_db_sel": {
					array_push($res['err'], 'db_name');
					array_push($res['ok'], 'db_host', 'db_user', 'db_pass', 'db_type');
					$res['msg']=Lang::t('_CANT_SELECT_DB');
				} break;
			}
		}


		if ($upload_method == 'ftp') {
			$err++;
			switch($this->checkFtp($ftp_host, $ftp_port, $ftp_user, $ftp_pass)) {
				case "ok": {
					$err--;
					array_push($res['ok'], 'ftp_host', 'ftp_port', 'ftp_user', 'ftp_pass');
				} break;
				case "err_not_supp": {
					array_push($res['ok'], 'ftp_host', 'ftp_port', 'ftp_user', 'ftp_pass');
					$res['msg']=Lang::t('_YOUR_PHP_DOESNT_SUPPORT_FTP');
				} break;
				case "err_connect": {
					array_push($res['err'], 'ftp_host', 'ftp_port');
					array_push($res['ok'], 'ftp_user', 'ftp_pass');
					$res['msg']=Lang::t('_CANT_CONNECT_WITH_FTP');
				} break;
				case "err_login": {
					array_push($res['ok'], 'ftp_host', 'ftp_port');
					array_push($res['err'], 'ftp_user', 'ftp_pass');
					$res['msg']=Lang::t('_CANT_CONNECT_WITH_FTP');
				} break;
				case "err_param": {
					array_push($res['err'], 'ftp_user');
					$res['msg']=Lang::t('_CANT_CONNECT_WITH_FTP');
				} break;
			}
		}

		switch ($op) {
			case 'final_check': {
				if (empty($db_user)) {
					array_push($res['err'], 'db_host', 'db_user', 'db_pass', 'db_type');
					array_push($res['ok'], 'db_name');
					$res['msg']=Lang::t('_CANT_CONNECT_WITH_DB');
					$err++;
				}
			} break;
		}

		/*if (!empty($db_user) && $this->checkConnection($db_host, $db_name, $db_user, $db_pass) != 'ok') {
			array_push($res['err'], 'db_host', 'db_name', 'db_user', 'db_pass');
			$err++;
		}*/
		//else array_push($res['ok'], 'db_host', 'db_name', 'db_user', 'db_pass');

		$res['success'] =($err > 0 ? false : true);

		$this->ajax_out($res);
	}


	private function checkConnection($db_type, $db_host, $db_name, $db_user, $db_pass) {
		$res ='err_connect';

		$GLOBALS['db_link'] = DbConn::getInstance(false, array(
			"db_type" => $db_type, 
			"db_host" => $db_host, 
			"db_user" => $db_user, 
			"db_pass" => $db_pass
		));
		if ($GLOBALS['db_link']::$connected) {
		    if ($db_name==""){
		        return 'err_db_sel';
            }
            $res =sql_select_db($db_name, $GLOBALS['db_link']);
            if (!$res){
                return 'create_db';
            } else {
                return 'ok';
            }
		}
        return $res;
	}

	function checkDBEmpty($db_name) {
        $row=sql_query("SELECT COUNT(DISTINCT `table_name`) FROM `information_schema`.`columns` WHERE `table_schema` = '".$db_name."'", $GLOBALS['db_link']);
        list($count)=sql_fetch_row($row);
        return $count==0?true:false;
    }

    function checkDBCharset() {
        $row=sql_query("show variables like 'character_set_database'", $GLOBALS['db_link']);
        list(,$charset)=sql_fetch_row($row);
        return $charset=="utf8"?true:false;
    }

	function checkStrictMode() {
		$qtxt ="SELECT @@GLOBAL.sql_mode AS res";
		$q =sql_query($qtxt, $GLOBALS['db_link']);
		list($r1)=sql_fetch_row($q);
		$qtxt ="SELECT @@SESSION.sql_mode AS res";
		$q =sql_query($qtxt, $GLOBALS['db_link']);
		list($r2)=sql_fetch_row($q);
		$res =((strpos($r1.$r2, 'STRICT_') === false) ? true : false);

		return $res;
	}


	private function checkFtp($ftp_host, $ftp_port, $ftp_user, $ftp_pass) {

		if (empty($ftp_host) || empty($ftp_port) || empty($ftp_user) || empty($ftp_pass))
			return 'err_param';

		if (!function_exists("ftp_connect"))
			return 'err_not_supp';

		$timeout =10;

		$ftp =ftp_connect( $ftp_host, $ftp_port, $timeout );
		if( $ftp === FALSE ) {
			return 'err_connect';
		}
		
		return (ftp_login($ftp, $ftp_user, $ftp_pass) ? 'ok' : 'err_login');
	}


	public function validate() {
		$_SESSION['site_url'] =Get::pReq('site_url', DOTY_STRING);
		$_SESSION['db_info'] =Get::pReq('db_info');
		if($_SESSION['db_info']['db_pass']==="_fromconfig"){
			if (file_exists(_base_.'/config.php')){
				define('IN_FORMA',true);
				include _base_.'/config.php';
				$_SESSION['db_info']['db_pass'] = $cfg['db_pass'];
			}
		}
		$_SESSION['upload_method'] =Get::pReq('upload_method');
		$_SESSION['ul_info'] =Get::pReq('ul_info');

		return true;
	}

}


?>