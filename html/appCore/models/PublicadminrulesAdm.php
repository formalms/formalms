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

class PublicadminrulesAdm extends Model
{
	protected $acl_man;
	protected $preference;
	protected $rules_path;
	protected $rules_cache;

	public function  __construct()
	{
		require_once(_base_.'/lib/lib.preference.php');
		$this->preference = new AdminPreference();
		$this->acl_man =& Docebo::user()->getAclManager();
		$this->rules_path = '/framework/publicadminrules/';
		$this->rules_cache = NULL;
	}

	public function getPerm()
	{
		return array();
	}

	public function totalGroup()
	{
		$query =	"SELECT COUNT(*)"
					." FROM %adm_group"
					." WHERE groupid LIKE '".$this->rules_path."%'";

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function loadGroup($start_index, $results, $sort, $dir, $filter_text = "")
	{
		$query =	"SELECT idst, groupid"
					." FROM %adm_group"
					." WHERE groupid LIKE '".$this->rules_path."%".($filter_text != "" ? $filter_text."%" : "")."' "
					." ORDER BY groupid ".$dir;

		($start_index === false ? '' : $query .= " LIMIT ".$start_index.", ".$results);

		$result = sql_query($query);
		$res = array();

		while(list($idst, $groupid) = sql_fetch_row($result))
		{
			$users = $this->loadUserSelectorSelection($idst);
			$menu = $this->preference->getAdminPerm($idst);
			
			$have_user = false;
			$have_menu = false;

			if(count($users) > 0)	$have_user = true;
			if(count($menu) > 0) $have_menu = true;

			$name = str_replace($this->rules_path, '', $groupid);

			$res[] = array(
				'id' => (int)$idst,
				'idst' => (int)$idst,
				'groupid' => str_replace($this->rules_path, '', $groupid),
				'special' => '<a id="special_'.$idst.'" href="ajax.adm_server.php?r=adm/publicadminrules/special&amp;idst='.$idst.'" class="ico-sprite subs_conf" title="'.Lang::t('_SPECIAL_SETTING', 'adminrules').': '.$name.'"><span>'.Lang::t('_SPECIAL_SETTING', 'adminrules').'</span></a>',
				'menu' => '<a name="menu" href="index.php?r=adm/publicadminrules/menu&amp;idst='.$idst.'" class="ico-sprite '.($have_menu ? 'subs_elem' : 'fd_notice').'" title="'.Lang::t('_EDIT_SETTINGS', 'adminrules').': '.$name.'"><span>'.Lang::t('_EDIT_SETTINGS', 'adminrules').'</span></a>',
				'admin_manage' => '<a href="index.php?r=adm/publicadminrules/admin_manage&amp;idst='.$idst.'" class="ico-sprite '.($have_user ? 'subs_users' : 'fd_notice').'" title="'.Lang::t('_MANAGE_SUBSCRIPTION', 'adminrules').': '.$name.'"><span>'.Lang::t('_MANAGE_SUBSCRIPTION', 'adminrules').'</span></a>',
				'del' => 'ajax.adm_server.php?r=adm/publicadminrules/delGroup&amp;idst='.$idst
			);
		}

		return $res;
	}

	public function addGroup($name)
	{
		$idst = $this->acl_man->_createST();

		$query =	"INSERT INTO %adm_group"
					." (idst, groupid, hidden)"
					." VALUES ('".$idst."', '".$this->rules_path.$name."', 'true')";

		$res = sql_query($query);

		return $res;
	}

	public function delGroup($idst)
	{
		$query =	"DELETE FROM %adm_group"
					." WHERE idst = '".$idst."'";

		if(sql_query($query))
		{
			$this->preference->clearAdmRules($idst);
			$this->preference->clearAdmPerm($idst);
			$this->clearAdminAssociation($idst);

			return true;
		}

		return false;
	}

	public function getGroupName($idst)
	{
		$query =	"SELECT groupid"
					." FROM %adm_group"
					." WHERE idst = '".$idst."'";

		list($groupid) = sql_fetch_row(sql_query($query));

		$res = str_replace('/framework/publicadminrules/', '', $groupid);

		return $res;
	}

	public function printPage($idst)
	{
		$adm_old_perm = $this->preference->getAdminPerm($idst);
		$html = '';
		$array_image = array(	'view' => 'ico-sprite subs_view',
								'add' => 'ico-sprite subs_add',
								'mod' => 'ico-sprite subs_mod',
								'del' => 'ico-sprite subs_del',
								'approve_waiting_user' => 'ico-sprite subs_users',
								'subscribe' => 'ico-sprite subs_users',
								'moderate' => 'ico-sprite subs_wait');

		$query =	"SELECT module_name, default_name, file_name, class_name, mvc_path"
					." FROM %lms_module"
					." WHERE module_info = 'public_admin'";

		$result = sql_query($query);

		if(sql_num_rows($result) > 0)
		{
			$tb = new Table(NULL);

			$th = array(Lang::t('_MAN_MENU', 'adminrules'));
			$ts = array('');

			$total_perm = array();
			$module_perm = array();

			while(list($module_name, $default_name, $file_name, $class_name, $mvc_path) = sql_fetch_row($result))
			{
				if($mvc_path !== '')
				{
					$tmp = explode('/', $mvc_path);
					$mvc_name = ucwords($tmp[1]);
					$perm_path = '/lms/course/public/'.strtolower($mvc_name).'/';

					require_once(_lms_.'/controllers/'.$mvc_name.'LmsController.php');

					$class_name = $mvc_name.'LmsController';
					$tmp_class = new $class_name('');

					$perm = $tmp_class->getPerm();

					if(!empty($perm))
					{
						foreach($perm as $perm_name => $img)
						{
							if(array_search($perm_name, array_keys($total_perm)) == false)
								$total_perm[$perm_name] = $img;

							list($perm_idst) = sql_fetch_row(sql_query("SELECT idst FROM %adm_role WHERE roleid = '".$perm_path.$perm_name."'"));

							$module_perm[$mvc_name][$perm_name] = $perm_idst;
						}
					}
				}
				else
				{
					require_once(_lms_.'/class.module/'.$file_name);

					$tmp_class = new $class_name();
					$perm_path = '/lms/course/public/'.strtolower($module_name).'/';

					$perm = $tmp_class->getAllToken('lang');

					if(!empty($perm))
					{
						foreach($perm as $perm_name => $info)
						{
							if(array_search($perm_name, array_keys($total_perm)) == false)
								$total_perm[$perm_name] = $info['image'];

							list($perm_idst) = sql_fetch_row(sql_query("SELECT idst FROM %adm_role WHERE roleid = '".$perm_path.$info['code']."'"));
							$module_perm[$class_name][$perm_name] = $perm_idst;
						}
					}
				}
			}//End while 1

			foreach($total_perm as $perm => $img)
			{
				$th[] = '<span class="'.$array_image[$perm].'" title="'.Lang::t('_'.strtoupper($perm), 'adminrules').'"><span>'.Lang::t('_'.strtoupper($perm), 'adminrules').'</span></span>';
				$ts[] = 'image';
			}

			$tb->addHead($th);
			$tb->setColsStyle($ts);

			$result = sql_query($query);

			while(list($module_name, $default_name, $file_name, $class_name, $mvc_path) = sql_fetch_row($result))
			{
				if($mvc_path !== '')
				{
					$tmp = explode('/', $mvc_path);
					$mvc_name = ucwords($tmp[1]);
					if(isset($module_perm[$mvc_name]))
					{
						$content = array(Lang::t($default_name, 'menu_over'));

						foreach($total_perm as $perm => $img)
							if(isset($module_perm[$mvc_name][$perm]))
								$content[] = Form::getInputCheckbox('adm_perm_'.$module_perm[$mvc_name][$perm], 'adm_perm['.$module_perm[$mvc_name][$perm].']', '1', (isset($adm_old_perm[$module_perm[$mvc_name][$perm]])), '');
							else
								$content[] = '';

						reset($total_perm);
						$tb->addBody($content);
					}
				}
				elseif(isset($module_perm[$class_name]))
				{
					$content = array(Lang::t($default_name, 'menu_over'));

					foreach($total_perm as $perm => $img)
						if(isset($module_perm[$class_name][$perm]))
							$content[] = Form::getInputCheckbox('adm_perm_'.$module_perm[$class_name][$perm], 'adm_perm['.$module_perm[$class_name][$perm].']', '1', (isset($adm_old_perm[$module_perm[$class_name][$perm]])), '');
						else
							$content[] = '';

					reset($total_perm);
					$tb->addBody($content);
				}
			}//End while 2

			$html .= $tb->getTable();
		}

		return $html;
	}

	public function saveAdminPerm($idst, $adm_perm)
	{
		return $this->preference->saveAdminPerm($idst, $adm_perm);
	}

	public function totalAdmin($idst)
	{
		$query =	"SELECT COUNT(*)"
					." FROM %adm_group_members"
					." WHERE idst = '".$idst."'";

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function loadAdmin($idst, $start_index, $results, $sort, $dir)
	{
		$query =	"SELECT g.idstMember, u.userid, u.firstname, u.lastname"
					." FROM %adm_group_members as g"
					." JOIN %adm_user as u ON g.idstMember = u.idst"
					." WHERE g.idst = '".$idst."'"
					." ORDER BY ".$sort." ".$dir;

		($start_index === false ? '' : $query .= " LIMIT ".$start_index.", ".$results);

		$result = sql_query($query);
		$res = array();

		while(list($id_user, $userid, $firstname, $lastname) = sql_fetch_row($result))
		{
			$res[] = array(	'id_user' => $id_user,
							'userid' => $this->acl_man->relativeId($userid),
							'firstname' => $firstname,
							'lastname' => $lastname,
							'del' => 'ajax.adm_server.php?r=adm/publicadminrules/delAdmin&amp;idst='.$idst.'&amp;idstMember='.$id_user);
		}

		return $res;
	}

	public function loadUserSelectorSelection($idst)
	{
		$query =	"SELECT idstMember"
					." FROM %adm_group_members"
					." WHERE idst = '".$idst."'";

		$result = sql_query($query);
		$res = array();

		while(list($id_user) = sql_fetch_row($result))
			$res[$id_user] = $id_user;

		return $res;
	}

	public function saveNewAdmin($idst, $user_selected)
	{
		$this->clearAdminAssociation(false, $user_selected);

		$query =	"INSERT INTO %adm_group_members"
					." (idst, idstMember)"
					." VALUES ";

		$first = true;

		foreach($user_selected as $id_user)
		{
			if($first)
				$first = false;
			else
				$query .= ", ";

			$query .= "('".$idst."', '".$id_user."')";
		}

		return sql_query($query);
	}

	public function clearAdminAssociation($idst = false, $user_selected = array())
	{
		if (is_numeric($user_selected)) $user_selected = array((int)$user_selected);
		if (!is_array($user_selected)) return false;
		if (empty($user_selected)) return true;

		$groups = $this->idstGroup();
		if (isset($groups[0])) unset($groups[0]);
		if (empty($groups)) return true;

		$query =	"DELETE FROM %adm_group_members"
					." WHERE idst ".($idst !== false ? "= '".$idst."'" : "IN (".implode(',', $groups).")")
					.(!empty($user_selected) ? " AND idstMember IN (".implode(',', $user_selected).")" : "");

		return sql_query($query);
	}

	public function idstGroup()
	{
		$query =	"SELECT idst"
					." FROM %adm_group"
					." WHERE groupid LIKE '".$this->rules_path."%'";

		$result = sql_query($query);
		$res = array(0 => 0);

		while(list($idst) = sql_fetch_row($result))
			$res[$idst] = $idst;

		return $res;
	}

	public function getGroupForDropdown()
	{
		$query =	"SELECT idst, groupid"
					." FROM %adm_group"
					." WHERE groupid LIKE '".$this->rules_path."%' "
					." ORDER BY groupid";

		$result = sql_query($query);
		$res = array(0 => '('.strtolower(Lang::t('_NONE', 'adminrules')).')');

		while(list($idst, $groupid) = sql_fetch_row($result))
			$res[$idst] = str_replace($this->rules_path, '', $groupid);

		return $res;
	}

	public function getProfileAssociatedToAdmin($id_user)
	{
		$query =	"SELECT idst"
					." FROM %adm_group_members"
					." WHERE idstMember = '".$id_user."'"
					." AND idst IN (".implode(',', $this->idstGroup()).")";

		list($res) = sql_fetch_row(sql_query($query));

		if(!$res)
			$res = 0;

		return $res;
	}

	public function saveSingleAdminAssociation($idst, $id_user)
	{
		$this->clearAdminAssociation(false, array($id_user));

		$query =	"INSERT INTO %adm_group_members"
					." (idst, idstMember)"
					." VALUES "
					."('".$idst."', '".$id_user."')";

		return sql_query($query);
	}


	public function getAllRules() {
		if (!is_array($this->rules_cache)) {
			$query = "SELECT idst, groupid FROM %adm_group WHERE groupid LIKE '".$this->rules_path."%'";
			$res = sql_query($query);
			if (!$res) return false;
			$output = array();
			while (list($idst, $groupid) = sql_fetch_row($res)) {
				$output[$idst] = $groupid;
			}
			$this->rules_cache = $output;
		}
		return $this->rules_cache;
	}


	public function renameProfile($id_profile, $new_name) {
		$output = FALSE;
		if ($id_profile) {
			$query = "UPDATE %adm_group SET groupid = '".$this->rules_path.$new_name."' WHERE idst = ".(int)$id_profile;
			$res = sql_query($query);
			$output = $res ? TRUE : FALSE;
		}
		return $output;
	}

}
?>