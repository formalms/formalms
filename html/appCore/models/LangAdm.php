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
 * The language model class
 *
 * This class can be used in order to retrive and manipulate all kind of
 * information about the languages of the platforma nd the string
 * localization maded and uploadaded inside the system.
 * @since 4.0
 */
class LangAdm extends Model {

	protected $db;

	public function __construct() {

		$this->db = DbConn::getInstance();
	}

	/**
	 * Retrun the permission list for this module
	 * @return array
	 */
	public function getPerm() {
		return array();
	}

	/**
	 * Return information about a language
	 * @param string $lang_code the lang_code to fined
	 * @return stdClass an object with the language informations
	 */
	public function getLanguage($lang_code) {
	 	$query = "SELECT lang_code, lang_description, lang_direction, lang_browsercode "
				." FROM %adm_lang_language "
				." WHERE lang_code = '".$lang_code."'";
		$rs = $this->db->query($query);
		return $this->db->fetch_obj($rs);
	}

	/**
	 * Return true if the language exist, false otherwise
	 * @param string $lang_code the lang_code to fined
	 * @return bool
	 */
	public function languageExist($lang_code) {
	 	$query = "SELECT lang_code "
				." FROM %adm_lang_language "
				." WHERE lang_code = '".$lang_code."'";
		$rs = $this->db->query($query);
		return ( $this->db->num_rows($rs) > 0);
	}

	/**
	 * Return the list of all the current active languages with the relative infos
	 * @param int $startIndex return the list starting from this index
	 * @param int $results return X results
	 * @param string $sort sorted by this column
	 * @param string $dir in this direction (asc,desc)
	 * @return array an array of lang obj records
	 */
	public function getLangList($startIndex = false, $results = false, $sort = false, $dir = false) {

		$query = "SELECT COUNT(*) as lang_max "
				." FROM %adm_lang_text "
				." WHERE 1 ";
		$rs = $this->db->query($query);
		$text = $this->db->fetch_obj($rs);

		$query = "SELECT l.*, COUNT(t.id_text) AS lang_stats "
				." FROM %adm_lang_language AS l LEFT JOIN %adm_lang_translation AS t ON (l.lang_code =t.lang_code ) "
				." WHERE 1 "
				." GROUP BY l.lang_code";
		if($sort && $dir) $query .= " ORDER BY $sort $dir ";
		if($startIndex && $results) $query .= " LIMIT ".(int)$startIndex.", ".(int)$results;
		$rs = $this->db->query($query);

		$result = array();
		while( $lang = $this->db->fetch_obj($rs) ) {
			$diff = $text->lang_max - $lang->lang_stats;
			if($diff != 0) $lang->lang_stats .= ' / '.$text->lang_max.($diff ? ' ('.$diff.')' : '');
			$result[$lang->lang_code] = $lang;
		}
		return $result;
	}

	/**
	 * Return the list of all the current active languages with the relative infos
	 * @param int $startIndex return the list starting from this index
	 * @param int $results return X results
	 * @param string $sort sorted by this column
	 * @param string $dir in this direction (asc,desc)
	 * @return array an array of lang obj records
	 */
	public function getLangListNoStat($startIndex = false, $results = false, $sort = false, $dir = false) {

		$query = "SELECT * "
				." FROM %adm_lang_language "
				." WHERE 1 ";
		if($sort && $dir) $query .= " ORDER BY $sort $dir ";
		if($startIndex && $results) $query .= " LIMIT ".(int)$startIndex.", ".(int)$results;
		$rs = $this->db->query($query);

		$result = array();
		while( $lang = $this->db->fetch_obj($rs) ) {
			$result[$lang->lang_code] = $lang;
		}
		return $result;
	}

	/**
	 * Return the number of languages loaded into the system
	 * @return int
	 */
	public function getLangTotal() {

		$query = "SELECT COUNT(*) "
				." FROM %adm_lang_language "
				." WHERE 1 ";
		if(!$rs = $this->db->query($query)) return 0;
		list($tot) = $this->db->fetch_row($rs);
		return $tot;
	}

