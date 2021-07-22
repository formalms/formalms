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


class KbAlms extends Model {

	protected $db;
	protected $json;
	private $_root_id=0;


	public function  __construct() {
		require_once(_base_.'/lib/lib.json.php');
		$this->db = DbConn::getInstance();
		$this->json = new Services_JSON();
	}

	public function getPerm() {
		return array(
			'view'	=> 'standard/view.png',
			'mod'		=> 'standard/edit.png'
		);
	}


	// --- Kb resources: ---------------------------------------------------------


	/**
	 * get the list of cathegorized resources
	 * @param int $folder_id
	 * @param string $lang
	 * @param int $start
	 * @param int $limit
	 * @param string $sort
	 * @param string $dir
   * @param string $where
   * @param string $search (advanced and partial search in name, desc. and tags)
	 * @param bool $count If true will return also the count of all records found
	 * @param bool $all_if_root If true won't filter by folder when root folder
	 *                          is selected.
	 * @param bool $show_what [all|categorized|uncategorized] show all resources
	 *                        or only all that have been / not been categorized.
	 * @return array With data array that contains all the fields information
	 *               plus the id_arr data with only the id of the resouces found.
	 */
	public function getResources($folder_id, $start=false, $limit=false, $sort=false, $dir=false, $where=false, $search=false, $count=false, $all_if_root=false, $show_what=true) {
		$res =array("data"=>array(), "id_arr"=>array(), "count"=>-1);

		$fields ="*";
		$qtxt ="SELECT ".$fields." FROM %lms_kb_res as kr ";

		if (!$all_if_root || $folder_id != $this->_root_id) {
			$qtxt.="JOIN %lms_kb_rel as rel
			ON (rel.res_id=kr.res_id AND rel.parent_id='".(int)$folder_id."'
			AND rel.rel_type='folder') ";
		}

		if (!empty($where)) $where = " WHERE ".$where;

		$_where = "";
    if (!empty($search)) {

			$_where ='(';

			$matches =array();
			preg_match_all("/[\\\\\"]([\\w\\s]+)[\\\\\"]|([\\S]+)/is", $search, $matches);
			$search_arr =$matches[0];
			//$res['matches']=$matches; $res['matches']['mm']=$search;

			$where_arr =array();
			foreach($search_arr as $val) {
				$s =trim($val, '\" '); //remove spaces or quotes
				if (!empty($s)) {
					$where_arr[]="(kr.r_name LIKE '%".$s."%' OR kr.r_desc LIKE '%".$s."%')";
				}
			}
			if (!empty($where_arr)) {
				$_where.='(';
				$_where.=implode(' AND ', $where_arr);
				$_where.=')';
			}

			//$search_arr =explode(" ", $search);
			$tag_search =array();
			foreach($search_arr as $kw) {
				if (strlen($kw) > 2) { // we only search words with a length > of 2
					$tag_search[]="'".trim($kw, '\" ')."'";  //remove spaces or quotes
				}
			}
			if (!empty($tag_search)) {
				// tf = tag filter
				$tf_qtxt ="SELECT tag.tag_id, tag.tag_name, rel.res_id FROM
					%lms_kb_tag as tag
					JOIN %lms_kb_rel as rel
					ON (rel.parent_id=tag.tag_id)
					WHERE tag_name IN (".implode(',', $tag_search).")
					AND rel.rel_type='tag'";

				$tf_q =$this->db->query($tf_qtxt);
				$found_by_tag =array();
				while($row = $this->db->fetch_array($tf_q)) {
					if (!in_array($row['res_id'], $found_by_tag)) {
						$found_by_tag[]=$row['res_id'];
					}
				}
				if (!empty($found_by_tag)) {
					$_where.=" OR kr.res_id IN (".implode(',', $found_by_tag).")";
				}
			}

			$_where.=')';
    }

		if (!empty($_where)) {
			if (!empty($where)) $where .= ' AND '.$_where.' ';
			else $where .= ' WHERE '.$_where.' ';
		}

		if ($show_what === true) {
			$show_what ='all';
		}

		if ($show_what == 'categorized' || $show_what == 'uncategorized') {
			$where .= (empty($where) ? ' WHERE ' : ' AND ').' kr.is_categorized=';
			$where .= ($show_what == 'categorized' ? 1 : 0).' ';
		}

		// we don't show parent objects when sub-items are categorized

		$where .= (empty($where) ? ' WHERE ' : ' AND ').' kr.sub_categorize < 1 ';

		$qtxt .= $where;

		if ($count) {
			$q =$this->db->query($qtxt);
			$res['count']=$this->db->num_rows($q);
		}

		if (!empty($sort) && !empty($dir)) {
			$qtxt.="ORDER BY ".$sort." ".$dir." ";
		}
		if (!empty($start) && !empty($limit)) {
			$qtxt.="LIMIT ".$start.",".$limit;
		}

		//$res['qtxt']=$qtxt;
		$q =$this->db->query($qtxt);

		if(!$q) return $res;
		while($row = $this->db->fetch_assoc($q)) {
			$res["data"][]=$row;
			$res["id_arr"][]=$row["res_id"];
		}

		return $res;
	}


