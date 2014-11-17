<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

define("SMS_GROUP", 11);

class SettingAdm extends Model {

	protected $db;

	protected $table;

	public function  __construct() {
		$this->db = DbConn::getInstance();
		$this->table = $GLOBALS['prefix_fw'].'_setting';
	}

	public function getPerm()
	{
		return array(	'view' => 'standard/view.png');
	}

	/**
	 * @return 	array 	this array contains association trought id and name of the regroup units
	 *
	 * @access 	public
	 */
	function getRegroupUnit() {

		$lang =& DoceboLanguage::createInstance('configuration', 'framework');

		$re_regroup = sql_query("SELECT DISTINCT regroup
		FROM ".$this->table."
		WHERE hide_in_modify = '0'
		ORDER BY regroup ");

		$names = array(
			1	=> 'Main_options',
			3	=> 'User',
			4	=> 'ELearning',
			6	=> 'Videoconference',
			7	=> 'Ldap',
			8	=> 'Advanced',
			5	=> 'Ecommerce',
			9	=> 'Api_SSO',
			10	=> 'Google',
			11	=> 'Sms',
			12	=> 'Social',
		);

		$group = array();
		while(list($id_regroup) = sql_fetch_row($re_regroup))  {

			$group[$id_regroup] = $names[$id_regroup];
		}

		//$group['suite_man'] = 'suite_man';

		return $group;
	}

	function server_info() {

		$lang =& DoceboLanguage::createInstance('configuration', 'framework');

		$php_conf = ini_get_all();

		$intest = '<div>'
				.'<div class="label_effect">';

		$html = '<div class="conf_line_title">'.$lang->def('_SERVERINFO').'</div>'
			.config_line($lang->def('_SERVER_ADDR'), $_SERVER['SERVER_ADDR'] )
			.config_line($lang->def('_SERVER_PORT'), $_SERVER['SERVER_PORT'] )
			.config_line($lang->def('_SERVER_NAME'), $_SERVER['SERVER_NAME'] )
			.config_line($lang->def('_SERVER_ADMIN'), $_SERVER['SERVER_ADMIN'] )
			.config_line($lang->def('_SERVER_SOFTWARE'), $_SERVER['SERVER_SOFTWARE'] )
			.'<br />'

			.'<div class="conf_line_title">'.$lang->def('_SERVER_MYSQL').'</div>'
			.config_line($lang->def('_MYSQL_VERS'), mysql_get_server_info())
			.'<br />'

			.'<div class="conf_line_title">'.$lang->def('_PHPINFO').'</div>'
			.config_line($lang->def('_PHPVERSION'), phpversion())
			.config_line($lang->def('_SAFEMODE'), ( $php_conf['safe_mode']['local_value']
				? $lang->def('_ON')
				: $lang->def('_OFF') ))
			.config_line($lang->def('_REGISTER_GLOBAL'), ( $php_conf['register_globals']['local_value']
				? $lang->def('_ON')
				: $lang->def('_OFF') ))
			.config_line($lang->def('_MAGIC_QUOTES_GPC'), ( $php_conf['magic_quotes_gpc']['local_value']
				? $lang->def('_ON')
				: $lang->def('_OFF') ))
			.config_line($lang->def('_UPLOAD_MAX_FILESIZE'), $php_conf['upload_max_filesize']['local_value'])
			.config_line($lang->def('_POST_MAX_SIZE'), $php_conf['post_max_size']['local_value'])
			.config_line($lang->def('_MAX_EXECUTION_TIME'), $php_conf['max_execution_time']['local_value'].'s' )
			.config_line($lang->def('_LDAP'), ( extension_loaded('ldap')
				? $lang->def('_ON')
				: '<span class="font_red">'.$lang->def('_OFF').' '.$lang->def('_USEFULL_ONLY_IF').'</span>') )
			.config_line($lang->def('_PHP_TIMEZONE'), @date_default_timezone_get() );

		if(version_compare(phpversion(), "5.0.0") == -1) {

			echo config_line($lang->def('_DOMXML'), ( extension_loaded('domxml')
					? $lang->def('_ON')
					: '<span class="font_red">'.$lang->def('_OFF').' ('.$lang->def('_NOTSCORM').')</span>' ));
		}
		if (version_compare(phpversion(), "5.2.0", ">"))
		{
			echo config_line($lang->def('_ALLOW_URL_INCLUDE'), ( $php_conf['allow_url_include']['local_value']
				? '<span class="font_red">'.$lang->def('_ON').'</span>'
				: $lang->def('_OFF') ));
		}
		if(Get::cfg('uploadType') == 'ftp') {

			if(function_exists("ftp_connect")) {

				require_once( _base_.'/lib/lib.upload.php' );
				$re_con = sl_open_fileoperations();
				echo config_line($lang->def('_UPLOADFTP'), ( $re_con
					? $lang->def('_FTPOK')
					: '<span class="font_red">'.$lang->def('_FTPERR').'</span>') );
				if($re_con) sl_close_fileoperations();
			} else {

				echo config_line($lang->def('_UPLOADFTP'), '<span class="font_red">'.$lang->def('_FTPERR').'</span>' );
			}
		}
		echo '<div class="nofloat"></div><br />';
		return $html;
	}

	/**
	 * Draw the mask for the template manager, i hope to remove it from here
	 * @return <string>
	 */
	function _maskSuiteManager() {

		require_once(_base_.'/lib/lib.form.php');
		require_once(_base_.'/lib/lib.platform.php');

		$lang =& DoceboLanguage::createInstance('configuration', 'framework');
		$plat_man =& PlatformManager::createInstance();

		$all_platform 		= $plat_man->getPlatformsInfo();
		$code_list_home 	= array();

		$html = Form::getOpenFieldset($lang->def('_LOAD_UNLOAD_PLATFORM'));
		reset($all_platform);
		while(list($code, $info) = each($all_platform)) {
			if($info['hidden_in_config'] != 'true') {

				$code = $info['platform'];
				echo Form::getCheckbox(	$info['name'],
												'activate_platform_'.$code,
												'activate_platform['.$code.']',
												1,
												( $info['is_active'] == 'true' ),
												( $info['mandatory'] == 'true' ? ' disabled="disabled"' : '' ) );

				if($info['is_active'] == 'true') $code_list_home[$code] = $info['name'];
			}
		}
		unset($code_list_home['scs']);
		unset($code_list_home['framework']);

		echo Form::getCloseFieldset();
		echo Form::getDropdown($lang->def('_HOME_PLATFORM'),
									'platform_in_home',
									'platform_in_home',
									$code_list_home,
									$plat_man->getHomePlatform() );
		return '';
	}

	/**
	 * Draw the mask for the template manager, i hope to remove it from here
	 * @return <string>
	 */
	function _saveSuiteManager() {

		require_once(_base_.'/lib/lib.platform.php');

		$plat_man =& PlatformManager::createInstance();

		$all_platform 		= $plat_man->getPlatformsInfo();
		$re = true;

		reset($all_platform);
		while(list($code, $info) = each($all_platform)) {
			if($info['hidden_in_config'] != 'true') {
				$code = $info['platform'];
				if(isset($_POST['activate_platform'][$code])) {

					$re &= $plat_man->activatePlatform($code);
					$code_list_home[$code] = $info['name'];
				} elseif($info['mandatory'] == 'false') $re &= $plat_man->deactivatePlatform($code);
			}
		}
		if(isset($code_list_home[$_POST['platform_in_home']])) $re &= $plat_man->putInHome($_POST['platform_in_home']);
		return $re;
	}

	/**
	 * REturnes the displayable information for a selected group
	 * @return string
	 */
	function printPageWithElement($regroup) {

		require_once(_base_.'/lib/lib.form.php');

		//if($regroup == 'templ_man') return $this->_maskTemplateManager();

		if($regroup == 'suite_man') return $this->_maskSuiteManager();

		$lang =& DoceboLanguage::createInstance('configuration', 'framework');

		$reSetting = sql_query("
		SELECT pack, param_name, param_value, value_type, max_size
		FROM ".$this->table."
		WHERE regroup = '".$regroup."' AND
			hide_in_modify = '0'
		ORDER BY pack, sequence");

		$prev_pack = '';
		while(list($pack, $var_name, $var_value, $value_type, $max_size ) = sql_fetch_row( $reSetting ) ) {

			if($prev_pack != $pack && $pack != 'main' && $pack != '0') {
				echo '<br/><h3>'.Lang::t('_'.strtoupper($pack), 'configuration').'</h3>';
			}
			$prev_pack = $pack;
			$i_after = '';
			//$i_after = ' <span class="ico-tooltip" id="tt_target_'.$var_name.'" title="'.$lang->def('_CONF_DESCR_'.strtoupper($var_name)).'">info</span>';
			switch( $value_type ) {
				case "register_type" : {
					//on off
					echo Form::getOpenCombo( $lang->def('_'.strtoupper($var_name)) )
							.Form::getLineRadio('', 'label_bold', $lang->def('_REGISTER_TYPE_SELF'), $var_name.'_self', 'option['.$var_name.']',
								'self', ($var_value == 'self'))
                            .Form::getLineRadio('', 'label_bold', $lang->def('_REGISTER_TYPE_SELF_OPTIN'), $var_name.'_self_optin', 'option['.$var_name.']',
								'self_optin', ($var_value == 'self_optin'))
							.Form::getLineRadio('', 'label_bold', $lang->def('_REGISTER_TYPE_MODERATE'), $var_name.'_moderate', 'option['.$var_name.']',
								'moderate', ($var_value == 'moderate'))
							.Form::getLineRadio('', 'label_bold', $lang->def('_REGISTER_TYPE_ADMIN'), $var_name.'_admin', 'option['.$var_name.']',
								'admin', ($var_value == 'admin'))

							.Form::getCloseCombo($i_after);
				};break;
				case "registration_code_type" : {
					//on off

					echo Form::getOpenCombo( $lang->def('_'.strtoupper($var_name)) )

							.Form::getLineRadio('', 'label_bold', $lang->def('_NONE'), $var_name.'_0', 'option['.$var_name.']',
								'0', ($var_value == '0'))
							.Form::getLineRadio('', 'label_bold', $lang->def('_ASK_FOR_MANUAL_TREE_CODE'), $var_name.'_tree_man', 'option['.$var_name.']',
								'tree_man', ($var_value == 'tree_man'))
							.Form::getLineRadio('', 'label_bold', $lang->def('_ASK_FOR_DROPDOWN_TREE_CODE'), $var_name.'_tree_drop', 'option['.$var_name.']',
								'tree_drop', ($var_value == 'tree_drop'))
							.Form::getLineRadio('', 'label_bold', $lang->def('_ASK_FOR_TREE_COURSE_CODE'), $var_name.'_tree_course', 'option['.$var_name.']',
								'tree_course', ($var_value == 'tree_course'))
							.Form::getLineRadio('', 'label_bold', $lang->def('_ASK_FOR_CODE_MODULE'), $var_name.'_code_module', 'option['.$var_name.']',
								'code_module', ($var_value == 'code_module'))
							//Uncomment for the custom folder association during user registration
							//.Form::getLineRadio('', 'label_bold', $lang->def('_CUSTOM_CODE'), $var_name.'_custom', 'option['.$var_name.']',
							//	'custom', ($var_value == 'custom'))

							.Form::getCloseCombo($i_after);
				};break;
				/*
				case "register_tree" :{

					$register_possible_option = array(
						'off' => $lang->def('_DONT_USE_TREE_REGISTRATION'),
						'manual_insert' => $lang->def('_USE_WITH_MANUALEINSERT'),
						'selection' => $lang->def('_USE_WITH_SELECTION')
					);

					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$register_possible_option,
												$var_value,
												$i_after);
				};break;
				case "field_tree" : {

					require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

					$fl = new FieldList();
					$all_fields = $fl->getAllFields(false);
					$fields[0] = $lang->def('_NO_VALUE');
					foreach($all_fields as $key=>$val) {
						$fields[$val[FIELD_INFO_ID]] = $val[FIELD_INFO_TRANSLATION];
					}
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$fields,
												$var_value,
												$i_after);
				} break;*/
				case "save_log_attempt" : {
					//on off

					echo Form::getOpenCombo( $lang->def('_'.strtoupper($var_name)) )
							.Form::getLineRadio('', 'label_bold', $lang->def('_SAVE_LA_ALL'), $var_name.'_all', 'option['.$var_name.']',
								'all', ($var_value == 'all'))
							.Form::getLineRadio('', 'label_bold', $lang->def('_SAVE_LA_AFTER_MAX'), $var_name.'_after_max', 'option['.$var_name.']',
								'after_max', ($var_value == 'after_max'))
							.Form::getLineRadio('', 'label_bold', $lang->def('_NO'), $var_name.'_no', 'option['.$var_name.']',
								'no', ($var_value == 'no'))
							.Form::getCloseCombo($i_after);
				};break;
				case "language" : {
					//drop down language
					$langs = Docebo::langManager()->getAllLangCode();
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$langs,
												array_search($var_value, $langs),
												$i_after);

				};break;
				case "template" : {
					//drop down template
					$templ = getTemplateList();
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$templ,
												array_search($var_value, $templ),
												$i_after);
				};break;
				case "hteditor" : {
					//drop down hteditor
					$ht_edit = getHTMLEditorList();
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$ht_edit,
												$var_value,
												$i_after);
				};break;
				case "layout_chooser" : {
					//drop down hteditor
					$layout = array(
						'left' => Lang::t('_LAYOUT_LEFT'),
						'over' => Lang::t('_LAYOUT_OVER'),
						'right' => Lang::t('_LAYOUT_RIGHT'));
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$layout,
												$var_value,
												$i_after);
				};break;
				case "pubflow_method_chooser" : {
					//drop down hteditor
					$options = array(
						'onestate' => Lang::t('_PUBFLOW_ONESTATE'),
						'twostate' => Lang::t('_PUBFLOW_TWOSTATE'),
						'advanced' => Lang::t('_PUBFLOW_ADVANCED'));
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$options,
												$var_value,
												$i_after);
				};break;
				case "field_select" : {
					require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

					$fl=new FieldList();
					$all_fields=$fl->getAllFields();
					$fields=array();
					foreach($all_fields as $key=>$val) {
						$fields[$val[FIELD_INFO_ID]]=$val[FIELD_INFO_TRANSLATION];
					}
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$fields,
												$var_value,
												$i_after);
				} break;
				case "sel_sms_gateway" : {
					$options = array(
						'0' => Lang::t('_SMS_GATEWAY_AUTO'),
						'1' => Lang::t('_SMS_GATEWAY_1'),
						'2' => Lang::t('_SMS_GATEWAY_2'),
						'3' => Lang::t('_SMS_GATEWAY_3'),
						'4' => Lang::t('_SMS_GATEWAY_4'));
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$options,
												$var_value,
												$i_after);
				} break;
				case "layout_chooser" : {
					//drop down hteditor
					$layout = array(
						'left' => Lang::t('_LAYOUT_LEFT'),
						'over' => Lang::t('_LAYOUT_OVER'),
						'right' => Lang::t('_LAYOUT_RIGHT'));
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$layout,
												$var_value,
												$i_after);
				};break;
				case "grpsel_chooser" : {
					$layout = array(
						'group' => $lang->def('_GROUPS'),
						'orgchart' => $lang->def('_ORGCHART'));
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$layout,
												$var_value,
												$i_after);
				};break;
