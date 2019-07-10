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

class UsermanagementAdm extends Model {

	protected $db;
	protected $aclManager;
	protected $json;
	protected $sessionPrefix;

	protected $orgUser;
	protected $orgCache;

	public function  __construct() {
		require_once(_base_.'/lib/lib.json.php');
		$this->db = DbConn::getInstance();
		$this->aclManager = Docebo::user()->getAclManager();
		$this->json = new Services_JSON();
		$this->orgUser = false;
		$this->orgCache = false;
		$this->sessionPrefix = 'usermanagement';
	}

	public function getPerm() {
		return array(
			'view'					=> 'standard/view.png',
			'add'					=> 'standard/add.png',
			'mod'					=> 'standard/edit.png',
			'del'					=> 'standard/delete.png',
			'approve_waiting_user'	=> 'standard/wait_alarm.png',
			'associate_user'		=> 'standard/moduser.png',
			// Enable orgchart nodes creation and edit permission for admins
			'add_org'				=> 'standard/add.png',
			'mod_org'                               => 'standard/modadmin.png',
			'del_org'				=> 'standard/delete.png'
		);
	}

	public function getOrgPath($idOrg) {
		$output = "";
		$query = "SELECT idOrg, idParent, path FROM %adm_org_chart_tree WHERE idOrg=".(int)$idOrg;
		$res = $this->db->query($query);
		list($idOrg, $idParent, $path) = $this->db->fetch_row($res);
		if ($path!="") {
			$list = explode('/', str_replace('/root/', '', $path));
			for ($i=0; $i<count($list); $i++) $list[$i] = (int)$list[$i];

			//languages
			$names = array();
			$query = "SELECT id_dir, translation FROM %adm_org_chart WHERE lang_code='".getLanguage()."'";
			$res = $this->db->query($query);
			while(list($id_dir, $translation) = $this->db->fetch_row($res)) {
				$names[$id_dir] = $translation;
			}

			return $names[$idOrg];
		} else {
			$output .= Get::sett('title_organigram_chart', "")." (root)";
		}
		return $output;
	}

	public function isFolderEnabled($idOrg, $idstUser = false) {
		$output = true;
		$userlevelid = $this->getUserLevel($idstUser);
		if ($userlevelid != ADMIN_GROUP_GODADMIN) {
			$org_groups = $this->_getAdminOrgTree($idstUser);
			$output = in_array($idOrg, $org_groups);
		}
		return $output;
	}

	protected function _getOrgGroups($idOrg, $descendants) {
		$output = array();
		if ($descendants) {
			list($left, $right) = $this->getFolderLimits($idOrg);
			$query = "SELECT idOrg FROM %adm_org_chart_tree WHERE iLeft>=".$left." AND iRight<=".$right;
			$res = $this->db->query($query);
			$arr_org = array();
			while (list($id_org) = $this->db->fetch_row($res)) {
				$arr_org[] = "'/oc_".$id_org."'";
				$arr_org[] = "'/ocd_".$id_org."'";
			}
			if ($idOrg==0) {
				$arr_org[] = "'/oc_0'";
				$arr_org[] = "'/ocd_0'";
			}
			$query = "SELECT idst FROM %adm_group WHERE groupid IN (".implode(",", $arr_org).")";
			$res = $this->db->query($query);
			while (list($idg) = $this->db->fetch_row($res)) {
				$output[] = $idg;
			}
		} else {
			$query = "SELECT idst FROM %adm_group WHERE groupid='/oc_".(int)$idOrg."' OR groupid='/ocd_".(int)$idOrg."'";
			$res = $this->db->query($query);
			while(list($idst) = $this->db->fetch_row($res))
				$output[] = $idst;
		}
		return $output;
	}


	public function _getRootGroups() {
		$output = array();
		$arr_org = array("'/oc_0'", "'/ocd_0'");
		$query = "SELECT idst FROM %adm_group WHERE groupid IN (".implode(",", $arr_org).")";
		$res = $this->db->query($query);
		while (list($idg) = $this->db->fetch_row($res)) {
			$output[] = $idg;
		}
		return $output;
	}


	public function getUsersList($idOrg, $descendants = false, $pagination = array(), $filter = false, $usersFilter = false, $learning_filter = 'none') {

		require_once(_adm_.'/lib/lib.field.php');

		$acl_man = Docebo::user()->getAclManager();
		$acl_man->include_suspended = TRUE;

		//retrieve custom fields definitions data
		$fman = new FieldList();
		$fields = $fman->getAllFields();

		//validate parameters
		if (!is_array($pagination)) $pagination = array();

		//read values for pagination, or use default if a value is not set
		$startIndex = (isset($pagination['startIndex']) ? $pagination['startIndex'] : 0);
		$results = (isset($pagination['results']) ? $pagination['results'] : Get::sett('visuItem', 25));

		$sort = 'u.userid';
		$dir = 'ASC';
		$query_type = 'standard';

		if (isset($pagination['sort'])) {
			if (is_numeric($pagination['sort'])) {
				//custom field
				$sort = $pagination['sort'];
				if (isset($fields[$sort][FIELD_INFO_TYPE]) && $fields[$sort][FIELD_INFO_TYPE] == "dropdown")
					$query_type = 'custom_sons';
				else
					$query_type = 'custom';
			} else {

				switch ($pagination['sort']) {
					//%adm_user fields
					case 'firstname': $sort = 'u.firstname'; break;
					case 'lastname': $sort = 'u.lastname'; break;
					case 'fullname': $sort = "u.lastname, u.firstname"; break;
					case 'email': $sort = "u.email"; break;
					case 'lastenter': $sort = "u.lastenter"; break;
					case 'register_date': $sort = "u.register_date"; break;

					//dynamic other fields
					case 'level': $query_type = 'level'; break;
					case 'language': $query_type = 'language'; break;

					default: $sort = 'u.userid';
				}
			}
		}
		if( isset($pagination['dir']) ) $dir = $this->clean_dir($pagination['dir']);

		$useAnonymous	= false;
		$searchFilter	= isset($filter['text']) ? $filter['text'] : false;
		$useSuspended	= isset($filter['suspended']) ? (bool)$filter['suspended'] : true;
		$dynFilter		= isset($filter['dyn_filter']) ? $filter['dyn_filter'] : false;

		//list of users idst to apply in main query as a filter
		$admin_info['users'] = array();
		$usersList = array();

		//detect admin level, if requested
		$is_subadmin = false;
		if ($usersFilter) {
			$userlevelid = $this->getUserLevel();
			if( $userlevelid !== ADMIN_GROUP_GODADMIN && $userlevelid !== ADMIN_GROUP_USER ) {

				require_once(_base_.'/lib/lib.preference.php');
				$adminManager	= new AdminPreference();
				$admin_info		= $adminManager->getAdminAllSett( Docebo::user()->getIdSt(), 'u.idst' );
				$is_subadmin	= true;
			}
		}

		//apply the dynamic conditional filter, if set. First extract all users idst,
		//then apply a filter in the main query (implementing the complex filter
		//directly in the main query may result impractical due to physical impossibility
		//or extremely poor performance)
		$is_dynfiltered = false;
		if ($dynFilter) {
			require_once(_adm_.'/lib/user_selector/lib.dynamicuserfilter.php');
			$obj_dynfilter = new DynamicUserFilter("user_dyn_filter");
			$usersList = $obj_dynfilter->getUsers($dynFilter);
			$is_dynfiltered = true;
		}

		//built users list's filter, if requested
		$queryUserFilter_1 = "";
		$queryUserFilter_2 = "";
		$queryUserFilter_3 = "";
		if ($is_subadmin && $is_dynfiltered) {
			$t_arr = array_intersect($usersList, $admin_info['users']);
			if (count($t_arr) <= 0) return array(); //return a 0-length array, because at this point the selection is void and no more query is necessary
			$queryUserFilter_1 .= " ) AND u.idst IN (".implode(',', $t_arr).") ";
			$queryUserFilter_2 .= " ) AND gm.idstMember IN (".implode(',', $t_arr).") ";
			$queryUserFilter_3 .= " AND gm.idstMember IN (".implode(',', $t_arr).") ";
		} elseif ($is_subadmin && !$is_dynfiltered) {
			if (count($admin_info['users']) <= 0) return array(); //if (count($admin_tree) <= 0) return array(); //return a 0-length array, because at this point the selection is void and no more query is necessary
			$queryUserFilter_1 .= " AND u.idst IN (".implode(',', $admin_info['users']).") ) ";//" AND gm.idst IN (".implode(',', $admin_tree).") ) ";
			$queryUserFilter_2 .= " AND u.idst IN (".implode(',', $admin_info['users']).") ) ";//" AND gm.idst IN (".implode(',', $admin_tree).") ) ";
			$queryUserFilter_3 .= " AND u.idst IN (".implode(',', $admin_info['users']).") ";//" AND gm.idst IN (".implode(',', $admin_tree).") ";
		} elseif (!$is_subadmin && $is_dynfiltered) {
			if (count($usersList) <= 0) return array(); //return a 0-length array, because at this point the selection is void and no more query is necessary
			$queryUserFilter_1 .= " AND u.idst IN (".implode(',', $usersList).") ) ";
			$queryUserFilter_2 .= " AND u.idst IN (".implode(',', $usersList).") ) ";
			$queryUserFilter_3 .= " AND u.idst IN (".implode(',', $usersList).") ";
		} else { //no filter to apply
			$queryUserFilter_1 .= ")";
			$queryUserFilter_2 .= ")";
			$queryUserFilter_3 .= "";
		}

		switch ($learning_filter) {
			case 'message':
				$id_course = $_SESSION['message_filter'];

				if($id_course != 0)
					$res = $this->aclManager->getGroupsIdstFromBasePath('/lms/course/'.$id_course.'/subscribed/');
				else
				{
					if($userlevelid !== ADMIN_GROUP_GODADMIN)
					{
						require_once(_lms_.'/lib/lib.course.php');
						$course_man = new Man_Course();
						$all_courses = $course_man->getUserCourses(Docebo::user()->getIdSt());
						$res = array();
						foreach($all_courses as $id_course => $name)
						{
							$arr_idst_group = $this->aclManager->getGroupsIdstFromBasePath('/lms/course/'.$id_course.'/subscribed/');
							$res = array_merge($res, $arr_idst_group);
						}
					}
				}

				$queryUserFilter_1 .=	($userlevelid !== ADMIN_GROUP_GODADMIN ? " AND u.idst IN ( SELECT idstMember FROM %adm_group_members as gm WHERE gm.idst IN (".implode(",", $res).") ) " : '')
									." AND u.idst <> '".Docebo::user()->getIdSt()."' ";
				$queryUserFilter_2 .=	($userlevelid !== ADMIN_GROUP_GODADMIN ? " AND u.idst IN ( SELECT idstMember FROM %adm_group_members as gm WHERE gm.idst IN (".implode(",", $res).") ) " : '')
									." AND u.idst <> '".Docebo::user()->getIdSt()."' ";
				$queryUserFilter_3 .=	($userlevelid !== ADMIN_GROUP_GODADMIN ? " AND u.idst IN ( SELECT idstMember FROM %adm_group_members as gm WHERE gm.idst IN (".implode(",", $res).") ) " : '')
									." AND u.idst <> '".Docebo::user()->getIdSt()."' ";
			break;
			case 'course':
				$id_course = $_SESSION['idCourse'];

				$res = $this->aclManager->getGroupsIdstFromBasePath('/lms/course/'.$id_course.'/subscribed/');

				$queryUserFilter_1 .= " AND u.idst IN ( SELECT idstMember FROM %adm_group_members as gm WHERE gm.idst IN (".implode(",", $res).") ) ";
				$queryUserFilter_2 .= " AND u.idst IN ( SELECT idstMember FROM %adm_group_members as gm WHERE gm.idst IN (".implode(",", $res).") ) ";
				$queryUserFilter_3 .= " AND u.idst IN ( SELECT idstMember FROM %adm_group_members as gm WHERE gm.idst IN (".implode(",", $res).") ) ";
			break;
		}

		// Contruct user query
		if($idOrg != 0)
		{
			$id_groups = $this->_getOrgGroups($idOrg, $descendants);
			if ($is_subadmin) $id_groups = array_intersect($id_groups, $admin_info['groups']);
			if (!$id_groups || (is_array($id_groups) && count($id_groups) <= 0)) return array();
		}
		else
		{
			if($is_subadmin) {
				$id_groups = $admin_info['groups'];
				if (empty($id_groups)) $id_groups = $this->_getRootGroups();
			} /*else {
				$id_groups = $this->_getOrgGroups(0, true);
			}*/
		}

		//user levels
		$levels_idst = $this->aclManager->getAdminLevels();
		$levels_flip = array_flip($levels_idst);

		$query = "";
		switch ($query_type) {
			//query with sorting on standard %adm_user field
			case 'standard': {
				$query = "SELECT DISTINCT u.idst, u.userid, u.lastname, u.firstname, u.email, u.register_date, u.lastenter, u.valid "
					." FROM %adm_user as u ";
                                if($idOrg || $is_subadmin) {
                                    $query .=   " JOIN (SELECT idstMember FROM %adm_group_members AS gm WHERE 1 AND gm.idst IN ( ".implode(",", $id_groups)." ))"
                                              . " AS uit ON uit.idstMember = u.idst";
                                }
                                $query .=" WHERE (1 = 1"
					//." WHERE u.idst IN ( SELECT idstMember FROM %adm_group_members as gm WHERE gm.idst IN ( ".implode(",", $id_groups)." )  "// )
					.$queryUserFilter_1
					.($useSuspended ? "" : " AND u.valid = 1 ")." "
					.($useAnonymous ? "" : " AND u.userid <> '/Anonymous' ")." ";

				if ($searchFilter) {
					$query .= " AND (
						u.userid LIKE '%".$searchFilter."%' OR
						u.firstname LIKE '%".$searchFilter."%' OR
						u.lastname LIKE '%".$searchFilter."%' OR
						u.email LIKE '%".$searchFilter."%'
					)";
				}
				$query .= " ORDER BY ".$sort." ".$dir." ".
					" LIMIT ".(int)$startIndex.", ".(int)$results;
			} break;

			//query with sorting on a custom field (like texts)
			case 'custom': {

				$query = "SELECT DISTINCT u.idst, u.userid, u.lastname, u.firstname, u.email, u.register_date, u.lastenter, u.valid "
					." FROM %adm_user as u LEFT JOIN %adm_field_userentry as f ON (u.idst=f.id_user AND f.id_common=".(int)$sort.") ";
                                if($idOrg || $is_subadmin) {
                                    $query .=   " JOIN (SELECT idstMember FROM %adm_group_members AS gm WHERE 1 AND gm.idst IN ( ".implode(",", $id_groups)." ))"
                                              . " AS uit ON uit.idstMember = u.idst";
                                }
                                $query .=" WHERE (1 = 1"
					.$queryUserFilter_2
					.($useSuspended ? "" : " AND u.valid = 1 ")." "
					.($useAnonymous ? "" : " AND u.userid <> '/Anonymous' ")." ";

				if ($searchFilter) {
					$query .= " AND (
						u.userid LIKE '%".$searchFilter."%' OR
						u.firstname LIKE '%".$searchFilter."%' OR
						u.lastname LIKE '%".$searchFilter."%' OR
						u.email LIKE '%".$searchFilter."%'
					)";
				}

				$query .= " ORDER BY f.user_entry ".$dir." ".
					" LIMIT ".(int)$startIndex.", ".(int)$results;
			} break;

