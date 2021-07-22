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
 * @subpackage group_code
 */

class CodeManager
{
	public function __construct()
	{

	}

	public function __destruct()
	{

	}

	protected function _getCourseTable()
	{
		return $GLOBALS['prefix_lms'].'_course';
	}

	protected function _getCodeTable()
	{
		return $GLOBALS['prefix_fw'].'_code';
	}

	protected function _getCodeGroupsTable()
	{
		return $GLOBALS['prefix_fw'].'_code_groups';
	}

	protected function _getCodeCourseTable()
	{
		return $GLOBALS['prefix_fw'].'_code_course';
	}

	protected function _getCodeOrgTable()
	{
		return $GLOBALS['prefix_fw'].'_code_org';
	}

	protected function _getCodeAssociationTable()
	{
		return $GLOBALS['prefix_fw'].'_code_association';
	}

	public function getCodeGroupNumber()
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->_getCodeGroupsTable();

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getUsedCodeGroupCode($id_code_group)
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->_getCodeTable()
					." WHERE idCodeGroup = '".$id_code_group."'"
					." AND used = '1'";

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getTotalCodeGroupCode($id_code_group)
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->_getCodeTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getCodeGroupsList($ini = '0')
	{
		$query =	"SELECT idCodeGroup, title, description"
					." FROM ".$this->_getCodeGroupsTable()
					." LIMIT ".$ini.', 20';

		$result = sql_query($query);

		$res = array();

		while(list($id_code_group, $title, $description) = sql_fetch_row($result))
		{
			$res[$id_code_group]['id_code_group'] = $id_code_group;
			$res[$id_code_group]['title'] = stripslashes($title);
			$res[$id_code_group]['description'] = stripslashes($description);
			$res[$id_code_group]['code_used'] = $this->getUsedCodeGroupCode($id_code_group).' / '.$this->getTotalCodeGroupCode($id_code_group);
			$res[$id_code_group]['course_associated'] = $this->getCourseAssociated($id_code_group);
			$res[$id_code_group]['folder_associated'] = $this->getOrgAssociated($id_code_group);
		}

		return $res;
	}

	public function addCodeGroup($title, $description)
	{
		$query =	"INSERT INTO ".$this->_getCodeGroupsTable()
					." (title, description)"
					." VALUES ('".$title."', '".$description."')";

		return sql_query($query);
	}

	public function getCodeGroupInfo($id_code_group)
	{
		$query =	"SELECT title, description"
					." FROM ".$this->_getCodeGroupsTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		list($title, $description) = sql_fetch_row(sql_query($query));

		$res = array(	'title' => stripslashes($title),
						'description' => stripslashes($description));

		return $res;
	}

	public function updateCodeGroup($id_code_group, $title, $description)
	{
		$query =	"UPDATE ".$this->_getCodeGroupsTable()
					." SET title = '".$title."',"
					." description = '".$description."'"
					." WHERE idCodeGroup = '".$id_code_group."'";

		return sql_query($query);
	}

	public function delCodeGroup($id_code_group)
	{
		$query =	"DELETE"
					." FROM ".$this->_getCodeOrgTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		if(!sql_query($query))
			return false;

		$query =	"DELETE"
					." FROM ".$this->_getCodeCourseTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		if(!sql_query($query))
			return false;

		$query =	"DELETE"
					." FROM ".$this->_getCodeTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		if(!sql_query($query))
			return false;

		$query =	"DELETE"
					." FROM ".$this->_getCodeGroupsTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		return sql_query($query);
	}

	public function getCodeNumber($id_code_group)
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->_getCodeTable()
					." WHERE idCodeGroup = '".$id_code_group."'"
					.((isset($_POST['filter']) && $_POST['code_filter'] !== ''
						? " AND code LIKE '%".$_POST['code_filter']."%'"
						: ''));

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getCodeList($id_code_group, $ini = '0', $apply_limit = true)
	{
		$query =	"SELECT code, used, idUser, unlimitedUse"
					." FROM ".$this->_getCodeTable()
					." WHERE idCodeGroup = '".$id_code_group."'"
					.((isset($_POST['filter']) && $_POST['code_filter'] !== ''
						? " AND code LIKE '%".$_POST['code_filter']."%'"
						: ''))
					.($apply_limit?" LIMIT ".$ini.", 20":"");

		$result = sql_query($query);

		$res = array();

		$counter = 0;

		while(list($code, $used, $id_user, $unlimited_use) = sql_fetch_row($result))
		{
			$res[$counter]['code'] = stripslashes($code);
			$res[$counter]['used'] = $used;
			$res[$counter]['id_user'] = $id_user;
			$res[$counter]['unlimited_use'] = $unlimited_use;

			$counter++;
		}

		return $res;
	}

