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

/**
 * @package admin-core
 * @subpackage configuration
 * @author 	Pirovano Fabio (fabio@docebo.com)
 * @version $Id: class.conf.php 113 2006-03-08 18:08:42Z ema $
 **/

class Config_Framework {

	private $table = false;

	/**
	 * class constructor
	 */
	public function __construct() {

		$this->table = $GLOBALS['prefix_fw'].'_setting';
	}

	/**
	 * @return 	array 	this array contains association trought id and name of the regroup units
	 *
	 * @access 	public
	 */
	function getRegroupUnit($with_invisible = false) {

		$lang =& DoceboLanguage::createInstance('configuration', 'framework');

		$query_regroup = "
		SELECT DISTINCT regroup
		FROM ".$this->table."
		WHERE 1 "
		.( $with_invisible ? " AND hide_in_modify = '0' " : '' )
		."ORDER BY regroup ";
		$re_regroup = sql_query($query_regroup);

		$names = array(
			1/*'main'*/		=> 'Main options',
			2/*'view'*/		=> 'Display options',
			3/*'user'*/		=> 'User',
			4/*'lms'*/		=> 'E-Learning',
			//5/*'cms'*/		=> 'Cms',
			6/*'video'*/	=> 'Videoconference',
			7/*'auth'*/		=> 'Authentication',
			8/*'advanced'*/	=> 'Advanced',
			9/*'api'*/		=> 'Api',
			10/*'google'*/	=> 'Google',
			11/*'sms'*/		=> 'Sms',
		);

		$descr = array(
			1	=> 'Here you can find the main options for the platform.',
			2	=> 'Qui puoi modificare le impostazioni che influenzano la visualizzazione dei dati, ad esempio il tema principale, la lingua di default, il numero di elementi per pagina.',
			3	=> 'Qui puoi modificare tutte le opzioni che influenzano i dettagli dell\'utente, i metodi di autoregistrazione, le politiche reltive alle password e via dicendo.',
			4	=> 'Qui puoi modificare le opzioni specifiche riguardanti l\'e-learning.',
			//5	=> 'Qui puoi modificare le opzioni specifiche riguardanti il portale.',
			6	=> 'Qui puoi impostare l\'interfacciamento con i sistemi di Videoconferenza supportati per il SSO e la gestione.',
			7	=> 'Qui puoi modificare le impostazioni più particolari legate a Docebo.',
			8	=> 'Qui puoi configurare e attivare il SSO con e da altri applicativi.',
			9	=> 'Qui puoi attivare e configurare le API Soap e Rest per l\'interscambio dati con altri aplicativi.',
			10	=> 'Qui puoi impostare l\'interfacciamento con i moduli Google quali, ad esempio, codici per Google Analytics o codice applicazione per Google Maps e altri.',
			11	=> 'Qui puoi impostare il gateway per l\'invio degli sms e visualizzare il credito residuo.',

		);

		$group = array();
		while(list($id_regroup) = sql_fetch_row($re_regroup))  {

			$group[$id_regroup] = array(
				'name' => /*$lang->def('_CONF_NAME_'.$id_regroup).*/$names[$id_regroup],
				'descr' =>  /*$lang->def('_CONF_DESCR_'.$id_regroup).*/$descr[$id_regroup]
			);
		}
		
		return $group;
	}

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
				$html .= Form::getCheckbox(	$info['name'],
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

		$html .= Form::getCloseFieldset();
		$html .= Form::getDropdown($lang->def('_HOME_PLATFORM'),
									'platform_in_home',
									'platform_in_home',
									$code_list_home,
									$plat_man->getHomePlatform() );
		return $html;
	}

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

