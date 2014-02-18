<?php



class LangAdm {

	public function __construct() {}
	/**
	 * Insert a new language into the database
	 * @param <type> $lang_code
	 * @param <type> $lang_description
	 * @param <type> $lang_direction
	 * @param <type> $lang_browsercode
	 */
	public function newLanguage($lang_code, $lang_description, $lang_direction, $lang_browsercode) {

		$query = "INSERT INTO core_lang_language "
			." (lang_code, lang_description, lang_direction, lang_browsercode) VALUES ("
			." '".$lang_code."', "
			." '".$lang_description."', "
			." '".$lang_direction."', "
			." '".$lang_browsercode."' "
			.")";
		if( !mysql_query($query)) return false;
		return true;
	}

	/**
	 * Return true if the language exist, false otherwise
	 * @param string $lang_code the lang_code to fined
	 * @return bool
	 */
	public function languageExist($lang_code) {
	 	$query = "SELECT lang_code "
				." FROM core_lang_language "
				." WHERE lang_code = '".$lang_code."'";
		$rs = mysql_query($query);
		return ( mysql_num_rows($rs) > 0);
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

		$query = " UPDATE core_lang_language ";
		$query .= " SET lang_description = '".$lang_description."', lang_direction = '".$lang_direction."', lang_browsercode = '".$lang_browsercode."' ";
		$query .= " WHERE lang_code = '".$lang_code."' ";
		if( !mysql_query($query)) return false;
		return true;
	}

	public function getAllTranslation($lang_code) {

		$qtxt = "
		SELECT lt.id_text as id, lt.text_key, lt.text_module, ta.translation_text
		FROM  core_lang_text AS lt
		LEFT JOIN core_lang_translation AS ta ON ( lt.id_text = ta.id_text AND ta.lang_code = '".$lang_code."')
		WHERE 1 ";

		$data = array();
		$result = mysql_query($qtxt);
		while($obj = mysql_fetch_object($result)) {

			$data[$obj->text_module][$obj->text_key] = array($obj->id, $obj->translation_text);
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
	public function insertKey($text_key, $text_module, $text_attributes) {

		$query = "INSERT INTO core_lang_text "
			." ( id_text, text_key, text_module, text_attributes ) VALUES ( "
			." NULL, '".$text_key."', '".$text_module."', '".$text_attributes."' "
			.") ";
		if( !mysql_query($query) ) return false;
		return mysql_insert_id();
	}

	public function insertTranslation($id_text, $lang_code, $new_value) {

		$query = "INSERT INTO core_lang_translation "
			."( id_text, lang_code, translation_text, save_date ) VALUES ("
			." ".(int)$id_text.",  "
			." '".$lang_code."', "
			." '".$new_value."', "
			." NOW() )";
		return mysql_query($query);
	}

	public function updateTranslation($id_text, $lang_code, $new_value) {

		$query = "UPDATE core_lang_translation "
				."SET translation_text = '".$new_value."', "
				." save_date = NOW() "
				."WHERE id_text = ".(int)$id_text." "
				." AND lang_code = '".$lang_code."'";
		return mysql_query($query);
	}

	public function importTranslation($lang_file, $overwrite, $noadd_miss) {
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
			mysql_query("START TRANSACTION");
			foreach($keys as $key) {

				$text_module = $key->parentNode->getAttribute('id');
				$text_key = array_pop(explode("&", str_replace('&amp;', '&', $key->getAttribute( 'id' ))));
				$translation = $this->cleanImport($key->nodeValue);

				$re = true;
				if(isset($current_translation[$text_module][$text_key])) {
					//the key exists
					$id_text = $current_translation[$text_module][$text_key][0];
					if($current_translation[$text_module][$text_key][1] == NULL) {
						// no translation loaded
						$re = $this->insertTranslation($id_text, $lang_code, $translation);
					} elseif($overwrite) {
						// a previous translation exist, and the user request an update
						$re = $this->updateTranslation($id_text, $lang_code, $translation);
					}
				} elseif(!$noadd_miss) {
					// we must also create the key, and we are required to create if
					$text_attributes = $key->getAttribute('attributes');
					$id_text = $this->insertKey($text_key, $text_module, $text_attributes);
					//now we can insert the translation
					if($id_text) $re = $this->insertTranslation($id_text, $lang_code, $translation);
				}
				if($re) $definitions++;

			} // end foreach
			mysql_query("COMMIT");
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