	public function controlCode($code, $old_code = '')
	{
		if($code === $old_code)
			return true;

		$query =	"SELECT COUNT(*)"
					." FROM ".$this->_getCodeTable()
					." WHERE `code` = '".$code."'";

		list($control) = sql_fetch_row(sql_query($query));

		if($control == 1)
			return 'dup';
		return true;
	}

	public function addCode($code, $id_code_group, $unlimited_use = false)
	{
		$control = $this->controlCode($code);

		if($control !== 'dup')
		{
			$query =	"INSERT INTO ".$this->_getCodeTable()
						." (`code`, `idCodeGroup`, `unlimitedUse`)"
						." VALUES ('".$code."', '".$id_code_group."', '".($unlimited_use ? '1' : '0')."')";

			return sql_query($query);
		}

		return $control;
	}

	public function codeIsUnlimited($code)
	{
		$query =	"SELECT unlimitedUse"
					." FROM ".$this->_getCodeTable()
					." WHERE `code` = '".$code."'";

		list($result) = sql_fetch_row(sql_query($query));

		if($result == '1')
			return true;
		return false;
	}

	public function modCode($code, $old_code, $unlimited_use)
	{
		$control = $this->controlCode($code, $old_code);

		if($control !== 'dup')
		{
			$query =	"UPDATE ".$this->_getCodeTable()
						." SET `code` = '".$code."',"
						." `unlimitedUse` = '".($unlimited_use ? '1' : '0')."'"
						." WHERE `code` = '".$old_code."'";

			return sql_query($query);
		}

		return $control;
	}

	public function delCode($code)
	{
		$query =	"DELETE"
					." FROM ".$this->_getCodeTable()
					." WHERE `code` = '".$code."'";

		return sql_query($query);
	}

	public function getCourseAssociated($id_code_group)
	{
		$query =	"SELECT idCourse"
					." FROM ".$this->_getCodeCourseTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		$result = sql_query($query);

		$res = array();

		while (list($id_course) = sql_fetch_row($result))
			$res[$id_course] = $id_course;

		return $res;
	}

	public function getAvailableCourseAssociated($id_code_group)
	{
		$query = "SELECT ccc.idCourse"
		." FROM ".$this->_getCodeCourseTable()." AS ccc JOIN ".$this->_getCourseTable()." AS lc ON ccc.idCourse = lc.idCourse"
		." WHERE ccc.idCodeGroup  = '".$id_code_group."'
		 	AND ((lc.can_subscribe = 2
			AND (lc.sub_end_date = '0000-00-00'
				OR lc.sub_end_date >= '2015-02-06')
			AND (lc.sub_start_date = '0000-00-00'
				OR '2015-02-06' >= lc.sub_start_date))
		OR (lc.can_subscribe = 1))";

		$result = sql_query($query);

		$res = array();

		while (list($id_course) = sql_fetch_row($result))
			$res[$id_course] = $id_course;

		return $res;
	}

	protected function clearCourseAssociation($id_code_group)
	{
		$query =	"DELETE"
					." FROM ".$this->_getCodeCourseTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		return sql_query($query);
	}

	public function insertCourseAssociation($course_selected, $id_code_group)
	{
		$this->clearCourseAssociation($id_code_group);

		if(!count($course_selected))
			return true;

		$first = true;

		$query =	"INSERT INTO ".$this->_getCodeCourseTable()
					." (idCodeGroup, idCourse)"
					." VALUES ";

		foreach($course_selected as $id_course)
		{
			if($first)
			{
				$query .= "('".$id_code_group."', '".$id_course."')";
				$first = false;
			}
			else
				$query .= ", ('".$id_code_group."', '".$id_course."')";
		}

		return sql_query($query);
	}

