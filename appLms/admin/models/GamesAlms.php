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

class GamesAlms extends Model {

	protected $db;

	public function __construct() {
		$this->db = DbConn::getInstance();
	}

	public function getPerm() {
		return array(
			'view' => 'standard/view.png',
			'add' => '',
			'mod' => '',
			'del' => '',
			'subscribe' => ''
		);
	}

	public function findAll($start_index, $results, $sort, $dir, $filter = false) {

		$sortable = array('title', 'description', 'type_of', 'start_date', 'end_date');
		$sortable = array_flip($sortable);

		$records = array();
		$qtxt = "SELECT c.id_game, title, description, start_date, end_date, type_of, id_resource, COUNT(ca.id_game) as access_entity "
			." FROM %lms_games AS c "
			." LEFT JOIN %lms_games_access AS ca ON (c.id_game = ca.id_game)"
			." WHERE 1 "
			.( !empty($filter['text']) ? " AND ( title LIKE '%".$filter['text']."%' OR description LIKE '%".$filter['text']."%' ) " : "" )
			.( !empty($filter['viewer']) ? " AND ca.idst IN ( ".implode(',', $filter['viewer'])." ) " : "" )
			." GROUP BY c.id_game"
			.( isset($sortable[$sort])
				? " ORDER BY ".$sort." ".( $dir == 'asc' ? 'ASC' : 'DESC' )." "
				: '' )
			.( $results != 0 ? " LIMIT ".(int)$start_index.", ".(int)$results : '' );
		$re = $this->db->query($qtxt);

		if(!$re) return $records;
		while($row = $this->db->fetch_array($re)) {

			$records[] = $row;
		}
		return $records;
	}

	public function findAllUnread($start_index, $results, $sort, $dir, $reader, $filter = false) {

		$sortable = array('title', 'description', 'type_of', 'start_date', 'end_date');
		$sortable = array_flip($sortable);

		$records = array();
		$qtxt = "SELECT c.id_game, title, description, start_date, end_date, type_of, id_resource, COUNT(ca.id_game) as access_entity "
			."	, ct.current_score, ct.max_score, ct.num_attempts, ct.status "
			." FROM ( %lms_games AS c "
			."	JOIN %lms_games_access AS ca ON (c.id_game = ca.id_game) ) "
			."	LEFT JOIN %lms_games_track AS ct ON (c.id_game = ct.idReference AND ct.idUser = ".(int)$reader."  )"
			." WHERE c.start_date <= '".date("Y-m-d")."'"
			."	AND c.end_date >= '".date("Y-m-d")."'"
			.( !empty($filter['text']) ? " AND ( title LIKE '%".$filter['text']."%' OR description LIKE '%".$filter['text']."%' ) " : "" )
			.( !empty($filter['viewer']) ? " AND ca.idst IN ( ".implode(',', $filter['viewer'])." ) " : "" )
			." GROUP BY c.id_game"
			.( isset($sortable[$sort])
				? " ORDER BY ".$sort." ".( $dir == 'asc' ? 'ASC' : 'DESC' )." "
				: '' )
			.( $results != 0 ? " LIMIT ".(int)$start_index.", ".(int)$results : '' );
		$re = $this->db->query($qtxt);

		if(!$re) return $records;
		while($row = $this->db->fetch_array($re)) {

			$records[] = $row;
		}
		return $records;
	}