	/**
	 * Insert a new language into the database
	 * @param <type> $lang_code
	 * @param <type> $lang_description
	 * @param <type> $lang_direction
	 * @param <type> $lang_browsercode
	 */
	public function newLanguage($lang_code, $lang_description, $lang_direction, $lang_browsercode) {

		$query = "INSERT INTO %adm_lang_language "
			." (lang_code, lang_description, lang_direction, lang_browsercode) VALUES ("
			." '".$lang_code."', "
			." '".$lang_description."', "
			." '".$lang_direction."', "
			." '".$lang_browsercode."' "
			.")";
		if( !$this->db->query($query)) return false;
		return true;
	}

	/**
	 * Updates a loaded language
	 * @param <type> $lang_code
	 * @param <type> $lang_description
	 * @param <type> $lang_direction
	 * @param <type> $lang_browsercode
	 * @return <type>
	 */
	public function updateLanguage($lang_code, $lang_description, $lang_direction, $lang_browsercode) {

		$query = " UPDATE %adm_lang_language ";
		$query .= " SET lang_description = '".$lang_description."', lang_direction = '".$lang_direction."', lang_browsercode = '".$lang_browsercode."' ";
		$query .= " WHERE lang_code = '".$lang_code."' ";
		if( !$this->db->query($query)) return false;
		return true;
	}

	/**
	 * Delete a loaded language and the related translations
	 * @param string $lang_code the lang_code of the language to delete
	 * @return bool true if succeded false otherwise
	 */
	public function delLanguage($lang_code) {

		$query = "DELETE FROM %adm_lang_language WHERE lang_code = '".$lang_code."' ";
		if( !$this->db->query($query)) return false;
		return true;
	}

	/**
	 * Return the list of all the languages only with the code
	 * @return array an array of lang_code list
	 */
	public function getLangCodeList() {

		$query = "SELECT lang_code, lang_description, lang_direction "
				." FROM %adm_lang_language "
				." WHERE 1";
		$rs = $this->db->query($query);
		$result = array();
		while( list($lang_code, $lang_description, $lang_direction) = $this->db->fetch_row($rs) ) {

			$result[$lang_code] = $lang_code;
		}
		return $result;
	}

	/**
	 * Return all the list of modules transleated
	 * @return array an array of module names
	 */
	public function getModuleList() {

		$qtxt = "SELECT DISTINCT text_module "
		."FROM %adm_lang_text "
		."ORDER BY text_module";
		$re = $this->db->query($qtxt);
		$module_list = array();
		while(list($module) = $this->db->fetch_row($re)) {

			$module_list[$module] = $module;
		}
		return $module_list;
	}

	/**
	 * Return all the list of plugins
	 * @return array an array of module names
	 */
	public function getPluginsList() {
		$qtxt = "SELECT DISTINCT p.plugin_id, p.name "
		."FROM %adm_lang_text AS lt "
		."LEFT JOIN %adm_plugin AS p ON ( lt.plugin_id = p.plugin_id ) "
		."WHERE p.plugin_id IS NOT NULL AND p.active = 1";
		$re = $this->db->query($qtxt);
		$plugin_id_list = array();
		while(list($plugin_id, $name) = $this->db->fetch_row($re)) {

			$plugin_id_list[$plugin_id] = $name;
		}
		
		return $plugin_id_list;
	}

