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

class CommunicationAlms extends Model {

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

	public function findAll($start_index, $results, $sort, $dir, $filter = false, $id_category = false, $show_descendants = false) {

		$sortable = array('title', 'description', 'type_of', 'publish_date');
		$sortable = array_flip($sortable);

		$_categories = array();
		if ($id_category !== false) {
			if ($show_descendants) {
				$_categories = $this->getSubCategories($id_category);
			}
			$_categories[] = (int)$id_category;
		}

		$records = array();
		$qtxt = "SELECT c.id_comm, title, description, publish_date, type_of, id_resource, COUNT(ca.id_comm) as access_entity "
			." FROM %lms_communication AS c "
			." LEFT JOIN %lms_communication_access AS ca ON (c.id_comm = ca.id_comm)"
			." WHERE 1 "
			.( !empty($filter['text']) ? " AND ( title LIKE '%".$filter['text']."%' OR description LIKE '%".$filter['text']."%' ) " : "" )
			.( !empty($filter['viewer']) ? " AND ca.idst IN ( ".implode(',', $filter['viewer'])." ) " : "" )
			.( !empty($_categories) ? " AND c.id_category IN (".implode(",", $_categories).") " : "")
			." GROUP BY c.id_comm"
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

		$sortable = array('title', 'description', 'type_of', 'publish_date');
		$sortable = array_flip($sortable);

		$records = array();
		$qtxt = "SELECT c.id_comm, title, description, publish_date, type_of, id_resource, COUNT(ca.id_comm) as access_entity "
			." FROM ( %lms_communication AS c "
			."	JOIN %lms_communication_access AS ca ON (c.id_comm = ca.id_comm) ) "
			."	LEFT JOIN %lms_communication_track AS ct ON (c.id_comm = ct.idReference AND ct.idUser = ".(int)$reader."  )"
			." WHERE ( ct.status = 'failed' OR  ct.status = 'ab-initio' OR  ct.status = 'attempted' OR ct.idReference IS NULL ) "
			.( !empty($filter['text']) ? " AND ( title LIKE '%".$filter['text']."%' OR description LIKE '%".$filter['text']."%' ) " : "" )
			.( !empty($filter['viewer']) ? " AND ca.idst IN ( ".implode(',', $filter['viewer'])." ) " : "" )
			." GROUP BY c.id_comm"
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

		$sortable = array('title', 'description', 'type_of', 'publish_date');
		$sortable = array_flip($sortable);

		$records = array();
		$qtxt = "SELECT c.id_comm, title, description, publish_date, type_of, id_resource, COUNT(ca.id_comm) as access_entity "
			." FROM ( %lms_communication AS c "
			."	JOIN %lms_communication_access AS ca ON (c.id_comm = ca.id_comm) ) "
			."	JOIN %lms_communication_track AS ct ON (c.id_comm = ct.idReference AND ct.idUser = ".(int)$reader."  )"
			." WHERE (  ct.status = 'passed' OR  ct.status = 'completed' ) "
			.( !empty($filter['text']) ? " AND ( title LIKE '%".$filter['text']."%' OR description LIKE '%".$filter['text']."%' ) " : "" )
			.( !empty($filter['viewer']) ? " AND ca.idst IN ( ".implode(',', $filter['viewer'])." ) " : "" )
			." GROUP BY c.id_comm"
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

	public function findByPk($id_comm, $viewer = false) {

		if(!empty($viewer)) {
			
			$qtxt = "SELECT c.id_comm, title, description, publish_date, type_of, id_resource, c.id_category, c.id_course "
				." FROM %lms_communication AS c "
				." LEFT JOIN %lms_communication_access AS ca ON (c.id_comm = ca.id_comm)"
				." WHERE c.id_comm = ".(int)$id_comm." "
				." AND ca.idst IN ( ".implode(',', $viewer)." ) "
				." GROUP BY c.id_comm";
		} else {
			
			$qtxt = "SELECT id_comm, title, description, publish_date, type_of, id_resource, id_category, id_course "
				." FROM %lms_communication "
				." WHERE id_comm = ".(int)$id_comm." ";
		}
		$re = $this->db->query($qtxt);
		if(!$re) return false;
		
		return $this->db->fetch_array($re);
	}

	public function total($filter = false, $id_category = false, $show_descendants = false) {

		$sortable = array('title', 'description');
		$sortable = array_flip($sortable);

		//validate filter
		$filter_text = "";
		if (is_string($filter)) $filter_text = $filter;
		if (is_array($filter) && isset($filter['text'])) $fitler_text = $filter['text'];

		$_categories = array();
		if ($id_category !== false) {
			if ($show_descendants) {
				$_categories = $this->getSubCategories($id_category);
			}
			$_categories[] = (int)$id_category;
		}

		//mount and execute query
		$results = array();
		$qtxt = "SELECT COUNT(*) "
			." FROM %lms_communication "
			." WHERE 1 "
			.( !empty($_categories) ? " AND id_category IN (".implode(",", $_categories).") " : "")
			.( $filter_text ? " AND ( title LIKE '%".$filter_text."%' OR description LIKE '%".$filter_text."%' ) " : "" );
		$re = $this->db->query($qtxt);
		if(!$re) return 0;
		list($total) = $this->db->fetch_row($re);

		return $total;
	}

	public function save($data) {

		if(!isset($data['id_comm']) || $data['id_comm'] == false) {
			// insert new
			$qtxt = "INSERT INTO %lms_communication (title, description, publish_date, type_of, id_resource, id_category, id_course) "
				." VALUES ("
				." '".$data['title']."', "
				." '".$data['description']."', "
				." '".$data['publish_date']."', "
				." '".$data['type_of']."', "
				." ".(int)( isset($data['id_resource']) ? $data['id_resource'] : 0 ).", "
				." ".(int)( isset($data['id_category']) ? $data['id_category'] : 0 ).", "
				." ".(int)( isset($data['id_course']) ? $data['id_course'] : 0 )." "
				." )";
			$re = $this->db->query($qtxt);
			if(!$re) return false;

			return $this->db->insert_id();
		} else {
			//update one// insert new
			$qtxt = "UPDATE %lms_communication "
				." SET ";
			if(isset($data['title'])) $qtxt .= " title = '".$data['title']."',";
			if(isset($data['description'])) $qtxt .= " description = '".$data['description']."',";
			if(isset($data['publish_date'])) $qtxt .= " publish_date = '".$data['publish_date']."',";
			if(isset($data['type_of'])) $qtxt .= " type_of = '".$data['type_of']."',";
			if(isset($data['id_resource'])) $qtxt .= " id_resource = '".$data['id_resource']."',";
			if(isset($data['id_category'])) $qtxt .= " id_category = '".$data['id_category']."',";
			if(isset($data['id_course'])) $qtxt .= " id_course = '".$data['id_course']."',";
			$qtxt = substr($qtxt, 0, -1);
			$qtxt .= " WHERE id_comm = ".(int)$data['id_comm']." ";
			$re = $this->db->query($qtxt);
			if(!$re) return false;
			return $data['id_comm'];
		}
	}

	public function delByPk($id_comm) {
		
		$qtxt = "DELETE FROM %lms_communication_track "
			." WHERE idReference = ".(int)$id_comm." ";
		if(!$this->db->query($qtxt)) return false;

		$qtxt = "DELETE FROM %lms_communication_access "
			." WHERE id_comm = ".(int)$id_comm." ";
		if(!$this->db->query($qtxt)) return false;

		$qtxt = "DELETE FROM %lms_communication "
			." WHERE id_comm = ".(int)$id_comm." ";
		if(!$this->db->query($qtxt)) return false;
		return true;
	}

	public function accessList($id_comm) {

		$records = array();
		$qtxt = "SELECT idst "
			." FROM %lms_communication_access "
			." WHERE id_comm = ".(int)$id_comm." ";
		$re = $this->db->query($qtxt);
		if(!$re) return $records;
		while($row = $this->db->fetch_array($re)) {

			$records[] = $row[0];
		}
		return $records;
	}

	public function updateAccessList($id_comm, $old_selection, $new_selection) {
		
		$add_reader = array_diff($new_selection, $old_selection);
		$del_reader = array_diff($old_selection, $new_selection);
		
		$re = true;
		if(is_array($add_reader)) {

			while(list(, $idst) = each($add_reader)) {

				$query_insert = "INSERT INTO %lms_communication_access ( id_comm, idst ) VALUES ("
					." ".(int)$id_comm.", "
					." ".(int)$idst." "
					.") ";
				$re &= $this->db->query($query_insert);
			}
		}
		if(is_array($del_reader)) {

			while(list(, $idst) = each($del_reader)) {

				$query_delete = "
				DELETE FROM %lms_communication_access
				WHERE idst = ".(int)$idst." AND id_comm = ".(int)$id_comm." ";
				$re &= $this->db->query($query_delete);
			}
		}
		return $re;
	}

	public function markAsRead($id_comm, $id_user) {

		$query_insert = "INSERT INTO %lms_communication_track "
			."( `idReference`, `idUser`, `idTrack`, `objectType`, `firstAttempt`, `dateAttempt`, `status` ) VALUES ("
			." ".(int)$id_comm.", "
			." ".(int)$id_user.", "
			." 0, "
			." 'none', "
			." '".date("Y-m-d H:i:s")."', "
			." '".date("Y-m-d H:i:s")."', "
			." 'completed'"
			.")";
		return $this->db->query($query_insert);
	}



	//--- tree functions ---------------------------------------------------------


	public function getCategories($id_parent, $language = false) {
		$lang_code = ($language == false ? getLanguage() : $language);
		$query = "SELECT	t1.id_category, t2.translation, t1.level, t1.iLeft, t1.iRight "
			." FROM %lms_communication_category AS t1 LEFT JOIN %lms_communication_category_lang AS t2 "
			." ON (t1.id_category = t2.id_category AND t2.lang_code = '".$lang_code."' ) "
			." WHERE t1.id_parent = '".(int)$id_parent."' ORDER BY t2.translation";
		$res = $this->db->query($query);
		if (!$res) return false;

		//count competences contained in each extracted node
		$count_competences = $this->getCategoryCommunicationsCount();

		$output = array();
		while(list($id, $translation, $level, $left, $right) = $this->db->fetch_row($res)) {
			$label = $translation;
			$is_leaf = ($right-$left) == 1;
			$count = (int)(($right-$left-1)/2);
			$style = false;

			//set node for output
			$output[$id] = array(
				'id' => $id,
				'label' => $label,
				'is_leaf' => $is_leaf,
				'count_content' => $count,
				'count_objects' => (isset($count_competences[$id]) ? (int)$count_competences[$id] : 0),
				'style' => $style
			);
		}

		return array_values($output);
	}


	/*
	 * returns an ordered list of ids (like a path)
	 */
	public function getOpenedCategories($node_id, $language = false) {
		$folders = array(0);
		if (!$language) $language = getLanguage();
		if ($node_id <= 0) return $folders;
		list($ileft, $iright) = $this->getCategoryLimits($node_id);
		$query = "SELECT id_category FROM %lms_communication_category "
			." WHERE iLeft<=".$ileft." AND iRight>=".$iright." AND id_category>0 ORDER BY iLeft";
		$res = $this->db->query($query);
		if ($res) {
			while (list($id_org) = $this->db->fetch_row($res)) { $folders[] = (int)$id_org; }
			return  $folders;
		} else
			return false;
	}



	public function getInitialCategories($node_id, $language = false) {
		$results = array();

		$folders = $this->getOpenedCategories($node_id);
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

			$children = $this->getCategories($folder, $language);
			foreach ($children as $child) {
				$ref[] = array(
					'node' => array(
						'id' => $child['id'],
						'label' => $child['label'],
						'is_leaf' => $child['is_leaf'],
						'count_content' => $child['count_content'],
						'count_objects' => $child['count_objects'],
						'options' => array(),
						'style' => false
					)
				);
			}
		}

		return $results;
	}