	public function findAllReaded($start_index, $results, $sort, $dir, $reader, $filter = false) {

		$sortable = array('title', 'description', 'type_of', 'start_date', 'end_date');
		$sortable = array_flip($sortable);

		$records = array();
		$qtxt = "SELECT c.id_game, title, description, start_date, end_date, type_of, id_resource, COUNT(ca.id_game) as access_entity "
			."	, ct.current_score, ct.max_score, ct.num_attempts "
			." FROM ( %lms_games AS c "
			."	JOIN %lms_games_access AS ca ON (c.id_game = ca.id_game) ) "
			."	LEFT JOIN %lms_games_track AS ct ON (c.id_game = ct.idReference AND ct.idUser = ".(int)$reader." )"
			." WHERE c.end_date < '".date("Y-m-d")."'"
			.( !empty($filter['text']) ? " AND ( title LIKE '%".$filter['text']."%' OR description LIKE '%".$filter['text']."%' ) " : "" )
			.( !empty($filter['viewer']) ? " AND ca.idst IN ( ".implode(',', $filter['viewer'])." ) " : "" )
			." GROUP BY c.id_game"
			.( isset($sortable[$sort])
				? " ORDER BY ".$sort." ".( $dir == 'asc' ? 'ASC' : 'DESC' )." "
				: '' )
			.( $results != 0 ? " LIMIT ".(int)$start_index.", ".(int)$results : '' );
		$re = $this->db->query($qtxt);

		if(!$re) return $records;
		while($row = $this->db->fetch_array($re)) {

			$records[] = $row;
		}
		return $records;
	}

	public function findByPk($id_game, $viewer = false) {

		if(!empty($viewer)) {

			$qtxt = "SELECT c.id_game, title, description, start_date, end_date, type_of, id_resource, play_chance "
				." FROM %lms_games AS c "
				." LEFT JOIN %lms_games_access AS ca ON (c.id_game = ca.id_game)"
				." WHERE c.id_game = ".(int)$id_game." "
				." AND ca.idst IN ( ".implode(',', $viewer)." ) "
				." GROUP BY c.id_game";
		} else {

			$qtxt = "SELECT id_game, title, description, start_date, end_date, type_of, id_resource, play_chance "
				." FROM %lms_games "
				." WHERE id_game = ".(int)$id_game." ";
		}
		$re = $this->db->query($qtxt);
		if(!$re) return false;

		return $this->db->fetch_array($re);
	}

	public function total($filter = false) {

		$sortable = array('title', 'description');
		$sortable = array_flip($sortable);

		$results = array();
		$qtxt = "SELECT COUNT(*) "
			." FROM %lms_games "
			." WHERE 1 "
			.( $filter ? " AND ( title LIKE '%".$filter."%' OR description LIKE '%".$filter."%' ) " : "" );
		$re = $this->db->query($qtxt);
		if(!$re) return 0;
		list($total) = $this->db->fetch_row($re);

		return $total;
	}

	public function save($data) {

		if(!isset($data['id_game']) || $data['id_game'] == false) {
			// insert new
			$qtxt = "INSERT INTO %lms_games (title, description, start_date, end_date, type_of, play_chance, id_resource) "
				." VALUES ("
				." '".$data['title']."', "
				." '".$data['description']."', "
				." '".$data['start_date']."', "
				." '".$data['end_date']."', "
				." '".$data['type_of']."', "
				." '".$data['play_chance']."', "
				." ".(int)( isset($data['id_resource']) ? $data['id_resource'] : 0 )." "
				." )";
			$re = $this->db->query($qtxt);
			if(!$re) return false;

			return $this->db->insert_id();
		} else {
			//update one// insert new
			$qtxt = "UPDATE %lms_games "
				." SET ";
			if(isset($data['title'])) $qtxt .= " title = '".$data['title']."',";
			if(isset($data['description'])) $qtxt .= " description = '".$data['description']."',";
			if(isset($data['start_date'])) $qtxt .= " start_date = '".$data['start_date']."',";
			if(isset($data['end_date'])) $qtxt .= " end_date = '".$data['end_date']."',";
			if(isset($data['type_of'])) $qtxt .= " type_of = '".$data['type_of']."',";
			if(isset($data['play_chance'])) $qtxt .= " play_chance = '".$data['play_chance']."',";
			if(isset($data['id_resource'])) $qtxt .= " id_resource = '".$data['id_resource']."',";
			$qtxt = substr($qtxt, 0, -1);
			$qtxt .= " WHERE id_game = ".(int)$data['id_game']." ";
			$re = $this->db->query($qtxt);

			if(!$re) return false;
			return $data['id_game'];
		}
	}

