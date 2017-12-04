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
 * @package  admin-library
 * @subpackage user
 * @author 		Fabio Pirovano <fabio@docebo.com>
 * @version 	$Id:$
 */

/**
 * this class is minded as an abstract level for manage users preferences
 *
 * @author	Fabio Pirovano <fabio (@) docebo (.) com>
 */
class UserPreferencesDb {

	var $_db_conn;

	/**
	 * @return string the name of the table with all the possible preference
	 * @access private
	 */
	function _getTablePreference() {

		return $GLOBALS['prefix_fw'].'_setting_list';
	}

	/**
	 * @return string the name of the table with the users preferences
	 * @access private
	 */
	function _getTableUser() {

		return $GLOBALS['prefix_fw'].'_setting_user';
	}

	function _executeQuery($query) {

		if($this->_db_conn == false) $result = sql_query( $query );
		else $result = sql_query( $query, $this->_db_conn );
		return $result;
	}

	/**
	 * class construtor
	 */
	function UserPreferencesDb($_db_conn = NULL) {

		$this->_db_conn = $_db_conn;
	}

	/**
	 * return info of all the preferences
	 * @param	string	$base_path	if is passed only the preferences that is based on this path will be returned
	 *
	 * @return 	mixed	an array with the info of the preferences founded
	 *					[path_name] => (	[path_name]
	 *										[label]
	 *										[default_value]
	 *										[type]
	 *										[visible]
	 *										[load_at_startup] ), ...
	 */
	function getAllPreference($base_path = false) {

		$query_all_preferences = "
		SELECT path_name, label, default_value, type, visible, load_at_startup
		FROM ".$this->_getTablePreference()."
		WHERE 1
		ORDER BY sequence";
		if($base_path == false) $base_path = " AND path_name LIKE '".$base_path."%' ";
		$re_all_preferences = $this->_executeQuery($query_all_preferences);

		$all_preferences = array();
		while(list($path_name, $label, $default_value, $type, $visible, $load_at_startup) = sql_fetch_row($re_all_preferences)) {

			$all_preferences[$path_name] = array(
				'path_name'			=> $path_name,
				'label'				=> $label,
				'default_value'		=> $default_value,
				'type'				=> $type,
				'visible'			=> $visible,
				'load_at_startup'	=> $load_at_startup );
		}
		return $all_preferences;
	}

	/**
	 * return info of a preference
	 * @param	string	$path	the preference
	 *
	 * @return 	mixed	an array with the info of the preferencs if founded or FALSE
	 *					array(	[path_name]
	 *							[label]
	 *							[default_value]
	 *							[type]
	 *							[visible]
	 *							[load_at_startup] )
	 */
	function getPreference($path) {

		$query_preference = "
		SELECT path_name, label, default_value, type, visible, load_at_startup
		FROM ".$this->_getTablePreference()."
		WHERE path_name = '".$path."'
		ORDER BY sequence";
		$re_preference = $this->_executeQuery($query_preference);

		$preference = array();
		if( sql_num_rows($re_preference) > 0) {

			list($path_name, $label, $default_value, $type, $visible, $load_at_startup) = sql_fetch_row($re_preference);
			$preference = array(
				'path_name'			=> $path_name,
				'label'				=> $label,
				'default_value'		=> $default_value,
				'type'				=> $type,
				'visible'			=> $visible,
				'load_at_startup'	=> $load_at_startup );
		} else {

			return false;
		}
		return $preference;
	}

	/**
	 * return the default value for the preference
	 * @param	string	$path	the preference
	 *
	 * @return 	mixed	the default_value for the preference if exists or FALSE
	 */
	function getDefaultValue($path) {

		$query_preference = "
		SELECT default_value, type
		FROM ".$this->_getTablePreference()."
		WHERE path_name = '".$path."'
		ORDER BY sequence";
		$re_preference = $this->_executeQuery($query_preference);

		$preference = array();
		if( sql_num_rows($re_preference) > 0) {

			list($default_value) = sql_fetch_row($re_preference);
			return $default_value;
		} else {

			return false;
		}
	}

	/**
	 * create a new preference
	 * @param 	string 	$path				the path of the preference
	 * @param 	string 	$label				the label for the name
	 * @param 	string 	$default_value		the default value
	 * @param 	string 	$type				an identifier for the type of the preference
	 * @param 	bool 	$visible			if the field is visible
	 * @param 	bool 	$load_at_startup	if it must loaded at class instanciation or only at request
	 *
	 * @return true if success false otherwise
	 */
	function createPreference($path, $label, $default_value, $type, $visible, $load_at_startup) {

		$query_ins_preferences = "
		INSERT INTO ".$this->_getTablePreference()."
		( path_name, label, default_value, type, visible, load_at_startup )
		VALUES
		( 	'".$path."',
			'".$label."',
			'".$default_value."',
			'".$type."',
			'".( $visible ? 1 : 0 )."',
			'".( $load_at_startup ? 1 : 0 )."' )";

		return $this->_executeQuery($query_ins_preferences);
	}

	/**
	 * update an existing preference
	 * @param 	string 	$path				the path of the preference
	 * @param 	string 	$label				the label for the name
	 * @param 	string 	$default_value		the default value
	 * @param 	string 	$type				an identifier for the type of the preference
	 * @param 	bool 	$visible			if the field is visible
	 * @param 	bool 	$load_at_startup	if it must loaded at class instanciation or only at request
	 *
	 * @return true if success false otherwise
	 */
	function updatePreference($path, $label, $default_value, $type, $visible, $load_at_startup) {

		$query_update_preferences = "
		UPDATE ".$this->_getTablePreference()."
		SET label = '".$label."',
			default_value = '".$default_value."',
			type = '".$type."',
			visible = '".( $visible ? 1 : 0 )."',
			load_at_startup = '".( $load_at_startup ? 1 : 0 )."'
		WHERE path_name = '".$path."'";

		return $this->_executeQuery($query_update_preferences);
	}

