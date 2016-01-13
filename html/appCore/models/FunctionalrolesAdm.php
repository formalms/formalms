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

class FunctionalrolesAdm extends Model {

	protected $db;
	protected $acl_man;
	protected $_ugroups_cache;

	//--- init functions ---------------------------------------------------------

	public function __construct() {
		$this->db = DbConn::getInstance();
		$this->acl_man = Docebo::user()->getACLManager();
		$this->_ugroups_cache = false;
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

	public function _getRolesTable() { return "%adm_fncrole"; }
	public function _getRolesLangTable() { return "%adm_fncrole_lang"; }

	public function _getGroupsTable() { return "%adm_fncrole_group"; }
	public function _getGroupsLangTable() { return "%adm_fncrole_group_lang"; }

	public function _getRolesUsersTable() { return "%adm_fncrole_user"; }
	public function _getRolesUgroupsTable() { return "%adm_fncrole_ugroup"; }
	public function _getRolesCompetencesTable() { return "%adm_fncrole_competence"; }
	public function _getRolesCoursesTable() { return "%adm_fncrole_course"; }


	//--- operative methods ------------------------------------------------------

	public function getFunctionalRolesList($pagination, $filter = false) {

		//validate pagination data
		if (!is_array($pagination)) $pagination = array();
		$_startIndex = (isset($pagination['startIndex']) ? (int)$pagination['startIndex'] : 0);
		$_results = (isset($pagination['results']) ? (int)$pagination['results'] : Get::sett('visuItem', 25));
		$_sort = 'fl.name';
		$_dir = 'ASC';

		if (isset($pagination['dir'])) {
			switch (strtoupper($pagination['dir'])) {
				case 'ASC': $_dir = 'ASC'; break;
				case 'DESC': $_dir = 'DESC'; break;
			}
		}

		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'description': $_sort = 'fl.description'; break;
				case 'group': $_sort = 'fgl.name '.$_dir.', fl.name'; break;
				case 'users':
				case 'competences':
				case 'courses':
			}
		}

		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " AND (fl.name LIKE '%".$filter['text']."%' "
					." OR fl.description LIKE '%".$filter['text']."%' "
					." OR fgl.name LIKE '%".$filter['text']."%') ";
			if (isset($filter['group']) && (int)$filter['group']>0)
				$_filter .= " AND f.id_group = ".(int)$filter['group']." ";
		}

		//validate language for name and description
		$_language = (!empty($filter) && isset($filter['language']) ? $filter['language'] : getLanguage());

		//mount query
		$query = "SELECT g.idst as id_fncrole, f.id_group, fl.name, fl.description, fgl.name as group_name "
			." FROM (%adm_group as g LEFT JOIN ".$this->_getRolesTable()." as f ON (g.idst = f.id_fncrole)) "
			." LEFT JOIN ".$this->_getRolesLangTable()." as fl "
			." ON (g.idst = fl.id_fncrole AND fl.lang_code = '".$_language."') "
			." LEFT JOIN ".$this->_getGroupsLangTable()." as fgl "
			." ON (f.id_group = fgl.id_group AND fgl.lang_code = '".$_language."')"
			." WHERE g.groupid LIKE '/fncroles/%' ".($_filter != "" ? $_filter." " : "")
			." ORDER BY ".$_sort." ".$_dir." "
			." LIMIT ".(int)$_startIndex.", ".(int)$_results;
		$res = $this->db->query($query);

		//extract records from database
		$output = array();
		if ($res && $this->db->num_rows($res)>0) {

			while ($obj = $this->db->fetch_obj($res)) {
				$output[$obj->id_fncrole] = $obj;
			}

			$_arr_fncroles = array_keys($output);

			//insert values in output records
			reset($output);
			while (list($key, $value) = each($output)) {
				//WARNING: extremely expansive; TO DO: optimize the users count
				$count = count($this->getAllUsers($key));
				$value->users = $count > 0 ? $count : 0;
			}
			unset($_arr_users);

			//get competences count for every retrieved role
			$_arr_competences = array();
			$query = "SELECT id_fncrole, COUNT(*) FROM ".$this->_getRolesCompetencesTable()." "
				." WHERE id_fncrole IN (".implode(",", $_arr_fncroles).") "
				." GROUP BY id_fncrole";
			$res = $this->db->query($query);
			if ($res) {
				while (list($id_fncrole, $count) = $this->db->fetch_row($res)) {
					$_arr_competences[$id_fncrole] = $count;
				}
			}
			//insert values in output records
			reset($output);
			while (list($key, $value) = each($output)) {
				$value->competences = (isset($_arr_competences[$key]) ? (int)$_arr_competences[$key] : 0);
			}
			unset($_arr_competences);

		}

		return array_values($output);
	}


	public function getFunctionalRolesTotal($filter = false) {
		//validate filter data and adjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " AND (fl.name LIKE '%".$filter['text']."%' "
					." OR fl.description LIKE '%".$filter['text']."%' "
					." OR fgl.name LIKE '%".$filter['text']."%') ";
			if (isset($filter['group']) && (int)$filter['group']>0)
				$_filter .= " AND f.id_group = ".(int)$filter['group']." ";
		}

		//validate language for name and description
		$_language = (!empty($filter) && isset($filter['language']) ? $filter['language'] : getLanguage());

		//mount query
		$query = "SELECT COUNT(*) "
			." FROM (%adm_group as g LEFT JOIN ".$this->_getRolesTable()." as f ON (g.idst = f.id_fncrole)) "
			." LEFT JOIN ".$this->_getRolesLangTable()." as fl "
			." ON (g.idst = fl.id_fncrole AND fl.lang_code = '".$_language."') "
			." LEFT JOIN ".$this->_getGroupsLangTable()." as fgl "
			." ON (f.id_group = fgl.id_group AND fgl.lang_code = '".$_language."')"
			." WHERE g.groupid LIKE '/fncroles/%' ".($_filter != "" ? $_filter." " : "");
		$res = $this->db->query($query);

		//extract total value database
		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}



	public function selectAllFunctionalRoles($filter) {
		//validate filter data and adjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " AND (fl.name LIKE '%".$filter['text']."%' "
					." OR fl.description LIKE '%".$filter['text']."%' "
					." OR fgl.name LIKE '%".$filter['text']."%') ";
			if (isset($filter['group']) && (int)$filter['group']>0)
				$_filter .= " AND f.id_group = ".(int)$filter['group']." ";
		}

		//validate language for name and description
		$_language = (!empty($filter) && isset($filter['language']) ? $filter['language'] : getLanguage());

		//mount query
		$query = "SELECT f.id_fncrole "
			." FROM (%adm_group as g LEFT JOIN ".$this->_getRolesTable()." as f ON (g.idst = f.id_fncrole)) "
			." LEFT JOIN ".$this->_getRolesLangTable()." as fl "
			." ON (f.id_fncrole = fl.id_fncrole AND fl.lang_code = '".$_language."') "
			." LEFT JOIN ".$this->_getGroupsLangTable()." as fgl "
			." ON (f.id_group = fgl.id_group AND fgl.lang_code = '".$_language."')"
			.($_filter != "" ? " WHERE  g.groupid LIKE '/fncroles/%' ".$_filter." " : "");
		$res = $this->db->query($query);

		//extract records from database
		$output = array();
		if ($res && $this->db->num_rows($res)>0) {
			while (list($id_fncrole) = $this->db->fetch_row($res)) {
				$output[] = $id_fncrole;
			}
		}

		return $output;
	}


	public function getGroupsList($pagination, $filter = false) {

		//validate pagination data
		if (!is_array($pagination)) $pagination = array();
		$_startIndex = (isset($pagination['startIndex']) ? (int)$pagination['startIndex'] : 0);
		$_results = (isset($pagination['results']) ? (int)$pagination['results'] : Get::sett('visuItem', 25));
		$_sort = 'fgl.name';
		$_dir = 'ASC';

		if (isset($pagination['dir'])) {
			switch (strtoupper($pagination['dir'])) {
				case 'ASC': $_dir = 'ASC'; break;
				case 'DESC': $_dir = 'DESC'; break;
			}
		}

		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'description': $_sort = 'fgl.description'; break;
			}
		}

		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " WHERE (fgl.name LIKE '%".$filter['text']."%' "
					." OR fgl.description LIKE '%".$filter['text']."%') ";
		}

		//validate language for name and description
		$_language = (!empty($filter) && isset($filter['language']) ? $filter['language'] : getLanguage());

		//mount query
		$query = "SELECT fg.id_group, fgl.name, fgl.description "
			." FROM ".$this->_getGroupsTable()." as fg "
			." LEFT JOIN ".$this->_getGroupsLangTable()." as fgl "
			." ON (fg.id_group = fgl.id_group AND fgl.lang_code = '".$_language."') "
			.($_filter != "" ? $_filter : "")
			." ORDER BY ".$_sort." ".$_dir." "
			." LIMIT ".(int)$_startIndex.", ".(int)$_results;
		$res = $this->db->query($query);

		//extract records from database
		$output = array();
		if ($res && $this->db->num_rows($res)>0) {
			//$_arr_competences = array();
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = $obj;
			}
		}

		return $output;
	}


	public function getGroupsTotal($filter = false) {

		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " WHERE (fgl.name LIKE '%".$filter['text']."%' "
					." OR fgl.description LIKE '%".$filter['text']."%') ";
		}

		//validate language for name and description
		$_language = (!empty($filter) && isset($filter['language']) ? $filter['language'] : getLanguage());

		//mount query
		$query = "SELECT COUNT(*) "
			." FROM ".$this->_getGroupsTable()." as fg "
			." LEFT JOIN ".$this->_getGroupsLangTable()." as fgl "
			." ON (fg.id_group = fgl.id_group AND fgl.lang_code = '".$_language."') "
			.($_filter != "" ? $_filter : "");
		$res = $this->db->query($query);

		//extract total value database
		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}


	public function getGroupInfo($id_group) {
		if ($id_group <= 0) return false;
		$output = false;

		$query = "SELECT * FROM ".$this->_getGroupsTable()." WHERE id_group=".(int)$id_group;
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res)>0) {
			$output = $this->db->fetch_obj($res);
		} else {
			return false;
		}

		//initialize languages array
		$lang_codes = Docebo::langManager()->getAllLangCode();
		$langs = array();
		for ($i=0; $i<count($lang_codes); $i++) {
			$langs[$lang_codes[$i]] = array(
				'name' => '',
				'description' => ''
			);
		}

		//extract grop's languages
		$query = "SELECT * FROM ".$this->_getGroupsLangTable()." WHERE id_group=".(int)$id_group;
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				if (in_array($obj->lang_code, $lang_codes)) {
					$langs[$obj->lang_code]['name'] = $obj->name;
					$langs[$obj->lang_code]['description'] = $obj->description;
				}
			}
		}

		$output->langs = $langs;
		return $output;
	}


	public function getFunctionalRoleInfo($id_fncrole) {
		if ($id_fncrole <= 0) return false;
		$output = false;

		$query = "SELECT g.idst as id_fncrole, IF(f.id_group,f.id_group, 0) as id_group "
			." FROM %adm_group as g LEFT JOIN ".$this->_getRolesTable()." as f "
			." ON (g.idst = f.id_fncrole) WHERE g.groupid LIKE '/fncroles/%' AND g.idst=".(int)$id_fncrole;
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res)>0) {
			$output = $this->db->fetch_obj($res);
		} else {
			return false;
		}

		//initialize languages array
		$lang_codes = Docebo::langManager()->getAllLangCode();
		$langs = array();
		for ($i=0; $i<count($lang_codes); $i++) {
			$langs[$lang_codes[$i]] = array(
				'name' => '',
				'description' => ''
			);
		}

		//extract role's languages
		$query = "SELECT * FROM ".$this->_getRolesLangTable()." WHERE id_fncrole=".(int)$id_fncrole;
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				if (in_array($obj->lang_code, $lang_codes)) {
					$langs[$obj->lang_code]['name'] = $obj->name;
					$langs[$obj->lang_code]['description'] = $obj->description;
				}
			}
		}

		$output->langs = $langs;
		return $output;
	}


	public function getFunctionalRoleName($id_fncrole, $language = false) {
		if ($id_fncrole <= 0) return false;
		$_language = ($language ? $language : getLanguage());

		$output = false;
		$query = "SELECT name FROM ".$this->_getRolesLangTable()." "
			." WHERE id_fncrole=".(int)$id_fncrole." AND lang_code='".$_language."'";
		$res = $this->db->query($query);
		if ($res) {
			if ($this->db->num_rows($res) > 0) {
				list($name) = $this->db->fetch_row($res);
				$output = $name;
			} else
				$output = '';
		}

		return $output;
	}



	public function getGroupsDropdownList($language = false) {
		//validate language for name and description
		$_language = ($language ? $language : getLanguage());

		//initialize output
		$output = array('0' => Lang::t('_NONE', 'fncroles'));

		//extract groups names
		$query = "SELECT fg.id_group, fgl.name FROM ".$this->_getGroupsTable()." as fg "
			." LEFT JOIN ".$this->_getGroupsLangTable()." as fgl ON (fg.id_group = fgl.id_group) "
			." ORDER BY fgl.name";
		$res = $this->db->query($query);
		if ($res) {
			while (list($id_group, $name) = $this->db->fetch_row($res)) {
				$output[$id_group] = $name;
			}
		}

		//return output array
		return $output;
	}



	public function createFunctionalRole($id_group, $langs) {
		//validate role's assigned group (0 = 'no group')
		if ((int)$id_group < 0) $id_group = 0;

		//create a new group
		$idst = false;
		$query = "SELECT MAX(groupid) FROM %adm_group WHERE groupid LIKE '/fncroles/%'";
		$res = $this->db->query($query);
		if ($res) {
			list($groupid) = $this->db->fetch_row($res);
			$index = (int)str_replace('/fncroles/', '', $groupid);
			$idst = $this->acl_man->registerGroup('/fncroles/'.str_pad("".($index+1), 10, '0', STR_PAD_LEFT), '', true);
		} else {
			return false;
		}
		if ((int)$idst <= 0) return false;

		if ($id_group > 0) {
		//compose query
			$query = "INSERT INTO ".$this->_getRolesTable()." "
				." (id_fncrole, id_group) VALUES (".(int)$idst.", ".(int)$id_group.")";
			$res = $this->db->query($query);
		}

		//manage languages
		if ($res) {
			if (is_array($langs)) {
				//insert languages in database
				$conditions = array();
				foreach ($langs as $lang_code => $translation) { //TO DO: check if lang_code exists ...
					$name = $translation['name'];
					$description = $translation['description'];
					$conditions[] = "(".(int)$idst.", '".$lang_code."', '".$name."', '".$description."')";
				}
				$query = "INSERT INTO ".$this->_getRolesLangTable()." "
					." (id_fncrole, lang_code, name, description) "
					." VALUES ".implode(",", $conditions);
				$res = $this->db->query($query);
				$output = ($res ? $idst : false);
				return $output;
			}
		}

		return false;
	}


	public function createGroup($langs) {
		//compose query
		$query = "INSERT INTO ".$this->_getGroupsTable()." (id_group) VALUES (NULL)";
		$res = $this->db->query($query);

		//manage languages
		if ($res) {
			if (is_array($langs)) {
				$id = $this->db->insert_id();

				//insert languages in database
				$conditions = array();
				foreach ($langs as $lang_code => $translation) { //TO DO: check if lang_code exists ...
					$name = $translation['name'];
					$description = $translation['description'];
					$conditions[] = "(".(int)$id.", '".$lang_code."', '".$name."', '".$description."')";
				}
				$query = "INSERT INTO ".$this->_getGroupsLangTable()." "
					." (id_group, lang_code, name, description) "
					." VALUES ".implode(",", $conditions);
				$res = $this->db->query($query);
				$output = ($res ? $id : false);
				return $output;
			}
		} else {
			return false;
		}
	}



	public function updateFunctionalRole($id_fncrole, $id_group, $langs) {
		//check if given id is valid
		if ($id_fncrole <= 0) return false;
		if ($id_group < 0) $id_group = 0;

		$output = true;

		if ($id_group > 0) {
			$query = "SELECT * FROM ".$this->_getRolesTable()." WHERE id_fncrole=".(int)$id_fncrole;
			$res = $this->db->query($query);
			if ($res) {
				if ($this->db->num_rows($res) > 0) {
					$query = "UPDATE ".$this->_getRolesTable()." SET id_group=".(int)$id_group." "
						." WHERE id_fncrole = ".(int)$id_fncrole;
					$res = $this->db->query($query);
					$output = $res ? true : false;
				} else {
					$query = "INSERT INTO ".$this->_getRolesTable()." "
						." (id_fncrole, id_group) VALUES (".(int)$id_fncrole.", ".(int)$id_group.")";
					$res = $this->db->query($query);
				}
			}
		} else {
			$query = "DELETE FROM ".$this->_getRolesTable()." WHERE id_fncrole=".(int)$id_fncrole;
			$res = $this->db->query($query);
		}

		if ($output) {
			//insert languages in database
			if (is_array($langs)) {
				$_langs = Docebo::langManager()->getAllLangcode();
				$arr_langs = array();
				foreach ($_langs as $lang_code) {
					if (isset($langs[$lang_code])) {
						$_name = $langs[$lang_code]['name'];
						$_description = $langs[$lang_code]['description'];
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
				$re = $this->db->query("SELECT lang_code FROM ".$this->_getRolesLangTable()." WHERE id_fncrole = ".(int)$id_fncrole);
				while(list($lang_code) = $this->db->fetch_row($re)) {
					$prev_lang[$lang_code] = $lang_code;
				}

				$conditions = array();
				foreach ($arr_langs as $lang_code => $translation) {
					$name = $translation['name'];
					$description = $translation['description'];
					
					if(isset($prev_lang[$lang_code])) {
						
						$query = "UPDATE ".$this->_getRolesLangTable()." "
							." SET name = '".$name."', description = '".$description."' "
							." WHERE id_fncrole = ".(int)$id_fncrole." AND lang_code = '".$lang_code."'";
						$res = $this->db->query($query);
					} else {
						
						$query = "INSERT INTO ".$this->_getRolesLangTable()." "
							." (id_fncrole, lang_code, name, description) VALUES "
							." (".(int)$id_fncrole.", '".$lang_code."', '".$name."', '".$description."')";
						$res = $this->db->query($query);
					}
					
				}
			}
		}

		return $output;
	}


	public function updateGroup($id_group, $langs) {
		//check if given id is valid
		if ($id_group <= 0) return false;

		if (is_array($langs)) {
			$langcodes = Docebo::langManager()->getAllLangcode();
			$arr_langs = array();
			foreach ($langcodes as $lang_code) {
				if (isset($langs[$lang_code])) {
					$_name = $langs[$lang_code]['name'];
					$_description = $langs[$lang_code]['description'];
				} else {
					$_name = "";
					$_description = "";
				}
				$arr_langs[$lang_code] = array(
					'name' => $_name,
					'description' => $_description
				);
			}

			// retrive previous saved languages
			$prev_lang = array();
			$re = $this->db->query("SELECT lang_code FROM ".$this->_getGroupsLangTable()." WHERE id_group = ".(int)$id_group);
			while(list($lang_code) = $this->db->fetch_row($re)) {
				$prev_lang[$lang_code] = $lang_code;
			}
			$conditions = array();
			foreach ($arr_langs as $lang_code => $translation) { //TO DO: check if lang_code exists ...
				$name = $translation['name'];
				$description = $translation['description'];

				if(isset($prev_lang[$lang_code])) {
					$query = "UPDATE ".$this->_getGroupsLangTable()." SET name = '".$name."', description = '".$description."' "
						."WHERE lang_code='".$lang_code."' AND id_group=".(int)$id_group;
					$res = $this->db->query($query);
				} else {
					$query = "INSERT INTO ".$this->_getGroupsLangTable()." (id_group, lang_code, name, description) VALUES "
						."(".(int)$id_group.", '".$lang_code."', '".$name."', '".$description."')";
					$res = $this->db->query($query);
				}
			}
		}

		return true;
	}

	public function deleteFunctionalRole($id_fncrole) {
		//delete group and its members
		$res = $this->acl_man->deleteGroup((int)$id_fncrole);

		//delete all group's references
		$query = "DELETE FROM ".$this->_getRolesTable()." WHERE id_fncrole=".(int)$id_fncrole;
		$res = $this->db->query($query);

		$query = "DELETE FROM ".$this->_getRolesLangTable()." WHERE id_fncrole=".(int)$id_fncrole;
		$res = $this->db->query($query);

		$query = "DELETE FROM ".$this->_getRolesCompetencesTable()." WHERE id_fncrole=".(int)$id_fncrole;
		$res = $this->db->query($query);

		return $res ? true : false;
	}

	public function deleteGroup($id_group) {
		//delete group entity
		$query = "DELETE FROM ".$this->_getGroupsTable()." WHERE id_group=".(int)$id_group;
		$res = $this->db->query($query);
		if ($res) {
			//delete languages
			$query = "DELETE FROM ".$this->_getGroupsLangTable()." WHERE id_group=".(int)$id_group;
			$res = $this->db->query($query);
			//update groups references in functional roles table
			$query = "DELETE FROM ".$this->_getRolesTable()." WHERE id_group=".(int)$id_group;
			$res = $this->db->query($query);
		}
		return $res ? true : false;
	}


	//----------------------------------------------------------------------------


	public function getUsers($id_fncrole) {
		if ($id_fncrole <= 0) return false; //invalid role
		return $this->acl_man->getGroupUMembers($id_fncrole);
	}

	public function getUgroups($id_fncrole) {
		if ($id_fncrole <= 0) return false; //invalid role
		return $this->acl_man->getGroupGMembers($id_fncrole);
	}

	public function getMembers($id_fncrole) {
		if ($id_fncrole <= 0) return false; //invalid role
		return $this->acl_man->getGroupMembers($id_fncrole);
	}

	public function getUgroupsUsers($id_fncrole) {
		$_groups = $this->getUgroups($id_fncrole);
		return array_unique($this->acl_man->getAllUsersFromIdst($_groups));
	}

	public function getAllUsers($id_fncrole) {
		$members = $this->getMembers((int)$id_fncrole);
		//return array_unique($this->acl_man->getAllUsersFromIdst($members));
		return array_unique($this->acl_man->getAllUsersFromSelection($members));
	}

	public function assignMembers($id_fncrole, $arr_idst) {
		if ($id_fncrole <= 0) return false; //invalid role
		if (is_numeric($arr_idst)) $arr_idst = array($arr_idst); //handle single user case
		if (!is_array($arr_idst)) return false; //invalid user data
		if (count($arr_idst) <= 0) return true; //0 users operation: always "successfull"

		//prepare query and insert data in DB
		$res = $this->acl_man->addToGroup($id_fncrole, $arr_idst);

		return $res ? true : false;
	}

	public function deleteMembers($id_fncrole, $arr_idst) {
		if ($id_fncrole <= 0) return false; //invalid role
		if (is_numeric($arr_idst)) $arr_idst = array($arr_idst); //handle single user case
		if (!is_array($arr_idst)) return false; //invalid user data
		if (count($arr_idst) <= 0) return true; //0 users operation: always "successfull"

		$res = $this->acl_man->removeFromGroup($id_fncrole, $arr_idst);

		return $res ? true : false;
	}


	public function getCompetences($id_fncrole) {
		if ($id_fncrole <= 0) return false; //invalid role

		$output = false;
		$query = "SELECT * FROM ".$this->_getRolesCompetencesTable()." WHERE id_fncrole=".(int)$id_fncrole;
		$res = $this->db->query($query);
		if ($res) {
			$output = array();
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = $obj->id_competence;
			}
		}

		return $output;
	}

	public function assignCompetences($id_fncrole, $competences) {
		if ($id_fncrole <= 0) return false; //invalid role
		if (is_numeric($competences)) $competences = array($competences); //handle single course case
		if (!is_array($competences)) return false; //invalid course data
		if (count($competences) <= 0) return true; //0 courses operation: always "successfull"

		//prepare query and insert data in DB
		$values = array();
		foreach ($competences as $id_competence) $values[] = "(".(int)$id_fncrole.", ".(int)$id_competence.")";
		$query = "INSERT INTO ".$this->_getRolesCompetencesTable()." (id_fncrole, id_competence) "
			." VALUES ".implode(",", $values);
		$res = $this->db->query($query);

		return $res ? true : false;
	}

	public function deleteCompetences($id_fncrole, $competences) {
		if ($id_fncrole <= 0) return false; //invalid role
		if (is_numeric($competences)) $competences = array($competences); //handle single course case
		if (!is_array($competences)) return false; //invalid course data
		if (count($competences) <= 0) return true; //0 courses operation: always "successfull"

		$query = "DELETE FROM ".$this->_getRolesCompetencesTable()." "
			." WHERE id_fncrole=".(int)$id_fncrole." "
			." AND id_competence IN (".implode(",", $competences).")";
		$res = $this->db->query($query);

		return $res ? true : false;
	}


	//"alias" functions for single remove of users/competences/courses from role
	public function deleteMember($id_fncrole, $id_user) {
		return $this->deleteMembers($id_fncrole, $id_user);
	}

	public function deleteCompetence($id_fncrole, $id_competence) {
		return $this->deleteCompetences($id_fncrole, $id_competence);
	}



	public function getManageUsersList($id_fncrole, $pagination, $filter = false) {
		if ($id_fncrole <= 0) return false; //invalid role

		//validate pagination data
		if (!is_array($pagination)) $pagination = array();
		$_startIndex = (isset($pagination['startIndex']) ? (int)$pagination['startIndex'] : 0);
		$_results = (isset($pagination['results']) ? (int)$pagination['results'] : Get::sett('visuItem', 25));
		$_sort = 'u.userid';
		$_dir = 'ASC';

		if (isset($pagination['dir'])) {
			switch (strtoupper($pagination['dir'])) {
				case 'ASC': $_dir = 'ASC'; break;
				case 'DESC': $_dir = 'DESC'; break;
			}
		}

		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'firstname': $_sort = 'u.firstname '.$_dir.', u.lastname'; break;
				case 'lastname': $_sort = 'u.lastname '.$_dir.', u.firstname'; break;
			}
		}

		//validate filter data and adjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " AND (u.userid LIKE '%".$filter['text']."%' "
					." OR u.firstname LIKE '%".$filter['text']."%' "
					." OR u.lastname LIKE '%".$filter['text']."%') ";
		}

		$g_users = $this->getUgroupsUsers($id_fncrole);
		$users = $this->getAllUsers($id_fncrole);

		//mount query
		$query = "SELECT u.idst, u.userid, u.firstname, u.lastname "
			." FROM %adm_user as u WHERE u.idst IN (".implode(",", $users).") ".$_filter
			." ORDER BY ".$_sort." ".$_dir." "
			." LIMIT ".(int)$_startIndex.", ".(int)$_results;
		$res = $this->db->query($query);

		//extract records from database
		$output = array();
		if ($res && $this->db->num_rows($res)>0) {
			while ($obj = $this->db->fetch_obj($res)) {
				$obj->is_group = in_array($obj->idst, $g_users);
				$output[] = $obj;
			}
		}

		return $output;
	}

	public function getManageUsersTotal($id_fncrole, $filter = false) {
		if ($id_fncrole <= 0) return false; //invalid role

		//validate filter data and adjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " AND (u.userid LIKE '%".$filter['text']."%' "
					." OR u.firstname LIKE '%".$filter['text']."%' "
					." OR u.lastname LIKE '%".$filter['text']."%') ";
		}

		//$g_users = $this->getUgroupsUsers($id_fncrole);
		$users = $this->getAllUsers($id_fncrole);

		//mount query
		$query = "SELECT COUNT(*) "
			." FROM %adm_user as u WHERE u.idst IN (".implode(",", $users).") ".$_filter;
		$res = $this->db->query($query);

		//extract total value database
		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}


	
	
	public function getManageUsersAll($id_fncrole, $filter = false) {
		if ($id_fncrole <= 0) return false; //invalid role

		//validate filter data and adjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " AND (u.userid LIKE '%".$filter['text']."%' "
					." OR u.firstname LIKE '%".$filter['text']."%' "
					." OR u.lastname LIKE '%".$filter['text']."%') ";
		}

		//$g_users = $this->getUgroupsUsers($id_fncrole);
		$users = $this->getAllUsers($id_fncrole);

		//mount query
		$query = "SELECT u.idst "
			." FROM %adm_user as u WHERE u.idst IN (".implode(",", $users).") ".$_filter;
		$res = $this->db->query($query);

		//extract total value database
		$output = false;
		if ($res) {
			$output = array();
			while (list($id_user) = $this->db->fetch_row($res)) {
				$output[] = $id_user;
			}
		}

		return $output;
	}



	public function getManageCompetencesList($id_fncrole, $pagination, $filter = false) {
		if ($id_fncrole <= 0) return false; //invalid role

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
				case 'description': $_sort = 'cl.description'; break;
				case 'category': $_sort = 'ctl.name '.$_dir.', cl.name'; break;
				case 'type': $_sort = 'c.type'; break;
				case 'typology': $_sort = 'c.typology'; break;
				case 'score': $_sort = 'c.type '.$_dir.', fc.score'; break;
				case 'expiration': $_sort = 'fc.expiration'; break;
			}
		}

		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " WHERE (cl.name LIKE '%".$filter['text']."%' "
					." OR ctl.name LIKE '%".$filter['text']."%') ";
		}

		$_cmodel = new CompetencesAdm();
		$_language = getLanguage();

		//mount query
		$query = "SELECT c.id_competence, cl.name, cl.description, ctl.name as category, c.typology, c.type, fc.score, fc.expiration "
			." FROM (".$this->_getRolesCompetencesTable()." as fc JOIN ".$_cmodel->_getCompetencesTable()." as c "
			." ON (fc.id_competence = c.id_competence AND fc.id_fncrole=".(int)$id_fncrole.")) "
			." LEFT JOIN ".$_cmodel->_getCompetencesLangTable()." as cl ON (c.id_competence = cl.id_competence AND cl.lang_code='".$_language."') "
			." LEFT JOIN ".$_cmodel->_getCategoriesLangTable()." as ctl ON (c.id_category = ctl.id_category AND ctl.lang_code='".$_language."') "
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


	public function getManageCompetencesTotal($id_fncrole, $filter = false) {
		if ($id_fncrole <= 0) return false; //invalid role

		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " WHERE (cl.name LIKE '%".$filter['text']."%' "
					." OR ctl.name LIKE '%".$filter['text']."%') ";
		}

		$_cmodel = new CompetencesAdm();
		$_language = getLanguage();

		//mount query
		$query = "SELECT COUNT(*) "
			." FROM (".$this->_getRolesCompetencesTable()." as fc JOIN ".$_cmodel->_getCompetencesTable()." as c "
			." ON (fc.id_competence = c.id_competence AND fc.id_fncrole=".(int)$id_fncrole.")) "
			." LEFT JOIN ".$_cmodel->_getCompetencesLangTable()." as cl ON (c.id_competence = cl.id_competence AND cl.lang_code='".$_language."') "
			." LEFT JOIN ".$_cmodel->_getCategoriesLangTable()." as ctl ON (c.id_category = ctl.id_category AND ctl.lang_code='".$_language."') "
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




	public function getCompetencesProperties($arr_competences, $id_fncrole = false) {
		if (is_numeric($arr_competences) && $arr_competences <= 0) return false;
		if (is_numeric($arr_competences)) $arr_competences = array($arr_competences);
		if (count($arr_competences) <= 0) return array();

		$output = array();
		$query = "SELECT * FROM ".$this->_getRolesCompetencesTable()." "
			." WHERE id_competence IN (".implode(",", $arr_competences).")"
			.($id_fncrole > 0 ? " AND id_fncrole=".(int)$id_fncrole : "");
		$res = $this->db->query($query);
		if (!$res) return false;
		while ($obj = $this->db->fetch_obj($res)) {
			$output[$obj->id_competence] = $obj;
		}

		return $output;
	}


	public function updateCompetenceProperties($id_fncrole, $id_competence, $score, $expiration) {
		if ($id_fncrole <= 0) return false;
		if ($id_competence <= 0) return false;

		$query = "UPDATE ".$this->_getRolesCompetencesTable()." SET score='".(float)$score."', "
			." expiration=".(int)$expiration." WHERE id_fncrole=".(int)$id_fncrole." "
			." AND id_competence=".(int)$id_competence;
		$res = $this->db->query($query);
		//TO DO: check affected rows ...

		return $res ? true : false;
	}


	//----------------------------------------------------------------------------

	public function getCompetencesCoursesInfo($id_fncrole) {
		if ($id_fncrole <= 0) return false; //invalid input

		$output = array();
		$list = $this->getCompetences($id_fncrole);
		if (count($list) > 0) {
			$query = "SELECT cc.id_competence, cc.id_course, c.code, c.name, cc.score "
				." FROM %lms_competence_course as cc JOIN  %lms_course as c "
				." ON (cc.id_course = c.idCourse) WHERE cc.id_competence IN (".implode(",", $list).") "
				." ORDER BY c.code, c.name";
			$res = $this->db->query($query);

			if ($res) {
				while ($obj = $this->db->fetch_obj($res)) {
					$output[$obj->id_competence][] = array(
						'id_course' => $obj->id_course,
						'code' => $obj->code,
						'name' => $obj->name,
						'score' => $obj->score,
					);
				}
			}
		}

		return $output;
	}




	//----------------------------------------------------------------------------

	public function getGapList($id_fncrole, $pagination, $filter = false) {
		if ($id_fncrole <= 0) return false; //invalid role

		//validate pagination data
		if (!is_array($pagination)) $pagination = array();
		$_startIndex = (isset($pagination['startIndex']) ? (int)$pagination['startIndex'] : 0);
		$_results = (isset($pagination['results']) ? (int)$pagination['results'] : Get::sett('visuItem', 25));
		$_sort = 'competence_name';
		$_dir = 'ASC';

		if (isset($pagination['dir'])) {
			switch (strtoupper($pagination['dir'])) {
				case 'ASC': $_dir = 'ASC'; break;
				case 'DESC': $_dir = 'DESC'; break;
			}
		}

		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'competence': $_sort = 'competence_name '.$_dir.', usr.userid '; break;
				case 'userid': $_sort = 'usr.userid '.$_dir.', competence_name '; break;
				case 'lastname': $_sort = 'usr.lastname '.$_dir.', usr.firstname '.$_dir.', usr.userid'; break;
				case 'firstname': $_sort = 'usr.firstname '.$_dir.', usr.lastname '.$_dir.', usr.userid'; break;
				case 'score_got': $_sort = 'cmu.score_got '; break;
				case 'score_req': $_sort = 'fnc.score '; break;
				case 'gap': $_sort = 'gap '.$_dir.', competence_name '.$_dir.', usr.userid '; break;
				//case 'date_expire': $_sort = 'date_expire '; break;
                default: $_sort = $pagination['sort'].' '.$_dir.', competence_name '.$_dir.', usr.userid '; break;            
			}
		}

		//validate filter data and abjust query
		$where_filter = "";
		if (is_array($filter)) {
			$conditions = array();

			if (isset($filter['text']) && $filter['text'] != '') {
				$conditions[] = " (cml.name LIKE '%".$filter['text']."%' "
					." OR usr.userid LIKE '%".$filter['text']."%' "
					." OR usr.firstname LIKE '%".$filter['text']."%' "
					." OR usr.lastname LIKE '%".$filter['text']."%') ";
			}
			if (isset($filter['show_gap']) && $filter['show_gap'] != 0) {
				$conditions[] = $filter['show_gap'] == 1 
					? " fnc.score > IF(cmu.score_got,cmu.score_got,0) "
					: " fnc.score <= IF(cmu.score_got,cmu.score_got,0) ";
			}
			if (isset($filter['show_expired']) && $filter['show_expired'] != 0) {
				$conditions[] = $filter['show_expired'] == 1 
					? " ( (DATE_ADD(cmu.last_assign_date,INTERVAL fnc.expiration DAY)) < NOW() AND fnc.expiration > 0 ) "
					: " ( (DATE_ADD(cmu.last_assign_date,INTERVAL fnc.expiration DAY)) >= NOW() OR fnc.expiration <= 0)";
			}

			if (count($conditions) > 0) {
				$where_filter .= " WHERE ".implode(" AND ", $conditions);
			}
		}

		$language = getLanguage();

		$comps_table = '%lms_competence';										//cmp
		$clang_table = '%lms_competence_lang';							//cml
		$ucomp_table = '%lms_competence_user';							//cmu
		$users_table = '%adm_user';													//usr
		$fnccm_table = $this->_getRolesCompetencesTable();	//fnc

		//mount query
		if (isset($filter['user']) && $filter['user']>0)
			$_users = array($filter['user']);
		else
			$_users = $this->getAllUsers($id_fncrole);
        $dynFilter = isset($filter['dyn_filter']) ? $filter['dyn_filter'] : false;
        
        if ($dynFilter) {
            require_once(_adm_.'/lib/lib.field.php');
            //retrieve custom fields definitions data
            $fman = new FieldList();
            $fields = $fman->getAllFields();

            //retrieve which fields are required
            $custom_fields = array_keys($fields);
        }

        if (!empty($_users)) {
			$query = "SELECT usr.idst, usr.userid, usr.firstname, usr.lastname, cmu.last_assign_date, fnc.score as score_requested, "
				." fnc.id_competence, cml.name as competence_name, cmu.score_got, fnc.score, fnc.expiration, cmp.type "
				.", (fnc.score-IF(cmu.score_got,cmu.score_got,0)) as gap ";
				//.", (DATE_ADD(cmu.last_assign_date,INTERVAL fnc.expiration DAY)) as date_expire "
            if ($dynFilter) {
                $query .= ", usr.level, usr.email, usr.lastenter, usr.register_date, (SELECT su.value FROM %adm_setting_user AS su WHERE usr.idst = su.id_user AND su.path_name = 'ui.language' ) as language ";
                foreach ($fields as $field) {
                    $query .= ", (SELECT fu.user_entry FROM %adm_field_userentry AS fu JOIN %adm_field AS f ON fu.id_common=f.id_common WHERE fu.id_user=usr.idst AND fu.id_common=".$field[0]." LIMIT 1 ) as _custom_".$field[0];
                }
            }

            $query .= " FROM ( "
				." ((".$fnccm_table." as fnc "
				." JOIN ".$comps_table." as cmp ON (cmp.id_competence = fnc.id_competence AND fnc.id_fncrole=".(int)$id_fncrole.") ) "
				." JOIN ".$users_table." as usr ON (usr.idst IN (".implode(",", $_users).")) ) "
				." LEFT JOIN ".$ucomp_table." as cmu ON (cmu.id_user = usr.idst AND fnc.id_competence=cmu.id_competence) ) "
				." LEFT JOIN ".$clang_table." as cml ON (cmp.id_competence = cml.id_competence AND cml.lang_code='".$language."')"

				.$where_filter
				." ORDER BY ".$_sort." ".$_dir." "
				." LIMIT ".(int)$_startIndex.", ".(int)$_results;
			$res = $this->db->query($query);
		} else {
			$res = false;
		}

		//extract records from database
		$output = array();
		if ($res && $this->db->num_rows($res)>0) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = $obj;
			}
		}
        