/*
				case "tablist_coursecatalogue" : {

					$lang_c 	=& DoceboLanguage::createInstance('catalogue', 'lms');

					$tab_selected = unserialize(urldecode($var_value));

					$tab_list = array(
						'time' 		=> $lang_c->def('_TAB_VIEW_TIME'),
						'category' 	=> $lang_c->def('_TAB_VIEW_CATEGORY'),
						'all' 		=> $lang_c->def('_ALL')
					);
					if(Get::sett('use_coursepath') == '1') {
						$tab_list['pathcourse'] = $lang_c->def('_COURSEPATH');
					}
					if(Get::sett('use_social_courselist') == 'on') {
						$tab_list['mostscore'] 	= $lang_c->def('_TAB_VIEW_MOSTSCORE');
						$tab_list['popular'] 	= $lang_c->def('_TAB_VIEW_MOSTPOPULAR');
						$tab_list['recent'] 	= $lang_c->def('_TAB_VIEW_RECENT');
					}

					foreach($tab_list as $tab_code => $name) {

						echo Form::getCheckbox( $name , 'tablist_'.$tab_code, 'tablist['.$tab_code.']', 1, isset($tab_selected[$tab_code]), '', $i_after);
					}

				};break;

				case "first_coursecatalogue_tab" : {

					$lang_c 	=& DoceboLanguage::createInstance('catalogue', 'lms');

					$tab_list = array(
						'time' 		=> $lang_c->def('_TAB_VIEW_TIME'),
						'category' 	=> $lang_c->def('_TAB_VIEW_CATEGORY'),
						'all' 		=> $lang_c->def('_ALL')
					);
					if(Get::sett('use_coursepath') == '1') {
						$tab_list['pathcourse'] = $lang_c->def('_COURSEPATH');
					}
					if(Get::sett('use_social_courselist') == 'on') {
						$tab_list['mostscore'] 	= $lang_c->def('_TAB_VIEW_MOSTSCORE');
						$tab_list['popular'] 	= $lang_c->def('_TAB_VIEW_MOSTPOPULAR');
						$tab_list['recent'] 	= $lang_c->def('_TAB_VIEW_RECENT');
					}

					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$tab_list,
												$var_value,
												$i_after);

				};break;
*/
				case "tablist_mycourses" : {
				  //$var_value=deformat($var_value);
				  $arr_value = explode(',',$var_value);
				  //$arr_value=array();

				  $tab_list=array();
				  $tab_list[''] = $lang->def('_NONE');
				  $tab_list['status'] = $lang->def('_STATUS');
				  $tab_list['name'] = $lang->def('_NAME');
				  $tab_list['code'] = $lang->def('_CODE');

				  echo '<div class="form_line_l"><p>'.
						   '<label class="floating">'.$lang->def('_'.strtoupper($var_name)).'</label></p>';
							for ($i=0; $i<3; $i++) {
							echo Form::getInputDropdown('dropdown' , $var_name.'_'.$i,
													"mycourses[$i]", $tab_list,
													(isset($arr_value[$i]) ? $arr_value[$i] : '' ), '');
							}
							echo $i_after
								.'</div>';
				};break;
				case "point_field" : {
					require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
					$fl=new FieldList();
					$all_fields=$fl->getAllFields();
					$fields[0]=$lang->def('_NO_VALUE');
					foreach($all_fields as $key=>$val) {
						$fields[$val[FIELD_INFO_ID]]=$val[FIELD_INFO_TRANSLATION];
					}
					echo Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$fields,
												$var_value,
												$i_after);
				} break;
				case "rest_auth_sel_method": {
					$value_set = array(
						$lang->def('_REST_AUTH_CODE')=>0,
						$lang->def('_REST_AUTH_TOKEN')=>1
					);
					echo Form::getRadioSet($lang->def('_REST_AUTH_METHOD'), $var_name, 'option['.$var_name.']', $value_set, $var_value, $i_after);
				} break;

				// Common types
				case "password" : {
					echo Form::getPassword( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$max_size,
												$var_value,
												$i_after );
				} break;
				case "textarea" : {
					echo Form::getSimpletextarea( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$var_value,
												false,
												false,
												false,
												5,
												22,
												$i_after );

				} break;
				case "menuvoice" :
				case "menuvoice_course_public" :

				case "check" : {
					echo Form::getCheckbox( $lang->def('_'.strtoupper($var_name)) , $var_name, 'option['.$var_name.']', 1, ($var_value == 1), '', ' '.$i_after);
				};break;
				case "enum" : {
					echo Form::getCheckbox( $lang->def('_'.strtoupper($var_name)) , $var_name.'_on', 'option['.$var_name.']', 'on', ($var_value ==  'on'), '', ' '.$i_after);
				};break;
				default : {
					//string or int
					echo Form::getTextfield( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$max_size,
												$var_value,
												false,
												$i_after );
				}
			}
		}
		return;
	}

	/**
	 * Save the information recived for a group
	 * @return bool true if the operation was successfull false otherwise
	 */
	function saveElement($regroup) {

		if($regroup == 'suiteman') 		return $this->_saveSuiteManager();

		$reSetting = sql_query("
		SELECT param_name, value_type, extra_info
		FROM ".$this->table."
		WHERE regroup = '".$regroup."' AND hide_in_modify = '0'");

		$re = true;
		while( list( $var_name, $value_type, $extra_info ) = sql_fetch_row( $reSetting ) ) {

			switch( $value_type ) {

				case "menuvoice" : {

					require_once($GLOBALS['where_framework'].'/lib/lib.menu.php');
					$menu_man = new MenuManager();
					if( isset($_POST['option'][$var_name]) && $_POST['option'][$var_name] == 1) {

						$menu_man->addPerm(ADMIN_GROUP_GODADMIN, '/lms/admin'.$extra_info);
						$new_value = 1;
					} else {

						$menu_man->removePerm(ADMIN_GROUP_GODADMIN, '/lms/admin'.$extra_info);
						$new_value = 0;
					}
				};break;
				case "menuvoice_course_public" : {

					$after_reload_perm = true;
					require_once($GLOBALS['where_framework'].'/lib/lib.menu.php');
					$menu_man = new MenuManager();
					if( isset($_POST['option'][$var_name]) && $_POST['option'][$var_name] == 1) {

						$perm = explode(';', $extra_info);
						foreach($perm as $k => $perm_suffix) {
							$menu_man->addPerm('/oc_0', '/lms/course'.trim($perm_suffix));
						}
						$new_value = 1;
					} else {
						$perm = explode(';', $extra_info);
						foreach($perm as $k => $perm_suffix) {

							$menu_man->removePerm('/oc_0', '/lms/course'.trim($perm_suffix));

						}
						$new_value = 0;
					}
				};break;
				case "tablist_coursecatalogue" : {

					$tab_selected = array();
					foreach($_POST['tablist'] as $tab_code => $v) {

						$tab_selected[$tab_code] = 1;
					}
					$new_value = urlencode(serialize($tab_selected));
				};break;

				case "tablist_mycourses" : {
					$temp_arr=array();
					for ($i=0; $i<3; $i++) {
						$temp_var = $_POST['mycourses'][$i];
						if ($temp_var!='' && !in_array($temp_var,$temp_arr)) //avoid repeated params
							$temp_arr[] = $temp_var;
					}
					$new_value = implode(',' , $temp_arr);
				};break;

				//if is int cast it
				case "language" : {
					$lang = Docebo::langManager()->getAllLangCode();
					$new_value = $lang[$_POST['option'][$var_name]];
				};break;
				case "template" : {
					$templ = getTemplateList();
					$new_value = $templ[$_POST['option'][$var_name]];
				};break;
				case "int" : {
					$new_value = (int)$_POST['option'][$var_name];
				};break;
				//if is enum switch value to on or off
				case "enum" : {
					if( isset($_POST['option'][$var_name]) ) $new_value = 'on';
					else $new_value = 'off';
				};break;
				case "check" : {
					if( isset($_POST['option'][$var_name]) && $_POST['option'][$var_name] == 1) $new_value = 1;
					else $new_value = 0;
				};break;
				case "menuvoice" : {

					require_once($GLOBALS['where_framework'].'/lib/lib.menu.php');
					$menu_man = new MenuManager();
					if( isset($_POST['option'][$var_name]) && $_POST['option'][$var_name] == 1) {

						$menu_man->addPerm(ADMIN_GROUP_GODADMIN, '/framework/admin'.$extra_info);
						$new_value = 1;
					} else {

						$menu_man->removePerm(ADMIN_GROUP_GODADMIN, '/framework/admin'.$extra_info);
						$new_value = 0;
					}
				};break;
				//else simple assignament
				case "html" : {
					$new_value = $_POST['option'][$var_name];
					$new_value = strip_tags($_POST['option'][$var_name],'<a><b><i><sup>');
					$new_value = str_replace('"',"'",$new_value);
				};break;
				default : {
					$new_value = $_POST['option'][$var_name];
				}
			}
			if(!sql_query("UPDATE ".$this->table."
			SET param_value = '$new_value'
			WHERE param_name = '$var_name' AND regroup = '".$regroup."'")) {
				$re = false;
			}
		}

		return $re;
	}

}

?>