	public function getCategoryCommunicationsCount() {
		$output = array();
		$query = "SELECT id_category, COUNT(*) FROM %lms_communication GROUP BY id_category";
		$res = $this->db->query($query);
		while (list($id_category, $count) = $this->db->fetch_row($res)) {
			$output[$id_category] = (int)$count;
		}
		return $output;
	}


	public function getCategoryLimits($id_category) {
		$row = false;
		if ($id_category <= 0) {
			$query = "SELECT MIN(iLeft), MAX(iRight), 0 FROM %lms_communication_category";
			$res = $this->db->query($query);
			$row = $this->db->fetch_row($res);
			if (is_array($row)) {
				$row[0]--;
				$row[1]++;
			}
		} else {
			$query = "SELECT iLeft, iRight, level FROM %lms_communication_category WHERE id_category=".(int)$id_category;
			$res = $this->db->query($query);
			$row = $this->db->fetch_row($res);
		}
		return $row;
	}

	
	public function getSubCategories($id_category) {
		list($left, $right, $level) = $this->getCategoryLimits($id_category);
		$query = "SELECT id_category FROM %lms_communication_category "
			." WHERE iLeft >= ".(int)$left." AND iRight <= ".(int)$right;
		$res = $this->db->query($query);
		$output = array();
		if ($id_category <= 0) $output[] = 0;
		if ($res) {
			while (list($sub) = $this->db->fetch_row($res))
				$output[] = $sub;
		}
		return $output;
	}