//        //retrieve custom fields definitions data
//		$fman = new FieldList();
//		$fields = $fman->getAllFields();
//        
//		//retrieve which fields are required
//		$custom_fields = array_keys($fields);
//
//		if (count($users_rows) > 0 && !empty($custom_fields)) {
//			//fields
//			$query_fields = "SELECT f.id_common, f.type_field, fu.id_user, fu.user_entry ".
//				" FROM %adm_field_userentry AS fu JOIN %adm_field AS f ON (fu.id_common=f.id_common) ".
//				" WHERE id_user IN (".implode(",", array_keys($users_rows)).") AND fu.id_common IN (".implode(",", $custom_fields).") ";
//			$res_fields = $this->db->query($query_fields);
//
//			$field_sons = false;
//			$countries = false;
//
//			//get values to add in the row
//			$custom_values = array();
//			while ($frow = $this->db->fetch_obj($res_fields)) {
//				if (!in_array($frow->id_common, $custom_fields)) $custom_fields[] = $frow->id_common;
//
//				$field_value = "";
//				switch ($frow->type_field) {
//					case "yesno": {
//						switch($frow->user_entry) {
//							case 1 : $field_value = Lang::t('_YES', 'field');break;
//							case 2 : $field_value = Lang::t('_NO', 'field');break;
//							default: $field_value = '';break;
//						}
//					} break;
//					case "dropdown": {
//						if ($field_sons === false) {
//							//retrieve translations for dropdowns fields
//							$query_fields_sons = "SELECT idField, id_common_son, translation FROM %adm_field_son WHERE lang_code = '".getLanguage()."' ORDER BY idField, sequence";
//							$res_fields_sons = $this->db->query($query_fields_sons);
//							$field_sons = array();
//							while ($fsrow = $this->db->fetch_obj($res_fields_sons)) {
//								$field_sons[$fsrow->idField][$fsrow->id_common_son] = $fsrow->translation;
//							}
//						}
//						if (isset($field_sons[$frow->id_common][$frow->user_entry]))
//							$field_value = $field_sons[$frow->id_common][$frow->user_entry];
//						else
//							$field_value = "";
//					} break;
//					case "country": {
//						if ($countries === false) {
//							//retrieve countries names
//							$query_countries = "SELECT id_country, name_country FROM %adm_country ORDER BY name_country";
//							$res_countries = $this->db->query($query_countries);
//							$countries = array();
//							while ($crow = $this->db->fetch_obj($res_countries)) {
//								$countries[$crow->id_country] = $crow->name_country;
//							}
//						}
//						if (isset($countries[$frow->user_entry]))
//							$field_value = $countries[$frow->user_entry];
//						else
//							$field_value = "";
//					} break;
//					default: $field_value = $frow->user_entry; break;
//				}
//				$custom_values[$frow->id_user][ '_custom_'.$frow->id_common ] = $field_value; //$frow->user_entry;
//			}
//
//			foreach ($users_rows as $idst=>$value) {
//				foreach ($custom_fields as $id_field) {
//					$users_rows[$idst]['_custom_'.$id_field] = (isset($custom_values[$idst]['_custom_'.$id_field]) ? $custom_values[$idst]['_custom_'.$id_field] : '');
//				}
//			}
//
//			if ($descendants) {
//				//check which users are descendants, if option is selected
//				$idst_org = $acl_man->getGroupST('oc_'.$idOrg);
//				$query = "SELECT idstMember FROM %adm_group_members WHERE idst = ".$idst_org." AND idstMember IN (".implode(",", array_keys($users_rows)).")";
//				$res = $this->db->query($query);
//				$arr_no_descendants = array();
//				while (list($idst_user) = $this->db->fetch_row($res))
//					$arr_no_descendants[] = $idst_user;
//				foreach ($users_rows as $idst=>$value)
//					$users_rows[$idst]['is_descendant'] = !in_array($idst, $arr_no_descendants);
//			} else {
//				//no descendants selected => the condition is always false
//				foreach ($users_rows as $idst=>$value) $users_rows[$idst]['is_descendant'] = false;
//			}
//
//			//retrieve language and level for extracted users
//			$query_others = "(SELECT u.idst, su.value, 'language' AS type ".
//				" FROM %adm_user as u JOIN %adm_setting_user AS su ".
//				" ON (u.idst = su.id_user AND su.path_name = 'ui.language') ".
//				" WHERE u.idst IN (".implode(",", array_keys($users_rows)).") ) ".
//				" UNION ".
//				" (SELECT u.idst, gm.idst AS value, 'level' AS type ".
//				" FROM %adm_user AS u JOIN %adm_group_members AS gm ".
//				" ON (u.idst = gm.idstMember AND gm.idst IN (".implode(",", array_values($levels_idst))."))".
//				" WHERE u.idst IN (".implode(",", array_keys($users_rows)).") )";
//			$res_others = $this->db->query($query_others);
//			while (list($idst, $value, $type) = $this->db->fetch_row($res_others)) {
//				switch ($type) {
//					case 'language': $users_rows[$idst]['language'] = $value; break;
//					case 'level': $users_rows[$idst]['level'] = $levels_flip[$value]; break;
//				}
//			}
//		}
        
		return $output;
	}

	public function getGapTotal($id_fncrole, $filter = false) {
		if ($id_fncrole <= 0) return false; //invalid role

		//validate filter data and abjust query
		$where_filter = "";
		if (is_array($filter)) {
			$conditions = array();

			if (isset($filter['text']) && $filter['text'] != '') {
				$conditions[] = " (cml.name LIKE '%".$filter['text']."%' "
					." OR usr.userid LIKE '%".$filter['text']."%' "
					." OR usr.firstname LIKE '%".$filter['text']."%' "
					." OR usr.lastname LIKE '%".$filter['text']."%') ";
			}
			if (isset($filter['show_gap']) && $filter['show_gap'] != 0) {
				$conditions[] = $filter['show_gap'] == 1
					? " fnc.score > IF(cmu.score_got,cmu.score_got,0) "
					: " fnc.score <= IF(cmu.score_got,cmu.score_got,0) ";
			}
			if (isset($filter['show_expired']) && $filter['show_expired'] != 0) {
				$conditions[] = $filter['show_expired'] == 1
					? " ( (DATE_ADD(cmu.last_assign_date,INTERVAL fnc.expiration DAY)) < NOW() AND fnc.expiration > 0 ) "
					: " ( (DATE_ADD(cmu.last_assign_date,INTERVAL fnc.expiration DAY)) >= NOW() OR fnc.expiration <= 0)";
			}

			if (count($conditions) > 0) {
				$where_filter .= " WHERE ".implode(" AND ", $conditions);
			}
		}

		$language = getLanguage();

		$comps_table = '%lms_competence';										//cmp
		$clang_table = '%lms_competence_lang';							//cml
		$ucomp_table = '%lms_competence_user';							//cmu
		$users_table = '%adm_user';													//usr
		$fnccm_table = $this->_getRolesCompetencesTable();	//fnc

		//mount query
		if (isset($filter['user']) && $filter['user']>0)
			$_users = array($filter['user']);
		else
			$_users = $this->getAllUsers($id_fncrole);
		
		if (!empty($_users)) {
			$query = "SELECT COUNT(*) "
				." FROM ( "
				." ((".$fnccm_table." as fnc "
				." JOIN ".$comps_table." as cmp ON (cmp.id_competence = fnc.id_competence AND fnc.id_fncrole=".(int)$id_fncrole.") ) "
				." JOIN ".$users_table." as usr ON (usr.idst IN (".implode(",", $_users).")) ) "
				." LEFT JOIN ".$ucomp_table." as cmu ON (cmu.id_user = usr.idst AND fnc.id_competence=cmu.id_competence) ) "
				." LEFT JOIN ".$clang_table." as cml ON (cmp.id_competence = cml.id_competence AND cml.lang_code='".$language."')"
				.$where_filter;
			$res = $this->db->query($query);
		} else {
			$res = false;
		}

		//extract records from database
		$output = false;
		if ($res && $this->db->num_rows($res)>0) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}


	public function getAllFunctionalRoles($details = false) {
		$output = array();
		
		//if details requested, extract languges
		if ($details) {
			//extract langs from DB
			$langs = array();
			$query = "SELECT fl.* FROM ".$this->_getRolesTable()." as f JOIN ".$this->_getRolesLangTable()." as fl "
				." ON (f.id_fncrole = fl.id_fncrole)";
			$res = $this->db->query($query);
			if ($res) {
				while ($obj = $this->db->fetch_obj($res)) {
					$langs[$obj->id_fncrole][$obj->lang_code]['name'] = $obj->name;
					$langs[$obj->id_fncrole][$obj->lang_code]['description'] = $obj->description;
				}
			}
		}

		//get roles
		//$query = "SELECT * FROM ".$this->_getRolesTable();
		$query = "SELECT g.idst, f.id_group FROM %adm_group as g LEFT JOIN ".$this->_getRolesTable()." as f "
			." ON (g.idst = f.id_fncrole) WHERE groupid LIKE '/fncroles/%'";
		$res = $this->db->query($query);
		if ($res) {
			$lang_codes = Docebo::langManager()->getAllLangCode();
			while ($obj = $this->db->fetch_obj($res)) {
				$t_obj = new stdClass();
				$t_obj->id_fncrole = $obj->idst;
				if ($details) {

					$arr_langs = array();
					for ($i=0; $i<count($lang_codes); $i++) {
						$arr_langs[$lang_codes[$i]] = isset($langs[$obj->idst][$lang_codes[$i]])
							? array(
									'name' => $langs[$obj->idst][$lang_codes[$i]]['name'],
									'description' => $langs[$obj->idst][$lang_codes[$i]]['description']
								)
							: array(
									'name' => '',
									'description' => ''
								);
					}
					$t_obj->langs = $arr_langs;
					$t_obj->id_group = (int)$obj->id_group;

					$output[$obj->idst] = $t_obj;
				} else {
					$output[] = $obj->idst;
				}
			}
		}

		return $output;
	}

	/**
	 * Return a list of fnc names in the current language based on the ids given
	 * @param array $arr_fncroles an array of fnc roles ids
	 * @return array
	 */
	public function getFunctionalRolesNames($arr_fncroles) {
		if (is_numeric($arr_fncroles)) $arr_fncroles = array($arr_fncroles);
		if (!is_array($arr_fncroles)) return false;
		if (count($arr_fncroles) <= 0) return array();

		//extract fnc role data
		$output = array();
		$query = "SELECT r.idst as id_fncrole, rl.name "
			." FROM %adm_group as r "
			." LEFT JOIN ".$this->_getRolesLangTable()." as rl "
			."	ON ( r.idst = rl.id_fncrole AND r.groupid LIKE '/fncroles/%') "
			." WHERE rl.lang_code = '".getLanguage()."' AND r.idst IN (".implode(',', $arr_fncroles).")";
		$res = $this->db->query($query);
		if (!$res) return $output;
		while ($obj = $this->db->fetch_row($res)) {
			$output[$obj[0]] = $obj[1];
		}
		return $output;
	}

	/**
	 * Return a list of fnc names and description in all the the languages
	 * @param array $arr_fncroles an array of fnc roles ids
	 * @return array
	 */
	public function getFunctionalRolesInfo($arr_fncroles) {
		if (is_numeric($arr_fncroles)) $arr_fncroles = array($arr_fncroles);
		if (!is_array($arr_fncroles)) return false;
		if (count($arr_fncroles) <= 0) return array();

		//extract competence data
		$output = array();
		$query = "SELECT g.idst as id_fncrole, f.id_group "
			." FROM %adm_group as g LEFT JOIN ".$this->_getRolesTable()." as f "
			." ON (g.idst = f.id_fncrole AND g.groupid LIKE '/fncroles/%') "
			." WHERE g.idst IN (".implode(',', $arr_fncroles).")";
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[$obj->id_fncrole] = $obj;
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
		$_arr_fncroles = array_keys($output);
		foreach ($_arr_fncroles as $id_fncrole) {
			$langs[$id_fncrole] = $_void_lang_arr;
		}

		//extract languages from database
		$query = "SELECT * FROM ".$this->_getRolesLangTable()." "
			." WHERE id_fncrole IN (".implode(',', $_arr_fncroles).")";
		$res = $this->db->query($query);
		while ($obj = $this->db->fetch_obj($res)) {
			if (in_array($obj->lang_code, $lang_codes)) {
				$langs[$obj->id_fncrole][$obj->lang_code]['name'] = $obj->name;
				$langs[$obj->id_fncrole][$obj->lang_code]['description'] = $obj->description;
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



	public function getUserFunctionalRoles($id_user, $keys = false) {
		if ($id_user <= 0) return false;
		$output = array();
		$cmodel = new CompetencesAdm();

		//extract roles
		$roles = $this->getAllFunctionalRoles(true);
		$language = getLanguage();

		//for each role:
		foreach ($roles as $id_fncrole => $rdata) {
			//check if the user has been assigned to this role
			$all = $this->getAllUsers($id_fncrole);
			if (in_array($id_user, $all)) {
				
				$obj = new stdClass();
				$obj->name = $rdata->langs[$language]['name'];
				
				//extract role's required comeptences and users actual competences
				$f_competences = $this->getCompetences($id_fncrole);
				$u_competences = $cmodel->getUserCompetences($id_user);
				
				$f_cinfo = $this->getCompetencesProperties($f_competences, $id_fncrole);
				
				//compare them
				$obtained = 0;
				foreach ($f_cinfo as $id_competence => $properties) {
					if (array_key_exists($id_competence, $u_competences)) {
						//if the score obtained by the user for a given competences is more or equal
						//than the score required by the role, then consider th e competence obtained
						//and increment the counter
						$obtained += ($u_competences[$id_competence]->score_got >= $properties->score ? 1 : 0);
					}
				}
				
				$obj->competences_obtained = (int)$obtained;
				$obj->competences_required = count($f_competences);

				if ($keys)
					$output[$id_fncrole] = $obj;
				else
					$output[] = $obj;
			}
		}

		return $output;
	}


	public function getUserRequiredCompetences($id_user, $flat = false) {
		if ($id_user <= 0) return false;

		$output = array();

		//extract user roles
		$uroles = array();
		$roles = $this->getAllFunctionalRoles(true);
		foreach ($roles as $id_fncrole => $rdata) {
			//check if the user has been assigned to this role
			$all = $this->getAllUsers($id_fncrole);
			if (in_array($id_user, $all)) {
				$uroles[] = $id_fncrole;
			}
		}

		if (count($roles) > 0) {
			$query = "SELECT * FROM ".$this->_getRolesCompetencesTable()." WHERE id_fncrole IN (".implode(",", $uroles).")";
			$res = $this->db->query($query);
			while ($obj = $this->db->fetch_obj($res)) {
				$tobj = new stdClass();
				$tobj->score = $obj->score;
				$tobj->expiration = $obj->expiration;
				if ($flat)
					$output[$obj->id_competence] = $tobj;
				else
					$output[$obj->id_competence][$obj->id_fncrole] = $tobj;
			}
		}

		return $output;
	}



	public function searchFunctionalRolesByName($query, $limit = false, $language = false, $filter = false) {
		if ((int)$limit <= 0) $limit = Get::sett('visuItem', 25);
		$output = array();

		$_qfilter = "";
		if ($filter) {
			$ulevel = Docebo::user()->getUserLevelId();
			if ($ulevel != ADMIN_GROUP_GODADMIN) {
				require_once(_base_.'/lib/lib.preference.php');
				$adminManager = new AdminPreference();
				$admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
				$_qfilter .= " AND g.idst IN (".implode(",", $admin_tree).") ";
			}
		}

		$lang_code = $language ? $language : getLanguage();
		$query = "SELECT g.idst as id_fncrole, l.name FROM %adm_group as g "
			." JOIN ".$this->_getRolesLangTable()." as l ON (g.idst = l.id_fncrole AND l.lang_code='".$lang_code."')"
			." WHERE l.name LIKE '%".$query."%' ".$_qfilter." ORDER BY l.name "
			.((int)$limit>0 ? " LIMIT 0, ".(int)$limit : "");
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = $obj;
			}
		}
		return $output;
	}


}





?>