	/**
	 * Return all the translation according to the passed filters
	 * @param int $ini extract all the records starting from this one
	 * @param int $rows numbers of record that must be extracted
	 * @param string $module translations only for this module
	 * @param string $text only translation that contains this words
	 * @param string $lang_code return translation in this languages (default language will be used if not setted)
	 * @param string $lang_code_diff return also the translation in this language
	 * @param bool $only_empty return only untranslated words for the selected language
	 * @return array
	 */
	public function getAll($ini, $rows, $module = false, $text = false, $lang_code = false, $lang_code_diff = false, $only_empty = false, $sort = false, $dir = false, $plugin_id = false) {

		if(!$lang_code) $lang_code = getLanguage();
		/*
		// for a better display i need to know if the language is rtl or ltr
		$langs = Docebo::langManager()->getAllLanguages(true);
		$main_dir = $langs[$lang_code]['direction'];
		if($lang_code_diff != false) $diff_dir = $langs[$lang_code_diff]['direction'];
		*/
		if($lang_code_diff == false) {

			$qtxt = "
			SELECT lt.id_text as id, lt.text_key, lt.text_module, ta.translation_text, '' as translation_text_diff, ta.save_date, p.name plugin_name
			FROM  %adm_lang_text AS lt
			LEFT JOIN %adm_lang_translation AS ta ON ( lt.id_text = ta.id_text AND ta.lang_code = '".$lang_code."')
			LEFT JOIN %adm_plugin AS p ON ( lt.plugin_id = p.plugin_id )
			WHERE 1 ";
		} else {

			$qtxt = "
			SELECT lt.id_text as id, lt.text_key, lt.text_module, ta.translation_text, tad.translation_text as translation_text_diff, ta.save_date, p.name plugin_name
			FROM  (
				%adm_lang_text AS lt
				LEFT JOIN %adm_lang_translation AS ta ON ( lt.id_text = ta.id_text AND ta.lang_code = '".$lang_code."')
			)
			LEFT JOIN %adm_lang_translation AS tad ON (lt.id_text = tad.id_text AND tad.lang_code = '".$lang_code_diff."' )
			LEFT JOIN %adm_plugin AS p ON ( lt.plugin_id = p.plugin_id )
			WHERE 1 ";
		}

		if($module != false) $qtxt .= " AND lt.text_module LIKE  '".$module."' ";
		if($text != false && $only_empty == false) $qtxt .= " AND ( ta.translation_text LIKE  '%".$text."%' OR lt.text_key LIKE  '%".$text."%' ) ";
		if($only_empty != false) $qtxt .= " AND ta.translation_text IS NULL";
		if($plugin_id != false) $qtxt .= " AND lt.plugin_id = ". (int)$plugin_id;

		$dir = $this->clean_dir($dir);
		switch($sort) {
			case "text_key" : $qtxt .= " ORDER BY lt.text_key ".$dir.", ta.translation_text ASC";break;
			case "translation_text" : $qtxt .= " ORDER BY ta.translation_text ".$dir."";break;
			case "translation_text_diff" : $qtxt .= " ORDER BY translation_text_diff ".$dir."";break;
			case "save_date" : $qtxt .= " ORDER BY ta.save_date ".$dir."";break;
			default : $qtxt .= " ORDER BY lt.text_module ".$dir.", ta.translation_text ASC";break;
		}
		if($ini !== false && $rows !== false)$qtxt .= " LIMIT $ini, $rows";

		$data = array();
		$result = $this->db->query($qtxt);
		while($obj = $this->db->fetch_obj($result)) {

			//if($text != false) $obj->translation_text = Util::highlight($obj->translation_text, $text);
			//if($main_dir == 'rtl') $obj->translation_text = '<div style="direction:rtl;">'.$obj->translation_text.'</div>';
			//if($lang_code_diff != false && $diff_dir == 'rtl') $obj->translation_text_diff = '<div style="direction:rtl;">'.$obj->translation_text_diff.'</div>';
			$obj->delete = 'ajax.adm_server.php?r=adm/lang/deleteKey&id_text='.$obj->id;
			$data[] = $obj;
		}
		return $data;
	}

	/**
	 * Return the total numbers of record for the given search params
	 * @param string $module translations only for this module
	 * @param string $text only translation that contains this words
	 * @param string $lang_code return translation in this languages (default language will be used if not setted)
	 * @param bool $only_empty return only untranslated words for the selected language
	 * @return array
	 */
	public function getCount($module = false, $text = false, $lang_code = false, $only_empty = false) {

		if(!$lang_code) $lang_code = getLanguage();
		$qtxt = "
		SELECT COUNT(*)
		FROM  %adm_lang_text AS lt
		LEFT JOIN %adm_lang_translation AS ta ON ( lt.id_text = ta.id_text AND ta.lang_code = '".$lang_code."')
		WHERE 1 ";
		if($module != false) $qtxt .= " AND lt.text_module LIKE  '".$module."' ";
		if($text != false && $only_empty == false) $qtxt .= " AND ta.translation_text LIKE '%".$text."%' ";
		if($only_empty != false) $qtxt .= " AND ta.translation_text IS NULL";

		$re = $this->db->query($qtxt);
		list($count) = $this->db->fetch_row($re);
		return $count;
	}