	public function getCategoryInfo($id_category) {
		//extract competence data
		$query = "SELECT * FROM %lms_communication_category "
			." WHERE id_category = ".(int)$id_category;
		$res = $this->db->query($query);
		$output = $this->db->fetch_obj($res);

		//initialize languages array
		$lang_codes = Docebo::langManager()->getAllLangCode();
		$langs = array();
		for ($i=0; $i<count($lang_codes); $i++) {
			$langs[$lang_codes[$i]] = array(
				'name' => '',
				'description' => ''
			);
		}

		//extract languages from database
		$query = "SELECT * FROM %lms_communication_category_lang "
			." WHERE id_category = ".(int)$id_category;
		$res = $this->db->query($query);
		while ($obj = $this->db->fetch_obj($res)) {
			if (in_array($obj->lang_code, $lang_codes)) {
				$langs[$obj->lang_code]['name'] = $obj->translation;
				$langs[$obj->lang_code]['description'] = "";//$obj->description;
			}
		}

		$output->langs = $langs;
		return $output;
	}


	public function createCategory($id_parent, $langs) {
		if (!is_array($langs) || $id_parent < 0) return false;

		$output = false;
		if (is_array($langs)) {

			//adjust iLeft and iRight values in the categories tree
			list($left, $right, $level) = $this->getCategoryLimits($id_parent);

			//updating left limits
			$query = "UPDATE %lms_communication_category SET iRight=iRight+2 WHERE iRight>=".$right;
			$rsl = $this->db->query($query);
			//TO DO: handle error case (if !$rs ... )

			//updating right limits
			$query = "UPDATE %lms_communication_category SET iLeft=iLeft+2 WHERE iLeft>=".$right;
			$rsr = $this->db->query($query);
			//TO DO: handle error case (if !$rs ... )

			//insert node in the table, with newly calculated iLeft and iRight
			$query = "INSERT INTO %lms_communication_category (id_category, id_parent, level, iLeft, iRight) VALUES "
				."(NULL, '".(int)$id_parent."', '".((int)$level + 1)."', ".(int)$right.", ".((int)$right + 1).")";
			$res = $this->db->query($query);

			//if node has been correctly inserted then ...
			if ($res) {
				$id = $this->db->insert_id();

				//insert languages in database
				$conditions = array();
				foreach ($langs as $lang_code => $translation) { //TO DO: check if lang_code exists ...
					$name = $translation['name'];
					//$description = $translation['description'];
					$conditions[] = "(".(int)$id.", '".$lang_code."', '".$name."')";//, '".$description."')";
				}
				$query = "INSERT INTO %lms_communication_category_lang (id_category, lang_code, translation) "
					." VALUES ".implode(",", $conditions);
				$res = $this->db->query($query);
				if ($res) $output = $id;
			} else {
				$output = false;
			}
		}

		return $output;
	}


