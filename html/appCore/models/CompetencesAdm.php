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

class CompetencesAdm extends Model {

	protected $db;

	//--- init functions ---------------------------------------------------------

	public function __construct() {
		$this->db = DbConn::getInstance();
	}

	public function getPerm()	{
		return array(
			'view'						=> 'standard/view.png',
			'add'							=> 'standard/add.png',
			'mod'							=> 'standard/edit.png',
			'del'							=> 'standard/delete.png',
			'associate_user'	=> 'standard/moduser.png'
		);
	}


	//--- private internal methods -----------------------------------------------

	public function _getCompetencesTable() { return "%lms_competence"; }
	public function _getCompetencesLangTable() { return "%lms_competence_lang"; }

	public function _getCategoriesTable() { return "%lms_competence_category"; }
	public function _getCategoriesLangTable() { return "%lms_competence_category_lang"; }

	public function _getCompetencesUsersTable() { return "%lms_competence_user"; }
	public function _getCompetencesCoursesTable() { return "%lms_competence_course"; }
	public function _getCompetencesRequiredTable() { return "%lms_competence_required"; }
	public function _getCompetencesTrackTable() { return "%lms_competence_track"; }
	public function _getCompetencesFncRoleTable() { return "%adm_fncrole_competence"; }
	public function _getCourseUserTable() { return "%lms_courseuser"; }