	public function count($where) {
		$res =0;
		$qtxt ="SELECT COUNT(*) as tot FROM %lms_kb_res";
		if (!empty($where)) {
			$qtxt.=" WHERE ".$where;
		}

		$q =$this->db->query($qtxt);
		$res =$this->db->fetch_array($q);
		return $res["tot"];
	}


	public function getAllTagsForResources($res_id_arr) {
		require_once(_lms_.'/lib/lib.kbres.php');
		$kbres =new KbRes();
		return $kbres->getAllTagsForResources($res_id_arr);
	}


	/**
	 *
	 * @param array $fields
	 * @param array $condition
	 * @return mixed query result
	 */
	public function update($fields, $condition) {

		$qtxt ="UPDATE %lms_kb_res SET ";
		$qtxt.=implode(',', $this->_parseValueArr($fields));
		$qtxt.=' WHERE ';
		$qtxt.=implode(' AND ', $this->_parseValueArr($condition, true));

		return $this->db->query($qtxt);
	}


	/**
	 * returns an array with fields parsed for mysql query
	 * if compare is set to true then the last two characters
	 * of the fields key will be used for the comparation operator
	 */
	private function _parseValueArr($fields, $compare=false) {
		$res =array();

		foreach($fields as $key=>$val) {
			if ($compare) { // caop = compare/assign operator
				$caop =substr($key, -2);
				$caop =str_replace("==", "=", $caop);
				$key =substr($key, 0, -2);
			}
			else {
				$caop ='=';
			}
			$res[]=$key.$caop."'".$val."'";
		}

		return $res;
	}


	// -- Tree related: ----------------------------------------------------------


	public function getKbPath($node_id) {
		$output = "";
		$query = "SELECT node_id, parent_id, path FROM %lms_kb_tree WHERE node_id=".(int)$node_id;
		$res = $this->db->query($query);
		list($node_id, $parent_id, $path) = $this->db->fetch_row($res);
		if ($path!="") {
			$list = explode('/', str_replace('/root/', '', $path));
			for ($i=0; $i<count($list); $i++) $list[$i] = (int)$list[$i];

			//languages
			$names = array();
			$query = "SELECT id_dir, node_title FROM %lms_kb_tree_info WHERE lang_code='".getLanguage()."'";
			$res = $this->db->query($query);
			while(list($id_dir, $node_title) = $this->db->fetch_row($res)) {
				$names[$id_dir] = $node_title;
			}

			return $names[$node_id];
		} else {
			$output .= "(root)";
		}
		return $output;
	}


	protected function _checkSubnodesVisibility($id, $left, $right, $org_tree) {
		$output = 0;
		foreach ($this->orgCache as $node_id=>$value) //value => (iLEFT, iRIGHT)
			if ($value[0]>$left && $value[1]<$right) $output++;
		return $output;
	}


