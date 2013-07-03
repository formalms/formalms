<?php defined("IN_DOCEBO") or die("Direct access is forbidden");

/* ======================================================================== \
  | 	DOCEBO - The E-Learning Suite										|
  | 																		|
  | 	Copyright (c) 2010 (Docebo)											|
  | 	http://www.docebo.com												|
  |   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
  \ ======================================================================== */

Class CoursecategoryAlms extends TreeModel {

	public function __construct() {
		$this->db = DbConn::getInstance();
		$this->t_id = 'idCategory';
		$this->tree_table = '%lms_category';
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

		$folder_name = array_pop(explode('/', $src_path));

		$new_path = $this->db->escape(($dest_folder == 0?'/root/':( $dest_path. "/")).$folder_name);
		
		$query = "UPDATE ".$this->tree_table.""
				." SET path = REPLACE(path, '".$src_path."', '".$new_path."')"
				." WHERE path LIKE '".$src_path."%'";
		$this->db->query($query);
	}
}