			//query with sorting on a custom field with sons (like dropdowns)
			case 'custom_sons': {
				$query = "SELECT DISTINCT u.idst, u.userid, u.lastname, u.firstname, u.email, u.register_date, u.lastenter, u.valid ";

				$query .= " FROM (%adm_user as u JOIN %adm_group_members as gm ON ( u.idst = gm.idstMember )) "
					." LEFT JOIN (%adm_field_userentry as f LEFT JOIN %adm_field_son as fs "
					." ON (f.user_entry = fs.id_common_son AND f.id_common = fs.idField AND fs.lang_code='".getLanguage()."')) "
					." ON (u.idst=f.id_user AND f.id_common=".(int)$sort.") ";
                                if($idOrg || $is_subadmin) {
                                    $query .=   " JOIN (SELECT idstMember FROM %adm_group_members AS gm WHERE 1 AND gm.idst IN ( ".implode(",", $id_groups)." ))"
                                              . " AS uit ON uit.idstMember = u.idst";
                                }
                                $query .=" WHERE (1 = 1"
					.$queryUserFilter_3
					.($useSuspended ? "" : " AND u.valid = 1 ")." "
					.($useAnonymous ? "" : " AND u.userid <> '/Anonymous' ")." ";

				if ($searchFilter) {
					$query .= " AND (
						u.userid LIKE '%".$searchFilter."%' OR
						u.firstname LIKE '%".$searchFilter."%' OR
						u.lastname LIKE '%".$searchFilter."%' OR
						u.email LIKE '%".$searchFilter."%'
					)";
				}