	public function getAllTranslation($lang_code) {

		$qtxt = "
		SELECT lt.id_text as id, lt.text_key, lt.text_module, ta.translation_text, date_format(ta.save_date,'%Y-%m-%d %H:%i:%s') as save_date, lt.plugin_id
		FROM  %adm_lang_text AS lt
		LEFT JOIN %adm_lang_translation AS ta ON ( lt.id_text = ta.id_text AND ta.lang_code = '".$lang_code."')
		WHERE 1 ";

		$data = array();
		$result = $this->db->query($qtxt);
		while($obj = $this->db->fetch_obj($result)) {

			$data[$obj->text_module][$obj->text_key][(int)$obj->plugin_id] = array($obj->id, $obj->translation_text, $obj->save_date);
		}
		return $data;
	}

	/**
	 * Return all the translation according to the passed filters
	 * @param string $module translations only for this module
	 * @param string $lang_code return translation in this languages (default language will be used if not setted)
	 * @return array
	 */
	public function getTranslation($module, $lang_code = false) {

		if(!$lang_code) $lang_code = getLanguage();

		$qtxt = "
		SELECT lt.text_key, ta.translation_text, p.priority
		FROM  %adm_lang_text AS lt
		LEFT JOIN %adm_lang_translation AS ta ON ( lt.id_text = ta.id_text AND ta.lang_code = '".$lang_code."')
		LEFT JOIN %adm_plugin AS p ON lt.plugin_id = p.plugin_id
		WHERE lt.text_module = '".$module."'
		AND ( coalesce(lt.plugin_id, 0) = 0 OR p.active = 1 )
		ORDER BY p.priority DESC";

		$data = array();
		$result = $this->db->query($qtxt);
		while($obj = $this->db->fetch_obj($result)) {
			if(key_exists($obj->text_key, $data)){
				if($obj->priority==null){
					continue;
				}
			}
			$data[$obj->text_key] = $obj->translation_text;
		}
		return $data;
	}


	/**
	 * Return all the translation according to the passed filters
	 * @param string $module translations only for this module
	 * @param string $lang_code return translation in this languages (default language will be used if not setted)
	 * @return array
	 */
	public function langTranslation($lang_code = false) {

		if(!$lang_code) $lang_code = getLanguage();

		$qtxt = "
		SELECT lt.text_module, lt.text_key, ta.translation_text
		FROM  %adm_lang_text AS lt
		LEFT JOIN %adm_lang_translation AS ta ON ( lt.id_text = ta.id_text AND ta.lang_code = '".$lang_code."') ";

		$data = array();
		$result = $this->db->query($qtxt);
		while($obj = $this->db->fetch_obj($result)) {

			$data[$obj->text_module][$obj->text_key] = $obj->translation_text;
		}
		return $data;
	}

	/**
	 * Insert a new key for a module
	 * @param string $text_key the key to add
	 * @param string $text_module the module in which the key must be inserted
	 * @param strign $text_attributes the attributes for this key (mail, sms)
	 * @return bool
	 */
	public function insertKey($text_key, $text_module, $text_attributes, $idPlugin = 0) {

		$query = "INSERT INTO %adm_lang_text "
			." ( id_text, text_key, text_module, text_attributes, plugin_id ) VALUES ( "
			." NULL, '".$text_key."', '".$text_module."', '".$text_attributes."', $idPlugin "
			.") ";
		if( !$this->db->query($query) ) return false;
		return $this->db->insert_id();
	}

	/**
	 * Delete a lang key and it's translation
	 * @param int $id_text the id of the key to delete
	 * @return bool
	 */
	public function deleteKey($id_text) {

		$query = "DELETE FROM %adm_lang_translation WHERE id_text = ".(int)$id_text." ";
		if( !$this->db->query($query)) return false;

		$query = "DELETE FROM %adm_lang_text WHERE id_text = ".(int)$id_text." ";
		if( !$this->db->query($query)) return false;

		return true;
	}

