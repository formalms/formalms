<?php defined("IN_FORMA") or die("Direct access is forbidden");

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

define('LABEL_ID_COMMON', 1);
define('LABEL_LANG_CODE', 2);
define('LABEL_TITLE', 3);
define('LABEL_DESCRIPTION', 4);
define('LABEL_FILE', 5);
define('LABEL_SEQUENCE', 6);

class LabelAlms extends Model
{
	protected $db;
	protected $acl_man;

	public function __construct()
	{
		$this->db = DbConn::getInstance();
		$this->acl_man = Docebo::user()->getAclManager();
	}

	public function getPerm()
	{
		return array(
			'view' => 'standard/view.png',
			'mod' => 'standard/edit.png'
		);
	}

	public function getLabels($start_index, $results, $sort, $dir)
	{
		$query =	"SELECT id_common_label, lang_code, title, description, file_name, sequence"
					." FROM %lms_label"
					." WHERE lang_code = '".getLanguage()."'"
					." ORDER BY sequence " ;
		$result = sql_query($query);

		$res = array();
		while (list($id_common_label, $lang_code, $title, $description, $file, $sequence) = sql_fetch_row($result)) {
			$res[$id_common_label][LABEL_ID_COMMON] = $id_common_label;
			$res[$id_common_label][LABEL_LANG_CODE] = $lang_code;
			$res[$id_common_label][LABEL_TITLE] = $title;
			$res[$id_common_label][LABEL_DESCRIPTION] = $description;
			$res[$id_common_label][LABEL_FILE] = $file;
			$res[$id_common_label][LABEL_SEQUENCE] = $sequence;
		}

		return $res;
	}

	public function getTotalLabelsCount()
	{
		$query =	"SELECT DISTINCT(id_common_label)"
					." FROM %lms_label"
					." WHERE lang_code = '".getLanguage()."'";

		$res = sql_num_rows(sql_query($query));

		return $res;
	}

	public function getNewIdCommon()
	{
		$query =	"SELECT MAX(id_common_label)"
					." FROM %lms_label";

		$result = sql_query($query);

		if(sql_num_rows($result))
		{
			list($res) = sql_fetch_row($result);
			$res++;
			return $res;
		}

		return 1;
	}

	public function insertLabel($id_common_label, $lang_code, $title, $description, $file_name)
	{
		$query =	"INSERT INTO %lms_label"
					." (id_common_label, lang_code, title, description, file_name)"
					." VALUES (".$id_common_label.", '".$lang_code."', '".$title."', '".$description."', '".$file_name."')";
		return sql_query($query);
	}