				$query .= " ORDER BY fs.translation ".$dir." ".
					" LIMIT ".(int)$startIndex.", ".(int)$results;
			} break;

			//query with sorting on user level
			case 'level': {
				$query = "SELECT DISTINCT u.idst, u.userid, u.lastname, u.firstname, u.email, u.register_date, u.lastenter, u.valid "
					." FROM %adm_user as u JOIN %adm_group_members AS gm "
					." ON (u.idst = gm.idstMember AND gm.idst IN (".implode(",", array_values($levels_idst)).")) ";
                                if($idOrg || $is_subadmin) {
                                    $query .=   " JOIN (SELECT idstMember FROM %adm_group_members AS gm WHERE 1 AND gm.idst IN ( ".implode(",", $id_groups)." ))"
                                              . " AS uit ON uit.idstMember = u.idst";
                                }
                                $query .=" WHERE (1 = 1"
					.$queryUserFilter_1
					.($useSuspended ? "" : " AND u.valid = 1 ")." "
					.($useAnonymous ? "" : " AND u.userid <> '/Anonymous' ")." ";

				if ($searchFilter) {
					$query .= " AND (
						u.userid LIKE '%".$searchFilter."%' OR
						u.firstname LIKE '%".$searchFilter."%' OR
						u.lastname LIKE '%".$searchFilter."%' OR
						u.email LIKE '%".$searchFilter."%'
					)";
				}
				$query .= " ORDER BY gm.idst ".(strtolower($dir) == 'asc' ? "DESC" : "ASC").", u.userid ".$dir." ". //we assume that idsts of level groups are pre-ordered
					" LIMIT ".(int)$startIndex.", ".(int)$results;
			} break;

			//query with sorting on user language
			case 'language': {
				$levels_idst = array_values( $this->aclManager->getAdminLevels() );
				$query = "SELECT DISTINCT u.idst, u.userid, u.lastname, u.firstname, u.email, u.register_date, u.lastenter, u.valid "
					." FROM %adm_user as u LEFT JOIN %adm_setting_user AS su "
					." ON (u.idst = su.id_user AND su.path_name = 'ui.language') ";
                                if($idOrg || $is_subadmin) {
                                    $query .=   " JOIN (SELECT idstMember FROM %adm_group_members AS gm WHERE 1 AND gm.idst IN ( ".implode(",", $id_groups)." ))"
                                              . " AS uit ON uit.idstMember = u.idst";
                                }
                                $query .=" WHERE (1 = 1"
					.$queryUserFilter_1
					.($useSuspended ? "" : " AND u.valid = 1 ")." "
					.($useAnonymous ? "" : " AND u.userid <> '/Anonymous' ")." ";

				if ($searchFilter) {
					$query .= " AND (
						u.userid LIKE '%".$searchFilter."%' OR
						u.firstname LIKE '%".$searchFilter."%' OR
						u.lastname LIKE '%".$searchFilter."%' OR
						u.email LIKE '%".$searchFilter."%'
					)";
				}
				$query .= " ORDER BY su.value ".$dir.", u.userid ".$dir." ". //we assume that idsts of level groups are pre-ordered
					" LIMIT ".(int)$startIndex.", ".(int)$results;
			} break;

		}
		// Retrive all the user selected
		$users_rows = array();

		$res = $this->db->query($query);
		while($row = $this->db->fetch_obj($res)) {

			$users_rows[$row->idst] = array(
				'idst' => $row->idst,
				'userid' => $row->userid,
				'firstname' => $row->firstname,
				'lastname' => $row->lastname,
				'email' => $row->email,
				'register_date' => $row->register_date,
				'lastenter' => $row->lastenter,
				'valid' => $row->valid
			);

		} //end while

		//retrieve which fields are required
		$custom_fields = array_keys($fields);

		if (count($users_rows) > 0 && !empty($custom_fields)) {
			//fields
			$query_fields = "SELECT f.id_common, f.type_field, fu.id_user, fu.user_entry ".
				" FROM %adm_field_userentry AS fu JOIN %adm_field AS f ON (fu.id_common=f.id_common) ".
				" WHERE id_user IN (".implode(",", array_keys($users_rows)).") AND fu.id_common IN (".implode(",", $custom_fields).") ";
			$res_fields = $this->db->query($query_fields);

			$field_sons = false;
			$countries = false;

			//get values to add in the row
			$custom_values = array();
			while ($frow = $this->db->fetch_obj($res_fields)) {
				if (!in_array($frow->id_common, $custom_fields)) $custom_fields[] = $frow->id_common;

				$field_value = "";
				switch ($frow->type_field) {
					case "yesno": {
						switch($frow->user_entry) {
							case 1 : $field_value = Lang::t('_YES', 'field');break;
							case 2 : $field_value = Lang::t('_NO', 'field');break;
							default: $field_value = '';break;
						}
					} break;
					case "dropdown": {
						if ($field_sons === false) {
							//retrieve translations for dropdowns fields
							$query_fields_sons = "SELECT idField, id_common_son, translation FROM %adm_field_son WHERE lang_code = '".getLanguage()."' ORDER BY idField, sequence";
							$res_fields_sons = $this->db->query($query_fields_sons);
							$field_sons = array();
							while ($fsrow = $this->db->fetch_obj($res_fields_sons)) {
								$field_sons[$fsrow->idField][$fsrow->id_common_son] = $fsrow->translation;
							}
						}
						if (isset($field_sons[$frow->id_common][$frow->user_entry]))
							$field_value = $field_sons[$frow->id_common][$frow->user_entry];
						else
							$field_value = "";
					} break;
					//PURPLE fix class copy per visualizzazione corretta dei record nelle tabelle
					case "copy": {
						if ($field_sons === false) {
							//retrieve translations for dropdowns fields
							$query_fields_sons = "SELECT idField, id_common_son, translation FROM %adm_field_son WHERE lang_code = '".getLanguage()."' ORDER BY idField, sequence";
							$res_fields_sons = $this->db->query($query_fields_sons);
							$field_sons = array();
							while ($fsrow = $this->db->fetch_obj($res_fields_sons)) {
								$field_sons[$fsrow->idField][$fsrow->id_common_son] = $fsrow->translation;
							}
						}
						if (isset($field_sons[$frow->copy_of][$frow->user_entry]))
							$field_value = $field_sons[$frow->copy_of][$frow->user_entry];
						else
							$field_value = "";
					} break;
					//END PURPLE
					case "country": {
						if ($countries === false) {
							//retrieve countries names
							$query_countries = "SELECT id_country, name_country FROM %adm_country ORDER BY name_country";
							$res_countries = $this->db->query($query_countries);
							$countries = array();
							while ($crow = $this->db->fetch_obj($res_countries)) {
								$countries[$crow->id_country] = $crow->name_country;
							}
						}
						if (isset($countries[$frow->user_entry]))
							$field_value = $countries[$frow->user_entry];
						else
							$field_value = "";
					} break;
					default: $field_value = $frow->user_entry; break;
				}
				$custom_values[$frow->id_user][ '_custom_'.$frow->id_common ] = $field_value; //$frow->user_entry;
			}

			foreach ($users_rows as $idst=>$value) {
				foreach ($custom_fields as $id_field) {
					$users_rows[$idst]['_custom_'.$id_field] = (isset($custom_values[$idst]['_custom_'.$id_field]) ? $custom_values[$idst]['_custom_'.$id_field] : '');
				}
			}

			if ($descendants) {
				//check which users are descendants, if option is selected
				$idst_org = $acl_man->getGroupST('oc_'.$idOrg);
				$query = "SELECT idstMember FROM %adm_group_members WHERE idst = ".$idst_org." AND idstMember IN (".implode(",", array_keys($users_rows)).")";
				$res = $this->db->query($query);
				$arr_no_descendants = array();
				while (list($idst_user) = $this->db->fetch_row($res))
					$arr_no_descendants[] = $idst_user;
				foreach ($users_rows as $idst=>$value)
					$users_rows[$idst]['is_descendant'] = !in_array($idst, $arr_no_descendants);
			} else {
				//no descendants selected => the condition is always false
				foreach ($users_rows as $idst=>$value) $users_rows[$idst]['is_descendant'] = false;
			}

			//retrieve language and level for extracted users
			$query_others = "(SELECT u.idst, su.value, 'language' AS type ".
				" FROM %adm_user as u JOIN %adm_setting_user AS su ".
				" ON (u.idst = su.id_user AND su.path_name = 'ui.language') ".
				" WHERE u.idst IN (".implode(",", array_keys($users_rows)).") ) ".
				" UNION ".
				" (SELECT u.idst, gm.idst AS value, 'level' AS type ".
				" FROM %adm_user AS u JOIN %adm_group_members AS gm ".
				" ON (u.idst = gm.idstMember AND gm.idst IN (".implode(",", array_values($levels_idst))."))".
				" WHERE u.idst IN (".implode(",", array_keys($users_rows)).") )";
			$res_others = $this->db->query($query_others);
			while (list($idst, $value, $type) = $this->db->fetch_row($res_others)) {
				switch ($type) {
					case 'language': $users_rows[$idst]['language'] = $value; break;
					case 'level': $users_rows[$idst]['level'] = $levels_flip[$value]; break;
				}
			}
		}
		return $users_rows;
	}


	public function getTotalUsers($idOrg, $descendants = false, $filter = false, $usersFilter = false, $learning_filter = 'none') {
		$useAnonymous = false;
		$searchFilter = isset($filter['text']) ? $filter['text'] : false;
		$useSuspended = isset($filter['suspended']) ? (bool)$filter['suspended'] : true;
		$dynFilter = isset($filter['dyn_filter']) ? $filter['dyn_filter'] : false;

		$acl_man = Docebo::user()->getAclManager();
		$acl_man->include_suspended = TRUE;

		//list of users idst to apply in main query as a filter
		$admin_info = array('users' => array());
		$usersList = array();

		$is_subadmin = false;
		if ($usersFilter) {
			$userlevelid = $this->getUserLevel();
			if($userlevelid !== ADMIN_GROUP_GODADMIN && $userlevelid !== ADMIN_GROUP_USER) {

				require_once(_base_.'/lib/lib.preference.php');
				$adminManager	= new AdminPreference();
				$admin_info		= $adminManager->getAdminAllSett( Docebo::user()->getIdSt(), 'u.idst' );
				$is_subadmin	= true;
			}
		}

		if($idOrg != 0) {
			$id_groups = $this->_getOrgGroups($idOrg, $descendants);
			if ($is_subadmin) $id_groups = array_intersect($id_groups, $admin_info['groups']);
			if (!$id_groups || (is_array($id_groups) && count($id_groups) <= 0)) return 0;
		} else {
			if($is_subadmin) {
				$id_groups = $admin_info['groups'];
				if (empty($id_groups)) $id_groups = $this->_getRootGroups();
			} /*else {
				$id_groups = $this->_getOrgGroups(0, true);
			}*/
		}

		$is_dynfiltered = false;
		if ($dynFilter) {
			require_once(_adm_.'/lib/user_selector/lib.dynamicuserfilter.php');
			$obj_dynfilter = new DynamicUserFilter("user_dyn_filter");
			$usersList = $obj_dynfilter->getUsers($dynFilter);
			$is_dynfiltered = true;
		}

		//built users list filter, if requested
		$queryUserFilter = "";
		if ($is_subadmin && $is_dynfiltered) {
			$t_arr = array_intersect($usersList, $admin_info['users']);
			if (count($t_arr) <= 0) return 0; //no users can be extracted at this point: no more querying is necessary
			$queryUserFilter .= " AND u.idst IN (".implode(',', $t_arr).") ";
		} elseif ($is_subadmin && !$is_dynfiltered) {
			if (count($admin_info['users']) <= 0) return 0; //no users can be extracted at this point: no more querying is necessary
			$queryUserFilter .= " AND u.idst IN (".implode(',', $admin_info['users']).") ";
		} elseif (!$is_subadmin && $is_dynfiltered) {
			if (count($usersList) <= 0) return 0; //no users can be extracted at this point: no more querying is necessary
			$queryUserFilter .= " AND u.idst IN (".implode(',', $usersList).") ";
		} //else: no filter to apply

		$filtered_query = " select COUNT(DISTINCT u.idst) "
				." from %adm_user as u ";
                                if($idOrg || $is_subadmin) {
                                    $filtered_query .= " join %adm_group_members as gm on (u.idst = gm.idstMember) and gm.idst IN (".implode(",", $id_groups).") ";
                                }
                                $filtered_query .= " WHERE 1 = 1 ";

				$filtered_query .=$queryUserFilter
				.($useSuspended ? "" : " AND u.valid = 1 " )
				.($useAnonymous ? "" : " AND u.userid <> '/Anonymous' ");


		if ($searchFilter) {
			$filtered_query .= " AND ( ".
				"	u.userid LIKE '%".$searchFilter."%' OR ".
				"	u.firstname LIKE '%".$searchFilter."%' OR ".
				"	u.lastname LIKE '%".$searchFilter."%' OR ".
				"	u.email LIKE '%".$searchFilter."%' ".
				") ";
		}

		switch($learning_filter)
		{
			case 'message':
				$id_course = $_SESSION['message_filter'];

				if($id_course != 0)
					$res = $this->aclManager->getGroupsIdstFromBasePath('/lms/course/'.$id_course.'/subscribed/');
				else
				{
					if($userlevelid !== ADMIN_GROUP_GODADMIN)
					{
						require_once(_lms_.'/lib/lib.course.php');
						$course_man = new Man_Course();
						$all_courses = $course_man->getUserCourses(Docebo::user()->getIdSt());
						$res = array();
						foreach($all_courses as $id_course => $name)
						{
							$arr_idst_group = $this->aclManager->getGroupsIdstFromBasePath('/lms/course/'.$id_course.'/subscribed/');
							$res = array_merge($res, $arr_idst_group);
						}
					}
				}

				$filtered_query .=	($userlevelid !== ADMIN_GROUP_GODADMIN ? " AND u.idst IN ( SELECT idstMember FROM %adm_group_members as gm WHERE gm.idst IN (".implode(",", $res).") ) " : '')
								." AND u.idst <> '".Docebo::user()->getIdSt()."' ";
			break;
			case 'course':
				$id_course = $_SESSION['idCourse'];

				$res = $this->aclManager->getGroupsIdstFromBasePath('/lms/course/'.$id_course.'/subscribed/');

				$filtered_query .= " AND u.idst IN ( SELECT idstMember FROM %adm_group_members as gm WHERE gm.idst IN (".implode(",", $res).") ) ";
			break;
		}
		$res = $this->db->query($filtered_query);
		$row = $this->db->fetch_row($res);
		$output = $row[0];
		return $output;
	}


	public function getAllUsers($idOrg, $descendants = false, $filter = false, $usersFilter = false, $learning_filter = 'none') {
		$useAnonymous = false;
		$searchFilter = isset($filter['text']) ? $filter['text'] : false;
		$useSuspended = isset($filter['suspended']) ? (bool)$filter['suspended'] : true;
		$dynFilter = isset($filter['dyn_filter']) ? $filter['dyn_filter'] : false;

		//list of users idst to apply in main query as a filter
		$admin_info = array( 'users' => array() );
		$usersList = array();

		$acl_man = Docebo::user()->getACLManager();
		$acl_man->include_suspended = TRUE;

		//detect admin level, if requested
		$is_subadmin = false;
		if ($usersFilter) {
			$userlevelid = $this->getUserLevel();
			if( $userlevelid != ADMIN_GROUP_GODADMIN ) {
				//retrieve a list of idsts of the users that the sub-admin can view
				require_once(_base_.'/lib/lib.preference.php');
				$adminManager	= new AdminPreference();
				$admin_info		= $adminManager->getAdminAllSett( Docebo::user()->getIdSt(), 'u.idst' );
				$is_subadmin	= true;
			}
		}

		//if we are a sub admin and we don't have permission to view root folder (without descendants), check it
		if ($is_subadmin && $idOrg == 0) {
			$idst_oc0 = $acl_man->getGroupST('/oc_0');
			if (!$descendants) {
				if (!in_array($idst_oc0, $admin_info['groups'])) {
					$admin_info['groups'] = array($idst_oc0);
					$admin_info['users'] = $admin_info['tree']['users'];
					//return array();
				}
			} else {
				$admin_info['groups'][] = $idst_oc0;
			}
		}

		//apply the dynamic conditional filter, if set. First extract all users idst,
		//then apply a filter in the main query (implementing the complex filter
		//directly in the main query may result impractical due to physical impossibility
		//or extremely poor performance)
		$is_dynfiltered = false;
		if ($dynFilter) {
			require_once(_adm_.'/lib/user_selector/lib.dynamicuserfilter.php');
			$obj_dynfilter = new DynamicUserFilter("user_dyn_filter");
			$usersList = $obj_dynfilter->getUsers($dynFilter);
			$is_dynfiltered = true;
		}

		//built users list's filter, if requested
		$queryUserFilter = "";
		if ($is_subadmin && $is_dynfiltered) {
			$t_arr = array_intersect($usersList, $admin_info['users']);
			if (count($t_arr) <= 0) return array(); //return a 0-length array, because at this point the selection is void and no more query is necessary
			$queryUserFilter .= " AND u.idst IN (".implode(',', $t_arr).") ";
		} elseif ($is_subadmin && !$is_dynfiltered) {
			if (count($admin_info['users']) <= 0) return array(); //return a 0-length array, because at this point the selection is void and no more query is necessary
			$queryUserFilter .= " AND u.idst IN (".implode(',', $admin_info['users']).") ";
		} elseif (!$is_subadmin && $is_dynfiltered) {
			if (count($usersList) <= 0) return array(); //return a 0-length array, because at this point the selection is void and no more query is necessary
			$queryUserFilter .= " AND u.idst IN (".implode(',', $usersList).") ";
		} //else: no filter to apply


		// Contruct user query
		if($idOrg != 0) {
			$id_groups = $this->_getOrgGroups($idOrg, $descendants);
			if ($is_subadmin) $id_groups = array_intersect($id_groups, $admin_info['groups']);
			if (!$id_groups || (is_array($id_groups) && count($id_groups) <= 0)) return array();
		} else {
			if($is_subadmin) {
				$id_groups = $admin_info['groups'];
				if (empty($id_groups)) $id_groups = $this->_getRootGroups();
			} else {
				$id_groups = $this->_getOrgGroups(0, true);
			}
		}

		$query = "SELECT DISTINCT u.idst "
			." FROM %adm_user as u JOIN %adm_group_members as gm ON ( u.idst = gm.idstMember )"
			." WHERE gm.idst IN ( ".implode(",", $id_groups)." ) ".$queryUserFilter
			.($useSuspended ? "" : " AND u.valid = 1 ")." "
			.($useAnonymous ? "" : " AND u.userid <> '/Anonymous' ")." ";
		if ($searchFilter) {
			$query .= " AND (
				u.userid LIKE '%".$searchFilter."%' OR
				u.firstname LIKE '%".$searchFilter."%' OR
				u.lastname LIKE '%".$searchFilter."%' OR
				u.email LIKE '%".$searchFilter."%'
			)";
		}

		switch ($learning_filter) {
			case 'course':
				$id_course = $_SESSION['idCourse'];
				$res = $this->aclManager->getGroupsIdstFromBasePath('/lms/course/'.$id_course.'/subscribed/');
				$query .= " AND u.idst IN ( SELECT idstMember FROM %adm_group_members as gm WHERE gm.idst IN (".implode(",", $res).") ) ";
			break;
		}
                
		// Retrive all the user selected
		$output = array();
		$res = $this->db->query($query);
		while(list($idst) = $this->db->fetch_row($res))
			$output[] = $idst;

		return $output;
	}


	public function getUsersDetails($users, $objs = false, $keys = false) {
		require_once(_adm_.'/lib/lib.field.php');

		if (is_numeric($users)) $users = array((int)$users);
		if (!is_array($users)) return false;

		$fman = new FieldList();
		$field_list = $fman->getFlatAllFields();
		$field_entries = $fman->getUsersFieldEntryData($users, false, true);

		$levels = $this->aclManager->getAdminLevels();
		$levels_idst = array_flip($levels);

		$query =	"SELECT u.*, g.idst AS level"
					." FROM %adm_user AS u"
					." JOIN %adm_group_members AS g ON g.idstMember = u.idst"
					." WHERE u.idst IN (".implode(",", $users).")"
					." AND g.idst IN (".implode(",", $levels).")"
					." ORDER BY u.userid";
		$res = $this->db->query($query);
		if (!$res) return false;

		$output = array();
		$fetch = ($objs ? 'fetch_obj' : 'fetch_assoc');
		while ($row = $this->db->$fetch($res)) {
			$values = array();

			if ($objs)
				$row->level = $levels_idst[$row->level];
			else
				$row['level'] = $levels_idst[$row['level']];

			//retrieve custom fields values
			foreach ($field_list as $id_common=>$translation) {
				$uidst = $objs ? $row->idst : $row['idst'];
				$values[$id_common] = (isset($field_entries[$uidst][$id_common]) ? $field_entries[$uidst][$id_common] : "");
			}

			//set custom field values in the output record
			if ($objs)
				$row->_custom_fields = $values;
			else
				$row['_custom_fields'] = $values;

			//set the record in the output variable (with key association if needed)
			if ($keys)
				$output[$objs ? $row->idst : $row['idst']] = $row;
			else
				$output[] = $row;
		}

		return $output;
	}


	public function getUserLevel($idst = false) {
		$level = ADMIN_GROUP_USER;

		if (is_numeric($idst) && $idst>0) {
			$this->aclManager = Docebo::user()->getAclManager();
			$arr_levels_id = array_flip($this->aclManager->getAdminLevels());
			$arr_levels_idst = array_keys($arr_levels_id);

			$query = "SELECT idst FROM %adm_group_members WHERE idstMember=".(int)$idst." AND idst IN (".implode(",", $arr_levels_idst).")";
			$res = $this->db->query($query);
			if ($res) {
				if ($this->db->num_rows($res)>0) {
					list($idst_level) = $this->db->fetch_row($res);
					$level = $arr_levels_id[$idst_level];
				}
			}
		} else {
			$level = Docebo::user()->getUserLevelId();
		}
		return $level;
	}


	public function getUserId($idst) {
		$info = $this->aclManager->getUser($idst, false);
		return $this->aclManager->relativeId($info[ACL_INFO_USERID]);
	}


	public function getUsersCount() {
		$query = "SELECT COUNT(*) FROM %adm_user WHERE userid <> '/Anonymous'";
		$res = $this->db->query($query);
		list($total) = $this->db->fetch_row($res);
		return $total;
	}

	public function getProfileData($idst) {
		$query = "SELECT * FROM %adm_user as u WHERE u.idst=".(int)$idst;
		$res = $this->db->query($query);
		$output = $this->db->fetch_obj($res);

		return $output;
	}

	public function suspendUsers($users) {
		$query = "UPDATE %adm_user SET valid=0 WHERE ";
		if (is_array($users)) {
			if (count($users) > 0)
				$query .= " idst IN (".implode(",", $users).")";
			else
				return true;
		}
		else {
			$query .= " idst = ".(int)$users;
		}

		return $this->db->query($query) ? true : false;
	}

	public function unsuspendUsers($users) {
		$query = "UPDATE %adm_user SET valid=1 WHERE ";
		if (is_array($users)) {
			if (count($users) > 0)
				$query .= " idst IN (".implode(",", $users).")";
			else
				return true;
		}
		else {
			$query .= " idst = ".(int)$users;
		}

		return $this->db->query($query) ? true : false;
	}


	public function checkUserPassword($idst, $password) {
		$output = false;
		$query = "SELECT pass FROM %adm_user WHERE idst=".(int)$idst;
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res)>0) {
			list($user_pass) = $this->db->fetch_row($res);
			$acl_man = Docebo::user()->getAclManager();
			$check_pass = $acl_man->encrypt($password);
			$output = ($user_pass == $check_pass);
		}
		return $output;
	}



	public function createUser($userdata, $folders = false) {
		require_once(_base_.'/lib/lib.preference.php');
		require_once(_adm_.'/lib/lib.field.php');
		$result = false;

		if (property_exists($userdata, 'userid') && $userdata->userid != '') {
			$acl_man = Docebo::user()->getAclManager();
            if (Get::sett('custom_fields_mandatory_for_admin', 'off') == 'on') {
                $fields = new FieldList();
                $filledFieldsForUser = $fields->isFilledFieldsForUser(0);
                if($filledFieldsForUser !== true) {
                    return Lang::t('_OPERATION_FAILURE', 'standard'). '<br/>' . implode('<br/>', $filledFieldsForUser);
                }
            }
			$user_idst = $acl_man->registerUser(
				$userdata->userid,
				(property_exists($userdata, 'firstname') ? $userdata->firstname : ''),
				(property_exists($userdata, 'lastname') ? $userdata->lastname : ''),
				(property_exists($userdata, 'password') ? $userdata->password : ''),
				(property_exists($userdata, 'email') ? $userdata->email : ''),
				(property_exists($userdata, 'avatarl') ? $userdata->avatar : false),
				(property_exists($userdata, 'signature') ? $userdata->signature : ''),
				false, // $alredy_encripted
				false, // $idst
				'', // $pwd_expire_at
				(property_exists($userdata, 'force_change') ? $userdata->force_change : '' ), // $force_change
				(property_exists($userdata, 'facebook_id') ? $userdata->facebook_id : ''),
				(property_exists($userdata, 'twitter_id') ? $userdata->twitter_id : ''),
				(property_exists($userdata, 'linkedin_id') ? $userdata->linkedin_id : ''),
				(property_exists($userdata, 'google_id') ? $userdata->google_id : '')
			);
			if (is_numeric($user_idst) && $user_idst>0) {
				//add user to root branch
				$oc = $acl_man->getGroupST('oc_0');
				$ocd = $acl_man->getGroupST('ocd_0');

				$acl_man->addToGroup($oc, $user_idst);
				$acl_man->addToGroup($ocd, $user_idst);
/*
				if(isset($_SESSION[$this->sessionPrefix]['selected_node']) && $_SESSION[$this->sessionPrefix]['selected_node'] !== 0)
				{
					$oc_sn = $acl_man->getGroupST('oc_'.(int)$_SESSION[$this->sessionPrefix]['selected_node']);
					$ocd_sn = $acl_man->getGroupST('ocd_'.(int)$_SESSION[$this->sessionPrefix]['selected_node']);

					$acl_man->addToGroup($oc_sn, $user_idst);
					$acl_man->addToGroup($ocd_sn, $user_idst);
				}
*/
				//apply enroll rules
				$langs = Docebo::langManager()->getAllLangCode();
				$lang_code = ( isset($langs[(isset($_POST['user_preference']['ui.language']) ? $_POST['user_preference']['ui.language'] : 'eng')]) ? $langs[$_POST['user_preference']['ui.language']] : false );

				$enrollrules = new EnrollrulesAlms();				
                                
				$folder_count = 0;
				foreach ($folders as $folder) {
					if ((int)$folder > 0) {
						$folder_count++;
                                        }
                                }
				reset($folders);

				if($folder_count == 0) {
					if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
						$subadmin_folder = $this->getAdminFolder(Docebo::user()->getIdst(), true);
						if ($subadmin_folder > 0) {
							if (!is_array($folders)) $folders = array();
							$folders[] = $subadmin_folder;
						}
					}
                                }

				if (is_array($folders) && !empty($folders)) {
					foreach ($folders as $folder) {
						if ((int)$folder > 0) {
							$oc_sn = $acl_man->getGroupST('oc_'.(int)$folder);
							$ocd_sn = $acl_man->getGroupST('ocd_'.(int)$folder);

							$acl_man->addToGroup($oc_sn, $user_idst);
							$acl_man->addToGroup($ocd_sn, $user_idst);
                                                        
                                                        $enrollrules->newRules('_NEW_USER', array($user_idst), $lang_code, $folder);
						}
					}
				}

				//set level, preferences and custom fields
				$level = ADMIN_GROUP_USER;
				if (property_exists($userdata, 'level')) {
					//filter level property to get a valid value
					switch ($userdata->level) {
						case ADMIN_GROUP_ADMIN: $level = ADMIN_GROUP_ADMIN; break;
						case ADMIN_GROUP_GODADMIN: $level = ADMIN_GROUP_GODADMIN; break;
					}
				}

				//subscribe user to level group
				$lev_group = $acl_man->getGroupST($acl_man->relativeId($level));
				$acl_man->addToGroup($lev_group, $user_idst);

				//set preferences
				if (property_exists($userdata, 'preferences')) {
					$preference = new UserPreferences($user_idst);
					$preference->savePreferences($userdata->preferences, 'ui.');
				}

				//set custom fields
				$fields = new FieldList();
				$fields->storeFieldsForUser($user_idst);

                $result = $user_idst;
			}
		}

		return $result;
	}



	public function editUser($idst, $userdata) {
		require_once(_base_.'/lib/lib.preference.php');
		require_once(_adm_.'/lib/lib.field.php');
		$result = 'unable to edit user properties';

		if (is_numeric($idst) && $idst>0) {
			$acl_man = Docebo::user()->getAclManager();
			$res = $acl_man->updateUser(
				$idst,
				(property_exists($userdata, 'userid') ? $userdata->userid : false),
				(property_exists($userdata, 'firstname') ? $userdata->firstname : false),
				(property_exists($userdata, 'lastname') ? $userdata->lastname : false),
				(property_exists($userdata, 'password') ? $userdata->password : false),
				(property_exists($userdata, 'email') ? $userdata->email : false),
				(property_exists($userdata, 'avatarl') ? $userdata->avatar : false),
				(property_exists($userdata, 'signature') ? $userdata->signature : false),
				(property_exists($userdata, 'lastenterl') ? $userdata->lastenter : false),
				FALSE, // $resume
				//$userdata->force_change, //$force_change
				(property_exists($userdata, 'force_change') ? $userdata->force_change : ''),
				(property_exists($userdata, 'facebook_id') ? $userdata->facebook_id : false),
				(property_exists($userdata, 'twitter_id') ? $userdata->twitter_id : false),
				(property_exists($userdata, 'linkedin_id') ? $userdata->linkedin_id : false),
				(property_exists($userdata, 'google_id') ? $userdata->google_id : false)
			);
			if ($res) {

				//set level, preferences and custom fields

				//set level
				if (property_exists($userdata, 'level') && $userdata->level !== false) {
					//filter level property to get a valid value
					switch ($userdata->level) {
						case ADMIN_GROUP_ADMIN: $level = ADMIN_GROUP_ADMIN; break;
						case ADMIN_GROUP_GODADMIN: $level = ADMIN_GROUP_GODADMIN; break;
						default: $level = ADMIN_GROUP_USER; break;
					}
					//remove user to old group
					$old_level = $this->getUserLevel($idst);
					$old_group = $acl_man->getGroupST($old_level);
					$acl_man->removeFromGroup($old_group, $idst);

					//subscribe user to level group
					$lev_group = $acl_man->getGroupST($level);
					$acl_man->addToGroup($lev_group, $idst);
				}

				//set preferences
				if (property_exists($userdata, 'preferences')) {
					$preference = new UserPreferences($idst);
					$preference->savePreferences($userdata->preferences, 'ui.');
				}

				//set custom fields
				$fields = new FieldList();
				$fields->storeFieldsForUser($idst);
                if (Get::sett('custom_fields_mandatory_for_admin', 'off') == 'on') {
                    $result = $fields->isFilledFieldsForUser($idst);
                    if($result !== true) {
                        $result = implode('<br/>', $result);
                    }
                } else {
                    $result = true;
                }
			}
		}

		return $result;
	}

	/**
	 * delete a list of users (specified by their idst) from users table
	 *
	 * @param int|array $users
	 * @return array the list of users effectively deleted
	 */
	public function deleteUsers($users) {
		$output = array();
		if (is_numeric($users) && $users>0) {
			$users = array($users);
		}
		if (is_array($users)) {
			$acl_man = Docebo::user()->getAclManager();
			$current_user = Docebo::user()->getIdSt();
			$i = 0;
			$open_transaction = false;
			foreach ($users as $user) {

				if ($user != $current_user) {

					if($i == 0) {
						$this->db->start_transaction();
						$open_transaction = true;
					}

					if ($acl_man->deleteUser($user)) $output[] = $user;
					$i++;

					if($i == 100) {
						$this->db->commit();
						$open_transaction = false;
						$i = 0;
					}
				}
			}
			if($open_transaction) $this->db->commit();

			//other operations related to users
			$cmodel = new CompetencesAdm();
			$cmodel->removeUsersFromCompetences($users);
		}
		return $output;
	}


	//------------------------------------------------------------------------------


	protected function _getAdminOrgTree($idst = false) {
		$acl_man = Docebo::user()->getAclManager();
		$admin_idst = (is_numeric($idst) && $idst>0 ? $idst : Docebo::user()->getIdST());
		if ($this->orgCache === false || $this->orgUser!=$admin_idst) {
			$this->orgUser = $admin_idst;
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$admtree = $adminManager->getAdminTree($admin_idst);
			$admtree = $acl_man->getGroupsFromMixedIdst($admtree);
			$tmp_admin_tree = array();
			foreach($admtree as $id_group)
				$tmp_admin_tree = array_merge ($tmp_admin_tree, $acl_man->getGroupGDescendants ($id_group));
			$admtree = $tmp_admin_tree;
			//$admtree = $this->aclManager->getGroupsFromMixedIdst($admtree);
			//$admtree = $this->aclManager->getGroupGMembers($admtree);
			$this->orgCache = array();
			$orgtree = array();
			if (count($admtree) > 0) {
				$query = "SELECT idst, groupid FROM %adm_group WHERE idst IN (".implode(',', $admtree).")"; //equal to getGroupsFromMixedIdst
				$res = $this->db->query($query);
				if ($res && $this->db->num_rows($res)>0) {
					while (list($idst, $groupid) = $this->db->fetch_row($res)) {
						$str_id = str_replace('/oc_', '', $groupid);
						$str_id = str_replace('/ocd_', '', $str_id);
						$orgtree[] = (int)$str_id;
					}
					$orgtree = array_unique($orgtree);
				}
				//store left and right values in cache
				if (count($orgtree) > 0) { //this should be always true ...
					$query = "SELECT idOrg, iLeft, iRight FROM %adm_org_chart_tree WHERE idOrg IN (".implode(',', $orgtree).")";
					$res = $this->db->query($query);
					while (list($idorg, $left, $right) = $this->db->fetch_row($res)) {
						$this->orgCache[$idorg] = array($left, $right);
					}
				}
			}
		}
		return array_keys($this->orgCache);
	}

	protected function _checkSubnodesVisibility($id, $left, $right, $org_tree) {
		$output = 0;
		//$this->_setOrgCache();
		foreach ($this->orgCache as $idOrg=>$value) //value => (iLEFT, iRIGHT)
			if ($value[0]>$left && $value[1]<$right) $output++;
		return $output;
	}


	protected function _formatFolderCode($id, $code) {
		if (!$code || $id <= 0) return "";
		return '<span id="orgchart_code_'.(int)$id.'">['.$code.']&nbsp;</span>';
	}


	public function getOrgChartNodes($id_node, $recursive = false, $language = false, $userFilter = false) {
		$is_subadmin = false;
		if ($userFilter) {
			$userlevelid = $this->getUserLevel();
			if( $userlevelid != ADMIN_GROUP_GODADMIN ) {
				$orgTree = $this->_getAdminOrgTree();
				$is_subadmin = true;
			}
		}

		$lang_code = ($language == false ? getLanguage() : $language);
		$search_query = "SELECT	t1.idOrg, t1.path, t2.translation, t1.iLeft, t1.iRight, t1.code
			FROM %adm_org_chart_tree AS t1 LEFT JOIN %adm_org_chart AS t2
				ON (t1.idOrg = t2.id_dir AND t2.lang_code = '".$lang_code."' )
			WHERE t1.idParent = '".(int)$id_node."' ORDER BY t2.translation";
		$re = $this->db->query($search_query);

		$output = array();
		while(list($id, $path, $translation, $left, $right, $code) = $this->db->fetch_row($re)) {
			$is_node_visible = true;

			$code_label = $this->_formatFolderCode($id, $code);
			if ($is_subadmin) {
				$is_forbidden = !in_array($id, $orgTree);
				$count_subnodes = $this->_checkSubnodesVisibility($id, $left, $right, $orgTree);
				$has_visible_subnodes = ($count_subnodes > 0);

				if ($is_forbidden && !$has_visible_subnodes) {

					//forbidden with no visible subnodes:don't show it
					$is_node_visible = false;

				} else {

					if ($is_forbidden) {
						//forbidden, but with visible valid subnodes: show it
						$label = $code_label.$translation;
						$is_leaf = false;
						$count = $count_subnodes;
						$style = 'disabled';
					} else {
						//not forbidden, check as normal
						$label = $code_label.$translation;//end(explode('/', $path));
						$is_leaf = !$has_visible_subnodes;
						$count = $count_subnodes;
						$style = false;
					}

				}
			} else {
				$label = $code_label.$translation;//end(explode('/', $path));
				$is_leaf = ($right-$left) == 1;
				$count = (int)(($right-$left-1)/2);
				$style = false;
			}

			//set node for output
			if ($is_node_visible)
				$output[] = array('id'=>$id,'label'=> $label,'is_leaf'=>$is_leaf, 'count_content' => $count, 'style' => $style);
		}

		return array_values($output);
	}


	public function getOrgChartInitialNodes($idOrg, $userFilter = false) {
		$results = array();

		$is_subadmin = false;
		if ($userFilter) {
			$userlevelid = $this->getUserLevel();
			if( $userlevelid != ADMIN_GROUP_GODADMIN ) {
                            //if (!checkPerm('mod_org', true, 'usermanagement')){
				$orgTree = $this->_getAdminOrgTree();
				$is_subadmin = true;
                            //}
			}
		}

		$folders = $this->getOpenedFolders($idOrg);
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

			$children = $this->getSubFolders($folder, false, false);
			for ($i=0; $i<count($children); $i++) {
				$is_node_visible = true;
				list($id_org, $id_parent, $path, $lev, $left, $right, $translation, $code) = $children[$i];

				$code_label = $this->_formatFolderCode($id_org, $code);
				if ($is_subadmin) {
					$is_forbidden = !in_array($id_org, $orgTree);
					$count_subnodes = $this->_checkSubnodesVisibility($id_org, $left, $right, $orgTree);
					$has_visible_subnodes = ($count_subnodes > 0);

					if ($is_forbidden && !$has_visible_subnodes) {

						//forbidden with no visible subnodes:don't show it
						$is_node_visible = false;

					} else {

						if ($is_forbidden) {
							//forbidden, but with visible valid subnodes: show it
							$label = $code_label.$translation;
							$is_leaf = false;
							$count = $count_subnodes;
							$node_options = array();
							$style = 'disabled';
						} else {
							//not forbidden, check as normal
							$label = $code_label.$translation;//end(explode('/', $path));
							$is_leaf = !$has_visible_subnodes;
							$count_subnodes = $count_subnodes;
							$node_options = array();
							$style = false;
						}

					}

				} else {

					$is_leaf = ($right-$left) == 1;
					$label = $code_label.$translation;
					$node_options = array();//getNodeOptions($id_org, $is_leaf);
					$count_subnodes = (int)(($right-$left-1)/2);
					$style = false;

				}

				if ($is_node_visible)
					$ref[] = array(
						'node' => array(
							'id' => $id_org,
							'label' => $label,
							'is_leaf' => $is_leaf,
							'count_content' => $count_subnodes,
							'options' => $node_options,
							'style' => $style
						)
					);
			}
		}

		return $results;
	}


	public function getOrgchartIdstConversionTable() {
		$_arr_groupid = array('/oc_0', '/ocd_');

		$query = "SELECT idOrg FROM %adm_org_chart_tree";
		$res = $this->db->query($query);
		while (list($idOrg) = $this->db->fetch_row($res)) {
			$_arr_groupid[] = '/oc_'.$idOrg;
			$_arr_groupid[] = '/ocd_'.$idOrg;
		}

		$output = array(
			array(), //single groups (/oc_*)
			array()  //descendants (/ocd_*)
		);
		$query = "SELECT * FROM %adm_group WHERE groupid IN ('".implode("','", $_arr_groupid)."')";
		$res = $this->db->query($query);
		while ($obj = $this->db->fetch_obj($res)) {
			if (strpos($obj->groupid, 'oc_') !== false) {
				$idOrg = str_replace('/oc_', '', $obj->groupid);
				$output[0][$idOrg] = $obj->idst;
			} else {
				$idOrg = str_replace('/ocd_', '', $obj->groupid);
				$output[1][$idOrg] = $obj->idst;
			}
		}

		return $output;
	}



	/*
	 * returns iLeft and iRight of a node
	 */
	public function getFolderLimits($idOrg) {
		if ($idOrg <= 0) {
			$query = "SELECT MIN(iLeft), MAX(iRight) FROM %adm_org_chart_tree";
			$res = $this->db->query($query);
			$row = $this->db->fetch_row($res);
			if (is_array($row)) {
				$row[0]--;
				$row[1]++;
			}
		} else {
			$query = "SELECT iLeft, iRight, lev FROM %adm_org_chart_tree WHERE idOrg=".(int)$idOrg;
			$res = $this->db->query($query);
			$row = $this->db->fetch_row($res);
		}
		return $row;
	}

	/*
	 * returns an ordered list of ids (like a path)
	 */
	public function getOpenedFolders($idOrg) {
		$folders = array(0);
		if ($idOrg <= 0) return $folders;
		list($ileft, $iright) = $this->getFolderLimits($idOrg);
		$query = "SELECT idOrg FROM %adm_org_chart_tree WHERE iLeft<=".(int)$ileft." AND iRight>=".(int)$iright." AND idOrg>0 ORDER BY iLeft";
		$res = $this->db->query($query);
		if ($res) {
			while (list($id_org) = $this->db->fetch_row($res)) { $folders[] = (int)$id_org; }
			return  $folders;
		} else
			return false;
	}

	/**
	 * Return the idorg, idst of oc and idst of ocd for the idOrg specified including itself
	 * @param int $idOrg a valid idOrg
	 * @return array an array with separated ( id_org => array idst_oc => array idst_ocd => array )
	 */
	public function getAncestorInfoFolders($idOrg) {

		$folders = array(
			'id_org' => array(),
			'id_oc' => array(),
			'id_ocd' => array(),
		);
		if ($idOrg <= 0) return $folders;
		list($ileft, $iright) = $this->getFolderLimits($idOrg);
		$query = "SELECT idOrg, idst_oc, idst_ocd FROM %adm_org_chart_tree WHERE iLeft<=".(int)$ileft." AND iRight>=".(int)$iright." AND idOrg>0 ORDER BY iLeft";
		$res = $this->db->query($query);
		if ($res) {
			while (list($id_org, $id_oc, $id_ocd) = $this->db->fetch_row($res)) {
				$folders['id_org'][] = (int)$id_org;
				$folders['idst_oc'][] = (int)$id_oc;
				$folders['idst_ocd'][] = (int)$id_ocd;
			}
			return  $folders;
		} else
			return false;
	}


	/**
	 * Returns oc and ocd of a given folder
	 * @param  int $idOrg
	 * @return array
	 */
	public function getFolderGroups($idOrg) {
		$res =array();
		$query = "SELECT idst_oc, idst_ocd FROM %adm_org_chart_tree
			WHERE idOrg = '".(int)$idOrg."'";

		$q = $this->db->query($query);

		if ($q) {
			$row =$this->db->fetch_assoc($q);
			$res[]=$row['idst_oc'];
			$res[]=$row['idst_ocd'];
		}

		return $res;
	}


	/**
	 * Return the idorg, name, code, idst oc and idst ocd for all the folders in the org_chart
	 * @return array of object
	 */
	public function getAllFolders($use_as_key = false, $descendantof = false) {

		$folders = array();
		switch($use_as_key) {
			case 'code' : $use_as_key = 'code';break;
			case 'translation' : $use_as_key = 'translation';break;
			case 'both' : $use_as_key = 'both';break;
			default: $use_as_key = 'idOrg';break;
		}
		if($descendantof) {

			$result = $this->db->query("SELECT iLeft, iRight "
				."FROM %adm_org_chart_tree "
				."WHERE idOrg = ".(int)$descendantof);
			list($iLeft, $iRight) = $this->db->fetch_row($result);
		}

		$result = $this->db->query("SELECT ot.idOrg, ot.code, oc.translation, ot.idst_oc, ot.idst_ocd "
			."FROM %adm_org_chart_tree AS ot "
			."LEFT JOIN %adm_org_chart AS oc ON (ot.idOrg = oc.id_dir) "
			."WHERE oc.lang_code = '".Lang::get()."'"
			.( $descendantof ? " AND iLeft >= ".$iLeft." AND iRight <= ".$iRight." " : "" ) );
		while($folder = $this->db->fetch_obj($result)) {

			if($use_as_key != 'both') $folders[trim(strtolower($folder->$use_as_key))] = $folder;
			else {
				$folders[trim(strtolower($folder->translation))] = $folder;
				if($folder->code != '') $folders[trim(strtolower($folder->code))] = $folder;
			}
		}
		return $folders;
	}

	/**
	 * Return the idorg, idst of oc and idst of ocd for the idst from oc e ocd specified
	 * @param int $idOrg a valid idOrg
	 * @return array an array with separated ( id_org => array idst_oc => array idst_ocd => array )
	 */
	public function getInfoFolders($id_groups, $language = false) {

		$folders = array(
			'id_org' => array(),
			'idst' => array()
		);

		$groups = [];
		foreach ($id_groups as $id_group) {
			if ($id_group) {
				$groups[] = $id_group;
			}
		}

		if($language == false) $language = getLanguage();
		if(!is_array($id_groups) || empty($id_groups)) return $folders;
		$result = $this->db->query("SELECT ot.idOrg, oc.translation, ot.idst_oc, ot.idst_ocd "
			."FROM %adm_org_chart_tree AS ot JOIN %adm_org_chart AS oc ON (ot.idOrg = oc.id_dir)"
			." WHERE oc.lang_code = '".$language."' AND ( "
				." idst_oc IN (".implode(',', $groups).") "
				." OR idst_ocd IN (".implode(',', $groups).") "
			.")");

		while(list($id_dir, $dir_name, $oc, $ocd) = $this->db->fetch_row($result)) {

			$folders['id_org'][$id_dir] = $dir_name;
			$folders['idst'][$oc] = $dir_name;
			$folders['idst'][$ocd] = $dir_name.' <i>('.Lang::t('_INHERIT', 'standard').')</i>';
		}
		return $folders;
	}

	/*
	 * return a list of subfolders given a node id (idOrg)
	 */
	public function getSubFolders($idOrg, $language = false, $userFilter = false) {
		$query_filter = "";
		if (is_array($userFilter)) {
			if (count($userFilter) > 0) {
				$query_filter .= " AND t1.idOrg IN (".implode(',', $userFilter).")";
			} else
				return array();
		}

		$lang_code = ($language == false ? getLanguage() : $language);
		$search_query = "SELECT	t1.idOrg, t1.idParent, t1.path, t1.lev, t1.iLeft, t1.iRight, t2.translation, t1.code
			FROM %adm_org_chart_tree AS t1 LEFT JOIN	%adm_org_chart AS t2
			ON (t1.idOrg = t2.id_dir AND t2.lang_code = '".$lang_code."' )
			WHERE t1.idParent = '".(int)$idOrg."' ".$query_filter." ORDER BY t2.translation";
		$re = $this->db->query($search_query);

		$output = array();
		while(list($id, $parent, $path, $level, $ileft, $iright, $translation, $code) = $this->db->fetch_row($re)) {
			$output[] = array(
				$id,
				$parent,
				$path,
				$level,
				$ileft,
				$iright,
				$translation,
				$code
			);
		}

		return $output;
	}

	public function getOcFolders($org_list) {

		$search_query = "SELECT	idOrg, idst_oc, idst_ocd
			FROM %adm_org_chart_tree
			WHERE idOrg IN (".implode(',', $org_list).") ";
		$re = $this->db->query($search_query);

		$output = array();
		while(list($id, $oc, $ocd) = $this->db->fetch_row($re)) {

			$output[$id] = array($oc, $ocd);
		}
		return $output;
	}

	/*
	 * get folder's properties by ID
	 */
	public function getFolderById($idOrg, $array = false) {
		if ($idOrg <= 0) { //root node, not present in DB, but it's "virtual"
			list($left, $right) = $this->getFolderLimits(0);
			if ($array) {
				return array(
					'idOrg' => 0,
					'idParent' => 0,
					'path' => '/root/',
					'lev' => 0,
					'iLeft' => $left,
					'iRight' => $right
				);
			} else {
				$obj = new stdClass();
				$obj->idOrg = 0;
				$obj->idParent = 0; //or NULL ?
				$obj->path = "/root/";
				$obj->lev = 0;
				$obj->iLeft = $left;
				$obj->iRight = $right;
				return $obj;
			}
			return false;
		}
		$query = "SELECT * FROM %adm_org_chart_tree WHERE idOrg=".(int)$idOrg." LIMIT 1";
		$res = $this->db->query($query);
		if ($res) {
			if ($array)
				return $this->db->fetch_assoc($res);
			else
				return $this->db->fetch_obj($res);
		} else
			return false;
	}

	/*
	 * returns the code of a org branch
	 */
	public function getFolderCode($idOrg) {
		$query = "SELECT code FROM %adm_org_chart_tree WHERE idOrg=".(int)$idOrg;
		$res = $this->db->query($query);
		$output = false;
		if ($res && $this->db->num_rows($res)>0)
			list($output) = $this->db->fetch_row($res);
		return $output;
	}

	/*
	 * returns the code of a org branch
	 */
	public function getAllFolderNames($only_with_code=false) {

		$tree_codes = array();
		$query = "SELECT t1.idOrg, t1.code, t2.translation "
			."FROM %adm_org_chart_tree as t1 JOIN %adm_org_chart as t2 "
			."ON ( t1.idOrg = t2.id_dir ) "
			."WHERE t2.lang_code = '".Lang::get()."' "
			.($only_with_code ? " AND t1.code != '' " : '')
			."ORDER BY t2.translation";
		if(!$res = $this->db->query($query)) return $tree_codes;
		while(list($id, $code, $name) = $this->db->fetch_row($res)) {

			$tree_codes[$id] = $name;
		}
		return $tree_codes;
	}

	public function getAllFolderCode() {

		$tree_codes = array();
		$query = "SELECT idOrg, code FROM %adm_org_chart_tree WHERE 1";
		if(!$res = $this->db->query($query)) return $tree_codes;
		while(list($id, $code) = $this->db->fetch_row($res)) {

			$tree_codes[$id] = $code;
		}
		return $tree_codes;
	}

	/*
	 * returns the code of a org branch
	 */
	public function getFoldersFromCode($code) {

		$tree_folders = array();
		$query = "SELECT idOrg FROM %adm_org_chart_tree WHERE code = '".$code."' ";
		if(!$res = $this->db->query($query)) return $tree_folders;
		while(list($id) = $this->db->fetch_row($res)) {

			$tree_folders[$id] = $id;
		}
		return $tree_folders;
	}
	/*
	 * returns a set of folder name translations indexed by lang code
	 */
	public function getFolderTranslations($idOrg, $array = false) {
		if ($idOrg == 0) {
			$translation = Get::sett('title_organigram_chart', Lang::t('_ORG_CHART', ''));
		}

		$query = "SELECT * FROM %adm_org_chart WHERE id_dir=".(int)$idOrg;
		$res = $this->db->query($query);
		$output = ($array ? array() : new stdClass());
		while ($row = $this->db->fetch_obj($res)) {
			if ($array)
				$output[$row->lang_code] = $row->translation;
			else {
				$lang_code = $row->lang_code;
				$output->$lang_code = $row->translation;
			}
		}
		return $output;
	}


	/*
	 * returns a set of folder name translations indexed by lang code
	 */
	public function getFolderTranslation($idOrg, $lang_code = false) {
		if (!$lang_code) $lang_code = getLanguage();
		$query = "SELECT translation FROM %adm_org_chart WHERE id_dir=".(int)$idOrg." AND lang_code='".$lang_code."'";
		$res = $this->db->query($query);
		$output = false;
		if ($res && ($this->db->num_rows($res)>0))
			list($output) = $this->db->fetch_row($res);
		return $output;
	}


	/*
	 * add a node the the org chart tree
	 */
	public function addFolder($id_parent, $langs, $code = '') {
		$output = false;

		if (is_array($langs)) {
			//get directory's number and attach new folder at the end of the list
			$query = "SELECT MAX(path) FROM %adm_org_chart_tree WHERE idParent=".(int)$id_parent;
			$res = $this->db->query($query);
			if ($this->db->num_rows($res) > 0) { //check if there are any subfolder
				list($path) = $this->db->fetch_row($res);
				$folder_index = ((int)end(explode("/", $path)) + 1); //get next index
			} else {
				$folder_index = 1; //start with first folder index
			}

			//calculate new folder parameters in org_chart_tree table
			$parent = $this->getFolderById($id_parent);
			$path = addslashes($parent->path). "/" .str_pad($folder_index, 8, "0", STR_PAD_LEFT);
			$path = str_replace('//', '/', $path);
			$level = $parent->lev + 1;

			//$this->db->query("START TRANSACTION");
			$new_limits = array('iLeft' => $parent->iRight, 'iRight' => $parent->iRight);

			//updating left limits
			$query = "UPDATE %adm_org_chart_tree SET iRight=iRight+2 WHERE iRight>=".$new_limits['iRight'];
			$rsl = $this->db->query($query);
			//TO DO: handle error case (if !$rs ... )

			//updating right limits
			$query = "UPDATE %adm_org_chart_tree SET iLeft=iLeft+2 WHERE iLeft>=".$new_limits['iLeft'];
			$rsr = $this->db->query($query);
			//TO DO: handle error case (if !$rs ... )

			//insert node in the table, with newly calculated iLeft and iRight
			$query = "INSERT into %adm_org_chart_tree (idOrg, idParent, path, lev, iLeft, iRight, code) VALUES "
				."(NULL, '".(int)$id_parent."', '". $path. "', '". (int)$level ."', ".(int)$new_limits['iLeft'].", ".((int)$new_limits['iRight'] + 1).", '".$code."')";
			$res = $this->db->query($query);
			$id = $this->db->insert_id();

			//if node has been correctly inserted then ...
			if ($id) {

				//create group and descendants
				$acl =& Docebo::user()->getACLManager();
				$idst_oc = $acl->registerGroup('/oc_'.(int)$id, '', true);
				$idst_ocd = $acl->registerGroup('/ocd_'.(int)$id, '', true);
				$acl->addToGroup($acl->getGroupST('ocd_'.(int)$id_parent), $idst_ocd); //register the idst of the new branch's descendants into the parent node /ocd_
				$acl->addToGroup($idst_ocd, $idst_oc);

				//if the creator is a sub admin, make the folder visible for himself
				$userlevelid = $this->getUserLevel();
				if( $userlevelid != ADMIN_GROUP_GODADMIN ) {
					require_once(_base_.'/lib/lib.preference.php');
					$adminManager = new AdminPreference();
					$adminManager->addAdminTree($idst_oc, Docebo::user()->getIdST());
				}
                                
				// update the node inserted with the oc and ocd founded
				$query = "UPDATE %adm_org_chart_tree "
				."SET idst_oc = ".(int)$idst_oc.", "
				."	idst_ocd = ".(int)$idst_ocd." "
				."WHERE idOrg = ".(int)$id." ";
				$res = $this->db->query($query);

				//insert translations in database
				$conditions = array();
				foreach ($langs as $lang_code => $translation) { //TO DO: check if lang_code exists ...
					$conditions[] = "(".(int)$id.", '".$lang_code."', '".$translation."')";
				}
				$query = "INSERT INTO %adm_org_chart (id_dir, lang_code, translation) VALUES ".implode(",", $conditions);
				$res = $this->db->query($query);
				if ($res) $output = $id;
			} else
				$output = false;
		}

		return $output;
	}

	/*
	 * delete a folder from the orgchart tree
	 */
	public function deleteFolder($idOrg, $onlyLeaf = false) {
		$acl =& Docebo::user()->getACLManager();
		$folder = $this->getFolderById($idOrg);

		if (!$folder) return false;
		if ($idOrg <= 0) return false;

		list($left, $right) = $this->getFolderLimits($idOrg);
		$limits = array('iLeft'=>$left, 'iRight'=>$right);
		if ($onlyLeaf) {
			if ( ((int)$limits['iRight'] - (int)$limits['iLeft']) > 1 ) return FALSE;
		}

		$query = "SELECT idOrg FROM %adm_org_chart_tree WHERE iLeft>=".$limits['iLeft']." AND iRight<=" .$limits['iRight'];
		$res = $this->db->query($query);
		$nodes = array();
		while (list($node) = $this->db->fetch_row($res)) $nodes[] = $node;

		$query = "DELETE FROM %adm_org_chart_tree WHERE iLeft>=".$limits['iLeft']." AND iRight<=" .$limits['iRight'];
		$res = $this->db->query($query);
		$shift = $limits['iRight'] - $limits['iLeft'] + 1; //or -1 ??

		$query = "UPDATE %adm_org_chart_tree SET iLeft=iLeft-".$shift." WHERE iLeft>=".$limits['iLeft'];
		$res = $this->db->query( $query );
		$query = "UPDATE %adm_org_chart_tree SET iRight=iRight-".$shift." WHERE iRight>=".$limits['iRight'];
		$res = $this->db->query( $query );
		//handle error ....
		//...
        
        // deleting custom fields
        $this->deleteInfoCustomOrg($idOrg);

		$query = "DELETE FROM %adm_org_chart WHERE id_dir IN (".implode(",", $nodes).")";
		$res = $this->db->query($query);
		if ($res) {
			foreach ($nodes as $node) {
				$res = $acl->deleteGroup($acl->getGroupST('/oc_'.(int)$node));
				$res = $acl->deleteGroup($acl->getGroupST('/ocd_'.(int)$node));
			}
			//if ($res) return true; else return false;
			return true;
		} else
			return false;
	}

    // deleting custom fields
    private function deleteInfoCustomOrg($idOrg){
        $query = "delete from %adm_customfield_entry where id_obj=".$idOrg;
        sql_query($query);
    }

	public function moveFolder($src_folder, $dest_folder) {
		if ($src_folder <= 0) return false;
		if ($dest_folder < 0) return false;
		$output = false;

		$query =	"SELECT idParent"
					." FROM %adm_org_chart_tree"
					." WHERE idOrg = '".$src_folder."'";

		list($id_parent) = sql_fetch_row(sql_query($query));

		//todo: back compatibility
		/*
		$folder->idParent = $parentFolder->id;
		$folder->path = (($parentFolder->id == 0)?'/root/':($parentFolder->path . "/"))
						.(($newfoldername!==FALSE)?$newfoldername:$oldFolder->getFolderName());

		$folder->level = $parentFolder->level+1;
		$query = "UPDATE ". $this->table
				." SET "
				. $this->fields['idParent'] ." = '".$folder->idParent ."',"
				. $this->fields['path']	." = '". $folder->path ."',"
				. $this->fields['lev'] ." = '".$folder->level ."'"
				." WHERE (". $this->fields['id'] ." = '". $folder->id ."')"
				.$this->_getFilter();
		$this->_executeQuery( $query )
			or $this->_printSQLError( 'moveFolder' );
		$this->_propagateChange( $oldFolder, $folder);
		*/

		list($src_left, $src_right, $lvl_src) = $this->getFolderLimits($src_folder);
		list($dest_left, $dest_right, $lvl_dest) = $this->getFolderLimits($dest_folder);

		//dest folder is a son of the src ?
		if($src_left < $dest_left && $src_right > $dest_right) return $output;
		$output = true;

		$query =	"SELECT path"
					." FROM %adm_org_chart_tree"
					." WHERE idParent = ".(int)$dest_folder
					." ORDER BY path DESC"
					." LIMIT 0, 1";

		list($path_max_new_folder) = sql_fetch_row(sql_query($query));

		$dest_left = $dest_left + 1;
		$gap = $src_right - $src_left + 1;

		$this->shiftRL($dest_left, $gap);
		if ($src_left >= $dest_left) {
			// this happen when the src has shiften too
			$src_left += $gap;
			$src_right += $gap;
		}

		// update parent of source and level for descendants
		$lvl_gap = $lvl_dest - $lvl_src + 1;
		$query1 = "UPDATE %adm_org_chart_tree SET idParent = ".(int)$dest_folder." WHERE idOrg = ".(int)$src_folder;
		$query2 = "UPDATE %adm_org_chart_tree SET lev = lev + ".$lvl_gap." WHERE iLeft > ".$src_left." AND iRight < ".$src_right;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);

		//Update path
		$query =	"SELECT path"
					." FROM %adm_org_chart_tree"
					." WHERE idOrg = ".(int)$src_folder;

		list($src_path) = sql_fetch_row(sql_query($query));

		$query =	"SELECT path"
					." FROM %adm_org_chart_tree"
					." WHERE idOrg = ".(int)$dest_folder;

		list($dest_path) = sql_fetch_row(sql_query($query));

		$path_max = (int)str_replace($dest_path.'/', '', $path_max_new_folder);
		$path_max++;

		$new_path = $dest_path.'/'.sprintf('%08s', $path_max);

		$query =	"UPDATE %adm_org_chart_tree"
					." SET path = REPLACE(path, '".$src_path."', '".$new_path."')"
					." WHERE path LIKE '".$src_path."%'";

		sql_query($query);

		// move the subtree
		$this->shiftRLSpecific($src_left, $src_right, $dest_left - $src_left);

		// fix values from the gap created
		$this->shiftRL($src_right + 1, -$gap);

