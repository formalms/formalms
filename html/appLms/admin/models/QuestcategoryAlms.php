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

Class QuestcategoryAlms extends Model {

	protected $db;

	public function __construct() {
		$this->db = DbConn::getInstance();
	}

	public function getPerm() {
		return array(
			'view' => 'standard/view.png',
			'add' => '',
			'mod' => '',
			'del' => ''
		);
	}


	public function getQuestCategoriesList($pagination, $filter) {
		//validate pagination data
		if (!is_array($pagination)) $pagination = array();
		$_startIndex = (isset($pagination['startIndex']) ? (int)$pagination['startIndex'] : 0);
		$_results = (isset($pagination['results']) ? (int)$pagination['results'] : Get::sett('visuItem', 25));
		$_sort = 'name';
		$_dir = 'ASC';

		if (isset($pagination['dir'])) {
			switch (strtoupper($pagination['dir'])) {
				case 'YUI-DT-ASC': $_dir = 'ASC'; break;
				case 'YUI-DT-DESC': $_dir = 'DESC'; break;
				case 'ASC': $_dir = 'ASC'; break;
				case 'DESC': $_dir = 'DESC'; break;
			}
		}

		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'description': $_sort = 'description'; break;
			}
		}

		//validate filter data and create query conditions if any
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " WHERE (name LIKE '%".$filter['text']."%' "
					." OR textof LIKE '%".$filter['text']."%') ";
		}

		//mount query
		$query = "SELECT idCategory, name, textof as description, author "
			." FROM %lms_quest_category ".$_filter
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

	public function getQuestCategoriesTotal($filter) {
		//validate filter data and abjust query
		$_filter = "";
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$_filter .= " WHERE (name LIKE '%".$filter['text']."%' "
					." OR textof LIKE '%".$filter['text']."%') ";
		}

		//mount query
		$query = "SELECT COUNT(*) FROM %lms_quest_category ".$_filter;
		$res = $this->db->query($query);

		//extract records from database
		$output = false;
		if ($res) {
			list($output) = $this->db->fetch_row($res);
		}

		return $output;
	}


	public function getQuestCategoryInfo($id) {
		if ($id <= 0) return false;
		$output = false;
		$query = "SELECT idCategory, name, textof as description "
			." FROM %lms_quest_category WHERE idCategory=".(int)$id;
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res)>0) {
			$info = $this->db->fetch_obj($res);
			$output = $info;
		}
		return $output;
	}


	public function getUsedInTests($id) {
		if (is_array($id)) {
			if (count($id) <= 0) return false;
			$output = false;
			$query = "SELECT idCategory, COUNT(*) FROM %lms_testquest WHERE idCategory IN (".implode(",", $id).")";
			$res = $this->db->query($query);
			if ($res) {
				$output = array();
				foreach ($id as $id_category) $output[$id_category] = 0;
				while (list($id_category, $used_test) = $this->db->fetch_row($res)) {
					$output[$id_category] = $used_test;
				}
			}
			return $output;
		}

		$output = false;
		$query = "SELECT COUNT(*) FROM %lms_testquest WHERE idCategory = ".(int)$id;
		$res = $this->db->query($query);
		if ($res) {
			list($used_test) = $this->db->fetch_row($res);
			$output = $used_test;
		}
		return $output;
	}


	public function getUsedInPolls($id) {
		if (is_array($id)) {
			if (count($id) <= 0) return false;
			$output = false;
			$query = "SELECT id_category, COUNT(*) FROM %lms_pollquest WHERE id_category IN (".implode(",", $id).")";
			$res = $this->db->query($query);
			if ($res) {
				$output = array();
				foreach ($id as $id_category) $output[$id_category] = 0;
				while (list($id_category, $used_test) = $this->db->fetch_row($res)) {
					$output[$id_category] = $used_test;
				}
			}
			return $output;
		}

		$output = false;
		$query = "SELECT COUNT(*) FROM %lms_pollquest WHERE id_category = ".(int)$id;
		$res = $this->db->query($query);
		if ($res) {
			list($used_test) = $this->db->fetch_row($res);
			$output = $used_test;
		}
		return $output;
	}


	public function deleteQuestCategory($id) {
		if ($id <= 0) return false;
		$query = "DELETE FROM %lms_quest_category WHERE idCategory = ".(int)$id;
		$res = $this->db->query($query);
		//if (!$res);
		return $res ? true : false;
	}


	public function createQuestCategory($info) {
		if (is_array($info)) $info = Util::arrayToObject($info);
		if (!is_object($info)) return false;
		if (!property_exists($info, 'name')) return false;
		if (!is_string($info->name)) return false;

		$output = false;
		$description = property_exists($info, 'description') ? $info->description : "";
		$query = "INSERT INTO %lms_quest_category (name, textof) "
			." VALUES ('".$info->name."', '".$description."')";
		$res = $this->db->query($query);
		if ($res) {
			$output = $this->db->insert_id();
		}
		return $output;
	}


	public function editQuestCategory($id, $info) {
		if ($id <= 0) return false;
		if (is_array($info)) $info = Util::arrayToObject($info);
		if (!is_object($info)) return false;
		if (!property_exists($info, 'name')) return false;
		if (!is_string($info->name)) return false;

		$description = property_exists($info, 'description') ? $info->description : "";
		$query = "UPDATE %lms_quest_category SET "
			." name = '".$info->name."', textof = '".$description."' "
			." WHERE idCategory = ".(int)$id;
		$res = $this->db->query($query);
		return $res ? true : false;
	}

}


?>