	public function move_up($id_common_label) {

		list($seq) = sql_fetch_row(sql_query("SELECT sequence FROM %lms_label WHERE id_common_label = ".(int)$id_common_label.""));

		//move up
		$this->db->start_transaction();

		$re = sql_query("UPDATE %lms_label SET sequence = ".(int)$seq."
		WHERE sequence = ".(int)($seq - 1)."");
		if(!$re) return false;

		$re = sql_query("UPDATE %lms_label SET sequence = sequence - 1
		WHERE id_common_label = ".(int)$id_common_label."");
		if(!$re) {
			$this->db->rollback();
			return false;
		}
		$this->db->commit();

		return true;
	}

	public function move_down($id_common_label) {

		list($seq) = sql_fetch_row(sql_query("SELECT sequence FROM %lms_label WHERE id_common_label = ".(int)$id_common_label.""));

		//move up
		$this->db->start_transaction();

		$re = sql_query("UPDATE %lms_label SET sequence = ".(int)$seq."
		WHERE sequence = ".(int)($seq + 1)."");
		if(!$re) return false;

		$re = sql_query("UPDATE %lms_label SET sequence = sequence + 1
		WHERE id_common_label = ".(int)$id_common_label."");
		if(!$re) {
			$this->db->rollback();
			return false;
		}
		$this->db->commit();

		return true;
	}

	public function getLabelInfo($id_common_label)
	{
		$query =	"SELECT lang_code, title, description, file_name"
					." FROM %lms_label"
					." WHERE id_common_label = ".(int)$id_common_label;

		$result = sql_query($query);

		$res = array();

		while (list($lang_code, $title, $description, $file) = sql_fetch_row($result))
		{
			$res[$lang_code][LABEL_TITLE] = $title;
			$res[$lang_code][LABEL_DESCRIPTION] = $description;
			$res[$lang_code][LABEL_FILE] = $file;
		}

		return $res;
	}

	public function updateLabel($id_common_label, $lang_code, $title, $description, $file_name)
	{
		$query =	"UPDATE %lms_label"
					." SET title = '".$title."',"
					." description = '".$description."',"
					." file_name = '".$file_name."'"
					." WHERE id_common_label = ".$id_common_label
					." AND lang_code = '".$lang_code."'";

		return sql_query($query);
	}

	private function toggleLabelAssociation($id_common_label)
	{
		$query =	"DELETE FROM %lms_label_course"
					." WHERE id_common_label = ".(int)$id_common_label;

		return sql_query($query);
	}

	public function delLabel($id_common_label)
	{
		if(!$this->toggleLabelAssociation($id_common_label))
			return false;

		$file_name = $this->getLabelFile($id_common_label);
		$path = '/appLms/label/';

		require_once(_base_.'/lib/lib.upload.php');

		if($file_name !== '' && sl_file_exists($path.$file_name))
		{
			sl_open_fileoperations();
			sl_unlink($path.$file_name);
			sl_close_fileoperations();
		}

		$query =	"DELETE FROM %lms_label"
					." WHERE id_common_label = ".(int)$id_common_label;

		return sql_query($query);
	}

	public function getLabelFile($id_common_label)
	{
		$query =	"SELECT file_name"
					." FROM %lms_label"
					." WHERE id_common_label = ".(int)$id_common_label;

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getLabelFromDropdown($mod_course = false)
	{
		$query =	"SELECT id_common_label, title"
					." FROM %lms_label"
					." WHERE lang_code = '".getLanguage()."'"
					." ORDER BY title";

		$result = sql_query($query);
		if($mod_course)
			$res = array('0' => Lang::t('_NOT_ASSIGNED', 'label'));
		else
			$res = array('0' => Lang::t('_ALL', 'label'));

		while(list($id_common_label, $title) = sql_fetch_row($result))
			$res[$id_common_label] = $title;

		return $res;
	}

	public function getCourseLabel($id_course)
	{
		$query =	"SELECT id_common_label"
					." FROM %lms_label_course"
					." WHERE id_course = ".(int)$id_course;

		list($id_common_label) = sql_fetch_row(sql_query($query));

		return $id_common_label;
	}
	
	public function clearCourseLabel($id_course)
	{
		if (is_numeric($id_course)) $id_course = array( (int)$id_course );
		if (!is_array($id_course)) return false;
		if (empty($id_course)) return true;

		$query =	"DELETE FROM %lms_label_course"
			." WHERE id_course IN (".implode(",", $id_course).")";

		return sql_query($query);
	}

	public function associateLabelToCourse($id_common_label, $id_course)
	{
		if($this->clearCourseLabel($id_course))
		{
			$query =	"INSERT INTO %lms_label_course"
						." (id_common_label, id_course)"
						." VALUES ('".$id_common_label."', '".$id_course."')";

			return sql_query($query);
		}

		return false;
	}

	public function getLabelForUser($id_user)
	{
		$query =	"SELECT l.id_common_label, l.title, l.description, l.file_name"
					." FROM %lms_label AS l"
					." JOIN %lms_label_course AS lc ON lc.id_common_label = l.id_common_label"
					." JOIN %lms_courseuser AS cu ON cu.idCourse = lc.id_course"
					." WHERE cu.idUser = ".(int)$id_user." AND l.lang_code = '".Lang::get()."' "
					." GROUP BY l.id_common_label"
					." ORDER BY l.title";

		$result = sql_query($query);
		$res = array();

		$res['0']['title'] = Lang::t('_ALL', 'label');
		$res['0']['description'] = Lang::t('_ALL_DESCRIPTION', 'label');
		$res['0']['image'] = '';

		while(list($id_common_label, $title, $description, $file_name) = sql_fetch_row($result))
		{
			$res[$id_common_label]['title'] = $title;
			$res[$id_common_label]['description'] = $description;
			$res[$id_common_label]['image'] = $file_name;
		}

		return $res;
	}

	public function getDropdownLabelForUser($id_user)
	{
		$query =	"SELECT l.id_common_label, l.title"
					." FROM %lms_label AS l"
					." JOIN %lms_label_course AS lc ON lc.id_common_label = l.id_common_label"
					." JOIN %lms_courseuser AS cu ON cu.idCourse = lc.id_course"
					." WHERE cu.idUser = ".(int)$id_user." AND l.lang_code = '".Lang::get()."' "
					." GROUP BY l.id_common_label"
					." ORDER BY l.title";

		$result = sql_query($query);
		$res = array(	'-2' => Lang::t('_SELECT', 'label'),
						'0' => Lang::t('_ALL', 'label'));

		while(list($id_common_label, $title) = sql_fetch_row($result))
			$res[$id_common_label] = $title;

		return $res;
	}
}
?>