	protected function _shiftRL($from, $shift) {
		$query1 = "UPDATE ".$this->_getCategoriesTable()." SET iLeft = iLeft + ".$shift." WHERE iLeft >= ".$from;
		$query2 = "UPDATE ".$this->_getCategoriesTable()." SET iRight = iRight + ".$shift." WHERE iRight >= ".$from;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);
	}

	protected function _shiftRLSpecific($from, $to, $shift) {
		$query1 = "UPDATE ".$this->_getCategoriesTable()." SET iLeft = iLeft + ".$shift." WHERE iLeft >= ".$from." AND iRight <= ".$to;
		$query2 = "UPDATE ".$this->_getCategoriesTable()." SET iRight = iRight + ".$shift." WHERE iRight >= ".$from." AND iRight <= ".$to;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);
	}

	//--- operative methods --------------------------------------------------



	public function getCompetenceTypologies() {
		return array(
			'skill' => Lang::t('_COMPETENCES_TYPOLOGY_SKILL', 'competences'),
			'attitude' => Lang::t('_COMPETENCES_TYPOLOGY_ATTITUDE', 'competences'),
			'knowledge' => Lang::t('_COMPETENCES_TYPOLOGY_KNOWLEDGE', 'competences')
		);
	}

	public function getCompetenceTypes() {
		return array(
			'score' => Lang::t('_SCORE', 'competences'),
			'flag' => Lang::t('_TYPE_FLAG', 'competences')
		);
	}


	/*
	 * Returns an array of sub-categories given a parent category node
	 */
	public function getCategories($id_parent, $language = false) {
		$lang_code = ($language == false ? getLanguage() : $language);
		$query = "SELECT	t1.id_category, t2.name, t1.level, t1.iLeft, t1.iRight "
			." FROM ".$this->_getCategoriesTable()." AS t1 LEFT JOIN ".$this->_getCategoriesLangTable()." AS t2 "
			." ON (t1.id_category = t2.id_category AND t2.lang_code = '".$lang_code."' ) "
			." WHERE t1.id_parent = '".(int)$id_parent."' ORDER BY t2.name";
		$res = $this->db->query($query);
		if (!$res) return false;

		//count competences contained in each extracted node
		$count_competences = $this->getCategoryCompetencesCount();

		$output = array();
		while(list($id, $translation, $level, $left, $right) = $this->db->fetch_row($res)) {
			$label = $translation;
			$is_leaf = ($right-$left) == 1;
			$count = (int)(($right-$left-1)/2);
			$style = false;
		
			//set node for output
			$output[$id] = array(
				'id' => $id,
				'label' => $label,
				'is_leaf' => $is_leaf,
				'count_content' => $count,
				'count_objects' => (isset($count_competences[$id]) ? (int)$count_competences[$id] : 0),
				'style' => $style
			);
		}

		return array_values($output);
	}


	/*
	 * returns an ordered list of ids (like a path)
	 */
	public function getOpenedCategories($node_id, $language = false) {
		$folders = array(0);
		if (!$language) $language = getLanguage();
		if ($node_id <= 0) return $folders;
		list($ileft, $iright) = $this->getCategoryLimits($node_id);
		$query = "SELECT id_category FROM ".$this->_getCategoriesTable()." "
			." WHERE iLeft<=".$ileft." AND iRight>=".$iright." AND id_category>0 ORDER BY iLeft";
		$res = $this->db->query($query);
		if ($res) {
			while (list($id_org) = $this->db->fetch_row($res)) { $folders[] = (int)$id_org; }
			return  $folders;
		} else
			return false;
	}



	public function getInitialCategories($node_id, $language = false) {
		$results = array();

		$folders = $this->getOpenedCategories($node_id);
		if ($folders === false) return false;

		$ref =& $results;
		foreach ($folders as $folder) {

			if ($folder > 0) {
				for ($i=0; $i<count($ref); $i++) {
					if ($ref[$i]['node']['id'] == $folder) {
						$ref[$i]['children'] = array();
						$ref =& $ref[$i]['children'];
						break;
					}
				}
			}

			$children = $this->getCategories($folder, $language);
			foreach ($children as $child) {
				$ref[] = array(
					'node' => array(
						'id' => $child['id'],
						'label' => $child['label'],
						'is_leaf' => $child['is_leaf'],
						'count_content' => $child['count_content'],
						'count_objects' => $child['count_objects'],
						'options' => array(),
						'style' => false
					)
				);
			}
		}

		return $results;
	}


	public function getCompetences($id_category) {
		$lang_code = ($language == false ? getLanguage() : $language);
		$query = "SELECT	t1.* "
			." FROM ".$this->_getCompetencesTable()." AS t1 LEFT JOIN ".$this->_getCompetencesLangTable()." AS t2 "
			." ON (t1.id_competence = t2.id_competence AND t2.lang_code = '".$lang_code."' ) "
			." WHERE t1.id_category = '".(int)$id_category."' ORDER BY t2.translation";
		$res = $this->db->query($query);

		$output = array();
		while ($obj = $this->db->fetch_obj($res)) {
			$output[] = $obj;
		}

		return $output;
	}


	public function getCategoryCompetencesCount() {
		$output = array();
		$query = "SELECT id_category, COUNT(*) FROM ".$this->_getCompetencesTable()." GROUP BY id_category";
		$res = $this->db->query($query);
		while (list($id_category, $count) = $this->db->fetch_row($res)) {
			$output[$id_category] = (int)$count;
		}
		return $output;
	}



	/*
	 * returns iLeft and iRight of a node
	 */
	public function getCategoryLimits($id_category) {
		$row = false;
		if ($id_category <= 0) {
			$query = "SELECT MIN(iLeft), MAX(iRight), 0 FROM ".$this->_getCategoriesTable();
			$res = $this->db->query($query);
			$row = $this->db->fetch_row($res);
			if (is_array($row)) {
				$row[0]--;
				$row[1]++;
			}
		} else {
			$query = "SELECT iLeft, iRight, level FROM ".$this->_getCategoriesTable()." WHERE id_category=".(int)$id_category;
			$res = $this->db->query($query);
			$row = $this->db->fetch_row($res);
		}
		return $row;
	}
	
	public function getSubCategories($id_category) {
		list($left, $right, $level) = $this->getCategoryLimits($id_category);
		$query = "SELECT id_category FROM ".$this->_getCategoriesTable()." "
			." WHERE iLeft >= ".(int)$left." AND iRight <= ".(int)$right;
		$res = $this->db->query($query);
		$output = array();
		if ($id_category <= 0) $output[] = 0;
		if ($res) {
			while (list($sub) = $this->db->fetch_row($res))
				$output[] = $sub;
		}
		return $output;
	}


	public function moveCategory($src_folder, $dest_folder) {
		if ($src_folder <= 0) return false;
		if ($dest_folder <= 0) return false;
		$output = true;//false;

		list($src_left, $src_right, $lvl_src) = $this->getCategoryLimits($src_folder);
		list($dest_left, $dest_right, $lvl_dest) = $this->getCategoryLimits($dest_folder);

		//dest folder is a son of the src ?
		if($src_left < $dest_left && $src_right > $dest_right) return $output;

		$dest_left = $dest_left + 1;
		$gap = $src_right - $src_left + 1;

		$this->_shiftRL($dest_left, $gap);
		if ($src_left >= $dest_left) {
			// this happen when the src has shiften too
			$src_left += $gap;
			$src_right += $gap;
		}

		// update level for descendants
		$lvl_gap = $lvl_dest - $lvl_src + 1;
		$query1 = "UPDATE ".$this->_getCategoriesTable()." SET id_parent = ".(int)$dest_folder." WHERE id_category = ".(int)$src_folder;
		$query2 = "UPDATE ".$this->_getCategoriesTable()." SET level = level + ".$lvl_gap." WHERE iLeft > ".$src_left." AND iRight < ".$src_right;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);

		// move the subtree
		$this->_shiftRLSpecific($src_left, $src_right, $dest_left - $src_left);

		// fix values from the gap created
		$this->_shiftRL($src_right + 1, -$gap);

		return $output;
	}





	public function getCompetencesList($id_category, $descendants, $pagination, $filter = false) {
		//check if descendants are requested
		$categories = array();
		if ($descendants) {
			//retrieve sub categories folders and their id
			$categories = $this->getSubCategories($id_category);
		} else {
			//only the specified category id is needed
			$categories[] = $id_category;
		}

		//validate pagination data
		if (!is_array($pagination)) $pagination = array();
		$_startIndex = (isset($pagination['startIndex']) ? (int)$pagination['startIndex'] : 0);
		$_results = (isset($pagination['results']) ? (int)$pagination['results'] : Get::sett('visuItem', 25));
		$_sort = 't2.name';
		$_dir = 'ASC';

		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'description': $_sort = 't2.description'; break;
				case 'score': $_sort = 't1.score'; break;
				case 'typology': $_sort = 't1.typology'; break;
				case 'type': $_sort = 't1.type'; break;
				case 'expiration': $_sort = 't1.expiration'; break;
			}
		}

		if (isset($pagination['dir'])) {
			switch (strtoupper($pagination['dir'])) {
				case 'ASC': $_dir = 'ASC'; break;
				case 'DESC': $_dir = 'DESC'; break;
			}
		}

		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter) && isset($filter['text']) && $filter['text'] != '')
			$_filter .= " AND (t2.name LIKE '%".$filter['text']."%' OR t2.description LIKE '%".$filter['text']."%') ";
			//$_filter .= " AND t2.name LIKE '%".$filter['text']."%' ";

		//validate language for name and description
		$_language = (!empty($filter) && isset($filter['language']) ? $filter['language'] : getLanguage());

		//mount query
		$query = "SELECT t1.id_competence, t1.id_category, t1.typology, t1.type, "// t1.score, t1.expiration, "
			." t2.name, t2.description "
			." FROM ".$this->_getCompetencesTable()." as t1 "
			." LEFT JOIN ".$this->_getCompetencesLangTable()." as t2 "
			." ON (t1.id_competence = t2.id_competence AND t2.lang_code = '".$_language."') "
			." WHERE id_category IN (".implode(",", $categories).") ".$_filter
			." ORDER BY ".$_sort." ".$_dir." "
			." LIMIT ".(int)$_startIndex.", ".(int)$_results;
		$res = $this->db->query($query);

		//extract records from database
		$output = array();
		if ($res && $this->db->num_rows($res)>0) {
			//$_arr_competences = array();
			while ($obj = $this->db->fetch_obj($res)) {
				$output[$obj->id_competence] = $obj;
			}

			if (count($output) > 0) {
				//count users with competence for every competence
				$_users = array();
				$query = "SELECT id_competence, COUNT(*) as count FROM ".$this->_getCompetencesUsersTable()." "
					." WHERE id_competence IN (".implode(",", array_keys($output)).") GROUP BY id_competence";
				$res = $this->db->query($query);
				while (list($id_competence, $count) = $this->db->fetch_row($res)) {
					$_users[$id_competence] = $count;
				}
				while (list($key, $value) = each($output)) {
					$value->users = (array_key_exists($key, $_users) ? (int)$_users[$key] : 0);
				}

				//for every competence, count courses assigned
				$_courses = array();
				$query = "SELECT id_competence, COUNT(*) as count FROM ".$this->_getCompetencesCoursesTable()." "
					." WHERE id_competence IN (".implode(",", array_keys($output)).") GROUP BY id_competence";
				$res = $this->db->query($query);
				while (list($id_competence, $count) = $this->db->fetch_row($res)) {
					$_courses[$id_competence] = $count;
				}
				while (list($key, $value) = each($output)) {
					$value->courses = (array_key_exists($key, $_courses) ? (int)$_courses[$key] : 0);
				}
			}
			reset($output);

		}

		return array_values($output);
	}


	public function getCompetencesTotal($id_category, $descendants, $filter = false) {
		//check if descendants are requested
		$categories = array();
		if ($descendants) {
			//retrieve sub categories folders and their id
			$categories = $this->getSubCategories($id_category);
		} else {
			//only the specified category id is needed
			$categories[] = $id_category;
		}

		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter) && isset($filter['text']) && $filter['text'] != '')
			$_filter .= " AND (t2.name LIKE '%".$filter['text']."%' OR t2.description LIKE '%".$filter['text']."%') ";
			//$_filter .= " AND t2.name LIKE '%".$filter['text']."%' ";

		//validate language for name and description
		$_language = (!empty($filter) && isset($filter['language']) ? $filter['language'] : getLanguage());

		//mount query
		$query = "SELECT COUNT(*) "
			." FROM ".$this->_getCompetencesTable()." as t1 "
			." LEFT JOIN ".$this->_getCompetencesLangTable()." as t2 "
			." ON (t1.id_competence = t2.id_competence AND t2.lang_code = '".$_language."') "
			." WHERE id_category IN (".implode(",", $categories).") ".$_filter;
		$res = $this->db->query($query);

		//extract total value database
		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}


	public function getAllCategories($language = false, $keys = false) {
		$output = array();
		$_language = (!$language ? getLanguage() : $language);
		$query = "SELECT c.id_category, c.id_parent, c.level, c.iLeft, c.iRight, cl.name, cl.description, cl.lang_code "
			." FROM ".$this->_getCategoriesTable()." as c "
			." LEFT JOIN ".$this->_getCategoriesLangTable()." as cl "
			." ON (c.id_category = cl.id_category AND lang_code='".$_language."') "
			." ORDER BY c.iLeft";
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				if ($keys)
					$output[$obj->id_category] = $obj;
				else
					$output[] = $obj;
			}
		}
		return $output;
	}


	/*
	 * returns a flat list of all the competences, in a given language
	 */
	public function getAllCompetences($language = false, $keys = false) {
		$output = array();
		$_language = (!$language ? getLanguage() : $language);
		$query = "SELECT c.id_competence, c.id_category, c.typology, c.type, cl.name, cl.description "
			." FROM ".$this->_getCompetencesTable()." as c "
			." LEFT JOIN ".$this->_getCompetencesLangTable()." as cl "
			." ON (c.id_competence = cl.id_competence AND lang_code='".$_language."') "
			." ORDER BY cl.name";
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				if ($keys)
					$output[$obj->id_competence] = $obj;
				else
					$output[] = $obj;
			}
		}
		return $output;
	}



	public function getCategoryInfo($id_category) {
		//extract competence data
		$query = "SELECT * FROM ".$this->_getCategoriesTable()." "
			." WHERE id_category = ".(int)$id_category;
		$res = $this->db->query($query);
		$output = $this->db->fetch_obj($res);

		//initialize languages array
		$lang_codes = Docebo::langManager()->getAllLangCode();
		$langs = array();
		for ($i=0; $i<count($lang_codes); $i++) {
			$langs[$lang_codes[$i]] = array(
				'name' => '',
				'description' => ''
			);
		}

		//extract languages from database
		$query = "SELECT * FROM ".$this->_getCategoriesLangTable()." "
			." WHERE id_category = ".(int)$id_category;
		$res = $this->db->query($query);
		while ($obj = $this->db->fetch_obj($res)) {
			if (in_array($obj->lang_code, $lang_codes)) {
				$langs[$obj->lang_code]['name'] = $obj->name;
				$langs[$obj->lang_code]['description'] = $obj->description;
			}
		}

		$output->langs = $langs;
		return $output;
	}


	public function getCategoryName($id_category, $language = false) {
		$lang_code = (!$language ? getLanguage() : $language);
		$output = '';
		$query = "SELECT name FROM ".$this->_getCategoriesLangTable()." "
			." WHERE id_category = ".(int)$id_category." AND lang_code = '".$lang_code."'";
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res) > 0) {
			list($name) = $this->db->fetch_row($res);
			$output = $name;
		}
		return $output;
	}


	public function getCompetenceInfo($id_competence) {
		//extract competence data
		$query = "SELECT * FROM ".$this->_getCompetencesTable()." "
			." WHERE id_competence = ".(int)$id_competence;
		$res = $this->db->query($query);
		$output = $this->db->fetch_obj($res);

		//initialize languages array
		$lang_codes = Docebo::langManager()->getAllLangCode();
		$langs = array();
		for ($i=0; $i<count($lang_codes); $i++) {
			$langs[$lang_codes[$i]] = array(
				'name' => '',
				'description' => ''
			);
		}

		//extract languages from database
		$query = "SELECT * FROM ".$this->_getCompetencesLangTable()." "
			." WHERE id_competence = ".(int)$id_competence;
		$res = $this->db->query($query);
		while ($obj = $this->db->fetch_obj($res)) {
			if (in_array($obj->lang_code, $lang_codes)) {
				$langs[$obj->lang_code]['name'] = $obj->name;
				$langs[$obj->lang_code]['description'] = $obj->description;
			}
		}

		$output->langs = $langs;
		return $output;
	}


	public function getCompetencesInfo($arr_competences) {
		if (is_numeric($arr_competences)) $arr_competences = array($arr_competences);
		if (!is_array($arr_competences)) return false;
		if (count($arr_competences) <= 0) return array();

		//extract competence data
		$output = array();
		$query = "SELECT * FROM ".$this->_getCompetencesTable()." "
			." WHERE id_competence IN (".implode(',', $arr_competences).")";
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[$obj->id_competence] = $obj;
			}
		} else {
			return false;
		}

		//initialize languages array
		$lang_codes = Docebo::langManager()->getAllLangCode();
		$_void_lang_arr = array();
		for ($i=0; $i<count($lang_codes); $i++) {
			$_void_lang_arr[$lang_codes[$i]] = array(
				'name' => '',
				'description' => ''
			);
		}
		$langs = array();
		$_arr_competences = array_keys($output);
		foreach ($_arr_competences as $id_competence) {
			$langs[$id_competence] = $_void_lang_arr;
		}

		//extract languages from database
		$query = "SELECT * FROM ".$this->_getCompetencesLangTable()." "
			." WHERE id_competence IN (".implode(',', $_arr_competences).")";
		$res = $this->db->query($query);
		while ($obj = $this->db->fetch_obj($res)) {
			if (in_array($obj->lang_code, $lang_codes)) {
				$langs[$obj->id_competence][$obj->lang_code]['name'] = $obj->name;
				$langs[$obj->id_competence][$obj->lang_code]['description'] = $obj->description;
			}
		}

		while (list($key, $value) = each($output)) {
			if (isset($langs[$key])) {
				$value->langs = $langs[$key];
			}
		}

		reset($output);
		return $output;
	}


	public function getCountCompetences($id_category = false) {
		$query = "SELECT COUNT(*) FROM ".$this->_getCompetencesTable()." "
			.(is_numeric($id_category) ? " WHERE id_category = ".(int)$id_category : "");
		$res = $this->db->query($query);
		list($output) = $this->db->fetch_row($res);
		return $output;
	}


	/*
	 * add a node the the categories tree
	 */
	public function createCategory($id_parent, $langs) {
		if (!is_array($langs) || $id_parent < 0) return false;

		$output = false;
		if (is_array($langs)) {

			//adjust iLeft and iRight values in the categories tree
			list($left, $right, $level) = $this->getCategoryLimits($id_parent);

			//updating left limits
			$query = "UPDATE ".$this->_getCategoriesTable()." SET iRight=iRight+2 WHERE iRight>=".$right;
			$rsl = $this->db->query($query);
			//TO DO: handle error case (if !$rs ... )

			//updating right limits
			$query = "UPDATE ".$this->_getCategoriesTable()." SET iLeft=iLeft+2 WHERE iLeft>=".$right;
			$rsr = $this->db->query($query);
			//TO DO: handle error case (if !$rs ... )

			//insert node in the table, with newly calculated iLeft and iRight
			$query = "INSERT INTO ".$this->_getCategoriesTable()." (id_category, id_parent, level, iLeft, iRight) VALUES "
				."(NULL, '".(int)$id_parent."', '".((int)$level + 1)."', ".(int)$right.", ".((int)$right + 1).")";
			$res = $this->db->query($query);

			//if node has been correctly inserted then ...
			if ($res) {
				$id = $this->db->insert_id();

				//insert languages in database
				$conditions = array();
				foreach ($langs as $lang_code => $translation) { //TO DO: check if lang_code exists ...
					$name = $translation['name'];
					$description = $translation['description'];
					$conditions[] = "(".(int)$id.", '".$lang_code."', '".$name."', '".$description."')";
				}
				$query = "INSERT INTO ".$this->_getCategoriesLangTable()." (id_category, lang_code, name, description) "
					." VALUES ".implode(",", $conditions);
				$res = $this->db->query($query);
				if ($res) $output = $id;
			} else {
				$output = false;
			}
		}

		return $output;
	}


	public function updateCategory($id_category, $langs) {
		$output = false;

		$prev_lang = array();
		$re = $this->db->query("SELECT lang_code FROM ".$this->_getCategoriesLangTable()." WHERE id_category = ".(int)$id_category);
		while(list($lang_code) = $this->db->fetch_row($re)) {
			$prev_lang[$lang_code] = $lang_code;
		}

		if ($id_category > 0) {
				//insert languages in database
				foreach ($langs as $lang_code => $translation) { //TO DO: check if lang_code exists ...
					$name = $translation['name'];
					$description = $translation['description'];

					if(isset($prev_lang[$lang_code])) {
						
						$query = "UPDATE ".$this->_getCategoriesLangTable()." "
							." SET name = '".$name."', description = '".$description."' "
							." WHERE id_category = ".(int)$id_category." AND lang_code = '".$lang_code."'";
						$res = $this->db->query($query);
					} else {
						
						$query = "INSERT INTO ".$this->_getCategoriesLangTable()." "
							." (id_category, lang_code, name) VALUES "
							." (".(int)$id_category.", '".$lang_code."', '".$name."')";
						$res = $this->db->query($query);
					}					
				}
				$output = true; //TO DO: improve error detection in queries ...
		}

		return $output;
	}


	public function deleteCategory($id_category) {
		/*
		//delete category row from DB
		$query = "DELETE FROM ".$this->_getCategoriesTable()." WHERE id_category=".(int)$id_category;
		$res = $this->db->query($query);
		//delete languages from DB
		if ($res) {
			$query = "DELETE FROM ".$this->_getCategoriesLangTable()." WHERE id_category=".(int)$id_category;
			$res = $this->db->query($query);
		}
		return ($res ? true : false);
		*/

		if ($id_category <= 0) return false;

		list($left, $right, $level) = $this->getCategoryLimits($id_category);

		//we are allowed to delete only leaf folder nodes with no competences
		if (($right - $left) > 1 ) return false;
		if ($this->getCountCompetences($id_category) > 0) return false;

/*
		//delete sub tree nodes: useless, since we can't delete non-leaf nodes
		$query = "SELECT id_category FROM ".$this->_getCategoriesTable()." WHERE iLeft>=".$left." AND iRight<=" .$right;
		$res = $this->db->query($query);
		$nodes = array();
		while (list($node) = $this->db->fetch_row($res)) $nodes[] = $node;

		$query = "DELETE FROM ".$this->_getCategoriesTable()." WHERE iLeft>=".$left." AND iRight<=" .$right;
		$res = $this->db->query($query);
		$shift = $right - $left + 1; //or -1 ??
*/
		
		//delete node and then update tree iLefts and iRights
		$query = "DELETE FROM ".$this->_getCategoriesTable()." WHERE id_category=".(int)$id_category;
		$res = $this->db->query($query);
		if ($res) {
			//update indexes
			$shift = 2;
			$query = "UPDATE ".$this->_getCategoriesTable()." SET iLeft=iLeft-".$shift." WHERE iLeft>=".$left;
			$res = $this->db->query( $query );
			$query = "UPDATE ".$this->_getCategoriesTable()." SET iRight=iRight-".$shift." WHERE iRight>=".$right;
			$res = $this->db->query( $query );

			//delete languages from DB
			$query = "DELETE FROM ".$this->_getCategoriesLangTable()." WHERE id_category=".(int)$id_category;
			$res = $this->db->query($query);
			
			return true;
		} else
			return false;
	}



	public function createCompetence($id_category, $params) {
		//check if given id is valid
		if ($id_category < 0) return false;

		//validate input data
		$_typology = (property_exists($params, 'typology') ? $params->typology : 'skill');
		$_type = (property_exists($params, 'type') ? $params->type : 'score');
		//$_score = (property_exists($params, 'score') ? $params->score : 0);
		//$_expiration = (property_exists($params, 'expiration') ? (int)$params->expiration : 0);

		if (!array_key_exists($_typology, $this->getCompetenceTypologies())) $_typology = 'skill';
		if (!array_key_exists($_type, $this->getCompetenceTypes())) $_type = 'score';
		if (!is_numeric($_score)) $_score = 0;

		//compose query
		$query = "INSERT INTO ".$this->_getCompetencesTable()." "
			." (id_category, typology, type) VALUES "//, score, expiration) VALUES "
			." (".(int)$id_category.", '".$_typology."', '".$_type."')";
			//." ".$_score.", ".$_expiration.")";
		$res = $this->db->query($query);

		//manage languages
		if ($res) {
			if (property_exists($params, 'langs')) {
				$id = $this->db->insert_id();

				//insert languages in database
				$conditions = array();
				foreach ($params->langs as $lang_code => $translation) { //TO DO: check if lang_code exists ...
					$name = $translation['name'];
					$description = $translation['description'];
					$conditions[] = "(".(int)$id.", '".$lang_code."', '".$name."', '".$description."')";
				}
				$query = "INSERT INTO ".$this->_getCompetencesLangTable()." "
					." (id_competence, lang_code, name, description) "
					." VALUES ".implode(",", $conditions);
				$res = $this->db->query($query);
				$output = ($res ? $id : false);
				return $output;
			}
		} else {
			return false;
		}
	}


	public function updateCompetence($id_competence, $params) {
		//check if given id is valid
		if ($id_competence <= 0) return false;

		$conditions = array();

		if (property_exists($params, 'id_category')) $conditions[] = " id_category = '".$params->id_category."' ";
		if (property_exists($params, 'typology')) $conditions[] = " typology = '".$params->typology."' ";
		if (property_exists($params, 'type')) $conditions[] = " type = '".$params->type."' ";
		//if (property_exists($params, 'score')) $conditions[] = " score = '".$params->score."' ";
		//if (property_exists($params, 'expiration')) $conditions[] = " expiration = '".$params->expiration."' ";

		$output = true;
		if (count($conditions) > 0) {
			$query = "UPDATE ".$this->_getCompetencesTable()." SET "
				.implode(', ', $conditions)." WHERE id_competence = ".(int)$id_competence;
			$res = $this->db->query($query);
			$output = $res ? true : false;
		}

		if ($output) {
			//insert languages in database
			if (property_exists($params, 'langs')) {
				$langs = Docebo::langManager()->getAllLangcode();
				$arr_langs = array();
				foreach ($langs as $lang_code) {
					if (isset($params->langs[$lang_code])) {
						$_name = $params->langs[$lang_code]['name'];
						$_description = $params->langs[$lang_code]['description'];
					} else {
						$_name = "";
						$_description = "";
					}
					$arr_langs[$lang_code] = array(
						'name' => $_name,
						'description' => $_description
					);
				}

				$prev_lang = array();
				$re = $this->db->query("SELECT lang_code FROM ".$this->_getCompetencesLangTable()." WHERE id_competence = ".(int)$id_competence);
				while(list($lang_code) = $this->db->fetch_row($re)) {
					$prev_lang[$lang_code] = $lang_code;
				}

				$conditions = array();
				foreach ($arr_langs as $lang_code => $translation) { //TO DO: check if lang_code exists ...
					$name = $translation['name'];
					$description = $translation['description'];

					if(isset($prev_lang[$lang_code])) {
						
						$query = "UPDATE ".$this->_getCompetencesLangTable()." "
							." SET name = '".$name."', description = '".$description."' "
							." WHERE id_competence = ".(int)$id_competence." AND lang_code = '".$lang_code."'";
						$res = $this->db->query($query);
					} else {
						
						$query = "INSERT INTO ".$this->_getCompetencesLangTable()." "
							." (id_competence, lang_code, name, description) VALUES "
							." (".(int)$id_competence.", '".$lang_code."', '".$name."', '".$description."')";
						$res = $this->db->query($query);
					}
				}
			}
		}

		return $output;
	}


	public function deleteCompetence($id_competence) {
		//delete category row from DB
		$query = "DELETE FROM ".$this->_getCompetencesTable()." WHERE id_competence=".(int)$id_competence;
		$res = $this->db->query($query);

		//cascade actions
		if ($res) {
			//delete languages from DB
			$query = "DELETE FROM ".$this->_getCompetencesLangTable()." WHERE id_competence=".(int)$id_competence;
			$res = $this->db->query($query);

			//delete reference to this competence in functional roles
			$query = "DELETE FROM %adm_fncrole_competence WHERE id_competence=".(int)$id_competence;
			$res = $this->db->query($query);
		}

		return ($res ? true : false);
	}


	public function getCompetenceName($id_competence, $language = false) {
		$lang_code = (!$language ? getLanguage() : $language);
		$output = '';
		$query = "SELECT name FROM ".$this->_getCompetencesLangTable()." "
			." WHERE id_competence = ".(int)$id_competence." AND lang_code = '".$lang_code."'";
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res) > 0) {
			list($name) = $this->db->fetch_row($res);
			$output = $name;
		}
		return $output;
	}


	public function getCompetenceType($id_competence) {
		if ($id_competence <= 0) return false;
		$query = "SELECT type FROM ".$this->_getCompetencesTable()." WHERE id_competence = ".(int)$id_competence;
		$res = $this->db->query($query);
		list($type) = $this->db->fetch_row($res);
		return $type;
	}


	public function getRequiredUsers($id_competence) {
		//check competence validity
		if ($id_competence <= 0) return false;

		//search users/groups with required competence
		$output = false;
		$query = "SELECT idst FROM ".$this->_getCompetencesRequiredTable()." WHERE id_competence = ".(int)$id_competence;
		$res = $this->db->query($query);
		$list = array();
		if ($res) {
			$output = array();
			while (list($idst) = $this->db->fetch_row($res)) {
				$list[] = $idst;
			}
		}
		
		//retrive users list from idst list
		if (count($list) > 0) {
			$acl_man = Docebo::user()->getAclManager();
			$output = $acl_man->getAllUsersFromIdst($list);
		}
		
		return $output;
	}

	//----------------------------------------------------------------------------

	public function getCompetenceUsersList($id_competence, $pagination, $filter = false) {

		//validate pagination data
		if (!is_array($pagination)) $pagination = array();
		$_startIndex = (isset($pagination['startIndex']) ? (int)$pagination['startIndex'] : 0);
		$_results = (isset($pagination['results']) ? (int)$pagination['results'] : Get::sett('visuItem', 25));
		$_sort = 'u.userid';
		$_dir = 'ASC';

		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'firstname': $_sort = 'u.firstname'; break;
				case 'lastname': $_sort = 'u.lastname'; break;
				case 'score': $_sort = 'cu.score_got'; break;
				case 'last_assign_date': $_sort = 'cu.last_assign_date'; break;
				//case 'date_expire': $_sort = 'cu.date_expire'; break;
			}
		}

		if (isset($pagination['dir'])) {
			switch (strtoupper($pagination['dir'])) {
				case 'ASC': $_dir = 'ASC'; break;
				case 'DESC': $_dir = 'DESC'; break;
			}
		}

		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter) && isset($filter['text']) && $filter['text'] != '')
			$_filter .= " AND (u.userid LIKE '%".$filter['text']."%' "
				." OR u.firstname LIKE '%".$filter['text']."%' "
				." OR u.lastname LIKE '%".$filter['text']."%') ";

		$competence_type = $this->getcompetenceType($id_competence);
		$query = "SELECT cu.id_user, cu.score_got, cu.last_assign_date, "
			." u.userid, u.firstname, u.lastname "
			." FROM ".$this->_getCompetencesUsersTable()." as cu JOIN %adm_user as u "
			." ON (cu.id_user = u.idst) "
			." WHERE cu.id_competence = ".(int)$id_competence." ".$_filter
			.($competence_type == 'score' ? " AND score_got > 0 " : "") //security check against invalid/useless values
			." ORDER BY ".$_sort." ".$_dir." "
			." LIMIT ".(int)$_startIndex.", ".(int)$_results;
		$res = $this->db->query($query);

		$output = array();
		if ($res && $this->db->num_rows($res) > 0) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = $obj;
			}
		}

		return $output;
	}


	public function getCompetenceUsersTotal($id_competence, $filter = false) {
		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter) && isset($filter['text']) && $filter['text'] != '')
			$_filter .= " AND (u.userid LIKE '%".$filter['text']."%' "
				." OR u.firstname LIKE '%".$filter['text']."%' "
				." OR u.lastname LIKE '%".$filter['text']."%') ";

		$competence_type = $this->getcompetenceType($id_competence);
		$query = "SELECT COUNT(*) "
			." FROM ".$this->_getCompetencesUsersTable()." "
			." WHERE id_competence = ".(int)$id_competence." ".$_filter
			.($competence_type == 'score' ? " AND score_got > 0 " : ""); //security check against invalid/useless values
		$res = $this->db->query($query);

		//extract total value database
		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}



	public function getCompetenceUsers($id_competence, $userdata = false, $filter_text = "") {
		$output = false;
		if (!$filter_text) {
			$query = "SELECT * FROM ".$this->_getCompetencesUsersTable()." "
				." WHERE id_competence = ".(int)$id_competence." "
				.($this->getCompetenceType($id_competence) == 'score' ? " AND score_got > 0 " : "");
		} else {
			$query = "SELECT c.* FROM ".$this->_getCompetencesUsersTable()." as c JOIN %adm_user as u "
				." ON (c.id_user = u.idst)"
				." WHERE id_competence = ".(int)$id_competence." "
				." AND (u.userid LIKE '%".$filter_text."%' OR u.firstname LIKE '%".$filter_text."%' OR u.lastname LIKE '%".$filter_text."%') "
				.($this->getCompetenceType($id_competence) == 'score' ? " AND score_got > 0 " : "");
		}
		$res = $this->db->query($query);
		if ($res) {
			$output = array();
			while ($obj = $this->db->fetch_obj($res)) {
				if ($userdata) {
					$output[$obj->id_user] = $obj;
				} else {
					$output[] = $obj->id_user;
				}
			}
		}
		return $output;
	}



	public function assignCompetenceUsers($id_competence, $users, $track = false) {
		if ($id_competence <= 0) return false; //invalid competence
		if (count($users) <= 0) return true; //0 users operations always "successfull"

		//set insert values for query
		$values = array();
		foreach ($users as $id_user => $score) {
			if ($score > 0) {
				$values[] = "("
					.(int)$id_competence.", "
					.(int)$id_user.", "
					.(float)$score.", "
					." NOW()"
					.")";
			}
		}

		if (count($values) > 0) {
			$query = "INSERT INTO ".$this->_getCompetencesUsersTable()." "
				." (id_competence, id_user, score_got, last_assign_date) VALUES "
				.implode(",", $values);
			$res = $this->db->query($query);
		} else {
			//we were trying to assign some invalid score <= 0
			return false;
		}

		//track the operation
		if ($track) {
			$params = new stdClass();
			$params->operation = "manual_assign"; //the type of operation (manual, course etc.)
			$params->id_course = 0; //the id of the course which has assigned the score
			$params->assigned_by = Docebo::user()->getIdSt(); //user/administrator who has assigned the score to the user
			$params->date_assignment = date("Y-m-d H:i:s"); //the date of the operation
			$params->score_assigned = $score; //the score assigned
			$params->score_total = $score;
			$this->trackOperation($id_competence, array_keys($users), $params);
		}

		return $res ? true : false;
	}


	public function modifyCompetenceUsers($id_competence, $users, $params, $track = false) {
		if ($id_competence <= 0) return false; //invalid competence
		if (is_numeric($users)) $users = array($users);
		if (!is_array($users)) return false;
		if (count($users) <= 0) return true;

		//set values for query
		$set = "";
		if (property_exists($params, 'score_got')) $set .= "score_got=".$params->score_got;
		//if (property_exists($params, 'expire_date')) $set .= "expire_date=".($user->expire_date != "" ? $user->expire_date : "0000-00-00 00:00:00");
		if ($set == "") return true;

		$query = "UPDATE ".$this->_getCompetencesUsersTable()." SET ".$set
			." WHERE id_competence=".(int)$id_competence." AND id_user IN (".implode(",", $users).")";
		$res = $this->db->query($query);

		//track the operation
		if ($track) {
			$_params = new stdClass();
			$_params->operation = "manual_update"; //the type of operation (manual, course etc.)
			$_params->id_course = 0; //the id of the course which has assigned the score
			$_params->assigned_by = Docebo::user()->getIdSt(); //user/administrator who has assigned the score to the user
			$_params->date_assignment = date("Y-m-d H:i:s"); //the date of the operation
			$_params->score_assigned = $params->score_got; //the score assigned
			$_params->score_total = $params->score_got;
			$this->trackOperation($id_competence, $users, $_params);
		}

		return $res ? true : false;
	}


	public function removeCompetenceUsers($id_competence, $users, $track = false) {
		if ($id_competence <= 0) return false; //invalid competence
		if (is_numeric($users)) $users = array($users);
		if (!is_array($users)) return false;
		if (count($users) <= 0) return true;

		$query = "DELETE FROM ".$this->_getCompetencesUsersTable()." WHERE id_competence=".(int)$id_competence." AND id_user IN (".implode(",", $users).")";
		$res = $this->db->query($query);

		//track the operation
		if ($track) {
			$params = new stdClass();
			$params->operation = "manual_remove"; //the type of operation (manual, course etc.)
			$params->id_course = 0; //the id of the course which has assigned the score
			$params->assigned_by = Docebo::user()->getIdSt(); //user/administrator who has assigned the score to the user
			$params->date_assignment = date("Y-m-d H:i:s"); //the date of the operation
			$params->score_assigned = 0; //the score assigned
			$params->score_total = 0;
			$this->trackOperation($id_competence, $users, $params);
		}

		return $res ? true : false;
	}


	/*
	 * when a user is deleted, then remove all of his references in competences tables
	 */
	public function removeUsersFromCompetences($users) {
		if (is_numeric($users)) $users = array($users);
		if (!is_array($users)) return false;
		if (count($users) <= 0) return true;

		$query = "DELETE FROM ".$this->_getCompetencesUsersTable()." WHERE id_user IN (".implode(',', $users).")";
		$res = $this->db->query($query);

		return $res ? true : false;
	}

	/*
	 * add scores to a user's competence
	 */
	public function addScoreToUsers($id_competence, $users, $score, $update_date = true, $track = false) {
		if ($id_competence <= 0) return false; //invalid competence
		if (is_numeric($users)) $users = array($users);
		if (!is_array($users)) return false;
		if (count($users) <= 0) return true;

		$query = "UPDATE ".$this->_getCompetencesUsersTable()." SET score_got = score_got +".(int)$score." "
			.($update_date ? ", last_assign_date = NOW() " : "")
			." WHERE id_competence=".(int)$id_competence." AND id_user IN (".implode(",", $users).")";
		$res = $this->db->query($query);

		//track the operation
		if ($track) {
			$params = new stdClass();
			$params->operation = "manual_addscore"; //the type of operation (manual, course etc.)
			$params->id_course = 0; //the id of the course which has assigned the score
			$params->assigned_by = Docebo::user()->getIdSt(); //user/administrator who has assigned the score to the user
			$params->date_assignment = date("Y-m-d H:i:s"); //the date of the operation
			$params->score_assigned = $score; //the score assigned
			$params->score_total = 0;
			$this->trackOperation($id_competence, $users, $params);
		}

		return $res ? true : false;
	}



	public function getCourseCompetencesList($id_course, $pagination, $filter = false) {
		if ($id_course <= 0) return false; //invalid course

		//validate pagination data
		if (!is_array($pagination)) $pagination = array();
		$_startIndex = (isset($pagination['startIndex']) ? (int)$pagination['startIndex'] : 0);
		$_results = (isset($pagination['results']) ? (int)$pagination['results'] : Get::sett('visuItem', 25));
		$_sort = 'cl.name';
		$_dir = 'ASC';

		if (isset($pagination['dir'])) {
			switch (strtoupper($pagination['dir'])) {
				case 'ASC': $_dir = 'ASC'; break;
				case 'DESC': $_dir = 'DESC'; break;
			}
		}

		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'category': $_sort = 'ctl.name '.$_dir.', cl.name'; break;
				case 'score': $_sort = 'c.type '.$_dir.', c.score'; break;
			}
		}

		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " WHERE (cl.name LIKE '%".$filter['text']."%' "
					." OR cl.description LIKE '%".$filter['text']."%') ";
		}

		$_language = getLanguage();

		//mount query
		$query = "SELECT c.id_competence, cl.name, cl.description, c.typology, c.type, cc.score "
			." FROM (".$this->_getCompetencesTable()." as c JOIN ".$this->_getCompetencesCoursesTable()." as cc "
			." ON (c.id_competence = cc.id_competence AND cc.id_course=".(int)$id_course.")) "
			." LEFT JOIN ".$this->_getCompetencesLangTable()." as cl ON (c.id_competence = cl.id_competence AND cl.lang_code='".$_language."') "
			.$_filter
			." ORDER BY ".$_sort." ".$_dir." "
			." LIMIT ".(int)$_startIndex.", ".(int)$_results;
		$res = $this->db->query($query);

		//extract records from database
		$output = array();
		if ($res && $this->db->num_rows($res)>0) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = $obj;
			}
		}

		return $output;
	}


	public function getCourseCompetencesTotal($id_course, $filter = false) {
		if ($id_course <= 0) return false; //invalid course

		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " WHERE (cl.name LIKE '%".$filter['text']."%' "
					." OR cl.description LIKE '%".$filter['text']."%') ";
		}

		$_language = getLanguage();

		//mount query
		$query = "SELECT COUNT(*) "
			." FROM (".$this->_getCompetencesTable()." as c JOIN ".$this->_getCompetencesCoursesTable()." as cc "
			." ON (c.id_competence = cc.id_competence AND cc.id_course=".(int)$id_course.")) "
			." LEFT JOIN ".$this->_getCompetencesLangTable()." as cl ON (c.id_competence = cl.id_competence AND cl.lang_code='".$_language."') "
			.$_filter;
		$res = $this->db->query($query);

		//extract total value database
		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}


	public function getCourseCompetences($id_course, $details = false) {
		if ($id_course <= 0) return false; //invalid course

		$output = array();
		$query = "SELECT * FROM ".$this->_getCompetencesCoursesTable()." WHERE id_course=".(int)$id_course;
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				if ($details) {
					$output[$obj->id_competence] = $obj;
				} else {
					$output[] = $obj->id_competence;
				}
			}
		}

		return $output;
	}


	/*
	 * set assigned competences score in DB
	 */
	public function assignCourseCompetences($id_course, $scores) {
		if ($id_course <= 0) return false; //invalid course
		if (!is_array($scores)) return false; //invalid input
		if (count($scores) <= 0) return true; //0 inputs operation always "successfull"

		$values = array();
		foreach ($scores as $id_competence=>$score) {
			if ((int)$id_course > 0 && (int)$id_competence > 0)
				$values[] = "(".(int)$id_course.", ".(int)$id_competence.", '".(float)$score."')";
		}

		if (count($values) <= 0) return false;
		$query = "INSERT INTO ".$this->_getCompetencesCoursesTable()." (id_course, id_competence, score) "
			." VALUES ".implode(",", $values);
		$res = $this->db->query($query);

		return $res ? true : false;
	}

	public function deleteCourseCompetences($id_course, $competences) {
		if ($id_course <= 0) return false; //invalid course
		if (is_numeric($competences) && $competences <= 0) return false; //invalid input
		if (is_numeric($competences)) $competences = array($competences);
		if (!is_array($competences)) return false; //invalid input
		if (count($competences) <= 0) return true; //0 inputs operation always "successfull"

		$query = "DELETE FROM ".$this->_getCompetencesCoursesTable()." WHERE id_course=".(int)$id_course." "
			." AND id_competence IN (".implode(",", $competences).")";
		$res = $this->db->query($query);

		return $res ? true : false;
	}



	public function deleteAllCourseCompetences($id_course) {
		if ($id_course <= 0) return false; //invalid course

		$query = "DELETE FROM ".$this->_getCompetencesCoursesTable()." WHERE id_course=".(int)$id_course;
		$res = $this->db->query($query);

		return $res ? true : false;
	}



	public function updateCourseCompetences($id_course, $scores) {
		if ($id_course <= 0) return false; //invalid course
		if (!is_array($scores)) return false; //invalid input
		if (count($scores) <= 0) return true; //0 inputs operation always "successfull"

		$res = true;
		$count = 0;
		foreach ($scores as $id_competence=>$score) {
			if ((int)$id_course > 0 && (int)$id_competence > 0) {
				$query = "UPDATE ".$this->_getCompetencesCoursesTable()." SET score='".(float)$score."' "
					." WHERE id_course=".(int)$id_course." AND id_competence=".(int)$id_competence;
				$res = $this->db->query($query);
				if ($res) $count++;
			}
		}

		return $count > 0;
	}


	public function courseHasScoreCompetences($id_course) {
		if ($id_course <= 0) return false;

		$query = "SELECT COUNT(*) FROM ".$this->_getCompetencesCoursesTable()." as cc "
			." JOIN ".$this->_getCompetencesTable()." as c ON (c.id_competence = cc.id_competence"
			." AND cc.id_course=".(int)$id_course.") WHERE c.type='score'";
		$res = $this->db->query($query);
		list($count) = $this->db->fetch_row($res);

		return $count > 0;
	}



	/*
	 * Obtain user competences list with scores
	 */
	public function getUserCompetences($id_user, $fncroles = false) {
		if ($id_user <= 0) return false;
		$output = array();
		$query = "SELECT * FROM ".$this->_getCompetencesUsersTable()." WHERE id_user=".(int)$id_user." AND score_got>0";
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[$obj->id_competence] = $obj;
			}
		}

		//retrieve required competences too
		if ($fncroles) {
			$fmodel = new FunctionalrolesAdm();
			$req = $fmodel->getUserRequiredCompetences($id_user, true);
			//$cinfo = $this->getCompetencesInfo(array_keys($req));
			foreach ($req as $id_competence=>$cdata) {
				if (isset($output[$id_competence])) {
					$output[$id_competence]->required = true;
					$output[$id_competence]->gap = $output[$id_competence]->score_got - $cdata->score;
				} else {
					$t = new stdClass();
					$t->id_competence = $id_competence;
					$t->id_user = $id_user;
					$t->score_got = 0;
					$t->last_assign_date = '';
					$t->required = true;
					$t->gap = 0 - $cdata->score;
					$output[$id_competence] = $t;
				}
			}
		}

		return $output;
	}


	/*
	 * return a flat array with categories languages
	 */
	public function getCategoriesLangs() {
		//initialize output
		$output = array();
		$lang_codes = Docebo::langManager()->getAllLangCode();
		$_langs = array();
		for ($i=0; $i<count($lang_codes); $i++) {
			$_langs[$lang_codes[$i]] = array(
				'name' => '',
				'description' => ''
			);
		}
		$query = "SELECT id_category FROM ".$this->_getCategoriesTable();
		$res = $this->db->query($query);
		if ($res) {
			while (list($id_category) = $this->db->fetch_row($res)) {
				$output[$id_category] = $_langs;
			}
		}

		//extract languages from DB
		$query = "SELECT c.id_category, cl.lang_code, cl.name, cl.description "
			." FROM ".$this->_getCategoriesTable()." as c "
			." LEFT JOIN ".$this->_getCategoriesLangTable()." as cl "
			." ON (c.id_category = cl.id_category)";
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				if (isset($output[$obj->id_category][$obj->lang_code])) { //avoid possible invalid data in DB
					$output[$obj->id_category][$obj->lang_code]['name'] = $obj->name;
					$output[$obj->id_category][$obj->lang_code]['description'] = $obj->description;
				}
			}
		}

		return $output;
	}



	public function searchCompetencesByName($query, $limit) {
		if ((int)$limit <= 0) $limit = Get::sett('visuItem', 25);
		$output = array();

		$query = "SELECT c.id_competence, cl.name, c.type, c.typology "
			." FROM ".$this->_getCompetencesTable()." as c LEFT JOIN ".$this->_getCompetencesLangTable()." as cl "
			." ON (c.id_competence = cl.id_competence AND cl.lang_code='".getLanguage()."') "
			." WHERE cl.name LIKE '%".$query."%' ORDER BY cl.name "
			.((int)$limit>0 ? " LIMIT 0, ".(int)$limit : "");
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = $obj;
			}
		}
		return $output;
	}


	//tracking and assignments----------------------------------------------------

	public function assignCourseCompetencesToUser($id_course, $id_user, $track = true) {
		if ($id_course <= 0) return false;
		if ($id_user <= 0) return false;

		$ccomps = $this->getCourseCompetences($id_course, true);
		//$ccomps = $this->getCompetencesInfo($_comps);
		$ucomps = $this->getUserCompetences($id_user);

		//addScoreToUsers
		//assignCompetenceUsers
		$res = true;
		foreach ($ccomps as $id_competence => $competence) {
			if (array_key_exists($id_competence, $ucomps)) { //check if the competence already exists for the user
				$res = $this->addScoreToUsers($id_competence, $id_user, $competence->score);
			} else {
				$user_score = array($id_user => $competence->score);
				$res = $this->assignCompetenceUsers($id_competence, $user_score);
			}
			
			//track the operation
			if ($track) {
				$params = new stdClass();
				$params->operation = "course_finish"; //the type of operation (manual, course etc.)
				$params->id_course = $id_course; //the id of the course which has assigned the score
				$params->assigned_by = 0; //user/administrator who has assigned the score to the user
				$params->date_assignment = date("Y-m-d H:i:s"); //the date of the operation
				$params->score_assigned = $competence->score; //the score assigned
				$params->score_total = $competence->score + (array_key_exists($id_competence, $ucomps)
						? $ucomps[$id_competence]->score_got + $competence->score
						: 0);
				$this->trackOperation($id_competence, $id_user, $params);
			}
		}

		return $res;
	}


	public function userHasCompetence($id_competence, $id_user) {
		$output = false;
		$query = "SELECT * FROM ".$this->_getCompetencesUsersTable()." "
			." WHERE id_competence=".(int)$id_competence." AND id_user=".(int)$id_user;
		$res = $this->db->query($query);
		if ($res) {
			return ($this->db->num_rows($res) > 0 ? true : false);
		}
		return true;
	}


	public function trackOperation($id_competence, $users, $params) {
		if ($id_competence <= 0) return false;
		if (is_numeric($users)) $users = array((int)$users);
		if (!is_array($users)) return false;
		if (count($users) <= 0) return true;

		//validate params
		$_operation = $params->operation; //the type of operation (manual, course etc.)
		$_id_course = property_exists($params, 'id_course') ? (int)$params->id_course : 0; //the id of the course which has assigned the score
		$_assigned_by = property_exists($params, 'assigned_by') ? (int)$params->assigned_by : 0; //user/administrator who has assigned the score to the user
		$_date_assignment = property_exists($params, 'date_assignment') ? $params->date_assignment : date("Y-m-d H:i:s"); //the date of the operation
		$_score_assigned = $params->score_assigned; //the score assigned
		$_score_total = property_exists($params, 'score_total') ? (int)$params->score_total : 0; //the total score of the user at the moment of the tracking

		if ($_operation == 'manual_addscore') { //assumes that score adding operation has already been executed
			$new_scores = array();
			$query = "SELECT id_user, score_got FROM ".$this->_getCompetencesUsersTable()." "
				." WHERE id_competence=".(int)$id_competence." AND id_user IN (".implode(",", $users).")";
			$res = $this->db->query($query);
			while (list($id_user, $score_got) = $this->db->fetch_row($res)) {
				$new_scores[$id_user] = $score_got;
			}
		}

		$records = array();
		foreach ($users as $id_user) {
			if ($_operation == 'manual_addscore' && isset($new_scores[$id_user]))
				$_score_total = $new_scores[$id_user];
			else
				$_score_total = $_score_assigned;
			
			$records[] = "(NULL, ".(int)$id_competence.", ".(int)$id_user.", '".$_operation."', ".(int)$_id_course.", "
				." ".(int)$_assigned_by.", '".$_date_assignment."', ".(float)$_score_assigned.", ".(float)$_score_total.")";
		}

		if (count($records) > 0) {
			$query = "INSERT INTO ".$this->_getCompetencesTrackTable()." "
				." (id_track, id_competence, id_user, operation, id_course, assigned_by, date_assignment, score_assigned, score_total) "
				." VALUES ".implode(",", $records);
			$res = $this->db->query($query);
			return $res ? true : false;
		}
		return;
	}




	public function updateCompetenceName($id_competence, $name, $lang_code = false) {
		if ($id_competence <= 0) return false;
		if (!$lang_code) $lang_code = getLanguage();

		$query = "UPDATE ".$this->_getCompetencesLangTable()." "
			." SET name = '".$name."' "
			." WHERE id_competence = ".(int)$id_competence." AND lang_code = '".$lang_code."'";
		$res = $this->db->query($query);

		return $res ? true : false;
	}


	public function updateCompetenceDescription($id_competence, $description, $lang_code = false) {
		if ($id_competence <= 0) return false;
		if (!$lang_code) $lang_code = getLanguage();

		$query = "UPDATE ".$this->_getCompetencesLangTable()." "
			." SET description = '".$description."' "
			." WHERE id_competence = ".(int)$id_competence." AND lang_code = '".$lang_code."'";
		$res = $this->db->query($query);

		return $res ? true : false;
	}


	public function updateCompetenceTypology($id_competence, $typology) {
		if ($id_competence <= 0) return false;

		$list = array_keys($this->getCompetenceTypologies());

		$value_to_set = false;
		foreach ($list as $value) {
			if (strtolower($value) == strtolower($typology)) $value_to_set = $value;
		}

		if ($value_to_set) {
			$query = "UPDATE ".$this->_getCompetencesTable()." "
				." SET typology = '".$value_to_set."' "
				." WHERE id_competence = ".(int)$id_competence;
			$res = $this->db->query($query);
		} else {
			$res = false;
		}

		return $res ? true : false;
	}


	public function updateCompetenceType($id_competence, $type) {
		if ($id_competence <= 0) return false;

		$list = array_keys($this->getCompetenceTypes());

		$value_to_set = false;
		foreach ($list as $value) {
			if (strtolower($value) == strtolower($type)) $value_to_set = $value;
		}

		if ($value_to_set) {
			$query = "UPDATE ".$this->_getCompetencesTable()." "
				." SET type = '".$value_to_set."' "
				." WHERE id_competence = ".(int)$id_competence;
			$res = $this->db->query($query);
		} else {
			$res = false;
		}

		return $res ? true : false;
	}
	
	public function getCompetenceCoursesTotal($id_competence, $filter = false) {
		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter) && isset($filter['text']) && $filter['text'] != '')
			$_filter .= " AND (u.userid LIKE '%".$filter['text']."%' "
				." OR u.firstname LIKE '%".$filter['text']."%' "
				." OR u.lastname LIKE '%".$filter['text']."%') ";

		$competence_type = $this->getcompetenceType($id_competence);
		$query = "SELECT COUNT(*) "
			." FROM ".$this->_getCompetencesCoursesTable()." "
			." WHERE id_competence = ".(int)$id_competence." ".$_filter;
		$res = $this->db->query($query);

		//extract total value database
		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}
	
	public function getCompetenceFncRolesTotal($id_competence, $filter = false) {
		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter) && isset($filter['text']) && $filter['text'] != '')
			$_filter .= " AND (u.userid LIKE '%".$filter['text']."%' "
				." OR u.firstname LIKE '%".$filter['text']."%' "
				." OR u.lastname LIKE '%".$filter['text']."%') ";

		$competence_type = $this->getcompetenceType($id_competence);
		$query = "SELECT COUNT(*) "
			." FROM ".$this->_getCompetencesFncRoleTable()." "
			." WHERE id_competence = ".(int)$id_competence." ".$_filter;
		$res = $this->db->query($query);

		//extract total value database
		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}

}

?>