	function _maskTemplateManager() {

		require_once(_base_.'/lib/lib.form.php');
		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		require_once(_base_.'/lib/lib.table.php');

		$lang =& DoceboLanguage::createInstance('configuration', 'framework');
		$field_man = new FieldList();

		$html = '';
		if(isset($_POST['save_and_refresh'])) {

			if(!sql_query("
			UPDATE ".$this->table."
			SET param_value = '".$_POST['templ_use_field']."'
			WHERE pack = 'main' AND param_name = 'templ_use_field'")) {

				$html .= getErrorUi('_ERROR_WHILE_SAVING_NEW_FIELD');
			} else {

				setTemplate($_POST['templ_use_field']);
			}
		}

		$drop_field = array();
		$drop_field = $field_man->getFlatAllFields(false, 'dropdown');
		$drop_field[0] = $lang->def('_NO');

		$html .= Form::getDropdown($lang->def('_TEMPL_USE_FIELD'),
									'templ_use_field',
									'templ_use_field',
									$drop_field,
									Get::sett('templ_use_field') );

		$html .= Form::getButton('save_and_refresh', 'save_and_refresh', $lang->def('_SAVE_AND_REFRESH'));

		if(Get::sett('templ_use_field') != 0) {

			$field_obj =& $field_man->getFieldInstance(Get::sett('templ_use_field'));
			if($field_obj === NULL) return $html.getErrorUi('_ERROR_WITH_THIS_FIELD');

			$assignement = array();
			$query_template_assigned = "
			SELECT ref_id, template_code
			FROM ".$GLOBALS['prefix_fw']."_field_template
			WHERE id_common = '".Get::sett('templ_use_field')."'";
			$re_templ_assigned = sql_query($query_template_assigned);
			while(list($ref_id, $template_code) = sql_fetch_row($re_templ_assigned)) {
				$assignement[$ref_id] = $template_code;
			}

			$son_value 			= $field_obj->getAllSon();
			$template_list 		= getTemplateList(true);
			$default_template 	= getDefaultTemplate();

			$tb_son = new Table(	0,
									$lang->def('_ASSIGN_DROPDOWN_VALUE_TEMPLATE'),
									$lang->def('_ASSIGN_DROPDOWN_VALUE_TEMPLATE_SUMMARY'));

			$cont_h = array($lang->def('_VALUE'), $lang->def('_TEMPLATE_VALUE'));
			$type_h = array('','');
			$tb_son->setColsStyle($type_h);
			$tb_son->addHead($cont_h);
			while(list($id_son, $drop_son_name) = each($son_value)) {

				$cont = array(
					'<label for="template_selected_'.$id_son.'">'.$drop_son_name.'</label>',
					Form::getInputDropdown(	'dropdown',
											'template_selected_'.$id_son,
											'template_selected['.$id_son.']',
											$template_list,
											( isset($assignement[$id_son]) && isset($template_list[$assignement[$id_son]])
												? $assignement[$id_son]
												: $default_template ),
											''
										)
				);
				$tb_son->addBody($cont);
			}
			$html .= $tb_son->getTable();
		}

		return $html;
	}

	function _saveTemplateManager() {

		$re = true;
		if(!isset($_POST['template_selected'])) return true;

		$query_template_assigned = "
		SELECT ref_id, template_code
		FROM ".$GLOBALS['prefix_fw']."_field_template
		WHERE id_common = '".Get::sett('templ_use_field')."'";
		$re_templ_assigned = sql_query($query_template_assigned);
		while(list($ref_id, $template_code) = sql_fetch_row($re_templ_assigned)) {
			$assignement[$ref_id] = $template_code;
		}

		while(list($ref_id, $template_code) = each($_POST['template_selected'])) {

			if(isset($assignement[$ref_id])) {

				if(!sql_query("
				UPDATE ".$GLOBALS['prefix_fw']."_field_template
				SET template_code = '".$template_code."'
				WHERE id_common = '".Get::sett('templ_use_field')."'
					AND ref_id = '".$ref_id."'")) $re = false;
			} else {

				if(!sql_query("
				INSERT INTO ".$GLOBALS['prefix_fw']."_field_template
				( id_common, ref_id, template_code ) VALUES (
					'".Get::sett('templ_use_field')."',
					'".$ref_id."',
					'".$template_code."'
				)")) $re = false;
			}
		}
		return $re;
	}

	/**
	 * @return 	string 	contains the displayable information for a selected group
	 *
	 * @access 	public
	 */
	function getPageWithElement($regroup) {

		require_once(_base_.'/lib/lib.form.php');

		if($regroup == 'templ_man') return $this->_maskTemplateManager();

		elseif($regroup == 'suiteman') return $this->_maskSuiteManager();

		$lang =& DoceboLanguage::createInstance('configuration', 'framework');

		$reSetting = sql_query("
		SELECT param_name, param_value, value_type, max_size
		FROM ".$this->table."
		WHERE regroup = '".$regroup."' AND
			hide_in_modify = '0'
		ORDER BY regroup, pack, sequence");
		$html = '';
		while(list( $var_name, $var_value, $value_type, $max_size ) = sql_fetch_row( $reSetting ) ) {

			$i_after = ' <span class="ico-tooltip" id="tt_target_'.$var_name.'" title="'.$lang->def('_CONF_DESCR_'.strtoupper($var_name)).'">info</span>';
			switch( $value_type ) {
				case "register_type" : {
					//on off

					$html .= Form::getOpenCombo( $lang->def('_'.strtoupper($var_name)) )
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

				case "register_tree" :{

					$register_possible_option = array(
						'off' => $lang->def('_DONT_USE_TREE_REGISTRATION'),
						'manual_insert' => $lang->def('_USE_WITH_MANUALEINSERT'),
						'selection' => $lang->def('_USE_WITH_SELECTION')
					);

					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
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
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$fields,
												$var_value,
												$i_after);
				} break;
				case "save_log_attempt" : {
					//on off

					$html .= Form::getOpenCombo( $lang->def('_'.strtoupper($var_name)) )
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
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$langs,
												array_search($var_value, $langs),
												$i_after);

				};break;
				case "template" : {
					//drop down template
					$templ = getTemplateList();
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$templ,
												array_search($var_value, $templ),
												$i_after);
				};break;
				case "hteditor" : {
					//drop down hteditor
					$ht_edit = getHTMLEditorList();
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
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
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
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
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
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
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
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
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$options,
												$var_value,
												$i_after);
				} break;
				case "menuvoice" :
				case "menuvoice_course_public" :

				case "layout_chooser" : {
					//drop down hteditor
					$layout = array(
						'left' => Lang::t('_LAYOUT_LEFT'),
						'over' => Lang::t('_LAYOUT_OVER'),
						'right' => Lang::t('_LAYOUT_RIGHT'));
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
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
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$layout,
												$var_value,
												$i_after);
				};break;

				case "tablist_coursecatalogue" : {

					$lang_c 	=& DoceboLanguage::createInstance('catalogue', 'lms');

					$tab_selected = Util::unserialize(urldecode($var_value));

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

						$html .= Form::getCheckbox( $name , 'tablist_'.$tab_code, 'tablist['.$tab_code.']', 1, isset($tab_selected[$tab_code]), '', $i_after);
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

					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$tab_list,
												$var_value,
												$i_after);

				};break;

				case "tablist_mycourses" : {
				  //$var_value=deformat($var_value);
				  $arr_value = explode(',',$var_value);
				  //$arr_value=array();

				  $tab_list=array();
				  $tab_list[''] = $lang->def('_MYCOURSES_NOTUSED');
				  $tab_list['status'] = $lang->def('_STATUS');
				  $tab_list['name'] = $lang->def('_NAME');
				  $tab_list['code'] = $lang->def('_CODE');

				  $html .= '<div class="form_line_l"><p>'.
						   '<label class="floating">'.$lang->def('_'.strtoupper($var_name)).'</label></p>';
							for ($i=0; $i<3; $i++) {
							$html .= Form::getInputDropdown('dropdown' , $var_name.'_'.$i,
													"mycourses[$i]", $tab_list,
													(isset($arr_value[$i]) ? $arr_value[$i] : '' ), '');
							}
							$html .= $i_after
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
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$fields,
												$var_value,
												$i_after);
				} break;
				case "rest_auth_sel_method": {
					$value_set = array(
						$lang->def('_REST_AUTH_UCODE')=>0,
						$lang->def('_REST_AUTH_TOKEN')=>1
					);
					$html .= Form::getRadioSet($lang->def('_REST_AUTH_SEL_METHOD'), $var_name, 'option['.$var_name.']', $value_set, $var_value, $i_after);
				} break;
				
				// Common types
				case "password" : {
					$html .= Form::getPassword( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$max_size,
												$var_value,
												$i_after );
				} break;
				case "textarea" : {
					$html .= Form::getSimpletextarea( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$var_value,
												$i_after);
				} break;
				case "check" : {
					$html .= Form::getCheckbox( $lang->def('_'.strtoupper($var_name)) , $var_name, 'option['.$var_name.']', 1, ($var_value == 1), '', '', $i_after.' ');
				};break;
				case "enum" : {
					$html .= Form::getCheckbox( $lang->def('_'.strtoupper($var_name)) , $var_name.'_on', 'option['.$var_name.']', 'on', ($var_value ==  'on'), '', '', $i_after.' ');
				};break;
				default : {
					//string or int
					$html .= Form::getTextfield( $lang->def('_'.strtoupper($var_name)),
												$var_name,
												'option['.$var_name.']',
												$max_size,
												$var_value,
												false,
												$i_after );
				}
			}
		}
		return $html;
	}

	/**
	 * @return 	bool 	true if the operation was successfull false otherwise
	 *
	 * @access 	public
	 */
	function saveElement($regroup) {

		if($regroup == 'templ_man') 	return $this->_saveTemplateManager();
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
					$new_value = urlencode(Util::serialize($tab_selected));
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