	public function getKbNodes($id_node, $recursive = false, $language = false, $userFilter = false) {
		$is_subadmin = false;
/*		if ($userFilter) {
			$userlevelid = $this->getUserLevel();
			if( $userlevelid != ADMIN_GROUP_GODADMIN ) {
				$orgTree = $this->_getAdminOrgTree();
				$is_subadmin = true;
			}
		}*/

		$lang_code = ($language == false ? getLanguage() : $language);
		$search_query = "SELECT	t1.node_id, t2.node_title, t1.iLeft, t1.iRight
			FROM %lms_kb_tree AS t1 LEFT JOIN %lms_kb_tree_info AS t2
				ON (t1.node_id = t2.id_dir AND t2.lang_code = '".$lang_code."' )
			WHERE t1.parent_id = '".(int)$id_node."' ORDER BY t2.node_title";
		$re = $this->db->query($search_query);

		$output = array();
		while(list($id, $node_title, $left, $right) = $this->db->fetch_row($re)) {
			$is_node_visible = true;

			$code_label = ""; //($code != "" ? '['.$code.'] ' : "");
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
						$label = $code_label.$node_title;
						$is_leaf = false;
						$count = $count_subnodes;
						$style = 'disabled';
					} else {
						//not forbidden, check as normal
						$label = $code_label.$node_title;//end(explode('/', $path));
						$is_leaf = !$has_visible_subnodes;
						$count = $count_subnodes;
						$style = false;
					}

				}
			} else {
				$label = $code_label.$node_title;//end(explode('/', $path));
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


	public function getKbInitialNodes($node_id, $userFilter = false) {
		$results = array();

		$is_subadmin = false;
		if ($userFilter) {
			$userlevelid = 0;//$this->getUserLevel();
			if( $userlevelid != ADMIN_GROUP_GODADMIN ) {
				$orgTree = $this->_getAdminOrgTree();
				$is_subadmin = true;
			}
		}

		$folders = $this->getOpenedFolders($node_id);
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
				list($node_id, $id_parent, $lev, $left, $right, $node_title) = $children[$i];

				$code_label = ""; //($code != "" ? '['.$code.'] ' : "");
				if ($is_subadmin) {
					$is_forbidden = false; //!in_array($node_id, $orgTree);
					$count_subnodes = $this->_checkSubnodesVisibility($node_id, $left, $right, $orgTree);
					$has_visible_subnodes = ($count_subnodes > 0);

					if ($is_forbidden && !$has_visible_subnodes) {

						//forbidden with no visible subnodes:don't show it
						$is_node_visible = false;

					} else {

						if ($is_forbidden) {
							//forbidden, but with visible valid subnodes: show it
							$label = $code_label.$node_title;
							$is_leaf = false;
							$count = $count_subnodes;
							$node_options = array();
							$style = 'disabled';
						} else {
							//not forbidden, check as normal
							$label = $code_label.$node_title;//end(explode('/', $path));
							$is_leaf = !$has_visible_subnodes;
							$count_subnodes = $count_subnodes;
							$node_options = array();
							$style = false;
						}

					}

				} else {

					$is_leaf = ($right-$left) == 1;
					$label = $code_label.$node_title;
					$node_options = array();//getNodeOptions($id_org, $is_leaf);
					$count_subnodes = (int)(($right-$left-1)/2);
					$style = false;

				}

				if ($is_node_visible)
					$ref[] = array(
						'node' => array(
							'id' => $node_id,
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


	/*
	 * returns iLeft and iRight of a node
	 */
	public function getFolderLimits($node_id) {
		if ($node_id <= 0) {
			$query = "SELECT MIN(iLeft), MAX(iRight) FROM %lms_kb_tree";
			$res = $this->db->query($query);
			$row = $this->db->fetch_row($res);
			if (is_array($row)) {
				$row[0]--;
				$row[1]++;
			}
		} else {
			$query = "SELECT iLeft, iRight FROM %lms_kb_tree WHERE node_id=".(int)$node_id;
			$res = $this->db->query($query);
			$row = $this->db->fetch_row($res);
		}
		return $row;
	}


	public function getFolderParents($node_id) {
		if ($node_id <= 0) {
			return array();
		} else {
			list($iLeft, $iRight) = $this->getFolderLimits($node_id);
			$qtxt = "SELECT tree.node_id, info.node_title FROM %lms_kb_tree as tree
				LEFT JOIN %lms_kb_tree_info as info
				ON (tree.node_id=info.id_dir AND info.lang_code='".getLanguage()."')
				WHERE (tree.iLeft < ".$iLeft." AND tree.iRight > '".$iRight."')
				OR tree.node_id=".(int)$node_id."
				ORDER BY tree.iLeft DESC";
			$q =$this->db->query($qtxt);
			$res =array();
			while($row =$this->db->fetch_assoc($q)) {
				$res[$row['node_id']]=$row['node_title'];
			}
		}

		return $res;
	}


	/*
	 * returns an ordered list of ids (like a path)
	 */
	public function getOpenedFolders($node_id) {
		$folders = array(0);
		if ($node_id <= 0) return $folders;
		list($ileft, $iright) = $this->getFolderLimits($node_id);
		$query = "SELECT node_id FROM %lms_kb_tree WHERE iLeft<=".$ileft." AND iRight>=".$iright." AND node_id>0 ORDER BY iLeft";
		$res = $this->db->query($query);
		if ($res) {
			while (list($id_org) = $this->db->fetch_row($res)) { $folders[] = (int)$id_org; }
			return  $folders;
		} else
			return false;
	}


	/*
	 * return a list of subfolders given a node id (node_id)
	 */
	public function getSubFolders($node_id, $language = false, $userFilter = false) {
		$query_filter = "";
		if (is_array($userFilter)) {
			if (count($userFilter) > 0) {
				$query_filter .= " AND t1.node_id IN (".implode(',', $userFilter).")";
			} else
				return array();
		}

		$lang_code = ($language == false ? getLanguage() : $language);
		$search_query = "SELECT	t1.node_id, t1.parent_id, t1.lev, t1.iLeft, t1.iRight, t2.node_title
			FROM %lms_kb_tree AS t1 LEFT JOIN	%lms_kb_tree_info AS t2
			ON (t1.node_id = t2.id_dir AND t2.lang_code = '".$lang_code."' )
			WHERE t1.parent_id = '".(int)$node_id."' ".$query_filter." ORDER BY t2.node_title";
		$re = $this->db->query($search_query);

		$output = array();
		while(list($id, $parent, $level, $ileft, $iright, $node_title) = $this->db->fetch_row($re)) {
			$output[] = array(
				$id,
				$parent,
				$level,
				$ileft,
				$iright,
				$node_title,
			);
		}

		return $output;
	}


	/*
	 * get folder's properties by ID
	 */
	public function getFolderById($node_id, $array = false) {
		if ($node_id <= 0) { //root node, not present in DB, but it's "virtual"
			list($left, $right) = $this->getFolderLimits(0);
			if ($array) {
				return array(
					'node_id' => 0,
					'parent_id' => 0,
					'lev' => 0,
					'iLeft' => $left,
					'iRight' => $right
				);
			} else {
				$obj = new stdClass();
				$obj->node_id = 0;
				$obj->parent_id = 0; //or NULL ?
				$obj->lev = 0;
				$obj->iLeft = $left;
				$obj->iRight = $right;
				return $obj;
			}
			return false;
		}
		$query = "SELECT * FROM %lms_kb_tree WHERE node_id=".(int)$node_id." LIMIT 1";
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
	public function getFolderCode($node_id) {
		return "";
	}


	/*
	 * returns a set of folder name translations indexed by lang code
	 */
	public function getFolderTranslations($node_id, $array = false) {
		if ($node_id == 0) {
			$node_title = Get::sett('title_organigram_chart', Lang::t('_ORG_CHART', ''));
		}

		$query = "SELECT * FROM %lms_kb_tree_info WHERE id_dir=".(int)$node_id;
		$res = $this->db->query($query);
		$output = ($array ? array() : new stdClass());
		while ($row = $this->db->fetch_obj($res)) {
			if ($array)
				$output[$row->lang_code] = $row->node_title;
			else {
				$lang_code = $row->lang_code;
				$output->$lang_code = $row->node_title;
			}
		}
		return $output;
	}


	/*
	 * returns a set of folder name translations indexed by lang code
	 */
	public function getFolderTranslation($node_id, $lang_code = false) {
		if (!$lang_code) $lang_code = getLanguage();
		$query = "SELECT node_title FROM %lms_kb_tree_info WHERE id_dir=".(int)$node_id." AND lang_code='".$lang_code."'";
		$res = $this->db->query($query);
		$output = false;
		if ($res && ($this->db->num_rows($res)>0))
			list($output) = $this->db->fetch_row($res);
		return $output;
	}


	/*
	 * add a note the the org chart tree
	 */
	public function addFolder($id_parent, $langs, $code = '') {
		$output = false;

		if (is_array($langs)) {

			//calculate new folder parameters in org_chart_tree table
			$parent = $this->getFolderById($id_parent);
			$level = $parent->lev + 1;

			//$this->db->query("START TRANSACTION");
			$new_limits = array('iLeft' => $parent->iRight, 'iRight' => $parent->iRight);

			//updating left limits
			$query = "UPDATE %lms_kb_tree SET iRight=iRight+2 WHERE iRight>=".$new_limits['iRight'];
			$rsl = $this->db->query($query);
			//TO DO: handle error case (if !$rs ... )

			//updating right limits
			$query = "UPDATE %lms_kb_tree SET iLeft=iLeft+2 WHERE iLeft>=".$new_limits['iLeft'];
			$rsr = $this->db->query($query);
			//TO DO: handle error case (if !$rs ... )

			//insert node in the table, with newly calculated iLeft and iRight
			$query = "INSERT into %lms_kb_tree (node_id, parent_id, lev, iLeft, iRight) VALUES "
				."(NULL, '".(int)$id_parent."', '". (int)$level ."', ".(int)$new_limits['iLeft'].", ".((int)$new_limits['iRight'] + 1).")";
			$res = $this->db->query($query);
			$id = $this->db->insert_id();

			//if node has been correctly inserted then ...
			if ($id) {

				//insert translations in database
				$conditions = array();
				foreach ($langs as $lang_code => $node_title) { //TO DO: check if lang_code exists ...
					$conditions[] = "(".(int)$id.", '".$lang_code."', '".$node_title."')";
				}
				$query = "INSERT INTO %lms_kb_tree_info (id_dir, lang_code, node_title) VALUES ".implode(",", $conditions);
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
	public function deleteFolder($node_id, $onlyLeaf = false) {
		$acl =& Docebo::user()->getACLManager();
		$folder = $this->getFolderById($node_id);

		if (!$folder) return false;
		if ($node_id <= 0) return false;

		list($left, $right) = $this->getFolderLimits($node_id);
		$limits = array('iLeft'=>$left, 'iRight'=>$right);
		if ($onlyLeaf) {
			if ( ((int)$limits['iRight'] - (int)$limits['iLeft']) > 1 ) return FALSE;
		}

		$query = "SELECT node_id FROM %lms_kb_tree WHERE iLeft>=".$limits['iLeft']." AND iRight<=" .$limits['iRight'];
		$res = $this->db->query($query);
		$nodes = array();
		while (list($node) = $this->db->fetch_row($res)) $nodes[] = $node;

		$query = "DELETE FROM %lms_kb_tree WHERE iLeft>=".$limits['iLeft']." AND iRight<=" .$limits['iRight'];
		$res = $this->db->query($query);
		$shift = $limits['iRight'] - $limits['iLeft'] + 1; //or -1 ??

		$query = "UPDATE %lms_kb_tree SET iLeft=iLeft-".$shift." WHERE iLeft>=".$limits['iLeft'];
		$res = $this->db->query( $query );
		$query = "UPDATE %lms_kb_tree SET iRight=iRight-".$shift." WHERE iRight>=".$limits['iRight'];
		$res = $this->db->query( $query );
		//handle error ....
		//...

		/*$query = "DELETE FROM %lms_kb_tree WHERE node_id=".(int)$node_id." LIMIT 1";
		$res = $this->db->query($query);*/
		$query = "DELETE FROM %lms_kb_tree_info WHERE id_dir IN (".implode(",", $nodes).")";
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


	/*
	 * modify the name of a folde
	 */
	public function renameFolder($node_id, $langs) {
		if ($node_id <= 0) return false;
		$output = false;

		$qtxt ="SELECT lang_code FROM %lms_kb_tree_info WHERE id_dir=".(int)$node_id;
		$q =$this->db->query($qtxt);

		$avail_lang =array();
		while($row=$this->db->fetch_assoc($q)) {
			$avail_lang[]=$row['lang_code'];
		}

		if (is_array($langs)) {
			//update translations in database
			foreach ($langs as $lang_code => $node_title) {
				if (in_array($lang_code, $avail_lang)) {
					$query = "UPDATE %lms_kb_tree_info SET node_title = '".$node_title."' "
						."WHERE lang_code='".$lang_code."' AND id_dir=".(int)$node_id;
				}
				else {
					$query ="INSERT INTO %lms_kb_tree_info (id_dir, lang_code, node_title)
						VALUES (".(int)$node_id.", '".$lang_code."', '".$node_title."')";
				}
				$res = $this->db->query($query);
			}
			$output = true;
		}
		return $output;
	}


	public function modFolderCode($node_id, $code) {
		if ($node_id <= 0) return false;
		$query = "UPDATE %lms_kb_tree SET code = '".trim($code)."' WHERE node_id=".(int)$node_id;
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


	// --- Kb resources info: ----------------------------------------------------


	public function getEnvParentInfo($parent_id_arr, $r_env) {
		$res =array();
		$qtxt ='';

		if (empty($parent_id_arr)) {
			return $res;
		}

		switch ($r_env) {
			case 'course_lo': {
				$qtxt ="SELECT idCourse as id, name as label FROM %lms_course WHERE idCourse ";
			} break;
			case 'communication': {
				$qtxt ="SELECT id_comm as id, title as label FROM %lms_communication WHERE id_comm ";
			} break;
			case 'games': {
				$qtxt ="SELECT id_game as id, title as label FROM %lms_games WHERE id_game ";
			} break;
		}

		if (!empty($qtxt)) {
			$qtxt.="IN (".implode(',', $parent_id_arr).")";
			$q =$this->db->query($qtxt);
			while($row=$this->db->fetch_assoc($q)) {
				$res[$row['id']]['id']=$row['id'];
				$res[$row['id']]['label']=$row['label'];
			}
		}

		return $res;
	}


	public function getParentInfo($parent_id, &$array_comm, $type_arr) {
		foreach($type_arr as $r_env) {
			if (isset($parent_id[$r_env]) && !empty($parent_id[$r_env])) {
				$p_info =$this->getEnvParentInfo($parent_id[$r_env], $r_env);
				foreach($parent_id[$r_env] as $key=>$id) {
					if (isset($p_info[$id])) {
						$array_comm[$key]['r_env_parent']=$p_info[$id]['label'];
					}
				}
			}
		}
	}


	public function getCoursesVisibleToUser($user_id=false) {
		require_once(_lms_.'/lib/lib.subscribe.php');
		$res =array();
		$user_id =($user_id > 0 ? $user_id : getLogUserId());

		$allowed_status =array(
			_CUS_SUBSCRIBED,
			_CUS_BEGIN,
			_CUS_END,
		);

		$qtxt ="SELECT t1.idCourse, t2.name, t1.idUser, t1.status FROM  %lms_courseuser as t1,
			%lms_course as t2
			WHERE t1.idCourse = t2.idCourse AND
			t1.idUser = ".(int)$user_id." AND t1.status IN (".implode(',', $allowed_status).")
			ORDER BY t2.name ASC";

		$q =$this->db->query($qtxt);
		while($row=$this->db->fetch_assoc($q)) {
			$id =$row['idCourse'];
			$res[$id]=$row['name'];
		}

		return $res;
	}


	public function getCommunicationsVisibleToUser($user_id=false) {
		$res =array();

		if (empty($user_id)) {
			$arr_st =Docebo::user()->getArrst();
		}
		else {
			$acl =Docebo::user()->getACL();
			$arr_st =$acl->getUserAllST($user_id);
		}
		if (empty($arr_st)) { $arr_st =array(0); }

		$qtxt ="SELECT t1.id_comm, t2.title FROM %lms_communication_access as t1,
			%lms_communication as t2
			WHERE t1.id_comm = t2.id_comm AND t1.idst IN (".implode(',', $arr_st).")";

		$q =$this->db->query($qtxt);
		while($row=$this->db->fetch_assoc($q)) {
			$id =$row['id_comm'];
			$res[$id]=$row['title'];
		}

		return $res;
	}


	public function getGamesVisibleToUser($user_id=false) {
		$res =array();

		if (empty($user_id)) {
			$arr_st =Docebo::user()->getArrst();
		}
		else {
			$acl =Docebo::user()->getACL();
			$arr_st =$acl->getUserAllST($user_id);
		}
		$arr_st =Docebo::user()->getArrst();
		if (empty($arr_st)) { $arr_st =array(0); }

		$qtxt ="SELECT t1.id_game, t2.title FROM %lms_games_access as t1,
			%lms_games as t2
			WHERE t1.id_game = t2.id_game AND t1.idst IN (".implode(',', $arr_st).")";

		$q =$this->db->query($qtxt);
		while($row=$this->db->fetch_assoc($q)) {
			$id =$row['id_game'];
			$res[$id]=$row['title'];
		}

		return $res;
	}


	public function getSearchFilter($user_id=false, $filter_text=false, $course_filter=false, $res_id=false) {
		$res =array(
			'show_what'=>(Get::sett('kb_show_uncategorized') == 'on' ? 'all' : 'categorized'),
			'show_only_visible_by_user'=>(Get::sett('kb_filter_by_user_access') == 'on' ? true : false),
			'where'=>false,
			'search'=>'',
		);

		if ($course_filter === false) {
			$course_filter =-1;
		}


		$where = false;
		if ($res['show_only_visible_by_user']) {  // --- Access filters: ------------------
			$where.= ( !empty($where) ? " AND " : "");

			$courses_arr = array_keys($this->getCoursesVisibleToUser($user_id));
			if (empty($courses_arr)) {
				$courses_arr = array(0);
			}

			$comm_arr = array_keys($this->getCommunicationsVisibleToUser($user_id));
			if (empty($comm_arr)) {
				$comm_arr = array(0);
			}

			$games_arr = array_keys($this->getGamesVisibleToUser($user_id));
			if (empty($games_arr)) {
				$games_arr = array(0);
			}

			$where.="( " .
					"(kr.r_env = 'course_lo' AND kr.r_env_parent_id IN (" . implode(',', $courses_arr) . ")) OR " .
   					"(kr.r_env = 'communication' AND kr.r_env_parent_id IN (" . implode(',', $comm_arr) . ")) OR " .
        			"(kr.r_env = 'games' AND kr.r_env_parent_id IN (" . implode(',', $games_arr) . ")) OR " .
       				"(kr.force_visible='1') " .
			")";
		}
		if ($course_filter > 0) {  // --- Course filter: -----------------------
			$where.= ( !empty($where) ? " AND " : "") .
					"kr.r_env = 'course_lo' AND kr.r_env_parent_id = " . (int) $course_filter;
		}


		if ($res_id > 0) { // used to check perm on single item
			$where.= ( !empty($where) ? " AND " : "") .
					"kr.res_id = " . (int) $res_id;
		}


		$res['where']=$where;
		$res['search']=(!empty($filter_text) ? $filter_text : false);  // Search filter <-

		return $res;
	}


	public function checkResourcePerm($res_id, $user_id=false, $course_filter=false) {
		$user_id =(empty($user_id) ? Docebo::user()->getIdSt() : $user_id);

		$filter =$this->getSearchFilter(false, false, $course_filter, $res_id);

		$fields ="COUNT(*) as tot";
		$qtxt ="SELECT ".$fields." FROM %lms_kb_res as kr WHERE ".$filter['where'];

		$q =Docebo::db()->query($qtxt);
		$row =Docebo::db()->fetch_assoc($q);

		return ($row['tot'] > 0 ? true : false);
	}


}

?>
