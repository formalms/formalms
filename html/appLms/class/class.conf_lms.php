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
 * @package Configuration
 * @author 	Pirovano Fabio (fabio@docebo.com)
 * @version $Id: class.conf_lms.php 1002 2007-03-24 11:55:51Z fabio $
 **/

class Config_Lms extends Config {
	
	/**
	 * class constructor
	 */
	function Config_Lms($table = false) {
		
		parent::Config($table);
		
		if($table === false) $this->table = $GLOBALS['prefix_lms'].'_setting';
		else $this->table = $table;
	}
	
	/**
	 * @return 	array 	this array contains association trought id and name of the regroup units
	 *
	 * @access 	public
	 */
	function getRegroupUnit($with_invisible = false) {
		
		$lang =& DoceboLanguage::createInstance('admin_config', 'lms');
		
		$query_regroup = "
		SELECT DISTINCT regroup 
		FROM ".$this->table." "
		.( $with_invisible ? "  " : " WHERE hide_in_modify = '0' " ) 
		."ORDER BY regroup ";
		$re_regroup = sql_query($query_regroup);
		
		$group = array();
		while(list($id_regroup) = sql_fetch_row($re_regroup))  {
			
			$group[$id_regroup] = $lang->def('_RG_FW_'.$id_regroup);
		}
		return $group;
	}
	
	/**
	 * @return 	string 	contains the displayable information for a selected group
	 *
	 * @access 	public
	 */
	function getPageWithElement($regroup) {
		
		require_once(_base_.'/lib/lib.form.php');
		
		$lang =& DoceboLanguage::createInstance('configuration', 'lms');
		
		$reSetting = sql_query("
		SELECT param_name, param_value, value_type, max_size 
		FROM ".$this->table." 
		WHERE regroup = '".$regroup."' AND 
			hide_in_modify = '0'
		ORDER BY sequence");
		
		$html = '';
		while(list( $var_name, $var_value, $value_type, $max_size ) = sql_fetch_row( $reSetting ) ) {
			
			switch( $value_type ) {
				
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
												$var_value);
				} break;
				
				case "language" : {
					//drop down language
					$langs = Docebo::langManager()->getAllLangCode();
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)), 
												$var_name, 
												'option['.$var_name.']', 
												$langs, 
												array_search($var_value, $langs));
				
				
				
				};break;
				case "template" : {
					//drop down template
					$templ = getTemplateList();
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)), 
												$var_name, 
												'option['.$var_name.']', 
												$templ, 
												array_search($var_value, $templ));
				};break;
				case "hteditor" : {
					//drop down hteditor
					$ht_edit = getHTMLEditorList();
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)), 
												$var_name, 
												'option['.$var_name.']', 
												$ht_edit, 
												$var_value);
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
												$var_value);
				};break;
				case "sel_news" : {
					$mode = array(
						'off' => Lang::t('_DONT_SHOW'), 
						'link' => Lang::t('_SHOW_AS_LINK'), 
						'block' => Lang::t('_SHOW_AS_BLOCK'));
					$html .= Form::getDropdown( $lang->def('_'.strtoupper($var_name)), 
												$var_name, 
												'option['.$var_name.']', 
												$mode, 
												$var_value);
				};break;
				case "enum" : {
					//on off 
					$html .= Form::openFormLine()
							.Form::getInputCheckbox($var_name.'_on', 
											'option['.$var_name.']', 
											'on', 
											($var_value == 'on'), '' )
							.' '
							.Form::getLabel($var_name.'_on', $lang->def('_'.strtoupper($var_name)) )
							.Form::closeFormLine();
				};break;
				case "menuvoice" :
				case "menuvoice_course_public" :
				case "check" : {
					//on off
					
					$html .= Form::getCheckbox( $lang->def('_'.strtoupper($var_name)), $var_name, 'option['.$var_name.']', 1, ($var_value == 1));
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
						
						$html .= Form::getCheckbox( $name , 'tablist_'.$tab_code, 'tablist['.$tab_code.']', 1, isset($tab_selected[$tab_code]));
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
												$var_value );
	
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
					$html .= '</div>';
        };break;
				
				//string or int
				default : {
					$html .= Form::getTextfield( $lang->def('_'.strtoupper($var_name)), 
												$var_name, 
												'option['.$var_name.']', 
												$max_size, 
												$var_value );
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
		
		$reSetting = sql_query("
		SELECT param_name, value_type, extra_info 
		FROM ".$this->table." 
		WHERE regroup = '".$regroup."' AND 
			hide_in_modify = '0'");
		
		$after_reload_perm = false;
		$re = true;
		while( list( $var_name, $value_type, $extra_info ) = sql_fetch_row( $reSetting ) ) {
			
			
			switch( $value_type ) {
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
				
				//else simple assignament
				default : {
					$new_value = $_POST['option'][$var_name];
				}
			}
			
			if(!sql_query("
			UPDATE ".$this->table." 
			SET param_value = '$new_value' 
			WHERE param_name = '$var_name' AND regroup = '".$regroup."'")) {
				$re = false;
			}
			
		}
		
		if($after_reload_perm) {
			
			Docebo::user()->loadUserSectionST('/');
			Docebo::user()->SaveInSession();
		}
		return $re;
	}
}

?>