/*
		//update path column for source
		list($parent_path) = $this->db->fetch_row($this->db->query("SELECT path FROM %adm_org_chart_tree WHERE idOrg=".(int)$dest_folder));
		list($source_endpath) = $this->db->fetch_row($this->db->query("SELECT MAX(path) FROM %adm_org_chart_tree WHERE idParent=".(int)$dest_folder." GROUP BY idParent"));
		$_last_index = !$source_endpath ? 0 : (int)end(explode("/", $source_endpath));
		$source_name = str_pad("".$_last_index, 8, '0');
		list($old_src_path) = $this->db->query("SELECT path FROM %adm_org_chart_tree WHERE idOrg=".(int)$src_folder);
		$res = $this->db->query("UPDATE %adm_org_chart_tree SET path='".$parent_path.'/'.$source_name."' WHERE idOrg=".(int)$src_folder);
		$res = $this->db->query("UPDATE %adm_org_chart_tree SET path = REPLACE(path,'".$old_src_path."', '".$parent_path."')");
*/
		//if folder moving has been successfull than set new /ocd_ groups
		if ($output) {
			$query =	"SELECT idst"
						." FROM %adm_group"
						." WHERE groupid = '/ocd_".$src_folder."'";

			list($ocd_src) = sql_fetch_row(sql_query($query));

			$query =	"SELECT idst"
						." FROM %adm_group"
						." WHERE groupid = '/ocd_".$dest_folder."'";

			list($ocs_dest) = sql_fetch_row(sql_query($query));

			$query =	"SELECT idst"
						." FROM %adm_group"
						." WHERE groupid = '/ocd_".$id_parent."'";

			list($ocd_parent) = sql_fetch_row(sql_query($query));

			//Update groups
			$query =	"DELETE FROM %adm_group_members"
						." WHERE idst = '".$ocd_parent."'"
						." AND idstMember = '".$ocd_src."'";

			sql_query($query);

			$query =	"INSERT INTO %adm_group_members (idst, idstMember)"
						." VALUES ('".$ocs_dest."', '".$ocd_src."')";

			$output = sql_query($query);
		}

		return $output;
	}

	public function shiftRL($from, $shift) {

		$q[] = $query1 = "UPDATE %adm_org_chart_tree SET iLeft = iLeft + ".$shift." WHERE iLeft >= ".$from;
		$q[] = $query2 = "UPDATE %adm_org_chart_tree SET iRight = iRight + ".$shift." WHERE iRight >= ".$from;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);
	}

	public function shiftRLSpecific($from, $to, $shift) {

		$q[] = $query1 = "UPDATE %adm_org_chart_tree SET iLeft = iLeft + ".$shift." WHERE iLeft >= ".$from." AND iRight <= ".$to;
		$q[] = $query2 = "UPDATE %adm_org_chart_tree SET iRight = iRight + ".$shift." WHERE iRight >= ".$from." AND iRight <= ".$to;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);
	}

	/*
	 * modify the name of a folde
	 */
	public function renameFolder($idOrg, $langs) {
		if ($idOrg <= 0) return false;
		$output = false;
		if (is_array($langs)) {
			// retrive previous saved languages
			$prev_lang = array();
			$re = $this->db->query("SELECT lang_code FROM %adm_org_chart WHERE id_dir = ".(int)$idOrg);
			while(list($lang_code) = $this->db->fetch_row($re)) {
				$prev_lang[$lang_code] = $lang_code;
			}
			//update translations in database
			foreach ($langs as $lang_code => $translation) {

				if(isset($prev_lang[$lang_code])) {
					$query = "UPDATE %adm_org_chart SET translation = '".$translation."' "
						."WHERE lang_code='".$lang_code."' AND id_dir=".(int)$idOrg;
					$res = $this->db->query($query);
				} else {
					$query = "INSERT INTO %adm_org_chart (id_dir, lang_code, translation) VALUES "
						."(".(int)$idOrg.", '".$lang_code."', '".$translation."')";
					$res = $this->db->query($query);
				}
			}
			$output = true;
		}
		return $output;
	}


	public function modFolderCode($idOrg, $code) {
		if ($idOrg <= 0) return false;
		$query = "UPDATE %adm_org_chart_tree SET code = '".trim($code)."' WHERE idOrg=".(int)$idOrg;
		return $this->db->query($query) ? true : false;
	}


	public function modFolderCodeAndTemplate($idOrg, $code, $template) {
		if ($idOrg <= 0) return false;

		if ($template == getDefaultTemplate()) {
			$template =''; // set the value to NULL if no custom template selected
		}

		$query = "UPDATE %adm_org_chart_tree SET
			code = '".trim($code)."',
			associated_template = ".(!empty($template) ? "'".$template."'" : "NULL")."
			WHERE idOrg=".(int)$idOrg;
		return $this->db->query($query) ? true : false;
	}


	public function renameRootFolder($name) {
		$output = false;
		if (is_string($name) && $name != "") {
			$query = "UPDATE %adm_setting SET param_value='".$name."' WHERE param_name='title_organigram_chart'";
			$res = $this->db->query($query);
			$output = ($res ? true : false);
		}
		return $output;
	}



	/*
	 * get all users of a branch (optional descendants)
	 */
	public function getFolderUsers($idOrg, $descendants = false) {
		$output = false;
		$acl =& Docebo::user()->getACLManager();
		$groupidst = $acl->getGroupSt('/oc_'.(int)$idOrg);
		$query = "SELECT idstMember FROM %adm_group_members as gm JOIN %adm_user as u "
			." ON (gm.idstMember=u.idst) WHERE gm.idst=".(int)$groupidst." ";
		if ($descendants) {
			$groupidst_d = $acl->getGroupSt('/ocd_'.(int)$idOrg);
			$query .= " OR gm.idst=".(int)$groupidst_d." ";
		}
		$res = $this->db->query($query);

		if ($res) {
			$output = array();
			while (list($user) = $this->db->fetch_row($res)) {
				$output[] = $user;
			}
		}

		return $output;
	}

	/*
	 * assign users to a branch of the orgchart
	 */
	public function assignUsers($idOrg, $users) {
		$acl =& Docebo::user()->getACLManager();
		$acl->include_suspended = TRUE;
		$groupidst = $acl->getGroupSt('/oc_'.(int)$idOrg); //get group idst from group table
		$groupdesc = $acl->getGroupSt('/ocd_'.(int)$idOrg); //get descendants' group idst from group table
		$old_users = $acl->getGroupUMembers($groupidst); //users already assigned to the group
		$users = $acl->getUsersFromMixedIdst($users);
		$common = array_intersect($users, $old_users); //search common users by intersection
		$users_to_add = array_diff($users, $common); //users to add
		$users_to_rem = array_diff($old_users, $common); //users to remove
		if (count($users_to_add)>0) {
			$acl->addToGroup($groupidst, $users_to_add);
			$acl->addToGroup($groupdesc, $users_to_add);
		}
		if (count($users_to_rem)>0) {
			$acl->removeFromGroup($groupidst, $users_to_rem);
			$acl->removeFromGroup($groupdesc, $users_to_rem);
		}
		return true;
	}


	public function searchUsersByUserid($query, $limit = false, $filter = false) {
		if ((int)$limit <= 0) $limit = Get::sett('visuItem', 25);
		$output = array();

		$_qfilter = "";
		if ($filter) {
			$ulevel = Docebo::user()->getUserLevelId();
			if ($ulevel != ADMIN_GROUP_GODADMIN) {
				require_once(_base_.'/lib/lib.preference.php');
				$adminManager = new AdminPreference();
				//$admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
				$admin_users = $adminManager->getAdminUsers(Docebo::user()->getIdST());//$this->aclManager->getAllUsersFromIdst($admin_tree);
				$_qfilter .= " AND idst IN (".implode(",", $admin_users).") ";
			}
		}

		$query = "SELECT idst, userid, firstname, lastname, email FROM %adm_user "
			." WHERE userid LIKE '%".$query."%' ".$_qfilter." ORDER BY userid "
			.((int)$limit>0 ? " LIMIT 0, ".(int)$limit : "");
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = $obj;
			}
		}
		return $output;
	}


        public function randomPassword($idst) {
				$acl_manager =& Docebo::user()->getAclManager();
				$new_password = $this->aclManager->random_password();
				$userid = $acl_manager->getUserid($idst, false);
                if($this->changePassword($idst, $new_password)){
                    $array_subst = array(
                            '[url]' => Get::site_url(),
                            '[userid]' => $userid,
                            '[password]' => $new_password
                    );
                    require_once(_base_.'/lib/lib.eventmanager.php');
                    $e_msg = new EventMessageComposer();

                    $e_msg->setSubjectLangText('email', '_MODIFIED_USER_SBJ', false);
					$e_msg->setBodyLangText('email', '_MODIFIED_USER_TEXT', $array_subst );
                    $e_msg->setBodyLangText('email', '_PASSWORD_CHANGED', $array_subst );

                    $recipients = array($idst);
                    createNewAlert('UserMod', 'directory', 'edit', '1', 'New user created', $recipients, $e_msg, true );
                    
                    return true;
                }
                else return false;
        }
        
	public function changePassword($idst, $new_password, $force_changepwd = 0) {
		if ($idst <= 0 || $new_password == "") return false;
		$query = "UPDATE %adm_user SET pass='".$this->aclManager->encrypt($new_password)."', force_change = '".(int)$force_changepwd."' WHERE idst=".(int)$idst;
		$res = $this->db->query($query);
		return $res ? true : false; //TO DO: check affected rows
	}


	public function checkUserRequestedFields($user_idst = false) {
		require_once(_adm_.'/lib/lib.field.php');
		$fl = new FieldList();
		return $fl->checkUserMandatoryFields((int)$user_idst>0 ? (int)$user_idst : Docebo::user()->getIdSt());
	}



	public function confirmWaitingUsers($arr_idst) {
		require_once(_base_.'/lib/lib.preference.php');
		require_once(_base_.'/lib/lib.platform.php');
		require_once(_base_.'/lib/lib.eventmanager.php');

		if (is_numeric($arr_idst)) $arr_idst = array($arr_idst); //handle single user case
		if (!is_array($arr_idst)) return false; //invalid user data
		if (count($arr_idst) <= 0) return true; //0 users operation: always "successfull"

		$idst_usergroup = $this->aclManager->getGroupST($this->aclManager->relativeId(ADMIN_GROUP_USER));
		$idst_oc = $this->aclManager->getGroupST('oc_0');
		$idst_ocd = $this->aclManager->getGroupST('ocd_0');
		$uinfo = $this->aclManager->getTempUsers($arr_idst, false);
		$approved = array();

		while (list(,$idst) = each($arr_idst)) {
			$res = $this->aclManager->registerUser(
				addslashes($uinfo[$idst]['userid']),
				addslashes($uinfo[$idst]['firstname']),
				addslashes($uinfo[$idst]['lastname']),
				$uinfo[$idst]['pass'],
				addslashes($uinfo[$idst]['email']),
				'',
				'',
				true,
				$idst
			);
			if ($res) {
				$approved[] = $idst;
				$this->aclManager->addToGroup($idst_usergroup, $idst);
				$this->aclManager->addToGroup($idst_oc, $idst);
				$this->aclManager->addToGroup($idst_ocd, $idst);
				if($uinfo[$idst]['create_by_admin'] != 0) {
					$pref = new UserPreferences($uinfo[$idst]['create_by_admin']);
					if($pref->getAdminPreference('admin_rules.limit_user_insert') == 'on') {
						$max_insert = $pref->getAdminPreference('admin_rules.max_user_insert');
						$pref->setPreference('admin_rules.max_user_insert', $max_insert -1 );
					}
				}
				//$this->aclManager->deleteTempUser( $idst , false, false, false );
			}
		}

		$this->aclManager->deleteTempUsers($approved);

		if (!empty($approved)) {
			$pl_man =& PlatformManager::createInstance();
			$array_subst = array('[url]' => Get::site_url());//$GLOBALS[$pl_man->getHomePlatform()]['url']);
			$msg_composer2 = new EventMessageComposer();
			$msg_composer2->setSubjectLangText('email', '_REGISTERED_USER_SBJ', false);
			$msg_composer2->setBodyLangText('email', '_APPROVED_USER_TEXT', $array_subst);
			$msg_composer2->setBodyLangText('sms', '_APPROVED_USER_TEXT_SMS', $array_subst);
			createNewAlert(	'UserApproved', 'directory', 'edit', '1', 'Users approved', $approved, $msg_composer2, true );
		}

		//TO DO: more specific error messages, check if count($approved) == count($arr_idst)
		return (count($approved) > 0);
	}

	public function deleteWaitingUsers($arr_idst) {
		return $this->aclManager->deleteTempUsers($arr_idst);
	}


	public function getWaitingUsersIds($filter = false) {
		$query = "SELECT w.idst "
			." FROM %adm_user_temp as w LEFT JOIN %adm_user as u ON (w.create_by_admin = u.idst)"
			." WHERE w.confirmed > 0 ";

		if ($filter) {
			$query .= " AND (w.userid LIKE '%".$filter."%' "
				." OR w.firstname LIKE '%".$filter."%' "
				." OR w.lastname LIKE '%".$filter."%' "
				." OR w.email LIKE '%".$filter."%') ";
		}

		$res = $this->db->query($query);

		$output = array();
		if ($res) {
			while (list($idst) = $this->db->fetch_row($res)) {
				$output[] = (int)$idst;
			}
		}

		return $output;
	}

	public function getWaitingUsersTotal($filter = false) {
		$query = "SELECT COUNT(*) "
			." FROM %adm_user_temp as w LEFT JOIN %adm_user as u ON (w.create_by_admin = u.idst)"
            ." WHERE (w.confirmed > 0 OR  w.confirmed = 0)";

		if ($filter) {
			$query .= " AND (w.userid LIKE '%".$filter."%' "
				." OR w.firstname LIKE '%".$filter."%' "
				." OR w.lastname LIKE '%".$filter."%' "
				." OR w.email LIKE '%".$filter."%') ";
		}

		$res = $this->db->query($query);

		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = (int)$total;
		}

		return $output;
	}

	public function getWaitingUsersList($pagination = array(), $filter = false) {
		if (!is_array($pagination)) $pagination = array();

		$startIndex = (isset($pagination['startIndex']) ? $pagination['startIndex'] : 0);
		$results = (isset($pagination['results']) ? $pagination['results'] : Get::sett('visuItem', 25));

		$dir = 'ASC';
		if (isset($pagination['dir'])) {
			$_pdir = str_replace('yui-dt-', '', strtolower($pagination['dir']));
			switch ($_pdir) {
				case 'asc': $dir = 'ASC'; break;
				case 'desc': $dir = 'DESC'; break;
				default: $dir = 'ASC';
			}
		}
		$sort = 'w.userid';
		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'firstname': $sort = 'w.lastname '.$dir.', w.firstname'; break;
				case 'lastname': $sort = 'w.firstname '.$dir.', w.lastname'; break;
				case 'email': $sort = 'w.email'; break;
                case 'confirmed': $sort = 'w.confirmed'; break;
				case 'insert_date': $sort = 'insert_date'; break;
				case 'insert_by': $sort = 'insert_by'; break;
			}
		}


		$query = "SELECT w.idst, w.userid, w.firstname, w.lastname, w.email, w.confirmed, w.request_on as insert_date, u.userid as inserted_by "
			." FROM %adm_user_temp as w LEFT JOIN %adm_user as u ON (w.create_by_admin = u.idst) "
            ." WHERE (w.confirmed > 0 OR  w.confirmed = 0)";

		if ($filter) {
			$query .= " AND (w.userid LIKE '%".$filter."%' "
				." OR w.firstname LIKE '%".$filter."%' "
				." OR w.lastname LIKE '%".$filter."%' "
				." OR w.email LIKE '%".$filter."%') ";
		}

		$query .= " ORDER BY ".$sort." ".$dir." ";
		$query .= "LIMIT ".$startIndex.", ".$results;

		$res = $this->db->query($query);

		$output = array();
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = $obj;
			}
		} else {
			return false;
		}

		return $output;
	}


	public function getDeletedUsersTotal($filter = false) {
		$query = "SELECT COUNT(*) "
			." FROM %adm_deleted_user as d LEFT JOIN %adm_user as u ON (d.deleted_by = u.idst)";

		if ($filter) {
			$query .= " WHERE (d.userid LIKE '%".$filter."%' "
				." OR d.firstname LIKE '%".$filter."%' "
				." OR d.lastname LIKE '%".$filter."%' "
				." OR d.email LIKE '%".$filter."%') ";
		}

		$res = $this->db->query($query);

		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}

	public function getDeletedUsersList($pagination = array(), $filter = false) {
		if (!is_array($pagination)) $pagination = array();

		$startIndex = (isset($pagination['startIndex']) ? $pagination['startIndex'] : 0);
		$results = (isset($pagination['results']) ? $pagination['results'] : Get::sett('visuItem', 25));

		$dir = 'ASC';
		if (isset($pagination['dir'])) {
			$_pdir = str_replace('yui-dt-', '', strtolower($pagination['dir']));
			switch ($_pdir) {
				case 'asc': $dir = 'ASC'; break;
				case 'desc': $dir = 'DESC'; break;
				default: $dir = 'ASC';
			}
		}

		$sort = 'd.userid';
		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'userid': $sort = 'd.userid'; break;
				case 'firstname': $sort = 'd.lastname '.$dir.', d.firstname'; break;
				case 'lastname': $sort = 'd.firstname '.$dir.', d.lastname'; break;
				case 'email': $sort = 'd.email'; break;
				case 'deletion_date': $sort = 'd.deletion_date'; break;
				case 'deleted_by': $sort = 'deleted_by'; break;
			}
		}

		$query = "SELECT d.idst, d.userid, d.firstname, d.lastname, d.email, d.deletion_date, u.userid as deleted_by "
			." FROM %adm_deleted_user as d LEFT JOIN %adm_user as u ON (d.deleted_by = u.idst)";

		if ($filter) {
			$query .= " WHERE (d.userid LIKE '%".$filter."%' "
				." OR d.firstname LIKE '%".$filter."%' "
				." OR d.lastname LIKE '%".$filter."%' "
				." OR d.email LIKE '%".$filter."%') ";
		}

		$query .= " ORDER BY ".$sort." ".$dir." ";
		$query .= "LIMIT ".$startIndex.", ".$results;

		$res = $this->db->query($query);

		$output = array();
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				$output[] = $obj;
			}
		} else {
			return false;
		}

		return $output;
	}



	public function getOrgChartDropdownList($idstUser=null) {
        
        $output = array();
        if($idstUser==null){
            $output = array('0' => '('.Lang::t('_ROOT', 'standard').')');
        }else{
            $queryRoot = "SELECT count(G.idst) FROM %adm_admin_tree T JOIN %adm_group G WHERE T.idst = G.idst AND idstAdmin =".$idstUser." AND G.groupid = '/ocd_0'"; 
            list($control) = $this->db->fetch_row($this->db->query($queryRoot));
            if($control < 0) {
                $output = array('0' => '('.Lang::t('_ROOT', 'standard').')');
            } else if ($control == 0  && Docebo::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN ) { //#3725
                $output = array('0' => '('.Lang::t('_ROOT', 'standard').')');
            }
             
        }

		$org_lang = array();
		$query = "SELECT * FROM %adm_org_chart WHERE lang_code = '".getLanguage()."'";
		$res = $this->db->query($query);
		while ($obj = $this->db->fetch_obj($res)) {
			$org_lang[$obj->id_dir] = $obj->translation;
		}
        
          
          
        $query = "SELECT * FROM %adm_org_chart_tree ORDER BY path";
        if($idstUser!=null){
            $org_groups = $this->_getAdminOrgTree($idstUser);
            if (!empty($org_groups)){
                $query = "SELECT * FROM %adm_org_chart_tree where idOrg in (".  implode(",", $org_groups).") ORDER BY path";
            }
        }
		$res = $this->db->query($query);
		if ($res) {
			while ($obj = $this->db->fetch_obj($res)) {
				$indend = "";
				for ($i=0; $i<$obj->lev; $i++) $indend .= "&nbsp;&nbsp;";
				$output[$obj->idOrg] = $indend.(isset($org_lang[$obj->idOrg]) ? $org_lang[$obj->idOrg] : "");
			}
		}
		return $output;
	}



	public function updateMultipleUsers($users, $info) {
		if (is_numeric($users)) $users = array($users);
		if (!is_array($users)) return false;
		if (count($users) <= 0) return true;

		if (is_array($info)) $info = Util::arrayToObject($info);
		if (!is_object($info)) return false;

		$output = true;

		$user_conditions = array();

		if (property_exists($info, 'firstname')) $user_conditions[] = " firstname='".$info->firstname."' ";
		if (property_exists($info, 'lastname')) $user_conditions[] = " lastname='".$info->lastname."' ";
		if (property_exists($info, 'email')) $user_conditions[] = " email='".$info->email."' ";
		if (property_exists($info, 'password')) $user_conditions[] = "pass='".$this->aclManager->encrypt ($info->password)."' ";
		if (property_exists($info, 'force_change')) $user_conditions[] = "force_change=".($info->force_change ? '1' : '0');

		//set level
		if (property_exists($info, 'level')) {
			//filter level property to get a valid value
			switch ($info->level) {
				case ADMIN_GROUP_ADMIN: $level = ADMIN_GROUP_ADMIN; break;
				case ADMIN_GROUP_GODADMIN: $level = ADMIN_GROUP_GODADMIN; break;
				case ADMIN_GROUP_USER: $level = ADMIN_GROUP_USER; break;
				default: $level = false; break;
			}

			if ($level) {
				$arr_levels_id = $this->aclManager->getAdminLevels();
				$arr_levels_idst = array_values($arr_levels_id);

				//remove users to old level groups
				foreach ($arr_levels_id as $level_groupid => $level_idst) {
					$this->aclManager->removeFromGroup($level_idst, $users);
				}

				//subscribe users to level group
				$this->aclManager->addToGroup($arr_levels_id[$level], $users);
			}
		}


		$fields = property_exists($info, '__fields') ? $info->__fields : false;
		$preferences = property_exists($info, '__preferences') ? $info->__preferences : false;

		$output = true;

		//TO DO : start transaction ...

		if (!empty($user_conditions)) {
			$query = "UPDATE %adm_user SET ".implode(",", $user_conditions)." "
				." WHERE idst IN (".implode(",", $users).")";
			$res = $this->db->query($query);
			if (!$res) $output = false;
		}

		if (!empty($fields)) {
			require_once(_adm_.'/lib/lib.field.php');
			$fman = new FieldList();
			$res = $fman->storeDirectFieldsForUsers($users, $fields);
			if (!$res) $output = false;
		}

		if (!empty($preferences)) {

		}

		//TO DO : end transaction ...

		return $output;
	}




	public function getUserFolders($id_user, $language = false) {
		if (!$language) $language = getLanguage();

		$output = array();

		$groups = array();
		$query = "SELECT g.idst, g.groupid FROM %adm_group_members AS gm JOIN %adm_group AS g "
			." ON (gm.idst = g.idst AND g.hidden = 'true' AND g.groupid LIKE '/oc\_%') "
			." WHERE gm.idstMember = ".(int)$id_user." ";
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res) > 0) {
			while (list($idst, $groupid) = $this->db->fetch_row($res)) {
				$groups[$idst] = $this->aclManager->relativeId($groupid);
			}
		}

		//extract entire tree folders with translation
		$folders = array();
		$trans = array();
		$query = "SELECT * FROM %adm_org_chart_tree AS ot JOIN %adm_org_chart AS o "
			." ON (ot.idOrg = o.id_dir AND o.lang_code = '".$language."') ORDER BY ot.path";
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res) > 0) {
			while ($obj = $this->db->fetch_obj($res)) {
				$str_path = str_replace("/root/", "", $obj->path);
				$folders[$obj->idst_oc] = $str_path;
				$trans['/'.$str_path] = $obj->translation;
			}

			foreach ($groups as $idst => $groupid) {
				if (array_key_exists($idst, $folders)) {
					$arr_path = explode('/', $folders[$idst]);
					$str_path = "";
					$arr_output = array();
					foreach ($arr_path as $index) {
						$str_path .= '/'.$index;
						$arr_output[] = $trans[$str_path];
					}
					$output[$idst] = implode('/', $arr_output);
				}
			}

		}

		return $output;
	}

	public function getUserGroups($id_user) {
		$output = array();

		$groups = array();
		$query = "SELECT g.idst, g.groupid FROM %adm_group_members AS gm JOIN %adm_group AS g "
			." ON (gm.idst = g.idst AND g.hidden = 'false' AND g.type <> 'course') "
			." WHERE idstMember = ".(int)$id_user." ";
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res) > 0) {
			while (list($id_group, $group_name) = $this->db->fetch_row($res)) {
				$output[$id_group] = $this->aclManager->relativeId($group_name);
			}
		}

		return $output;
	}


	/**
	 * Return the idst of the public admin first orgchart folder
	 * @param int $id_admin
	 * @return int
	 */
	public function getAdminFolder($id_admin, $return_org = false) {
		$acl_man = Docebo::user()->getACLManager();
		require_once(_base_.'/lib/lib.preference.php');
		$adminManager	= new AdminPreference();
		$admin_tree		= $adminManager->getAdminTree( Docebo::user()->getIdST() );
		$admin_tree		= $acl_man->getGroupsFromMixedIdst($admin_tree);
		$tmp_admin_tree = array();
		foreach($admin_tree as $id_group)
			$tmp_admin_tree = array_merge($tmp_admin_tree, $acl_man->getGroupGDescendants($id_group));
		$admin_tree = $tmp_admin_tree;

		$output = 0;
		if (!empty($admin_tree)) {
			$query = "SELECT oct.idOrg, oct.idst_oc FROM %adm_org_chart_tree AS oct "
				." WHERE oct.idst_oc IN (".implode(",", $admin_tree).") OR oct.idst_ocd IN (".implode(",", $admin_tree).") "
				." ORDER BY oct.iLeft ASC LIMIT 1";
			$res = $this->db->query($query);
			if ($res && $this->db->num_rows($res) > 0) {
				list($id_org, $idst_oc) = $this->db->fetch_row($res);
				if($return_org) $output = (int)$id_org;
				else $output = (int)$idst_oc;
			}
		}
		return $output;
	}



	/**
	 * Return the users fields and custom fields values, given a set of idsts
	 * @param int|array a list of users idsts
	 * @return array extracted values, the idst of the user as key
	 */
	public function getCustomFieldValues($users) {
		$output = array();
		
		//validate input
		if (empty($users)) return $output;
		if (is_numeric($users)) $users = array((int)$users);
		if (!is_array($users)) return false;

		$query_std = "SELECT u.idst, u.email, u.register_date, u.lastenter "
			." FROM %adm_user AS u "
			." WHERE u.idst IN (".implode(",", $users).")";
		$res_std = $this->db->query($query_std);
		while ($urow = $this->db->fetch_obj($res_std)) {
			$output[$urow->idst] = array(
				'email' => $urow->email,
				'register_date' => Format::date($urow->register_date, 'datetime'), //formatting should be placed in the controller
				'lastenter' => Format::date($urow->lastenter, 'datetime') //formatting should be placed in the controller
			);
		}

		//user levels
		$levels_idst = Docebo::aclm()->getAdminLevels();
		$levels_flip = array_flip($levels_idst);

		//retrieve language and level for given users
		$query_others = "(SELECT u.idst, su.value, 'language' AS type ".
			" FROM %adm_user as u JOIN %adm_setting_user AS su ".
			" ON (u.idst = su.id_user AND su.path_name = 'ui.language') ".
			" WHERE u.idst IN (".implode(",", $users).") ) ".
			" UNION ".
			" (SELECT u.idst, gm.idst AS value, 'level' AS type ".
			" FROM %adm_user AS u JOIN %adm_group_members AS gm ".
			" ON (u.idst = gm.idstMember AND gm.idst IN (".implode(",", array_values($levels_idst))."))".
			" WHERE u.idst IN (".implode(",", $users).") )";
		$res_others = $this->db->query($query_others);
		while (list($idst, $value, $type) = $this->db->fetch_row($res_others)) {
			switch ($type) {
				case 'language': $output[(int)$idst]['language'] = $value; break;
				case 'level': $output[(int)$idst]['level'] = $levels_flip[$value]; break;
			}
		}


		//fields
		$query_fields = "SELECT f.id_common, f.type_field, fu.id_user, fu.user_entry ".
			" FROM %adm_field_userentry as fu JOIN %adm_field as f ON (fu.id_common=f.id_common) ".
			" WHERE id_user IN (".implode(",", $users).") ";
			//." AND fu.id_common IN (".implode(",", $custom_fields).") ";
		$res_fields = $this->db->query($query_fields);

		$field_sons = false;
		$countries = false;

		//get values to add in the row
		$custom_fields = array();
		while ($frow = $this->db->fetch_obj($res_fields)) {
			if (!in_array($frow->id_common, $custom_fields)) $custom_fields[] = $frow->id_common;

			$field_value = "";
			switch ($frow->type_field) {
				case "yesno": {
					switch($frow->user_entry) {
						case 1 : $field_value = Lang::t('_YES', 'field');break;
						case 2 : $field_value = Lang::t('_NO', 'field');break;
						default: $field_value = '';break;
					}
				} break;
				case "dropdown": {
					if ($field_sons === false) {
						//retrieve translations for dropdowns fields
						$query_fields_sons = "SELECT idField, id_common_son, translation FROM %adm_field_son WHERE lang_code = '".getLanguage()."' ORDER BY idField, sequence";
						$res_fields_sons = $this->db->query($query_fields_sons);
						while ($fsrow = $this->db->fetch_obj($res_fields_sons)) {
							$field_sons[$fsrow->idField][$fsrow->id_common_son] = $fsrow->translation;
						}
					}
					if (isset($field_sons[$frow->id_common][$frow->user_entry]))
						$field_value = $field_sons[$frow->id_common][$frow->user_entry];
				} break;
				case "date": {
					$field_value = Format::date(substr($frow->user_entry, 0, 10), 'date');
				} break;
                case "copy": {
                    if ($field_sons === false) {
                        //retrieve translations for dropdowns fields
                        $query_fields_sons = "SELECT idField, id_common_son, translation FROM %adm_field_son WHERE lang_code = '".getLanguage()."' ORDER BY idField, sequence";
                        $res_fields_sons = $this->db->query($query_fields_sons);
                        $field_sons = array();
                        while ($fsrow = $this->db->fetch_obj($res_fields_sons)) {
                            $field_sons[$fsrow->idField][$fsrow->id_common_son] = $fsrow->translation;
                        }
                    }
                    if (isset($field_sons[$frow->copy_of][$frow->user_entry]))
                        $field_value = $field_sons[$frow->copy_of][$frow->user_entry];
                    else
                        $field_value = "";
                } break;
				case "country": {
					if ($countries === false) {
						//retrieve countries names
						$query_countries = "SELECT id_country, name_country FROM %adm_country ORDER BY name_country";
						$res_countries = $this->db->query($query_countries);
						$countries = array();
						while ($crow = $this->db->fetch_obj($res_countries)) {
							$countries[$crow->id_country] = $crow->name_country;
						}
					}
					if (isset($countries[$frow->user_entry]))
						$field_value = $countries[$frow->user_entry];
					else
						$field_value = "";
				} break;
				default: $field_value = $frow->user_entry; break;
			}
			$output[ (int)$frow->id_user ][ (int)$frow->id_common ] = $field_value; //$frow->user_entry;
		}

		return $output;
	}

	public function checkUserid($userid)
	{
		$query =	"SELECT COUNT(*)"
					." FROM %adm_user"
					." WHERE userid like '".($userid[0] === '/' ? '' : '/').$userid."'";
		list($control) = $this->db->fetch_row($this->db->query($query));
		if($control > 0)
			return false;
		return true;
	}
    
    //LRZ ************
    // return vett of custom field for ORG-CHART
    public function getCustomFieldOrg($nodeid){
            $query = 'select %adm_customfield_lang.id_field, translation, type_field 
                from %adm_customfield_lang, %adm_customfield 
                where %adm_customfield_lang.id_field = %adm_customfield.id_field  and
                 %adm_customfield_lang.lang_code = \''.getLanguage().'\' and area_code="ORG_CHART"';
            $rs = sql_query($query) or 
                    errorCommunication( 'getCustomFieldOrg' );
            $result = array();
            while( list( $id_field, $translation, $type_field) = sql_fetch_row($rs) ){
                $arr = array(
                            'id_field' => $id_field,
                            'translation' => $translation,
                            'type_field' =>  $type_field
                                )   ;
                $result[$id_field] = $arr ;
            }    
            return $result;        
    }
    public function getLO_Custom_Value_Array($id_field){
         $query = "select %adm_customfield_son_lang.id_field_son ,  translation from
            %adm_customfield_son_lang  , %adm_customfield_son
            where %adm_customfield_son_lang.id_field_son=%adm_customfield_son.id_field_son and id_field=".$id_field. " order by sequence ";
        $rs = sql_query($query) or 
                    errorCommunication( 'getLO_Custom_Value_Array' );    
        $result = array();
            while( list( $id_field_son, $translation) = sql_fetch_row($rs) ){
                $result[$id_field_son] = $translation;
            }    
            return $result;       
    }  
    private function countCustomForItem($idOrg, $id_field){
        $query = "select * from %adm_customfield_entry where id_obj=".$idOrg." and id_field=".$id_field;
        $rs = sql_query($query) or 
                errorCommunication( 'countCustomForItem' );
        if( sql_num_rows( $rs ) > 0 ) {
            return TRUE;
        } else 
            return FALSE;
    }
    // UPDATE or ADD a ORG-CHART CUSTOM FIELD VALUE
    public  function addCustomFieldValue($idOrg, $id_field, $value){
         // controlla se esiste il record, se esiste aggiorna altrimenti aggiungi
         $res = $this->countCustomForItem($idOrg, $id_field);
        if($res){
            //aggiornamento
            $query = "UPDATE %adm_customfield_entry set obj_entry='".$value."' where id_obj=".$idOrg." and id_field='".$id_field."'";                
            sql_query( $query );
        }else{
            //inserimento
            $query = "INSERT INTO %adm_customfield_entry "
                    ."( id_field, id_obj, obj_entry)"
                    ." VALUES "
                    ."( '".($id_field) ."' ,".intval($idOrg)." , '".$value."')";
            sql_query( $query );
        }
         return $res;    
    } 
      // get custom for lo_org
      public function getCustomOrg(){
          $query = "select id_field from %adm_customfield where area_code = 'ORG_CHART'";
              $rs = sql_query($query) or 
                    errorCommunication( 'getCustomOrg' );    
        $result = array();
            while( list( $id_field, $translation) = sql_fetch_row($rs) ){
                $result[$id_field] = $id_field;
            }    
            return $result;   
      }
    public function getValueCustom($idOrg, $idField){
            $query = "SELECT obj_entry FROM %adm_customfield_entry "
                ."WHERE id_field = '".$idField."'"
                ."  AND id_obj = ".$idOrg;
        $rs = sql_query($query) or 
                errorCommunication( 'getValueCustom' );
        if( sql_num_rows( $rs ) == 1 ) {
            list( $obj_entry ) = sql_fetch_row( $rs );
            return $obj_entry;
        } else 
            return '';
    }
    
    /**
    *   Returns only custom fields values, given single user
    *   @param int|array a list of users idsts
    *   @return array extracted values, the idst of the user as key
    */
    public function getCustomFieldUserValues($user){
        $output = array();
        

        if (is_null($user)) return $output;
         $query = "select idField, user_entry from 
                    %adm_field JOIN %adm_field_userentry ON core_field.id_common = core_field_userentry.id_common where
                    id_user =".$user." and lang_code='".getLanguage()."' order by idField";
                    
        $res = sql_query($query);
                
    
        while (list($id_field, $user_entry ) = sql_fetch_row ($res)) {
            $output[$id_field] = $user_entry;                                          
        }    
  
       return $output;       
        
    }
    
}