	/**
	 * delete an existing preference, and the user value for it
	 * @param 	string 	$path				the path of the preference
	 *
	 * @return bool true if success false otherwise
	 */
	function deletePreference($path) {

		$query_delete_preferences = "
		DELETE FROM ".$this->_getTableUser()."
		WHERE path_name = '".$path."'";
		if(!$this->_executeQuery($query_delete_preferences)) return false;

		$query_delete_preferences = "
		DELETE FROM ".$this->_getTablePreference()."
		WHERE path_name = '".$path."'";
		return $this->_executeQuery($query_delete_preferences);
	}

	/**
	 * @param 	int		$id_user	the id of the user
	 * @param 	string	$path		the path of the preference
	 *
	 * @return	string	the value of the user for this preference or the default value of the preference
	 */
	function getUserValue($id_user, $path) {

		$query_preference = "
		SELECT value
		FROM ".$this->_getTableUser()."
		WHERE path_name = '".$path."' AND id_user = '".$id_user."'";
		$re_preference = $this->_executeQuery($query_preference);

		if( sql_num_rows($re_preference) > 0) {

			list($value) = sql_fetch_row($re_preference);
			return $value;
		} else {

			return $this->getDefaultValue($path);
		}
	}


	/**
	 * get all user preferences
	 * @param 	int		$id_user	the id of the user
	 * @param 	string	$base_path	the base_path of the preference
	 *
	 * @return	array	the value of the user for the various preferences
	 */
	function getAllUserValue($id_user, $base_path = false) {

		$query_preferences = "
		SELECT prdata.path_name, prdata.default_value, udata.value
		FROM ".$this->_getTablePreference()." AS prdata LEFT JOIN ".$this->_getTableUser()." AS udata
			ON ( prdata.path_name = udata.path_name AND id_user = '".$id_user."' )
		WHERE 1";
		if($base_path !== false) {
			$query_preferences .= " AND prdata.path_name LIKE '".$base_path."%'";
		}
		$query_preferences .= "ORDER BY sequence";
		$re_preferences = $this->_executeQuery($query_preferences);
		$pref = array();
		while(list($path, $default_value, $user_value) = sql_fetch_row($re_preferences)) {

			if($user_value === NULL) $pref[$path] = $default_value;
			else $pref[$path] = $user_value;
		}
		return $pref;
	}

	/**
	 * get all user preferences
	 * @param 	int		$id_user			the id of the user
	 * @param 	string	$visible			if true only the visible is returned
	 * @param 	string	$load_at_startup	if true only the load at startup is returned
	 * @param 	string	$base_path			the base_path of the preference
	 *
	 * @return	array	the value of the user for the various preferences [path] => value(user or default)
	 */
	function getFilteredUserValue($id_user, $visible = false, $load_at_startup = false, $base_path = false) {

		$query_preferences = "
		SELECT prdata.path_name, prdata.default_value, udata.value
		FROM ".$this->_getTablePreference()." AS prdata LEFT JOIN ".$this->_getTableUser()." AS udata
			ON ( prdata.path_name = udata.path_name AND id_user = '".$id_user."')
		WHERE 1 ";
		if($visible !== false) {
			$query_preferences .= " AND prdata.visible = '1'";
		}
		if($load_at_startup !== false) {
			$query_preferences .= " AND prdata.load_at_startup = '1'";
		}
		if($base_path !== false) {
			$query_preferences .= " AND prdata.path_name LIKE '".$base_path."%'";
		}
		$query_preferences .= "ORDER BY sequence";
		$re_preferences = $this->_executeQuery($query_preferences);
		$pref = array();
		while(list($path, $default_value, $user_value) = sql_fetch_row($re_preferences)) {

			if($user_value === NULL) $pref[$path] = $default_value;
			else $pref[$path] = $user_value;
		}
		return $pref;
	}

	/**
	 * get all user preferences and the value of a specific user for it, and in respect with passed filter
	 * @param 	int		$id_user			the id of the user
	 * @param 	int		$visible			filter preferences that is visible
	 * @param 	int		$load_at_startup	filter preferences that is loaded at startup
	 * @param 	string	$base_path			if you need to load the user preferences limited to a specific group of path
	 *
	 * @return	string	the value of the user for the various preferences
	 */
	function getFullPreferences($id_user, $visible = false, $load_at_startup =false, $base_path = false) {

		$query_all_preferences = "
		SELECT 	prdata.path_name,
				prdata.label,
				prdata.default_value,
				prdata.type,
				prdata.visible,
				prdata.load_at_startup,
				udata.value
		FROM ".$this->_getTablePreference()." AS prdata LEFT JOIN ".$this->_getTableUser()." AS udata
			ON ( prdata.path_name = udata.path_name AND id_user = '".$id_user."' )
		WHERE 1 ";
		if($visible !== false) {
			$query_all_preferences .= " AND prdata.visible = 1";
		}
		if($load_at_startup !== false) {
			$query_all_preferences .= " AND prdata.load_at_startup = 1";
		}
		if($base_path !== false) {
			$query_all_preferences .= " AND prdata.path_name LIKE '".$base_path."%'";
		}
		$query_all_preferences .= " ORDER BY prdata.sequence";
		$re_all_preferences = $this->_executeQuery($query_all_preferences);
		$pref = array();
		$all_preferences = array();
		while(list($path_name, $label, $default_value, $type, $visible, $load_at_startup, $user_value)
			= sql_fetch_row($re_all_preferences)) {

			$all_preferences[$path_name] = array(
				'path_name'			=> $path_name,
				'label'				=> $label,
				'default_value'		=> $default_value,
				'type'				=> $type,
				'visible'			=> $visible,
				'load_at_startup'	=> $load_at_startup,
				'user_value' 		=> ($user_value === NULL ? $default_value : $user_value ) );
		}
		return $all_preferences;
	}

