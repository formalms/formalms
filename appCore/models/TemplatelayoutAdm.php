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

require_once(_base_.'/lib/lib.json.php');

class TemplatelayoutAdm extends Model {

	protected $db;
	protected $aclManager;
	protected $json;

	public function __construct() {
		$this->db = DbConn::getInstance();
		$this->aclManager = Docebo::user()->getAclManager();
		$this->json = new Services_JSON();
	}

	public function getPerm()
	{
		return array(	'view' => 'standard/view.png');
	}

	protected function _getParam(&$params, $paramName, $default = false) {
		$output = $default;
		if (is_object($params)) {
			if (property_exists($params, $paramName)) $output = $params->$paramName;
		} elseif (is_array($params)) {
			if (isset($params[$paramName])) $output = $params[$paramName];
		}
		return $output;
	}

	public function getTemplates($params) {
		//extract parameters
		$filter = $this->_getParam($params, "filter", "");
		$startIndex = $this->_getParam($params, "startIndex", 0);
		$results = $this->_getParam($params, "results", Get::sett('visuItem'));
		$sort = $this->_getParam($params, "sort", "name");
		$dir = $this->_getParam($params, "dir", "asc");

		//validate parameters
		$sort = strtolower((string)$sort);
		switch ($sort) {
			case "name":
			case "date_creation":
			case "last_modify": break;
			default: $sort = "name";
		}

		$dir = strtolower((string)$dir);
		if ($dir != "asc" && $dir != "desc") $dir = "asc";

		//compose query
		$query = "SELECT * FROM %adm_template ";
		if ($filter != "") $query .= " WHERE name LIKE '%".$filter."%' ";
		$query .= " ORDER BY ".$sort." ".$dir." ";
		$query .= " LIMIT ".(int)$startIndex.", ".(int)$results." ";

		//extract data
		$output = array();
		$res = $this->db->query($query);
		if ($res) while ($row = $this->db->fetch_obj($res)) $output[] = $row;

		//return data
		return $output;
	}


	public function getTotalTemplates($filter) {
		$output = false;
		$query = "SELECT COUNT(*) FROM %adm_template".($filter ? " WHERE name LIKE '%".$filter."%'" : "");
		$res = $this->db->query($query);
		if ($res) list($output) = $this->db->fetch_row($res);
		return $output;
	}


	public function getTemplateData($id) {
		$output = false;
		$query = "SELECT template_data FROM %adm_template WHERE id_template=".(int)$id;
		$res = $this->db->query($query);
		if ($res) {
			list($json_data) = $this->db->fetch_row($res);
			$decoded = $this->json->decode($json_data);
			//validate ...
			$output = $decoded;
		}
		return $output;
	}

	public function createTemplate($params) {
		$output = false;
		$query = "INSERT INTO %adm_template (name, date_creation, last_modify, template_data) VALUES "
			."('".$params->name."', NOW(), NOW(), '".$this->json->encode($params->template_data)."')";
		$res = $this->db->query($query);
		if ($res) {
			$output = $this->db->insert_id();
		}
		return $output;
	}



	public function updateTemplate($id, $params) {
		$query = "UPDATE %adm_template SET ";

		$name = $this->_getParam($params, "name", false);
		$data = $this->_getParam($params, "template_data", false);
		if ($name!==false || $data!==false) {
			$query .= " last_modify = NOW() ";
			if ($name !== false) $query .= ", name = '".$name."' ";
			if ($data !== false) $query .= ", template_data = '".$this->json->encode($data)."' ";
		} else
			return true; //no query to execute

		$query .= " WHERE id_template = ".(int)$id;
		
		$res = $this->db->query($query);
		if ($res) return true;
		return false;
	}



	public function deleteTemplate($id) {
		$output = false;
		if (is_numeric($id) && $id>0) {
			$query = "DELETE FROM %adm_template WHERE id_template=".(int)$id;
			$res = $this->db->query($query);
			$output = ($res ? true : false);
		}
		return $output;
	}

}

?>