	/**
	 * Check if a language string is translated into a specific language
	 * @param int $id_text the id of the index
	 * @param string $lang_code the language code
	 * @return bool true if is translated
	 */
	public function isTranslated($id_text, $lang_code) {

		$query = "select * from %adm_lang_translation "
			."WHERE id_text = ".(int)$id_text." "
			." AND lang_code = '".$lang_code."'";
		$re = $this->db->query($query);
		return ($this->db->num_rows($re) > 0);
	}

	/**
	 * Save a new version of the translation
	 * @param int $id_text
	 * @param string $lang_code
	 * @param string $new_value
	 * @return bool
	 */
	public function saveTranslation($id_text, $lang_code, $new_value, $save_date = null) {

		if(!$this->isTranslated($id_text, $lang_code)) {

			return $this->insertTranslation($id_text, $lang_code, $new_value, $save_date);
		} else {

			return $this->updateTranslation($id_text, $lang_code, $new_value, $save_date);
		}
	}

	public function insertTranslation($id_text, $lang_code, $new_value, $save_date = null ) {

		if ( empty($save_date) ){
			$dt = 'NOW()';
		} else {
			$dt = "date_format('" . $save_date ."','%Y-%m-%d %H:%i:%s')";
		}

		$query = "INSERT INTO %adm_lang_translation "
			."( id_text, lang_code, translation_text, save_date ) VALUES ("
			." ".(int)$id_text.",  "
			." '".$lang_code."', "
			." '".$new_value."', "
			." ".$dt . " )";
		return $this->db->query($query);
	}

	public function updateTranslation($id_text, $lang_code, $new_value, $save_date = null) {

		if ( empty($save_date) ){
			$dt = 'NOW()';
		} else {
			$dt = "date_format('" . $save_date ."','%Y-%m-%d %H:%i:%s')";
		}

		$query = "UPDATE %adm_lang_translation "
				."SET translation_text = '".$new_value."', "
				." save_date = " . $dt . " "
				."WHERE id_text = ".(int)$id_text." "
				." AND lang_code = '".$lang_code."'";
		return $this->db->query($query);
	}

	public function exportTranslation($lang_code) {

		$doc = new DOMDocument('1.0');
		$root = $doc->createElement("LANGUAGES");
		$doc->appendChild($root);

		$elem = $doc->createElement("DATE");
		$elemText = $doc->createTextNode(date("Ymd"));
		$elem->appendChild($elemText);
		$root->appendChild($elem);

		$lang_info = $this->getLanguage($lang_code);

		$lang = $doc->createElement("LANG");
		$root->appendChild($lang);

		$elem = $doc->createElement("lang_code");
		$elemText = $doc->createTextNode($lang_info->lang_code);
		$elem->appendChild($elemText);
		$lang->appendChild($elem);
		$lang->setAttribute('id', $lang_info->lang_code );

		$elem = $doc->createElement("lang_description");
		$elemText = $doc->createTextNode($lang_info->lang_description);
		$elem->appendChild($elemText);
		$lang->appendChild($elem);

		$elem = $doc->createElement("lang_charset");
		$elemText = $doc->createTextNode('utf-8');
		$elem->appendChild($elemText);
		$lang->appendChild($elem);

		$elem = $doc->createElement("lang_browsercode");
		$elemText = $doc->createTextNode($lang_info->lang_browsercode);
		$elem->appendChild($elemText);
		$lang->appendChild($elem);

		$elem = $doc->createElement("lang_direction");
		$elemText = $doc->createTextNode($lang_info->lang_direction);
		$elem->appendChild($elemText);
		$lang->appendChild($elem);

		$elemPlatform = $doc->createElement("platform");
		$elemPlatform->setAttribute( "id", 'all' );
		$lang->appendChild( $elemPlatform );

		$arrModules = Docebo::langManager()->getAllModules();
		foreach( $arrModules as $module ) {
			$elemModule = $doc->createElement("module");
			$elemModule->setAttribute( "id", $module );
			$elemPlatform->appendChild( $elemModule );

			$arrTranslations = Docebo::langManager()->getModuleLangTranslations('all',$module,$lang_code, '', false, false, true);
			foreach( $arrTranslations as $tran ) {
				$elem = $doc->createElement("key");
				$elem->setAttribute('id',Docebo::langManager()->composeKey( $tran[1], $module, 'all') );
				$elem->setAttribute('attributes', $tran[3]);
				$elem->setAttribute('save_date', $tran[4]);
				$elemText = $doc->createCDATASection($tran[2]);
				$elem->appendChild($elemText);
				$elemModule->appendChild($elem);
			}
		}

		$doc->formatOutput = true;		// save XML in formatted style
		$out = $doc->saveXML();
		require_once(_lib_.'/lib.download.php');
		sendStrAsFile($out, 'lang['.$lang_info->lang_code.'].xml');

		exit();
	}