	public function updateCategory($id_category, $langs) {
		$output = false;

		if ($id_category > 0) {

			$prev_lang = array();
			$re = $this->db->query("SELECT lang_code FROM %lms_communication_category_lang WHERE id_category = ".(int)$id_category);
			while(list($lang_code) = $this->db->fetch_row($re)) {
				$prev_lang[$lang_code] = $lang_code;
			}
			//insert languages in database
			foreach ($langs as $lang_code => $translation) {
				$name = $translation['name'];
				$description = $translation['description'];

				if(isset($prev_lang[$lang_code])) {
					$query = "UPDATE %lms_communication_category_lang "
						." SET translation = '".$name."' "//, description = '".$description."' "
						." WHERE id_category = ".(int)$id_category." AND lang_code = '".$lang_code."'";
					$res = $this->db->query($query);
				} else {
					$query = "INSERT INTO %lms_communication_category_lang "
						." (id_category, lang_code, translation) VALUES "
						." (".(int)$id_category.", '".$lang_code."', '".$name."') ";
					$res = $this->db->query($query);
				}
			}
			$output = true; //TO DO: improve error detection in queries ...
		}

		return $output;
	}


	public function deleteCategory($id_category) {
		if ($id_category <= 0) return false;

		list($left, $right, $level) = $this->getCategoryLimits($id_category);

		//we are allowed to delete only leaf folder nodes with no competences
		if (($right - $left) > 1 ) return false;
		if ($this->getCountCommunications($id_category) > 0) return false;


		//delete node and then update tree iLefts and iRights
		$query = "DELETE FROM %lms_communication_category WHERE id_category=".(int)$id_category;
		$res = $this->db->query($query);
		if ($res) {
			//update indexes
			$shift = 2;
			$query = "UPDATE %lms_communication_category SET iLeft=iLeft-".$shift." WHERE iLeft>=".$left;
			$res = $this->db->query( $query );
			$query = "UPDATE %lms_communication_category SET iRight=iRight-".$shift." WHERE iRight>=".$right;
			$res = $this->db->query( $query );

			//delete languages from DB
			$query = "DELETE FROM %lms_communication_category_lang WHERE id_category=".(int)$id_category;
			$res = $this->db->query($query);

			return true;
		} else
			return false;
	}


