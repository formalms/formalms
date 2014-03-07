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

class TreeModel extends Model {

	protected $db = false;
	protected $tree_table = false;
	protected $t_id = false;

	public function  __construct() {
		$this->db = DbConn::getInstance();
	}

	public function getOpenedFolders($id) {
		$folders = array();
		if(!$id) return $folders;
		list($ileft, $iright) = $this->getFolderLimits($id);
		$query = "SELECT ".$this->t_id." FROM ".$this->tree_table." WHERE iLeft<=".(int)$ileft." AND iRight>=".(int)$iright." AND ".$this->t_id.">0 ORDER BY iLeft";
		$res = $this->db->query($query);
		if ($res) {
			while (list($id_org) = $this->db->fetch_row($res)) { $folders[] = (int)$id_org; }
			return  $folders;
		} else
			return false;
	}

	public function getAncestorInfoFolders($id) {

		$folders = array();
		if ($id <= 0) return $folders;
		list($ileft, $iright) = $this->getFolderLimits($id);
		$query = "SELECT id FROM ".$this->tree_table." WHERE iLeft<=".(int)$ileft." AND iRight>=".(int)$iright." AND ".$this->t_id." > 0 ORDER BY iLeft";
		$res = $this->db->query($query);
		if ($res) {
			while (list($id) = $this->db->fetch_row($res)) {
				$folders[] = (int)$id;
			}
			return  $folders;
		} else
			return false;
	}

	public function moveFolder($src_folder, $dest_folder) {
		if ($src_folder <= 0)
			return false;
		if ($dest_folder < 0)
			return false;
		$output = false;
		$query = "SELECT idParent"
				." FROM ".$this->tree_table.""
				." WHERE ".$this->t_id." = '".$src_folder."'";
		list($id_parent) = $this->db->fetch_row($this->db->query($query));
		list($src_left, $src_right, $lvl_src) = $this->getFolderLimits($src_folder);
		list($dest_left, $dest_right, $lvl_dest) = $this->getFolderLimits($dest_folder);

		//dest folder is a son of the src ?
		if ($src_left < $dest_left && $src_right > $dest_right)
			return $output;
		$output = true;

		$dest_left = $dest_left + 1;
		$gap = $src_right - $src_left + 1;

		$this->shiftRL($dest_left, $gap);
		if ($src_left >= $dest_left) {
			// this happen when the src has shiften too
			$src_left += $gap;
			$src_right += $gap;
		}

		// update parent of source and level for descendants
		$lvl_gap = $lvl_dest - $lvl_src + 1;
		$query1 = "UPDATE ".$this->tree_table." SET idParent = ".(int) $dest_folder." WHERE ".$this->t_id." = ".(int) $src_folder;
		$query2 = "UPDATE ".$this->tree_table." SET lev = lev + ".$lvl_gap." WHERE ".$this->t_id." = ".(int) $src_folder;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);

		// move the subtree
		$this->shiftRLSpecific($src_left, $src_right, $dest_left - $src_left);

		// fix values from the gap created
		$this->shiftRL($src_right + 1, -$gap);

		$this->fixPath($src_folder, $dest_folder);

		return $output;
	}

	public function fixPath($src_folder, $dest_folder) {
		
		//Update path
		$query = "SELECT path"
				." FROM ".$this->tree_table.""
				." WHERE ".$this->t_id." = ".(int) $src_folder;
		list($src_path) = $this->db->fetch_row($this->db->query($query));

		$query = "SELECT path"
				." FROM ".$this->tree_table.""
				." WHERE ".$this->t_id." = ".(int) $dest_folder;
		list($dest_path) = $this->db->fetch_row($this->db->query($query));

		$query = "SELECT path"
				." FROM ".$this->tree_table.""
				." WHERE idParent = ".(int) $dest_folder
				." ORDER BY path DESC"
				." LIMIT 0, 1";
		list($path_max_new_folder) = $this->db->fetch_row($this->db->query($query));
		$path_max = (int) str_replace($dest_path.'/', '', $path_max_new_folder);
		$path_max++;

		$new_path = $dest_path.'/'.sprintf('%08s', $path_max);

		$query = "UPDATE ".$this->tree_table.""
				." SET path = REPLACE(path, '".$src_path."', '".$new_path."')"
				." WHERE path LIKE '".$src_path."%'";
		$this->db->query($query);
	}
	
	/**
	 * returns iLeft and iRight of a node
	 */
	public function getFolderLimits($id) {
		if ($id <= 0) {
			$query = "SELECT MIN(iLeft), MAX(iRight), 0 as lev FROM ".$this->tree_table."";
			$res = $this->db->query($query);
			$row = $this->db->fetch_row($res);
			if (is_array($row)) {
				$row[0]--;
				$row[1]++;
			}
		} else {
			$query = "SELECT iLeft, iRight, lev FROM ".$this->tree_table." WHERE ".$this->t_id."=".(int) $id."";
			$res = $this->db->query($query);
			$row = $this->db->fetch_row($res);
		}
		return $row;
	}

	public function shiftRL($from, $shift) {

		$q[] = $query1 = "UPDATE ".$this->tree_table." SET iLeft = iLeft + ".$shift." WHERE iLeft >= ".$from;
		$q[] = $query2 = "UPDATE ".$this->tree_table." SET iRight = iRight + ".$shift." WHERE iRight >= ".$from;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);
	}

	public function shiftRLSpecific($from, $to, $shift) {

		$q[] = $query1 = "UPDATE ".$this->tree_table." SET iLeft = iLeft + ".$shift." WHERE iLeft >= ".$from." AND iRight <= ".$to;
		$q[] = $query2 = "UPDATE ".$this->tree_table." SET iRight = iRight + ".$shift." WHERE iRight >= ".$from." AND iRight <= ".$to;
		$res1 = $this->db->query($query1);
		$res2 = $this->db->query($query2);
	}
	
}