	public function importTranslation($lang_file, $overwrite, $noadd_miss, $plugin = 0) {
		$modules = 0;
		$definitions = 0;

		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = false;
		if(!$doc->load($lang_file)) {
			return false;
		}
		$xpath = new DOMXPath($doc);
		$root = $doc->documentElement;

		$langs = $xpath->query('//LANGUAGES/LANG');
		foreach($langs as $lang) {

			$lang_code = addslashes($lang->getAttribute('id'));

			$elem = $xpath->query('lang_description/text()',$lang);
			$lang_description = addslashes(urldecode($elem->item(0)->textContent));

			$elem = $xpath->query('lang_direction/text()',$lang);
			if($elem->length > 0) $lang_direction = addslashes($elem->item(0)->textContent);
			else $lang_direction = 'ltr';

			$elem = $xpath->query('lang_browsercode/text()',$lang);
			$lang_browsercode = addslashes($elem->item(0)->textContent);

			// Now we can create or update the language
			if($this->languageExist($lang_code)) {
				$re = $this->updateLanguage($lang_code, $lang_description, $lang_direction, $lang_browsercode);
			} else {
				$re = $this->newLanguage($lang_code, $lang_description, $lang_direction, $lang_browsercode);
			}

			// in order to insert the translation an the new language we can load the entire keys set
			$current_translation = $this->getAllTranslation($lang_code);

			// now we can go trough the xml keys adding them and the new translation
			$keys = $xpath->query('platform/module/key', $lang);
			$this->db->start_transaction();
			foreach($keys as $key) {

				$text_module = $key->parentNode->getAttribute('id');
				$text_key = array_pop(explode("&", str_replace('&amp;', '&', $key->getAttribute( 'id' ))));
				$text_savedt =  $key->getAttribute( 'save_date' );
				$translation = $this->cleanImport($key->nodeValue);

				$re = true;
				if(isset($current_translation[$text_module][$text_key][$plugin])) {
					//the key exists
					$id_text = $current_translation[$text_module][$text_key][$plugin][0];
					if($current_translation[$text_module][$text_key][$plugin][1] == NULL) {
						// no translation loaded
						$re = $this->insertTranslation($id_text, $lang_code, $translation, $text_savedt);
					} elseif($overwrite) {
						// a previous translation exist, and the user request an update
						$re = $this->updateTranslation($id_text, $lang_code, $translation, $text_savedt);
					}
				} elseif(!$noadd_miss) {
					// we must also create the key, and we are required to create if
					$text_attributes = $key->getAttribute('attributes');
					
					if($plugin === 0){
						$id_text = $this->insertKey($text_key, $text_module, $text_attributes);
					} else {
						$id_text = $this->insertKey($text_key, $text_module, $text_attributes, $plugin);
					}
					//now we can insert the translation
					if($id_text) $re = $this->insertTranslation($id_text, $lang_code, $translation, $text_savedt);
				}
				if($re) $definitions++;

			} // end foreach
			$this->db->commit();
		}
		return $definitions;
	}

	protected function cleanImport($text) {

		if (preg_match("/^<!\\[CDATA\\[/i", $text))
			$translation= addslashes(trim(preg_replace("/<!\\[CDATA\\[(.*?)\\]\\]>/si", "\$1", $text)));
		else
			$translation = addslashes(trim(urldecode($text)));
		return $translation;
	}

}