	/**
	 * assign to a user a value for a preferences
	 * @param 	int		$id_user	the id of the user
	 * @param 	int		$path		the path of the preference
	 * @param 	string	$new_value	the new value
	 *
	 * @return	bool true if success false otherwise
	 */
	function assignUserValue($id_user, $path, $new_value) {

		$query_preference = "
		SELECT value
		FROM ".$this->_getTableUser()."
		WHERE path_name = '".$path."' AND id_user = '".$id_user."'";
		$re_preference = $this->_executeQuery($query_preference);
		if( !sql_num_rows($re_preference) ) {

			// Insert new entry
			return $this->_executeQuery("
			INSERT INTO ".$this->_getTableUser()."
			( path_name, id_user, value )
			VALUES
			( '".$path."', '".$id_user."', '".$new_value."' )");
		} else {

			// Update existent entry
			return $this->_executeQuery("
			UPDATE ".$this->_getTableUser()."
			SET value = '".$new_value."'
			WHERE path_name = '".$path."'
				AND id_user = '".$id_user."'");
		}
	}

	/**
	 * delete all the preference value stored for a user or a specific one
	 * @param 	int		$id_user	the id of the user
	 * @param 	int		$path		(optional) the path of the preference
	 *
	 * @return	bool true if success false otherwise
	 */
	function removeUserValue($id_user, $path = false) {

		// Delete existent entry
		$delete_user_preferences = "
		DELETE FROM ".$this->_getTableUser()."
		WHERE id_user = '".$id_user."'";
		if($path !== false) {
			$delete_user_preferences .= " AND path_name = '".$path."'";
		}
		return $this->_executeQuery($delete_user_preferences);
	}

	/**
	 * delete all the preference value stored for a user from a base path
	 * @param 	int 	$id_user	the id of the user
	 * @param 	int		$base_path	the path of the preference
	 *
	 * @return	bool true if success false otherwise
	 */
	function removeUserValueOfPath($id_user, $base_path) {

		// Delete existent entry
		$delete_user_preferences = "
		DELETE FROM ".$this->_getTableUser()."
		WHERE id_user = '".$id_user."'
			AND path_name LIKE '".$base_path."%'";
		return $this->_executeQuery($delete_user_preferences);
	}
}

/**
 * this class is minded for manage the preferences of a specific user
 *
 * @uses 	class UserPreferencesDb
 * @author	Fabio Pirovano <fabio (@) docebo (.) com>
 */
class UserPreferences {

	var $id_user;
	var $is_anonymous;
	var $_up_db;
	var $_preferences;
	var $base_name;
	public $_admin_preference;
	protected $admin_preference = array();

	/**
	 * class constructor
	 * @param int	$id_user the id of the user
	 */
	 function UserPreferences($id_user, $db_conn = NULL) {

		$acl_man = new DoceboACLManager();

		$this->id_user 		= $id_user;

		if($acl_man->getAnonymousId() == $id_user) $this->is_anonymous = true;
		else $this->is_anonymous = false;

		$this->_up_db 		= new UserPreferencesDb($db_conn);
		$this->base_name 	= 'user_preference';
		// Load startup
		$this->_preferences = $this->_up_db->getFilteredUserValue($id_user, false, true, false);
        $this->_preferences['ui.lang_code'] = $this->getLanguageCode();

		$this->_admin_preference = new AdminPreference();
	}

	/**
	 * @param string	$preference the preference that must by find
	 *
	 * @return mixed	the value of the preference for the user if preference exist else FALSE
	 */
	function getPreference($preference) {

		if(isset($this->_preferences[$preference])) {

			// Return loaded value
			return $this->_preferences[$preference];
		} else {

			// If the value is not present in the pool of preference loaded at startup try to load it from db
			$loaded_pref = $this->_up_db->getUserValue($this->id_user, $preference);
			if($loaded_pref !== false) {

				$this->_preferences[$preference] = $loaded_pref;
				return $loaded_pref;
			} else {

				return false;
			}
		}
	}

	public function getAdminPreference($preference)
	{
	 	 if(empty($this->admin_preference))
			$this->admin_preference = $this->_admin_preference->getAdminRules($this->id_user);

		 return ( isset($this->admin_preference[$preference]) ? $this->admin_preference[$preference] : false );
	}

	/**
	 * save a specific value for the preference
	 * @param string	$preference the preference that must by find
	 * @param mixed		$new_value 	the new value to assign
	 *
	 * @return mixed	true if success false otherwise
	 */
	function setPreference($preference, $new_value) {

		if($this->is_anonymous) return true;

		if(!$this->_up_db->assignUserValue($this->id_user, $preference, $new_value)) {

			return false;
		} else {

			$this->_preferences[$preference] = $new_value;
			return true;
		}
	}

	/**
	 * @return string	the value of the preference 'ui.template'
	 */
	function getTemplate() {

		$value = $this->getPreference('ui.template');
		if($value == '' || $value == false) return false;
		return $value;
	}

	/**
	 * @param string	the value to assign at 'ui.template'
	 */
	function setTemplate($new_template) {

		$this->setPreference('ui.template', $new_template);
		if($this->id_user == getLogUserId() || Get::sett('templ_use_field') == 0) setTemplate($new_template);
		return true;
	}

	/**
	 * @return string	the value of the preference 'ui.language'
	 */
	function getLanguage() {

		$value = $this->getPreference('ui.language');
		if($value == '') { $value = Lang::getDefault(); }
		return $value;
	}

	/**
	 * @param string	the value to assign at 'ui.language'
	 */
	function setLanguage($new_language) {

		$this->setPreference('ui.language', $new_language);
		return true;
	}
    function getLanguageCode(){
        $locale_language_array = [
                    'arabic'  => 'ar',
                    'croatian' => 'hr',
                    'czech' => 'cs',
                    'dutch' => 'nl',
                    'english' => 'en',
                    'finnish'  => 'fi',                            
                    'french'  => 'fr',
                    'german' => 'de',
                    'greek' => 'el',
                    'italian' => 'it',
                    'spanish' => 'es',
                    'swedish' =>  'sv',
                    'norwegian' => 'nb'
                    ];
         if (array_key_exists($this->_preferences['ui.language'],$locale_language_array)) {          
            return $locale_language_array[$this->_preferences['ui.language']];             
         } else {
             if ($this->is_anonymous) {
                 $browser_locale = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
             }   return $browser_locale[0];
             return 'en';
         }         
    }


	/**
	 * @param string	$base_path 		if specified load only preference form this base_path
	 * @param bool		$only_visible 	if true only the visible
	 *
	 * @return string	the code for show the actual preference of the user
	 */
	function getFreezeMask($base_path = false, $only_visible = true) {

		require_once(_base_.'/lib/lib.form.php');
		$lang =& DoceboLanguage::createInstance('preferences', 'framework');

		$preferences = $this->_up_db->getFullPreferences($this->id_user, $only_visible, false, $base_path);

		$html = '';
		while(list(, $pref) = each($preferences)) {

			// Navigation trought the preferences
			// array( 'path_name', 'label', 'default_value', 'type', 'visible', 'load_at_startup', 'user_value' )
			switch( $pref['type'] ) {
				case "language" : {
					//drop down language
					$lang_sel = $this->getLanguage();
					$html .= Form::getLineBox( $lang->def($pref['label']),
												$lang_sel );

				};break;
				case "template" : {
					//drop down template
					$templ_sel = getTemplate();
					$html .= Form::getLineBox( $lang->def($pref['label']),
												$templ_sel );
				};break;
				case "hteditor" : {
					//drop down hteditor
					$ht_edit = getHTMLEditorList();
					$value = ( $pref['user_value'] ? $pref['user_value'] : $pref['default_value'] );
					$html .= Form::getLineBox( $lang->def($pref['label']),
												$ht_edit[$value] );
				};break;
				case "layout_chooser" : {
					//drop down hteditor
					$layout = array(
						'left' => Lang::t('_LAYOUT_LEFT'),
						'over' => Lang::t('_LAYOUT_OVER'),
						'right' => Lang::t('_LAYOUT_RIGHT'));
					$value = ( $pref['user_value'] ? $pref['user_value'] : $pref['default_value'] );
					$html .= Form::getLineBox( $lang->def($pref['label']),
												$layout[$value] );
				};break;
				case "enum" : {
					//on off
					$value = ( $pref['user_value'] ? $pref['user_value'] : $pref['default_value'] );
					$html .= Form::getLineBox( $lang->def($pref['label']),
												( $value == 'on' ?
													$lang->def('_ACTIVE') :
													$lang->def('_OFF') ) );
				};break;
				//string or int
				default : {
					$html .= Form::getLineBox( $lang->def($pref['label']),
												( $pref['user_value'] ?
														$pref['user_value'] :
														$pref['default_value'] ) );
				}
			}
		}
		return $html.'<div class="nofloat"></div>';
	}

	/**
	 * @param string	$base_path 		if specified load only preference form this base_path
	 * @param bool		$only_visible 	if true only the visible
	 *
	 * @return string	the code for the mod mask
	 */
	function getModifyMask($base_path = false, $only_visible = true, $separate_output = false ) {

		require_once(_base_.'/lib/lib.form.php');
		$lang =& DoceboLanguage::createInstance('preferences', 'framework');

		$preferences = $this->_up_db->getFullPreferences($this->id_user, $only_visible, false, $base_path);

		$html = array();
		while(list(, $pref) = each($preferences)) {

			// Navigation trought the preferences
			// array( 'path_name', 'label', 'default_value', 'type', 'visible', 'load_at_startup', 'user_value' )
			switch( $pref['type'] ) {
				case "language" : {
					//drop down language
					$lang_sel = $this->getLanguage();

					$langs_var = Docebo::langManager()->getAllLangCode();
					$langs = array();
					foreach($langs_var as $k => $v) {

						$langs[$k] = $v;
					}
					/* XXX: remove when alll lang ready*/
					$html[$pref['path_name']] = Form::getDropdown( $lang->def($pref['label']),
												$this->base_name.'_'.$pref['path_name'],
												$this->base_name.'['.$pref['path_name'].']',
												$langs,
												array_search($lang_sel, $langs));

				};break;
				case "template" : {
					//drop down template
					$templ_sel = $this->getTemplate();
					$templ = getTemplateList();

					$html[$pref['path_name']] = Form::getDropdown( $lang->def($pref['label']),
												$this->base_name.'_'.$pref['path_name'],
												$this->base_name.'['.$pref['path_name'].']',
												$templ,
												array_search($templ_sel, $templ));
				};break;
				case "hteditor" : {
					//drop down hteditor
					$ht_edit = getHTMLEditorList();
					$html[$pref['path_name']] = Form::getDropdown( $lang->def($pref['label']),
												$this->base_name.'_'.$pref['path_name'],
												$this->base_name.'['.$pref['path_name'].']',
												$ht_edit,
												( $pref['user_value'] ? $pref['user_value'] : $pref['default_value'] ) );
				};break;
				case "layout_chooser" : {
					//drop down hteditor
					$layout = array(
						'left' => Lang::t('_LAYOUT_LEFT'),
						'over' => Lang::t('_LAYOUT_OVER'),
						'right' => Lang::t('_LAYOUT_RIGHT'));
					$html[$pref['path_name']] = Form::getDropdown( $lang->def($pref['label']),
												$this->base_name.'_'.$pref['path_name'],
												$this->base_name.'['.$pref['path_name'].']',
												$layout,
												( $pref['user_value'] ? $pref['user_value'] : $pref['default_value'] ) );
				};break;
				case "enum" : {
					//on off
					$value = ( $pref['user_value'] ? $pref['user_value'] : $pref['default_value'] );
					$html[$pref['path_name']] = Form::openFormLine()
							.Form::getInputCheckbox( $this->base_name.'_'.$pref['path_name'].'_on',
											$this->base_name.'['.$pref['path_name'].']',
											'on',
											($value == 'on'), '' )
							.' '
							.Form::getLabel($this->base_name.'_'.$pref['path_name'].'_on', $lang->def($pref['label']) )
							.Form::closeFormLine();


				};break;
				//string or int
				default : {
					$html[$pref['path_name']] = Form::getTextfield( $lang->def($pref['label']),
												$this->base_name.'_'.$pref['path_name'],
												$this->base_name.'['.$pref['path_name'].']',
												'65535',
												( $pref['user_value'] ? $pref['user_value'] : $pref['default_value'] ) );
				}
			}
		}
		return $separate_output ? $html : implode("", $html).'<div class="nofloat"></div>';
	}

	/**
	 * @param array		$array_source 	save the preferences of a user
	 * @param string	$base_path 		if specified load only preference form this base_path
	 *
	 * @return nothing
	 */
	function savePreferences( $array_source, $base_path = false) {

		$info_pref = $this->_up_db->getFullPreferences($this->id_user, true, false, $base_path);

		if(!isset($array_source[$this->base_name])) return true;
		if(!is_array($array_source[$this->base_name])) return true;

		$re = true;
		while(list(, $pref) = each($info_pref)) {

			if(isset($array_source[$this->base_name][$pref['path_name']])) {
				$new_value = $array_source[$this->base_name][$pref['path_name']];
			} else $new_value = NULL;
			switch($pref['type']) {
				case "language" : {

					$langs = Docebo::langManager()->getAllLangCode();
					$re &= $this->setLanguage($langs[$new_value]);
				};break;
				case "template" : {

					$templ = getTemplateList();
					$re &= $this->setTemplate($templ[$new_value]);
				};break;
				case "enum" : {
					if($new_value == NULL) $re &= $this->setPreference($pref['path_name'], 'off');
					else $re &= $this->setPreference($pref['path_name'], 'on');
				};break;

				default : {

					$re &= $this->setPreference($pref['path_name'], $new_value);
				}
			}
		}
		return $re;
	}
}

define('_RULES_LIMIT_USER', 'admin_rules.limit_user_insert');
define('_RULES_MAX_USER', 'admin_rules.max_user_insert');
define('_RULES_DIRECT_USER', 'admin_rules.direct_user_insert');
define('_RULES_LIMIT_COURSE', 'admin_rules.limit_course_subscribe');
define('_RULES_MAX_COURSE', 'admin_rules.max_course_subscribe');
define('_RULES_DIRECT_COURSE', 'admin_rules.direct_course_subscribe');
define('_RULES_LANG', 'admin_rules.user_lang_assigned');

class AdminPreference
{
	var $json;

	public function  __construct()
	{
		require_once(_base_.'/lib/lib.json.php');

		$this->json = new Services_JSON();
	}

	public function getSpecialModifyMask($idst, $lang_module = 'adminrules')
	{
		$query =	"SELECT path_name, label, default_value, type"
					." FROM %adm_setting_list"
					." WHERE path_name LIKE 'admin_rules.%'"
					." AND visible = 1"
					." ORDER BY sequence";

		$result = sql_query($query);
		$old_admin_value = $this->getProfileRules($idst);
		$res = Form::getHidden('idst', 'idst', $idst);

		while(list($path, $label, $default, $type) = sql_fetch_row($result))
		{
			switch($type)
			{
				case 'enum':
					$res .= Form::getCheckbox(Lang::t($label, $lang_module), str_replace('.', '_', $path), str_replace('.', '_', $path), 'on', (isset($old_admin_value[$path]) && $old_admin_value[$path] === 'on' ? true : ($default === 'off' ? false : true)));
				break;

				case 'integer':
					$res .= Form::getTextfield(Lang::t($label, $lang_module), str_replace('.', '_', $path), str_replace('.', '_', $path), '255', (isset($old_admin_value[$path]) ? $old_admin_value[$path] : $default));
				break;
			}
		}

		return $res;
	}

	public function idstGroup()
	{
		$query =	"SELECT idst"
					." FROM %adm_group"
					." WHERE groupid LIKE '/framework/adminrules/%'";

		$result = sql_query($query);
		$res = array(0 => 0);

		while(list($idst) = sql_fetch_row($result))
			$res[$idst] = $idst;

		return $res;
	}

	public function getProfileAssociatedToAdmin($idst)
	{
		$query =	"SELECT idst"
					." FROM %adm_group_members"
					." WHERE idstMember = '".$idst."'"
					." AND idst IN (".implode(',', $this->idstGroup()).")";

		list($res) = sql_fetch_row(sql_query($query));

		if(!$res)
			$res = 0;

		return $res;
	}

	public function getAdminRules($idst)
	{
		$idst_group = $this->getProfileAssociatedToAdmin($idst);

		$query =	"SELECT path_name, value"
					." FROM %adm_setting_group"
					." WHERE path_name LIKE 'admin_rules.%'"
					." AND idst = '".$idst_group."'";

		$result = sql_query($query);
		$res = array();

		while(list($path, $value) = sql_fetch_row($result))
			if($path === _RULES_LANG)
				$res[$path] = $this->json->decode($value);
			else
				$res[$path] = $value;

		return $res;
	}

	public function getProfileRules($idst)
	{
		$query =	"SELECT path_name, value"
					." FROM %adm_setting_group"
					." WHERE path_name LIKE 'admin_rules.%'"
					." AND idst = '".$idst."'";

		$result = sql_query($query);
		$res = array();

		while(list($path, $value) = sql_fetch_row($result))
			if($path === _RULES_LANG)
				$res[$path] = $this->json->decode($value);
			else
				$res[$path] = $value;

		return $res;
	}

	public function getRules()
	{
		$query =	"SELECT path_name, default_value, type"
					." FROM %adm_setting_list"
					." WHERE path_name LIKE 'admin_rules.%'"
					." AND visible = 1"
					." ORDER BY sequence";

		$result = sql_query($query);
		$res = array();

		while(list($path, $default, $type) = sql_fetch_row($result))
		{
			$res[$path]['type'] = $type;
			$res[$path]['default'] = $default;
		}

		return $res;
	}

	public function saveSpecialAdminRules($idst, $rules)
	{
		$old_rules = $this->getProfileRules($idst);
		$res = true;
		foreach($rules as $path => $value)
		{
			if(isset($old_rules[$path]) && $old_rules[$path] !== $value)
			{
				$query =	"UPDATE %adm_setting_group"
							." SET value = '".$value."'"
							." WHERE path_name = '".$path."'"
							." AND idst = '".$idst."'";

				if(!sql_query($query))
					$res = false;
			}
			elseif(!isset($old_rules[$path]))
			{
				$query =	"INSERT INTO %adm_setting_group"
							." (path_name, idst, value)"
							." VALUES ('".$path."', '".$idst."', '".$value."')";

				if(!sql_query($query))
					$res = false;
			}
		}

		return $res;
	}

	public function getLangModifyMask($idst)
	{
		$old_rules = $this->getProfileRules($idst);
		$all_languages = Docebo::langManager()->getAllLangCode();

		$res = '';
		if(isset($old_rules[_RULES_LANG]))
			$old_rules[_RULES_LANG] = array_flip($old_rules[_RULES_LANG]);

		while(list(,$lang_code) = each($all_languages))
		{
			$res .=	Form::getCheckbox('<img src="'.getPathImage('cms').'language/'.$lang_code.'.png" alt="'.$lang_code.'" /> '.$lang_code,
					'admin_lang_'.$lang_code,
					'admin_lang['.$lang_code.']',
					'1',
					isset($old_rules[_RULES_LANG][$lang_code]));
		}

		return $res;
	}

	public function saveLangAdminRules($idst, $lang)
	{
		$old_rules = $this->getProfileRules($idst);

		if(isset($old_rules[_RULES_LANG]))
			$query =	"UPDATE %adm_setting_group"
						." SET value = '".$lang."'"
						." WHERE path_name = '"._RULES_LANG."'"
						." AND idst = '".$idst."'";
		else
			$query =	"INSERT INTO %adm_setting_group"
						." (path_name, idst, value)"
						." VALUES ('"._RULES_LANG."', '".$idst."', '".$lang."')";

		return sql_query($query);
	}

	public function clearAdmRules($idst)
	{
		$query =	"DELETE FROM %adm_setting_group"
					." WHERE idst = '".$idst."'";

		return sql_query($query);
	}

	public function clearAdmPerm($idst)
	{
		$query =	"DELETE FROM %adm_role_members"
					." WHERE idstMember = '".$idst."'";

		return sql_query($query);
	}

	public function saveAdminPerm($idst, $adm_perm)
	{
		if($this->clearAdmPerm($idst))
		{
			if(!empty($adm_perm))
			{
				$query =	"INSERT INTO %adm_role_members"
							." (idst, idstMember)"
							." VALUES ";

				$first = true;

				foreach($adm_perm as $idst_perm)
				{
					if($first)
						$first = false;
					else
						$query .= ", ";

					$query .=	"('".$idst_perm."', '".$idst."')";
				}

				return sql_query($query);
			}

			return true;
		}

		return false;
	}

	public function getAdminPerm($idst)
	{
		$query =	"SELECT idst"
					." FROM %adm_role_members"
					." WHERE idstMember = '".$idst."'";

		$result = sql_query($query);

		$res = array();

		while(list($idst) = sql_fetch_row($result))
			$res[$idst] = $idst;

		return $res;
	}

	public function getAdminTree($idst)
	{
		$query = "SELECT idst FROM %adm_admin_tree"
				." WHERE idstAdmin = '".$idst."'";

		$result = sql_query($query);
		$res = array();
		while(list($idst) = sql_fetch_row($result))
			$res[] = $idst;

		return $res;
	}


	public function getMultipleAdminTree($idst_list)
	{
		//validate input - accept a single integer value or an array of values
		if (is_numeric($idst_list)) $idst_list = array($idst_list);
		if (!is_array($idst_list)) return false;
		if (empty($idst_list)) return array();

		//compose query - $idst_list is now guaranteed to be a non-empty array.
		//so we can perform implode() function on it without risking a sql error
		$query = "SELECT idstAdmin, idst FROM %adm_admin_tree WHERE idstAdmin IN (".implode(",", $idst_list).")";
		$res = sql_query($query);

		//fetch data - the output will be a bi-dimensional array
		//with the idsts of the admins as key values
		$output = array();
		while(list($idst_admin, $idst_user) = sql_fetch_row($res)) {
			$output[$idst_admin] = $idst_user;
		}

		return $output;
	}



	public function saveAdminTree($idst, $user_selected)
	{
		if($this->clearAdminTree($idst))
		{
			if(!empty($user_selected))
			{
				$query =	"INSERT INTO %adm_admin_tree"
							." (idst, idstAdmin)"
							." VALUES ";

				$first = true;

				foreach($user_selected as $idst_associated)
				{
					if($first)
						$first = false;
					else
						$query .= ", ";

					$query .=	"('".$idst_associated."', '".$idst."')";
				}

				return sql_query($query);
			}

			return true;
		}

		return false;
	}

	public function clearAdminTree($idst)
	{
		$query =	"DELETE FROM %adm_admin_tree"
					." WHERE idstAdmin = '".$idst."'";

		return sql_query($query);
	}

	public function getAdminUsers($id_admin) {

		$acl_man		= Docebo::aclm();
		$admin_tree		= $this->getAdminTree( $id_admin );
		// separate the users and the groups
		$admin_users	= $acl_man->getUsersFromMixedIdst($admin_tree);
		$admin_groups	= $acl_man->getGroupsFromMixedIdst($admin_tree);
		
		$admin_userlist = array_merge($admin_users, $acl_man->getAllUsersFromIdst($admin_groups));
		return $admin_userlist;
	}

  public function getAdminUsersQuery($id_admin, $idst_field_name) {
 
        $acl_man = Docebo::aclm();
        $admin_tree     = $this->getAdminTree( $id_admin );
        // separate the users and the groups
        $admin_users    = $acl_man->getUsersFromMixedIdst($admin_tree);
        $admin_groups   = $acl_man->getGroupsFromMixedIdst($admin_tree);
 
        // retrive parent groups
        $tmp_admin_groups = array();
        foreach($admin_groups as $id_group) {
            $tmp_admin_groups = array_merge( $tmp_admin_groups, $acl_man->getGroupGDescendants($id_group) );
        }
        $admin_groups = $tmp_admin_groups;
 
        $arr_query = array();
        if(!empty($admin_users)) $arr_query[] = " $idst_field_name IN (".implode(',', $admin_users).") ";
        if(!empty($admin_groups))  $arr_query[] = " $idst_field_name IN ( SELECT idstMember FROM %adm_group_members WHERE idst IN (".implode(',', $admin_groups).") ) ";
         
        if(!empty($arr_query)) $query = "( ".implode($arr_query, 'OR')." )";
        else $query = " 0 ";
        return $query;
	}
	
	public function getAdminAllSett($id_admin, $idst_field_name) {

		$acl_man		= Docebo::aclm();
		$admin_tree		= $this->getAdminTree( $id_admin );
		// separate the users and the groups
		$admin_users	= $acl_man->getUsersFromMixedIdst($admin_tree);
		$admin_groups	= $acl_man->getGroupsFromMixedIdst($admin_tree);

		$tree = array(
			'users' => $admin_users,
			'groups' => $admin_groups
		);

		// retrive parent groups
		$tmp_admin_groups = array();
		foreach($admin_groups as $id_group) {
			$tmp_admin_groups = array_merge( $tmp_admin_groups, $acl_man->getGroupGDescendants($id_group) );
		}
		$admin_groups = array_merge($admin_groups, $tmp_admin_groups);//$admin_groups = $tmp_admin_groups;

		$admin_userlist = array_merge($admin_users, /*$acl_man->getAllUsersFromIdst($admin_groups)*/$acl_man->getGroupUMembers($admin_groups));
		if(!empty($admin_users)) $arr_query[] = " $idst_field_name IN (".implode(',', $admin_userlist).") ";
		if(!empty($admin_groups))  $arr_query[] = " $idst_field_name IN ( SELECT idstMember FROM %adm_group_members WHERE idst IN (".implode(',', $admin_groups).") ) ";

		if(!empty($arr_query)) $query = "( ".implode($arr_query, 'OR')." )";
		else $query = " 0 ";


		return array(
			'tree' => $tree,
			'users' => $admin_userlist,
			'groups' => $admin_groups,
			'query' => $query
		);
	}

	public function getAdminCourse($idst)
	{
		$query = "SELECT id_entry, type_of_entry FROM %adm_admin_course"
				." WHERE idst_user = '".$idst."'";

		$result = sql_query($query);
		$res = array(
			'course' => array(),
			'coursepath' => array(),
			'catalogue' => array()
		);
		while(list($id_course, $type_of_entry) = sql_fetch_row($result))
			$res[$type_of_entry][$id_course] = $id_course;

		return $res;
	}

	public function getAdminCourseResolved($idst) {
		$admin_list = $this->getAdminCourse($idst);

		if (!isset($admin_list['course'][0])) {
			require_once(_lms_.'/lib/lib.catalogue.php');
			$cat_man = new Catalogue_Manager();
			$user_catalogue = $cat_man->getUserAllCatalogueId($idst);
			if(count($user_catalogue) > 0) {
				$arr_courses = array();
				foreach($user_catalogue as $id_cat) {
					$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);
					$arr_courses = array_merge($arr_courses, $catalogue_course);
				}
				foreach ($arr_courses as $id_course) {
					$admin_list['course'][$id_course] = $id_course;
				}
			}
		}

		return $admin_list;
	}


	public function getMultipleAdminCourse($idst_list)
	{
		//validate input - accept a single integer value or an array of values
		if (is_numeric($idst_list)) $idst_list = array($idst_list);
		if (!is_array($idst_list)) return false;
		if (empty($idst_list)) return array();

		//compose query - $idst_list is now guaranteed to be a non-empty array.
		//so we can perform implode() function on it without risking a sql error
		$query = "SELECT idst_user, id_entry, type_of_entry FROM %adm_admin_course"
			." WHERE idst_user IN (".implode(",", $idst_list).") "
			." AND type_of_entry IN ('course', 'coursepath', 'catalogue')";
		$res = sql_query($query);

		//initialize output data
		$output = array();
		foreach ($idst_list as $idst_admin) {
			$output[$idst_admin] = array(
				'course'     => array(),
				'coursepath' => array(),
				'catalogue'  => array(),
				'category'   => array()
			);
		}

		//fetch data - the output will be a bi-dimensional array
		//with the idsts of the admins as key values
		while(list($idst_admin, $id_entry, $type) = sql_fetch_row($res)) {
			switch ($type) {
				case 'course':
				case 'coursepath':
				case 'catalogue':
				case 'category': $output[$idst_admin][$type][] = $id_entry; break;
				default: break;
			}
		}

		return $output;
	}


	public function getAdminClasslocation($idst)
	{
		$query = "SELECT id_entry, type_of_entry FROM %adm_admin_course "
				." WHERE idst_user = '".$idst."' AND type_of_entry = 'classlocation'";

		$result = sql_query($query);
		$output = array();
		while(list($id_entry, $type_of_entry) = sql_fetch_row($result))
			$output[] = $id_entry;

		return $output;
	}


	public function getMultipleAdminClasslocation($idst_list)
	{
		//validate input - accept a single integer value or an array of values
		if (is_numeric($idst_list)) $idst_list = array($idst_list);
		if (!is_array($idst_list)) return false;
		if (empty($idst_list)) return array();

		//compose query - $idst_list is now guaranteed to be a non-empty array.
		//so we can perform implode() function on it without risking a sql error
		$query = "SELECT idst_user, id_entry, type_of_entry FROM %adm_admin_course"
			." WHERE idst_user IN (".implode(",", $idst_list).")";
		$res = sql_query($query);

		//initialize output data
		$output = array();
		foreach ($idst_list as $idst_admin) {
			$output[$idst_admin] = array(
				'classlocation'	=> array()
			);
		}

		//fetch data - the output will be a bi-dimensional array
		//with the idsts of the admins as key values
		while(list($idst_admin, $id_entry, $type) = sql_fetch_row($res)) {
			switch ($type) {
				case 'classlocation': $output[$idst_admin][$type][] = $id_entry; break;
				default: break;
			}
		}

		return $output;
	}



	public function saveAdminCourse($idst, $course_selected, $coursepath_selected, $catalogue_selected)
	{
		if($this->clearAdminCourse($idst))
		{
			if(!empty($course_selected))
			{
				$query =	"INSERT INTO %adm_admin_course"
							." (id_entry, idst_user, type_of_entry)"
							." VALUES ";

				$first = true;

				foreach($course_selected as $idst_associated)
				{
					if($first)
						$first = false;
					else
						$query .= ", ";

					$query .=	"('".$idst_associated."', '".$idst."', 'course')";
				}

				if(!sql_query($query))
					return false;
			}

			if(!empty($coursepath_selected))
			{
				$query =	"INSERT INTO %adm_admin_course"
							." (id_entry, idst_user, type_of_entry)"
							." VALUES ";

				$first = true;

				foreach($coursepath_selected as $idst_associated)
				{
					if($first)
						$first = false;
					else
						$query .= ", ";

					$query .=	"('".$idst_associated."', '".$idst."', 'coursepath')";
				}

				if(!sql_query($query))
					return false;
			}

			if(!empty($catalogue_selected))
			{
				$query =	"INSERT INTO %adm_admin_course"
							." (id_entry, idst_user, type_of_entry)"
							." VALUES ";

				$first = true;

				foreach($catalogue_selected as $idst_associated)
				{
					if($first)
						$first = false;
					else
						$query .= ", ";

					$query .=	"('".$idst_associated."', '".$idst."', 'catalogue')";
				}

				if(!sql_query($query))
					return false;
			}

			return true;
		}

		return false;
	}


	public function saveAdminClasslocation($idst, $selection)
	{
		if (is_numeric($selection)) $selection = array( (int)$election );
		if (!is_array($selection)) return false;
		if (empty($selection)) return true;

		if($this->clearAdminClasslocation($idst))
		{
			if(!empty($selection))
			{
				$query = "INSERT INTO %adm_admin_course"
					." (id_entry, idst_user, type_of_entry)"
					." VALUES ";

				$first = true;
				foreach ($selection as $idst_associated)
				{
					if($first)
						$first = false;
					else
						$query .= ", ";

					$query .=	"('".$idst_associated."', '".$idst."', 'classlocation')";
				}

				if(!sql_query($query))
					return false;
			}

			return true;
		}

		return false;
	}


	public function clearAdminClasslocation($idst)
	{
		$query =	"DELETE FROM %adm_admin_course"
					." WHERE idst_user = '".$idst."'"
					." AND type_of_entry = 'classlocation'";

		return sql_query($query);
	}


	public function clearAdminCourse($idst)
	{
		$query =	"DELETE FROM %adm_admin_course"
					." WHERE idst_user = '".$idst."' "
					." AND type_of_entry IN ('course', 'coursepath', 'catalogue')";

		return sql_query($query);
	}


	public function addAdminTree($entries, $idst_admin)
	{
		$query = "INSERT INTO %adm_admin_tree"
				." (idst, idstAdmin) VALUES "
				." ('".$entries."','".$idst_admin."')";

		return sql_query($query);
                
	}


	public function removeAdminTree($idst, $idst_admin)
	{
		if (is_numeric($entries)) $entries = array( (int)$entries );
		if (!is_array($entries)) return false;
		if (empty($entries)) return true;

		$query = "DELETE FROM %adm_admin_tree"
			." WHERE idst IN (".implode(",", $idst_admin).") "
			." AND idstAdmin = '".$idst_admin."'";

		return sql_query($query);
	}




	protected function _addAdminEntries($entries, $idst_admin, $type) {
		if (is_numeric($entries)) $entries = array( (int)$entries );
		if (!is_array($entries)) return false;
		if (empty($entries)) return true;

		$query = "INSERT INTO %adm_admin_course "
			." (id_entry, idst_user, type_of_entry) "
			." VALUES ";

		$list = array();
		foreach ($entries as $entry) {
			$list[] = "('".(int)$entry."', '".$idst_admin."', '".$type."')";
		}
		$query .= implode(",", $list);

		return sql_query($query);
	}


	public function addAdminCourse($entries, $idst_admin) { return $this->_addAdminEntries($entries, $idst_admin, 'course'); }
	public function addAdminCatalogue($entries, $idst_admin) { return $this->_addAdminEntries($entries, $idst_admin, 'catalogue'); }
	public function addAdminCoursepath($entries, $idst_admin)  { return $this->_addAdminEntries($entries, $idst_admin, 'coursepath'); }
	public function addAdminClasslocation($entries, $idst_admin) { return $this->_addAdminEntries($entries, $idst_admin, 'classlocation'); }

}

class ControllerPreference extends AdminPreference
{
    public function getUsers($id_controller) {
	$ctrl_users = array();
	$admin_users = $this->getAdminUsers($id_controller);
	$admin_users[]=$id_controller;
	$ctrl_users = array_unique($admin_users);
	return $ctrl_users;
    }
}
?>