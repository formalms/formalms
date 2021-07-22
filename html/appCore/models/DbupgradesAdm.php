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

class DbupgradesAdm extends Model {

	protected $db;

	protected $table;

	public function  __construct() {
		$this->db = DbConn::getInstance();
		$this->table = $GLOBALS['prefix_fw'].'_db_upgrades';
	}

	public function getPerm()
	{
		return array(	'view' => 'standard/view.png');
	}

	public function getDbUpgradesTotal($filter = false) {
		$query = "SELECT COUNT(*) "
			." FROM %adm_db_upgrades as d ";

		if ($filter) {
			$query .= " WHERE (d.script_name LIKE '%".$filter."%' "
				." OR d.script_description LIKE '%".$filter."%') ";
		}

		$res = $this->db->query($query);

		$output = false;
		if ($res) {
			list($total) = $this->db->fetch_row($res);
			$output = $total;
		}

		return $output;
	}
        

	public function getDbUpgradesList($pagination = array(), $filter = false) {
		if (!is_array($pagination)) $pagination = array();

		$startIndex = (isset($pagination['startIndex']) ? $pagination['startIndex'] : 0);
		$results = (isset($pagination['results']) ? $pagination['results'] : Get::sett('visuItem', 25));

		$dir = 'DESC';
		if (isset($pagination['dir'])) {
			$_pdir = str_replace('yui-dt-', '', strtolower($pagination['dir']));
			switch ($_pdir) {
				case 'asc': $dir = 'ASC'; break;
				case 'desc': $dir = 'DESC'; break;
				default: $dir = 'DESC';
			}
		}

		$sort = 'd.execution_date';
		if (isset($pagination['sort'])) {
			switch ($pagination['sort']) {
				case 'script_name': $sort = 'd.script_name'; break;
				case 'script_name': $sort = 'd.script_name'; break;
                                case 'script_description': $sort = 'd.script_description'; break;
                                case 'script_version': $sort = 'd.script_version'; break;
                                case 'core_version': $sort = 'd.core_version'; break;
				case 'creation_date': $sort = 'd.creation_date'; break;
				case 'execution_date': $sort = 'd.execution_date'; break;
			}
		}

		$query = "SELECT d.script_id, d.script_name, d.script_description, d.script_version, d.core_version, d.creation_date, d.execution_date"
			." FROM %adm_db_upgrades as d";

		if ($filter) {
			$query .= " WHERE (d.script_name LIKE '%".$filter."%' "
				." OR d.script_description LIKE '%".$filter."%') ";
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
        
}

?>