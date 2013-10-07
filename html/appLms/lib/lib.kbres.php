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

class KbRes {

	protected $db;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = DbConn::getInstance();
	}


	public function getResource($res_id, $with_tags=false, $with_folders=false) {
		$res =false;

		$qtxt ="SELECT * FROM %lms_kb_res WHERE res_id='".(int)$res_id."'";
		$q =$this->db->query($qtxt);

		if ($this->db->num_rows($q) > 0) {
			$res =$this->db->fetch_assoc($q);
		}

		if ($res) {
			if ($with_tags) {
				$res["tags"]=$this->getResourceTags($res["res_id"]);
			}
			if ($with_folders) {
				$res["folders"]=$this->getResourceFolders($res["res_id"]);
			}
		}

		return $res;
	}


	public function getResourceFromItem($item_id, $type, $env, $with_tags=false, $with_folders=false) {
		$res =false;
		$r_type =$this->getResourceTypeName($type);

		$qtxt ="SELECT * FROM %lms_kb_res WHERE r_item_id='".(int)$item_id."'
			AND r_type='".$r_type."' AND r_env='".$env."'";
		$q =$this->db->query($qtxt);

		if ($this->db->num_rows($q) > 0) {
			$res =$this->db->fetch_assoc($q);
		}

		if ($res) {
			if ($with_tags) {
				$res["tags"]=$this->getResourceTags($res["res_id"]);
			}
			if ($with_folders) {
				$res["folders"]=$this->getResourceFolders($res["res_id"]);
			}
		}

		return $res;
	}


	public function getCategorizedResources($res_list_arr, $type, $env, $with_tags=false) {
		$res =false;

		$qtxt ="SELECT * FROM %lms_kb_res WHERE r_item_id IN (".implode(',', $res_list_arr).")
			AND r_type='".$type."' AND r_env='".$env."' AND is_categorized=1";
		$q =$this->db->query($qtxt);


		if(!$q) return false;
		$res_id_arr =array();
		while($row = $this->db->fetch_array($q)) {
			$id =$row['r_item_id'];
			$res[$id] =$row;
			$res_id_arr[]=$row['res_id'];
		}


		if ($res) {
			if ($with_tags) {
				$res["tags"]=$this->getAllTagsForResources($res_id_arr);
			}
		}

		return $res;
	}


	public function getAllTagsForResources($res_id_arr) {
		$res =false;

		$fields ="rel.res_id, tag.tag_name";
		$qtxt ="SELECT ".$fields." FROM %lms_kb_rel as rel
			JOIN %lms_kb_tag as tag ON (rel.parent_id = tag.tag_id) WHERE
			rel.res_id IN (".implode(',', $res_id_arr).") AND rel.rel_type='tag'";

		$q =$this->db->query($qtxt);

		if(!$q) return $res;
		while($row = $this->db->fetch_array($q)) {
			$res_id =$row["res_id"];
			if (isset($res[$res_id])) {
				$res[$res_id][]=$row["tag_name"];
			}
			else {
				$res[$res_id] =array($row["tag_name"]);
			}
		}

		return $res;
	}


	public function saveResource($res_id, $name, $original_name, $desc, $r_item_id, $type, $env, $env_parent_id, $param, $alt_desc, $lang, $force_visible, $is_mobile, $folders=false, $json_tags=false) {
		$res =false;

		$is_categorized ='1';
		if ($folders !== false && empty($folders)) { // if no folder selected
			$folders ='0'; // move this to the root folder
			//$is_categorized ='0'; // and set it to uncategorized
		}

		if ($res_id > 0) { // -- Update

			$qtxt ="UPDATE %lms_kb_res SET r_name='".$name."',
				".(!empty($original_name) ? "original_name='".$original_name."'," : '')."
				r_desc='".$desc."',
				r_item_id='".(int)$r_item_id."', r_type='".$type."', r_env='".$env."',
				r_env_parent_id=".($env_parent_id > 0 ? "'".$env_parent_id."'" : "NULL").",
				r_param='".$param."', r_alt_desc='".$alt_desc."', r_lang='".$lang."',
				force_visible='".(int)$force_visible."', is_mobile='".(int)$is_mobile."',
				is_categorized='".$is_categorized."'
				WHERE res_id='".(int)$res_id."'";

			$res =(int)$res_id;
		}
		else { // -- Insert

			$qtxt ="INSERT INTO %lms_kb_res (r_name,
				".(!empty($original_name) ? "original_name," : '')."
				r_desc, r_item_id, r_type, r_env, r_env_parent_id,
				r_param, r_alt_desc, r_lang, force_visible, is_mobile, is_categorized)
				VALUES('".addslashes($name)."',
				".(!empty($original_name) ? "'".addslashes($original_name)."'," : '')."
				'".$desc."', '".(int)$r_item_id."', '".$type."',
				'".$env."', ".($env_parent_id > 0 ? "'".$env_parent_id."'" : "NULL").",
				'".$param."', '".$alt_desc."', '".$lang."',
				'".(int)$force_visible."', '".(int)$is_mobile."',
				'".$is_categorized."')";
			$res ='last_id';
		}

		$q =$this->db->query($qtxt); if (!$q) {echo $qtxt; die(); }

		if ($res == 'last_id') {
			$res =$this->db->insert_id();
		}


		if ($res > 0 && !empty($json_tags)) {

			$json_tags = str_replace("[", "", $json_tags);
			$json_tags = str_replace("]", "", $json_tags);
			$json_tags = str_replace('"', "", $json_tags);
			$json_tags = str_replace("\\", "", $json_tags);
			$tags_arr = explode(",", $json_tags);

			$this->setResourceTags($res_id, $tags_arr);
		}

		if ($res > 0 && !empty($folders)) {
			$this->assignToFolders($res_id, explode(',', $folders));
		}


		return $res;
	}


	public function saveUncategorizedResource($original_name, $r_item_id, $type, $env, $env_parent_id, $param=false, $lang=false) {
		$res =true;
		$r_type =$this->getResourceTypeName($type);
		$lang =(empty($lang) ? getLanguage() : $lang);

		// we quit if the resource doesn't have to be categorized
		if (in_array($type, $this->getNotToCategorizeArr())) return $res;

		$resource =$this->getResourceFromItem($r_item_id, $type, $env);

		if ($resource === false) {

			/* if ($r_type == 'scorm') { // if resource is a scorm, we categorize his chapters
				$this->saveUncategorizedScoResource($r_item_id, $env, $env_parent_id, $lang);
			}
			else { // normal resource				 */

			$qtxt ="INSERT INTO %lms_kb_res (r_name, original_name,
				r_item_id, r_type, r_env, r_env_parent_id,
				r_param, r_lang, is_categorized)
				VALUES('".addslashes($original_name)."', '".addslashes($original_name)."',
				'".(int)$r_item_id."', '".$r_type."',
				'".$env."', ".($env_parent_id > 0 ? "'".$env_parent_id."'" : "NULL").",
				'".$param."', '".$lang."', '0')";

			$q =$this->db->query($qtxt); if (!$q) {echo $qtxt; die(); }
			$res =(!$q ? false : true);

			//}
		}

		return $res;
	}


	public function saveUncategorizedScoResource($idscorm_org, $env, $env_parent_id, $lang=false) {
		$res =true;
		$lang =(empty($lang) ? getLanguage() : $lang);

		$qtxt ="SELECT idscorm_item, title, item_identifier FROM %lms_scorm_items
			WHERE idscorm_organization='".(int)$idscorm_org."' AND idscorm_resource <> 0 ";

		$q =$this->db->query($qtxt);
		if (!$q) $res =false;

		$ins_arr =array();
		while($row = $this->db->fetch_array($q)) {
			$sco_id =$row["idscorm_item"];
			$param ='chapter='.$row['item_identifier'];
			$title =$row["title"];
			$ins_arr[]="('".addslashes($title)."', '".addslashes($title)."', '".$sco_id."',
				'scoitem', '".$env."', ".($env_parent_id > 0 ? "'".$env_parent_id."'" : "NULL").",
				'".$param."', '".$lang."', '0')";
		}

		if (!empty($ins_arr)) {
			$qtxt ="INSERT INTO %lms_kb_res (r_name, original_name,
					r_item_id, r_type, r_env, r_env_parent_id,
					r_param, r_lang, is_categorized) VALUES ";
			$qtxt.=implode(",", $ins_arr);

			$res =$this->db->query($qtxt);
		}

		return $res;
	}


	public function saveResourceSubCategorizePref($res_id, $cat_sub_items) {
		$res =false;

		$r_data =$this->getResource($res_id);
		$sub_categorize =$r_data['sub_categorize']; // old / prev. status
		$r_type =$this->getResourceTypeName($r_data['r_type']);

		if ($sub_categorize < 1 && $cat_sub_items == 1) { // from "no" to "yes"
			$this->deleteResource($res_id, true);

			switch ($r_type) {
				case 'scorm': { // if resource is a scorm, we categorize his chapters
					$this->saveUncategorizedScoResource($r_data['r_item_id'], $r_data['r_env'], $r_data['r_env_parent_id']);
				} break;
			}
		} else if ($sub_categorize == 1 && $cat_sub_items < 1) { // from "yes" to "no"
			switch ($r_type) {
				case 'scorm': { // if resource is a scorm, we remove his chapters
					$this->deleteScoResource($r_data['r_item_id'], $r_data['r_env']);
				} break;
			}
		}

		$qtxt ="UPDATE %lms_kb_res SET
			sub_categorize='".($cat_sub_items == 1 ? $cat_sub_items : 0)."'
			WHERE res_id='".(int)$res_id."'";

		$res =$this->db->query($qtxt);
		return $res;
	}


	public function deleteResource($res_id, $only_relations=false) {
		$res =false;

		$res_id =(int)$res_id;
		if (!$res_id > 0) return $res;

		if (!$only_relations) {
			$qtxt ="DELETE FROM %lms_kb_res WHERE res_id='".$res_id."' LIMIT 1";
			$q =$this->db->query($qtxt);
		} // if we only delete relations, we set the resource back as uncategorized:
		else {
			$qtxt ="UPDATE %lms_kb_res SET
				is_categorized=0
				WHERE res_id='".(int)$res_id."'";
			$this->db->query($qtxt);
		}

		if ($q || $only_relations) { // delete relations with tags and folders.
			$res =true;
			$qtxt ="DELETE FROM %lms_kb_rel WHERE res_id='".$res_id."'";
			$q =$this->db->query($qtxt);
		}

		return $res;
	}


	public function deleteResourceFromItem($item_id, $type, $env, $only_relations=false) {
		$r_type =$this->getResourceTypeName($type);

		// we quit if the resource doesn't have to be categorized
		if (in_array($type, $this->getNotToCategorizeArr())) return true;

		$r_data =$this->getResourceFromItem($item_id, $r_type, $env, $only_relations);

		// if resource is a scorm, and its items are categorized, we remove them
		if ($r_type == 'scorm' && $r_data['sub_categorize'] == 1) {
			$this->deleteResourcesByParent($env, 'scoitem', $r_data['r_env_parent_id']);
		}

		// normal resource
		$this->deleteResource($r_data['res_id'], $only_relations);
	}


	public function deleteResourcesByParent($parent_env, $type, $parent_id) {
		$qtxt ="SELECT res_id FROM %lms_kb_res
			WHERE r_env='".$parent_env."' AND r_type='".$type."'
			AND r_env_parent_id=".(int)$parent_id;

		$q =$this->db->query($qtxt);
		if (!$q) $res =false;

		while($row = $this->db->fetch_array($q)) {
			$this->deleteResource($row['res_id']);
		}
	}


	public function deleteScoResource($idscorm_org, $env) {
		//return false;

		$qtxt ="SELECT idscorm_item, title, item_identifier FROM %lms_scorm_items
			WHERE idscorm_organization='".(int)$idscorm_org."'";

		$q =$this->db->query($qtxt);
		if (!$q) $res =false;

		while($row = $this->db->fetch_array($q)) {
			$item_id =$row['idscorm_item'];
			$data =$this->getResourceFromItem($item_id, 'scoitem', $env);
			$this->deleteResource($data['res_id']);
		}

	}


	public function playResource($res_id, $back_url) {
		require_once($GLOBALS['where_lms'].'/lib/lib.param.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.module.php');

		$data =$this->getResource($res_id);

		$idResource =$data['r_item_id'];
		$env =$data['r_env'];
		$objectType =$this->getObjectTypeName($data['r_type'], $env);

		if ($data['r_type'] == 'scoitem') { // play the single scorm chapter
			$idResource =$this->getIdResourceFromSco($data['r_item_id']);
			$param =$this->readParam($data['r_param']);
			$GLOBALS['chapter']=$param['chapter'];
		}

		$lo =createLO($objectType, $idResource, $env);
		$idParams =$lo->getIdParam($env);

		if ($data['force_visible']) {
			$lo->setNoRestrictions(true);
		}

		$lo->play($idResource, $idParams, $back_url);
	}


	public function readParam($param) {
		$res =array();
		$arr =explode('&', $param);

		foreach ($arr as $p) {
			$kv =explode('=', trim($p));
			$res[$kv[0]]=$kv[1];
		}

		return $res;
	}


	public function getIdResourceFromSco($sco_id) {
		$qtxt ="SELECT idscorm_organization as idResource FROM %lms_scorm_items
			WHERE idscorm_item='".(int)$sco_id."'";

		$q =$this->db->query($qtxt);
		$row =$this->db->fetch_assoc($q);

		return $row['idResource'];
	}


	public function getResourceTags($res_id) {
		$res =array();

		$qtxt ="SELECT * FROM %lms_kb_rel as rel
			JOIN %lms_kb_tag as tag ON (rel.parent_id = tag.tag_id) WHERE
			rel.res_id='".(int)$res_id."' AND rel.rel_type='tag'";

		$q =$this->db->query($qtxt);

		if(!$q) return false;
		while($row = $this->db->fetch_array($q)) {
			$res[$row["tag_id"]]=$row["tag_name"];
		}

		return $res;
	}


	/**
	 * get an array with the list of resource types available
	 * @param bool $with_label
	 * @return array
	 */
	public function getResourceTypeArr($with_label=false) {

		$arr =array( 'faq', 'htmlpage', 'poll',	'scoitem' );

		if (!$with_label) {
			$res =$arr;
			sort($res);
		} else {
			foreach ($arr as $val) {
				$res[$val]=Lang::t('_LONAME_'.$val, 'storage');
			}
			asort($res);
		}
		return $res;
	}


	public function addResourceTag($res_id, $tag) {
		$res =false;
		if (!is_array($tag)) {
			$tag =array($tag);
		}

		$tags_arr =$this->getTagArray($tag);

		$val_arr =array();
		$qtxt ="INSERT INTO %lms_kb_rel (res_id, parent_id, rel_type) VALUES ";
		foreach($tags_arr as $tag_id=>$tag_name) {
			$val_arr[]="('".(int)$res_id."', '".$tag_id."', 'tag')";
		}

		if (!empty($val_arr)) {
			$qtxt.=implode(',', $val_arr);
			$res =$this->db->query($qtxt);
		}

		return $res;
	}


	/**
	 * returns an array with tag_id=>tag_name for the requested tag names;
	 * if a tag name doesn't exists, it will be added to the tags table.
	 * @param <array> $tag_list
	 */
	public function getTagArray($tag_list) {

		$escaped_tags =array();
		foreach ($tag_list as $tag_name) {
			$escaped_tags[]="'".$tag_name."'";
		}

		$qtxt ="SELECT * FROM %lms_kb_tag WHERE tag_name IN (".implode(',', $escaped_tags).")";
		$q =$this->db->query($qtxt);

		$current_tags =array();
		while($row = $this->db->fetch_array($q)) {
			$current_tags[$row["tag_id"]]=$row["tag_name"];
		}

		foreach($tag_list as $tag_name) { // Add missing tags
			if (!in_array($tag_name, $current_tags)) {
				$qtxt ="INSERT INTO %lms_kb_tag (tag_name) VALUES ('".$tag_name."')";
				$q =$this->db->query($qtxt);
				$id =$this->db->insert_id();
				$current_tags[$id]=$tag_name;
			}
		}


		return $current_tags;
	}


	/**
	 * Returns array with all tags available in the database
	 * If with_id is true then the array will be tag_id=>tag_name
	 * else it will just be a list of tag names
	 * @param bool $with_id
	 * @return array
	 */
	public function getAllTags($with_id=false) {
		$res =array();

		$qtxt ="SELECT * FROM %lms_kb_tag";
		$q =$this->db->query($qtxt);

		$i =0;
		while($row = $this->db->fetch_array($q)) {
			$id =($with_id ? $row["tag_id"] : $i);
			$res[$id]=$row["tag_name"];
			$i++;
		}

		return $res;
	}


  /**
   * Returns an array with tag_id=>array(tag_name, count);
   * useful, for example, to generate a tag cloud..
   * @return array
   */
  public function getTagUseCount() {
    $res =array();

    $qtxt ="SELECT tag.tag_id, tag.tag_name, COUNT(*) as use_count
      FROM %lms_kb_tag as tag
      JOIN %lms_kb_rel as rel
      ON (tag.tag_id=rel.parent_id)
      WHERE rel.rel_type='tag'
      GROUP BY tag.tag_id
      ORDER BY tag.tag_name";

    $q =$this->db->query($qtxt);
    if (!$q) return false;
    while($row=$this->db->fetch_assoc($q)) {
      $id =$row['tag_id'];
      $res[$id]=$row;
    }

    return $res;
  }


	/**
	 * Set all tags associated with a resource according to $tags_arr
	 * if a tag is not already associated with the given resource then it will be
	 * added, if a tag is not present it will be removed..
	 * @param <type> $res_id
	 * @param <type> $tags_arr
	 */
	public function setResourceTags($res_id, $tags_arr) {

		$to_add =array();
		$to_rem =array();
		$current_tags =$this->getResourceTags($res_id);
		$tags_not_to_rem =array_diff($tags_arr, $current_tags);

		foreach($tags_arr as $tag_name) {
			if (!in_array($tag_name, $current_tags)) {
        		if ($tag_name == "") {
          			$tags_not_to_rem[]=$tag_name;
   				} else {
          			$to_add[]=$tag_name;
   				}
			}else {
   				$tags_not_to_rem[]=$tag_name;
			}
		}

		$this->addResourceTag($res_id, $to_add);


		foreach($current_tags as $tag_id=>$tag_name) {
			if (!in_array($tag_name, $tags_not_to_rem)) {
				$to_rem[]=$tag_name;
			}
		}
//print_r($tags_arr); print_r($current_tags); print_r($tags_not_to_rem); print_r($to_rem); die();
		$this->remResourceTag($res_id, $to_rem);
	}


	public function remResourceTag($res_id, $tag) {
		if (!is_array($tag)) {
			$tag =array($tag);
		}

		$tags_arr =$this->getTagArray($tag);

		foreach($tags_arr as $tag_id=>$tag_name) {
			$qtxt ="DELETE FROM %lms_kb_rel WHERE res_id='".(int)$res_id."'
				AND parent_id='".(int)$tag_id."' AND rel_type='tag' LIMIT 1";
			$q =$this->db->query($qtxt);
		}
	}


	public function getResourceFolders($res_id) {
		$res =array();

		$qtxt ="SELECT rel.parent_id,info.node_title FROM %lms_kb_rel as rel
			JOIN %lms_kb_tree_info as info ON (rel.parent_id = info.id_dir) WHERE
			rel.res_id='".(int)$res_id."' AND rel.rel_type='folder'
			AND info.lang_code='".getLanguage()."'";

		$q =$this->db->query($qtxt);

		if(!$q) return false;
		while($row = $this->db->fetch_array($q)) {
			$res[$row["parent_id"]]=$row["node_title"];
		}

		return $res;
	}


	public function assignToFolders($res_id, $folder_arr) {
		$res =false;

		$qtxt ="DELETE FROM %lms_kb_rel WHERE res_id='".(int)$res_id."'
			AND rel_type='folder'";
		$q =$this->db->query($qtxt);

		$val_arr =array();
		$qtxt ="INSERT INTO %lms_kb_rel (res_id, parent_id, rel_type) VALUES ";
		foreach($folder_arr as $folder_id) {
			$val_arr[]="('".(int)$res_id."', '".$folder_id."', 'folder')";
		}

		if (!empty($val_arr)) {
			$qtxt.=implode(',', $val_arr);
			$res =$this->db->query($qtxt);
		}

		return $res;
	}


	/**
	 * Read resource data from $_GET
	 */
	public function getResDataFromRequest() {
		$data =array();

		$data["r_name"]=Get::req('name', DOTY_STRING, "");
		$data["r_type"]=Get::req('type', DOTY_STRING, "");
		$data["r_env"]=Get::req('env', DOTY_STRING, "");
		$data["r_param"]=Get::req('param', DOTY_STRING, "");

		return $data;
	}


	public function getRawResources() {

		$res =false;
		$qtxt_arr =array();

		$qtxt_arr['course_lo'] ="SELECT title, objectType as type
			,idResource as item_id, idResource as scorm_org_id FROM %lms_organization";

		$qtxt_arr['communication'] ="SELECT title,type_of as type,
			id_comm as item_id, id_resource as scorm_org_id FROM %lms_communication
			WHERE type_of IN('file', 'scorm')";

		//$qtxt_arr['games'] ="";

		$i =0;
		$sco_arr =array();
		$org_id_arr =array();
		foreach($qtxt_arr as $env=>$qtxt) {
			$q =$this->db->query($qtxt);

			if ($q) {
				while ($row =$this->db->fetch_assoc($q)) {
					$type =$row['type'];
					$type =($type == 'scormorg' ? 'scorm' : $type);

					if ($type == 'scorm') {
						$org_id_arr[$i] =$row['scorm_org_id'];
						$sco_arr[$i] =array();
						// we pass it by reference (note the "&");
						// we will fill this later..
						$row['sco_arr']=& $sco_arr[$i];
					}
					else {
						$row['sco_arr']=false;
					}

					$row['r_env']=$env;

					$res[$type][]=$row;
				}

				$i++;
			}
			else {
				//echo $qtxt; die();
			}
		}


		// Retrive sco chapters

		$qtxt ="SELECT * FROM %lms_scorm_items
			WHERE idscorm_organization
			IN (".implode(',', array_unique($org_id_arr)).")
			ORDER BY idscorm_item";

		$q =$this->db->query($qtxt);
		if ($q) {
			while ($row =$this->db->fetch_assoc($q)) {
				$id_org =$row['idscorm_organization'];
				$sco_items[$id_org][]=$row;
			}
		}


		// Fill in the sco_arr values

		foreach($org_id_arr as $key=>$val) {
			$sco_arr[$key]=$sco_items[$val];
		}

		return $res;
	}


	/**
	 * Find all categorized resources; can be filtered by type.
	 * Returns an array with $res[<environment>][<type>]
	 * as resources are returned from all environments
	 * @param string $type
	 * @param bool $no_data
	 * @return array
	 */
	public function getAllCategorized($type=false, $no_data=false) {
		$res =array();
		$where ='1';

		switch ($type) {
			case "scorm":
			case "scormorg": {
				$where ="r_type='scorm' OR r_type='scormorg'";
			} break;
			default: {
				if (!empty($type)) {
					$where ="r_type='".$type."'";
				}
			} break;
		}

		$qtxt ="SELECT * FROM %lms_kb_res WHERE ".$where;

		$q =$this->db->query($qtxt);
		if ($q) {
			while ($row =$this->db->fetch_assoc($q)) {
				$env =$row['r_env'];
				$type =$row['r_type'];
				$res[$env][$type]=($no_data ? 1 : $row);
			}
		}

		return $res;
	}


	/**
	 *
	 * @param string $type
	 * @return array
	 */
	public function getUnCategorizedResourcesByType($type, $where=false, $limit=false) {
		$res =false;
		$qtxt ='';
		$r_type ='';


		switch($type) {
			case "scoitem": {
				$r_type ='scoitem';

				$qtxt ="SELECT 'course_lo' AS env, idscorm_item as r_item_id,
					o.title as scorm_title, items.title, cr.res_id
					FROM %lms_organization AS o
					JOIN %lms_scorm_items AS items
					LEFT JOIN %lms_kb_res as cr ON (cr.r_env='course_lo' AND
					cr.r_type='".$r_type."' AND cr.r_item_id=items.idscorm_item)
					WHERE o.objectType = 'scormorg' AND (cr.res_id IS NULL OR cr.is_categorized=0)
					".(!empty($where) ? ' AND '.$where : '')."
					UNION
					SELECT 'communication' AS env, idscorm_item as r_item_id,
					c.title as scorm_title, items.title, cr.res_id
					FROM learning_communication AS c
					JOIN learning_scorm_items AS items
					LEFT JOIN %lms_kb_res as cr ON (cr.r_env='communication' AND
					cr.r_type='".$r_type."' AND cr.r_item_id=items.idscorm_item)
					WHERE c.type_of = 'scorm' AND (cr.res_id IS NULL OR cr.is_categorized=0)
					".(!empty($where) ? ' AND '.$where : '');
			} break;
		}

		$q =$this->db->query($qtxt);
		$res['tot']=$this->db->num_rows($q);

		if (!empty($limit)) {
			$qtxt.=" LIMIT ".$limit;
		}


		$res['data']=array();

		$q =$this->db->query($qtxt);
		$i =0;
		if ($q) {
			while ($row =$this->db->fetch_assoc($q)) {
				$res['data'][$i]=$row;
				$res['data'][$i]['r_type']=$r_type;
				$i++;
			}
		}

		return $res;
	}


	/**
	 *
	 * @param integer $parent_folder
	 * @param integer $sub_lvl number of sub levels returned
	 * @return array
	 */
	public function getKbFolders($parent_folder, $sub_lvl) {
		$res =array();

		$kb_model =new KbAlms();
		$folder =$kb_model->getFolderById($parent_folder, true);

		$fields ='node_id, tree.parent_id, lev, node_title, COUNT(rel.res_id) as r_count';
		$qtxt ="SELECT ".$fields." FROM %lms_kb_tree as tree
			LEFT JOIN %lms_kb_tree_info AS info
			ON (tree.node_id=info.id_dir AND info.lang_code='".getLanguage()."')
			LEFT JOIN %lms_kb_rel AS rel
			ON (rel.parent_id=tree.node_id AND rel.rel_type='folder')
			WHERE tree.iLeft > '".$folder['iLeft']."' AND tree.iRight < '".$folder['iRight']."'
			AND tree.lev > '".$folder['lev']."' AND tree.lev <= '".($folder['lev']+$sub_lvl)."'
			GROUP BY tree.node_id
      ORDER BY tree.iLeft ASC";


		$q =$this->db->query($qtxt);

		if ($q) {
			$prev_lev =$folder['lev']+1;
			$res =$this->_fillFolders($q, $prev_lev);
		} $res['qtxt']=$qtxt;


		return $res;
	}


	private function _fillFolders(& $db_q, $prev_lev, $current=false) {

    // If we have already a current item processed then we
    // create an empty array to sum it later with the folders
    // array we've already created for the first child
		$arr =($current != false ? array() : array('folders'=>array()));

    // if we have a current one then the first child
    // has already been created, and then we start from 1
		$i =($current === false ? 0 : 1);
		while ($row =$this->db->fetch_assoc($db_q)) {
      $arr['folders'][$i]=array(
        'id'=>$row['node_id'],
        'name'=>$row['node_title'],
        'r_count'=>$row['r_count'],
      );
			if ($row['lev'] > $prev_lev) {
        // we get the other childs in tmp; note that the last item
        // we retrive has a different level so we put it in the 'last' key
        $tmp =$this->_fillFolders($db_q, $row['lev'], $arr['folders'][$i]);
        //$arr['folders'][$i]['dir']='up';
        //$arr['folders'][$i]['dbg']=print_r($tmp, true);

        // The current item will be the first one of the parent's
        // folder array...
        $arr['folders'][$i-1]['folders'][0]=$arr['folders'][$i];
        // then if we have other childs we add them to the parent's
        // folder array...
        if (isset($tmp['folders'])) {
          $arr['folders'][$i-1]['folders']+=$tmp['folders'];
        }
        // and we unset the current item
        unset($arr['folders'][$i]);
        // if we are coming back from a recursion then we should have
        // the last item that has a fewer level than our childs; we
        // should then add the last retrived item as a new item in the
        // current level..
        $i++;
        if (isset($tmp['last'])) {
          $arr['folders'][$i]=$tmp['last'];
        }
        unset($tmp);
			}
			else if ($row['lev'] < $prev_lev) {
        //$arr['folders'][$i]['dir']='down';

        // the last child found is actualy an item that
        // has the same level of our parent so we move it
        // to the 'last' key of our array...
        $arr['last']=$arr['folders'][$i];
        unset($arr['folders'][$i]);
				return $arr;
			}
			$i++;
		}

		return $arr;
	}


	public function getFolderParents($node_id) {
		$kb_model =new KbAlms();
		return $kb_model->getFolderParents($node_id);
	}


	/**
	 * Starting from the name of a categorized resource type,
	 * we convert it to the name of the type of the original object
	 * @param string $r_type type of the categorized resource
	 * @param string $env
	 * @return string
	 */
	public function getObjectTypeName($r_type, $env=false) {
		$res =$r_type;

		switch($r_type) {
			case "file": {
				$res ='item';
			} break;
			case "scoitem":
			case "scorm": {
				$res ='scormorg';
			} break;
		}

		return $res;
	}


	/**
	 * Starting from the name of an object type, we convert it
	 * to the name of the type of the categorized resource
	 * @param string $type type of the original object
	 * @param string $env
	 * @return <type>
	 */
	public function getResourceTypeName($type) {
		$res =$type;

		switch($type) {
			case "item": {
				$res ='file';
			} break;
			case "scormorg": {
				$res ='scorm';
			} break;
		}

		return $res;
	}


	/**
	 * returns an array of objects type that doesn't have to be categorized
	 * @return array
	 */
	function getNotToCategorizeArr() {
		$res =array();

		$res[]='poll';
		$res[]='test';

		return $res;
	}


}

?>