	public function getOrgAssociated($id_code_group)
	{
		$query =	"SELECT idOrg"
					." FROM ".$this->_getCodeOrgTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		$result = sql_query($query);

		$res = array();

		while (list($id_folder) = sql_fetch_row($result))
			$res[$id_folder] = $id_folder;

		return $res;
	}

	protected function clearOrgAssociation($id_code_group)
	{
		$query =	"DELETE"
					." FROM ".$this->_getCodeOrgTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		return sql_query($query);
	}

	public function insertOrgAssociation($folder_selected, $id_code_group)
	{
		$this->clearOrgAssociation($id_code_group);

		if(!count($folder_selected))
			return true;

		$first = true;

		$query =	"INSERT INTO ".$this->_getCodeOrgTable()
					." (idCodeGroup, idOrg)"
					." VALUES ";

		foreach($folder_selected as $id_folder)
		{
			if($first)
			{
				$query .= "('".$id_code_group."', '".$id_folder."')";
				$first = false;
			}
			else
				$query .= ", ('".$id_code_group."', '".$id_folder."')";
		}

		return sql_query($query);
	}

	public function controlCodeValidity($code)
	{
		if($code === '')
			return -1;

		$query =	"SELECT COUNT(*)"
					." FROM ".$this->_getCodeTable()
					." WHERE `code` = '".$code."'"
					." AND (used = '0'"
					." OR unlimitedUse = '1')";

		list($control) = sql_fetch_row(sql_query($query));

		if($control == 1)
			return 1;

		$query =	"SELECT COUNT(*)"
					." FROM ".$this->_getCodeTable()
					." WHERE `code` = '".$code."'";

		list($control) = sql_fetch_row(sql_query($query));

		if($control == 1)
			return 0;
		return -1;
	}

	public function getGroupOfCode($code)
	{
		$query =	"SELECT idCodeGroup"
					." FROM ".$this->_getCodeTable()
					." WHERE `code` = '".$code."'";

		list($id_code_group) = sql_fetch_row(sql_query($query));

		return $id_code_group;
	}

	public function getCourseAssociateWithCode($code)
	{
		$id_code_group = $this->getGroupOfCode($code);

		return $this->getCourseAssociated($id_code_group);
	}

	public function getAvailableCourseAssociateWithCode($code)
	{
		$id_code_group = $this->getGroupOfCode($code);

		return $this->getAvailableCourseAssociated($id_code_group);
	}

	public function getOrgAssociateWithCode($code)
	{
		$id_code_group = $this->getGroupOfCode($code);

		return $this->getOrgAssociated($id_code_group);
	}

	public function setCodeUsed($code, $id_user)
	{
		$is_unlimited = $this->codeIsUnlimited($code);

		if($is_unlimited)
			return $this->setCodeAssociation($code, $id_user);

		$query =	"UPDATE ".$this->_getCodeTable()
					." SET `idUser` = '".$id_user."',"
					." used = '1'"
					." WHERE `code` = '".$code."'";

		return sql_query($query);
	}

	protected function setCodeAssociation($code, $id_user)
	{
		$query =	"INSERT INTO ".$this->_getCodeAssociationTable()
					." (`code`, `idUser`)"
					." VALUES (NULL, '".$code."', '".$id_user."')";

		return sql_query($query);
	}

	public function getCodeAssociate($id_user)
	{
		$query =	"SELECT `code`"
					." FROM ".$this->_getCodeTable()
					." WHERE `idUser` = '".$id_user."'";

		$result = sql_query($query);

		if(sql_num_rows($result))
		{
			list($code) = sql_fetch_row($result);

			return $code;
		}

		$query =	"SELECT `code`"
					." FROM ".$this->_getCodeAssociationTable()
					." WHERE `idUser` = '".$id_user."'";

		$result = sql_query($query);

		if(sql_num_rows($result))
		{
			list($code) = sql_fetch_row($result);

			return $code;
		}

		return false;
	}