	public function delByPk($id_game) {

		$qtxt = "DELETE FROM %lms_games_track "
			." WHERE idReference = ".(int)$id_game." ";
		if(!$this->db->query($qtxt)) return false;

		$qtxt = "DELETE FROM %lms_games_access "
			." WHERE id_game = ".(int)$id_game." ";
		if(!$this->db->query($qtxt)) return false;

		$qtxt = "DELETE FROM %lms_games "
			." WHERE id_game = ".(int)$id_game." ";
		if(!$this->db->query($qtxt)) return false;
		return true;
	}

	public function accessList($id_game) {

		$records = array();
		$qtxt = "SELECT idst "
			." FROM %lms_games_access "
			." WHERE id_game = ".(int)$id_game." ";
		$re = $this->db->query($qtxt);
		if(!$re) return $records;
		while($row = $this->db->fetch_array($re)) {

			$records[] = $row[0];
		}
		return $records;
	}

	public function updateAccessList($id_game, $old_selection, $new_selection) {

		$add_reader = array_diff($new_selection, $old_selection);
		$del_reader = array_diff($old_selection, $new_selection);

		$re = true;
		if(is_array($add_reader)) {

			while(list(, $idst) = each($add_reader)) {

				$query_insert = "INSERT INTO %lms_games_access ( id_game, idst ) VALUES ("
					." ".(int)$id_game.", "
					." ".(int)$idst." "
					.") ";
				$re &= $this->db->query($query_insert);
			}
		}
		if(is_array($del_reader)) {

			while(list(, $idst) = each($del_reader)) {

				$query_delete = "
				DELETE FROM %lms_games_access
				WHERE idst = ".(int)$idst." AND id_game = ".(int)$id_game." ";
				$re &= $this->db->query($query_delete);
			}
		}
		return $re;
	}
	
	public function getUserStandings($id_game, $id_user) {

		$records = array();
		$qtxt = "SELECT idReference, dateAttempt, firstAttempt, status, current_score, max_score, num_attempts "
			." FROM %lms_games_track AS ct "
			." WHERE idReference = ".(int)$id_game." "
			."	AND idUser = ".(int)$id_user." ";
		$re = $this->db->query($qtxt);
		if(!$re) return $records;
		$records = $this->db->fetch_assoc($re);
		
		return $records;
	}
	
	public function getStandings($id_game, $start_index = 0, $results = false) {

		$records = array();
		$qtxt = "SELECT u.idst, u.userid, u.firstname, u.lastname, ct.current_score, ct.max_score, ct.num_attempts "
			." FROM %lms_games_track AS ct JOIN %adm_user AS u ON ( ct.idUser = u.idst )"
			." WHERE idReference = ".(int)$id_game." "
			." ORDER BY ct.max_score"
			.( $results != 0 ? " LIMIT ".(int)$start_index.", ".(int)$results : '' );
		$re = $this->db->query($qtxt);
		
		if(!$re) return $records;
		while($row = $this->db->fetch_assoc($re)) {
			$records[] = $row;
		}
		return $records;
	}

	public function getStandingsChartData($id_game) {

		$records = array();
		$qtxt = "SELECT ct.max_score, COUNT(u.idst) AS score_weight "
			." FROM %lms_games_track AS ct JOIN %adm_user AS u ON ( ct.idUser = u.idst )"
			." WHERE idReference = ".(int)$id_game." "
			." GROUP BY ct.max_score"
			." ORDER BY ct.max_score ASC";
		$re = $this->db->query($qtxt);
		
		if(!$re) return $records;
		while($row = $this->db->fetch_assoc($re)) {
			$records[] = array(
				'x_axis' => $row['max_score'],
				'y_axis' => $row['score_weight'],
			);
		}
		return $records;
	}
}