	protected function _shiftRL($from, $shift) {
		$query1 = "UPDATE %lms_communication_category SET iLeft = iLeft + ".$shift." WHERE iLeft >= ".$from;
		$query2 = "UPDATE %lms_communication_category SET iRight = iRight + ".$shift." WHERE iRight >= ".$from;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);
	}

	protected function _shiftRLSpecific($from, $to, $shift) {
		$query1 = "UPDATE %lms_communication_category SET iLeft = iLeft + ".$shift." WHERE iLeft >= ".$from." AND iRight <= ".$to;
		$query2 = "UPDATE %lms_communication_category SET iRight = iRight + ".$shift." WHERE iRight >= ".$from." AND iRight <= ".$to;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);
	}

	public function moveCategory($src_folder, $dest_folder) {
		if ($src_folder <= 0) return false;
		if ($dest_folder <= 0) return false;
		$output = true;//false;

		list($src_left, $src_right, $lvl_src) = $this->getCategoryLimits($src_folder);
		list($dest_left, $dest_right, $lvl_dest) = $this->getCategoryLimits($dest_folder);

		//dest folder is a son of the src ?
		if($src_left < $dest_left && $src_right > $dest_right) return $output;

		$dest_left = $dest_left + 1;
		$gap = $src_right - $src_left + 1;

		$this->_shiftRL($dest_left, $gap);
		if ($src_left >= $dest_left) {
			// this happen when the src has shiften too
			$src_left += $gap;
			$src_right += $gap;
		}

		// update level for descendants
		$lvl_gap = $lvl_dest - $lvl_src + 1;
		$query1 = "UPDATE %lms_communication_category SET id_parent = ".(int)$dest_folder." WHERE id_category = ".(int)$src_folder;
		$query2 = "UPDATE %lms_communication_category SET level = level + ".$lvl_gap." WHERE iLeft > ".$src_left." AND iRight < ".$src_right;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);

		// move the subtree
		$this->_shiftRLSpecific($src_left, $src_right, $dest_left - $src_left);

		// fix values from the gap created
		$this->_shiftRL($src_right + 1, -$gap);

		return $output;
	}



	public function getCountCommunications($id_category = false) {
		$query = "SELECT COUNT(*) FROM %lms_communication "
			.(is_numeric($id_category) ? " WHERE id_category = ".(int)$id_category : "");
		$res = $this->db->query($query);
		list($output) = $this->db->fetch_row($res);
		return $output;
	}


	public function getCategoryName($id_category, $language = false) {
		$lang_code = (!$language ? getLanguage() : $language);
		$output = '';
		$query = "SELECT translation FROM %lms_communication_category_lang "
			." WHERE id_category = ".(int)$id_category." AND lang_code = '".$lang_code."'";
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res) > 0) {
			list($name) = $this->db->fetch_row($res);
			$output = $name;
		}
		return $output;
	}

}