	public function resetUserAssociation($code, $id_user)
	{
		$is_unlimited = $this->codeIsUnlimited($code);

		if($is_unlimited)
		{
			$query =	"DELETE"
						." FROM ".$this->_getCodeAssociationTable()
						." WHERE code = '".$code."'"
						." AND idUser = '".$id_user."'";

			return sql_query($query);
		}

		$query =	"UPDATE ".$this->_getCodeTable()
					." SET `idUser` = '',"
					." used = '0'"
					." WHERE `code` = '".$code."'";

		return sql_query($query);
	}


	function resetUserCode($user_idst) {
		$query =	"UPDATE ".$this->_getCodeTable()
					." SET `idUser` = null,"
					." used = '0'"
					." WHERE `idUser` = '".$user_idst."' LIMIT 1";

		return sql_query($query);
	}


	function getTotalCodeNumber()
	{
		$query =	"SELECT COUNT(*)"
				." FROM ".$this->_getCodeTable();

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	function getMaxCode()
	{
		$query =	"SELECT code"
				." FROM ".$this->_getCodeTable()
				." WHERE code NOT LIKE '%a%'"
				." AND code NOT LIKE '%b%'"
				." AND code NOT LIKE '%c%'"
				." AND code NOT LIKE '%d%'"
				." AND code NOT LIKE '%e%'"
				." AND code NOT LIKE '%f%'"
				." AND code NOT LIKE '%g%'"
				." AND code NOT LIKE '%h%'"
				." AND code NOT LIKE '%i%'"
				." AND code NOT LIKE '%j%'"
				." AND code NOT LIKE '%k%'"
				." AND code NOT LIKE '%l%'"
				." AND code NOT LIKE '%m%'"
				." AND code NOT LIKE '%n%'"
				." AND code NOT LIKE '%o%'"
				." AND code NOT LIKE '%p%'"
				." AND code NOT LIKE '%q%'"
				." AND code NOT LIKE '%r%'"
				." AND code NOT LIKE '%s%'"
				." AND code NOT LIKE '%t%'"
				." AND code NOT LIKE '%u%'"
				." AND code NOT LIKE '%v%'"
				." AND code NOT LIKE '%z%'"
				." ORDER BY code DESC";

		list($res) = sql_fetch_row(sql_query($query));

		return (int)$res;
	}

	function getCodeNumberForTypeInGroup($id_code_group)
	{
		$query =	"SELECT used, unlimitedUse"
					." FROM ".$this->_getCodeTable()
					." WHERE idCodeGroup = '".$id_code_group."'";

		$result = sql_query($query);

		$res = array(	'unlimited' => 0,
					'not_used' => 0,
					'used' => 0);

		while(list($used, $unlimited) = sql_fetch_row($result))
		{
			if($unlimited == 1)
				$res['unlimited']++;
			elseif($used == 1)
				$res['used']++;
			else
				$res['not_used']++;
		}

		return $res;
	}

	function getGroupCodeByUse($id_code_group, $unlimited, $used, $not_used)
	{
		if($used && $not_used)
			$used_q = '0, 1';
		elseif($used)
			$used_q = '1';
		elseif($not_used)
			$used_q = '0';
		else
			$used_q = '-1';

		$res = array();

		$query =	"SELECT code"
					." FROM ".$this->_getCodeTable()
					." WHERE idCodeGroup = '".$id_code_group."'"
					." AND used IN (".$used_q.")"
					." AND unlimitedUse = 0";

		$result = sql_query($query);

		while(list($code) = sql_fetch_row($result))
			$res[] = $code;

		if($unlimited)
		{
			$query =	"SELECT code"
						." FROM ".$this->_getCodeTable()
						." WHERE idCodeGroup = '".$id_code_group."'"
						." AND unlimitedUse = 1";

			$result = sql_query($query);

			while(list($code) = sql_fetch_row($result))
				$res[] = $code;
		}

		return $res;
	}

	public function getAllCode()
	{
		$query =	"SELECT code"
					." FROM ".$this->_getCodeTable()
					." WHERE 1";

		$result = sql_query($query);
		$res = array();

		while(list($code) = sql_fetch_row($result))
			$res[] = $code;

		return $res;
	}
}
?>