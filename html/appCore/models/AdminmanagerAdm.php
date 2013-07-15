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

class AdminmanagerAdm extends Model
{
	//TO DO: change these in 'protected' and update the controller
	public $idst_admin_group;
	
	public $acl_man;
	public $preference;

	protected $model_adminrules;

	public function  __construct()
	{
		require_once(_base_.'/lib/lib.preference.php');
		$this->preference = new AdminPreference();

		$this->acl_man =& Docebo::user()->getAclManager();

		list($idst) = each($this->acl_man->getGroupsIdstFromBasePath('/framework/level/admin'));
		$this->idst_admin_group = $idst;

		$this->model_adminrules = new AdminrulesAdm();
	}

	public function getPerm()
	{
		return array();
	}

	public function getAdminFullname($id_user)
	{
		$user_info = $this->acl_man->getUser($id_user, false);

		if($user_info[ACL_INFO_FIRSTNAME] !== '' && $user_info[ACL_INFO_LASTNAME] !== '')
			$res = $user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME].' ('.$this->acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
		elseif($user_info[ACL_INFO_FIRSTNAME] !== '')
			$res = $user_info[ACL_INFO_FIRSTNAME].' ('.$this->acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
		elseif($user_info[ACL_INFO_LASTNAME] !== '')
			$res = $user_info[ACL_INFO_LASTNAME].' ('.$this->acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
		else
			$res = $this->acl_man->relativeId($user_info[ACL_INFO_USERID]);

		return $res;
	}

	public function totalAdmin($filter)
	{
		$array_idst = $this->acl_man->getGroupMembers($this->idst_admin_group);

		$query_filter = "";
		if ($filter != "") {
			$query_filter .= " AND (u.userid LIKE '%".$filter."%' "
				." OR u.firstname LIKE '%".$filter."%' "
				." OR u.lastname LIKE '%".$filter."%')";
		}

		$rules = $this->model_adminrules->getAllRules();
		
		if (!empty($rules)) {
			$query = "SELECT COUNT(*) "
				." FROM (%adm_user AS u LEFT JOIN %adm_group_members AS gm "
				." ON (u.idst = gm.idstMember AND gm.idst IN (".implode(",", array_keys($rules))."))) "
				." LEFT JOIN %adm_group AS g ON (g.idst = gm.idst) "
				." WHERE u.idst IN (".implode(',', $array_idst).") ".$query_filter;
		} else {
			$query = "SELECT COUNT(*) "
				." FROM %adm_user AS u WHERE u.idst IN (".implode(',', $array_idst).") ".$query_filter;
		}

		$result = sql_query($query);
		
		$output = false;
		if ($result) list($output) = sql_fetch_row($result);

		return $output;
	}

	public function loadAdmin($start_index, $results, $sort, $dir, $filter)
	{
		$array_idst = $this->acl_man->getGroupMembers($this->idst_admin_group);
		$output = array();

		if (!empty($array_idst)) {
			$query_filter = "";
			if ($filter != "") {
				$query_filter .= " AND (u.userid LIKE '%".$filter."%' "
					." OR u.firstname LIKE '%".$filter."%' "
					." OR u.lastname LIKE '%".$filter."%')";
			}

			$_dir = (trim(strtolower($dir)) == 'desc') ? 'DESC' : 'ASC';

			$_sort = 'u.userid';
			switch ($sort) {
				case 'firstname': $_sort =  'u.firstname '.$_dir.', u.lastname '.$_dir.', u.userid'; break;
				case 'lastname': $_sort =  'u.lastname '.$_dir.', u.firstname '.$_dir.', u.userid'; break;
				case 'user_profile': $_sort =  'g.groupid '.$_dir.', u.userid'; break;
			}

			$rules = $this->model_adminrules->getAllRules();
			
			if (!empty($rules)) {
				$query = "SELECT u.idst, u.userid, u.firstname, u.lastname, gm.idst, g.groupid "
					." FROM (%adm_user AS u LEFT JOIN %adm_group_members AS gm "
					." ON (u.idst = gm.idstMember AND gm.idst IN (".implode(",", array_keys($rules))."))) "
					." LEFT JOIN %adm_group AS g ON (g.idst = gm.idst) "
					." WHERE u.idst IN (".implode(',', $array_idst).") ".$query_filter
					." ORDER BY ".$_sort." ".$_dir
					.($start_index === false ? "" : " LIMIT ".(int)$start_index.", ".(int)$results);
			} else {
				$query = "SELECT u.idst, u.userid, u.firstname, u.lastname, NULL, NULL "
					." FROM %adm_user AS u WHERE u.idst IN (".implode(',', $array_idst).") ".$query_filter
					." ORDER BY ".$_sort." ".$_dir
					.($start_index === false ? "" : " LIMIT ".(int)$start_index.", ".(int)$results);
			}

			$result = sql_query($query);

			while (list($id_user, $userid, $firstname, $lastname, $id_group, $groupid) = sql_fetch_row($result))
			{
				$output[] = array(	'id_user' => $id_user,
					'userid' => $userid,
					'firstname' => $firstname,
					'lastname' => $lastname,
					'user_profile' => $groupid,
					'idst_profile' => $id_group
				);
			}
		}

		return $output;
	}

	public function getAllProfile()
	{
		return $this->model_adminrules->getGroupForDropdown();
	}
	
	public function  getProfileAssociatedToAdmin($id_user)
	{
		return $this->model_adminrules->getProfileAssociatedToAdmin($id_user);
	}

	public function saveSingleAdminAssociation($idst_profile, $id_user)
	{
		return $this->model_adminrules->saveSingleAdminAssociation($idst_profile, $id_user);
	}

	public function removeAdminAssociation($id_user)
	{
		return $this->model_adminrules->clearAdminAssociation(false, (int)$id_user);
	}

	public function loadUserSelectorSelection($id_user)
	{
		return $this->preference->getAdminTree($id_user);
	}

	public function saveUsersAssociation($id_user, $user_selected)
	{
		return $this->preference->saveAdminTree($id_user, $user_selected);
	}

	public function loadCourseSelectorSelection($id_user)
	{
		return $this->preference->getAdminCourse($id_user);
	}

	public function saveCoursesAssociation($id_user, $course_selected, $coursepath_selected, $catalogue_selected)
	{
		return $this->preference->saveAdminCourse($id_user, $course_selected, $coursepath_selected, $catalogue_selected);
	}

	public function loadClasslocationsSelection($id_user)
	{
		return $this->preference->getAdminClasslocation($id_user);
	}

	public function saveClasslocationsAssociation($id_user, $classlocations_selected)
	{
		return $this->preference->saveAdminClasslocation($id_user, $classlocations_selected);
	}
}
?>