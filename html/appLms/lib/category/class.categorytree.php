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

require_once($GLOBALS['where_lms'].'/admin/modules/category/tree.category.php');

class CategoryTree extends TreeDb_CatDb {

	// Constructor of CategoryTree class
	function CategoryTree() {
		$this->table = '%lms_category';
		$this->fields = array(
			'id' => 'idCategory',
			'idParent' => 'idParent',
			'path' => 'path',
			'lev' => 'lev',
			'iLeft' => 'iLeft',
			'iRight' => 'iRight'
		);
	}

	function _getBaseFields($tname = FALSE) {
		if( $tname === FALSE )
			return $this->fields['id'] .', '
			. $this->fields['idParent'] .', '
			. $this->fields['path'] .', '
			. $this->fields['lev'] .', '
			. $this->fields['iLeft'] .', '
			. $this->fields['iRight'];
		else
			return $tname.'.'.$this->fields['id'] .', '
			. $tname.'.'.$this->fields['idParent'] .', '
			. $tname.'.'.$this->fields['path'] .', '
			. $tname.'.'.$this->fields['lev'] .', '
			. $tname.'.'.$this->fields['iLeft'] .', '
			. $tname.'.'.$this->fields['iRight'];
	}

	function _getArrBaseFields( $tname ) {
		return array(
			'id' => $tname.'.'.$this->fields['id'],
			'idParent' => $tname.'.'.$this->fields['idParent'],
			'path' => $tname.'.'.$this->fields['path'],
			'lev' => $tname.'.'.$this->fields['lev'],
			'iLeft' => $tname.'.'.$this->fields['iLeft'],
			'iRight' => $tname.'.'.$this->fields['iRight']
		);
	}

	function _getOtherFields($tname = FALSE) { return ""; }
	function _getOtherSelectFields($tname = FALSE) { return ""; }

	function _getOrderBy( $tname ) {
		$fields = $this->_getArrBaseFields( $tname );
		return $fields['path'];
	}

	function &getFolderById( $id ) {

		if( $id <= 0 ) {
			$folder =& $this->getRootFolder();
		} else {
			$fields = $this->_getArrBaseFields( $this->table );
			$query = "SELECT ".$this->_getDISTINCT(). $this->_listFields($this->table)
                ." FROM ". $this->table.$this->_getOtherTables()
                .$this->_outJoinFilter($this->table)
                ." WHERE (". $fields['id'] ." = '". (int)$id ."') "
                .$this->_getFilter($this->table);
			$rs = $this->_executeQuery( $query )
			or $this->_printSQLError( 'getFolderById' );
			if( sql_num_rows($rs) == 0 ) {
				$false_var = NULL;
				return $false_var;
			}
            
			$folder = new Folder( $this, sql_fetch_row($rs), false, true );
		}
		return $folder;
	}

	function &getFolderByPath( $path ) {
		$fields = $this->_getArrBaseFields( $this->table );
		$query = "SELECT ".$this->_getDISTINCT(). $this->_listFields($this->table)
				." FROM ". $this->table.$this->_getOtherTables()
				.$this->_outJoinFilter($this->table)
				." WHERE (". $fields['path']. "='".sql_escape_string($path)."')"
				.$this->_getFilter($this->table);
		$rs = $this->_executeQuery( $query )
				or $this->_printSQLError( 'getFolderByPath: '. $query );
		if( sql_num_rows($rs) == 0 ) {
			$false_var = NULL;
			return $false_var;
		}
		$folder = new Folder( $this, sql_fetch_row($rs), false, true );
		return $folder;
	}

	function &getRootFolder() {
		
		$fields = $this->_getArrBaseFields( $this->table );
		$query = "SELECT COUNT(*)"
		." FROM ". $this->table.$this->_getOtherTables()
		.$this->_outJoinFilter($this->table)
		." WHERE 1 "
		.$this->_getFilter($this->table);
		$rs = $this->_executeQuery( $query )
			or $this->_printSQLError( 'getFolderById' );
		if( sql_num_rows($rs) == 0 ) {
			$false_var = NULL;
			return $false_var;
		}
		list($num_row) = sql_fetch_row($rs);
		$folder = new Folder( $this, array( 0, 0, "/root", 0, 1, $num_row * 2 ), false, true );
		return $folder;
		//return $this->getFolderById( 0 );
	}

	function _addFolder( $idParent, $path, $level ) {
		$fields = $this->_getArrBaseFields( $this->table );
		$limits = $this->_getFolderLimits( $idParent );
		$new_limits = array( 'iLeft' => $limits['iRight'], 'iRight' => $limits['iRight']);

		//updating left limits
		$query = "UPDATE ". $this->table
		." SET ".$fields['iRight']."=".$fields['iRight']."+2"
		." WHERE ".$fields['iRight'].">=".$new_limits['iRight'];
		$rsl = $this->_executeQuery( $query );
		//TO DO: handle error case (if !$rs ... )

		//updating right limits
		$query = "UPDATE ". $this->table
		." SET ".$fields['iLeft']."=".$fields['iLeft']."+2"
		." WHERE ".$fields['iLeft'].">=".$new_limits['iLeft'];
		$rsr = $this->_executeQuery( $query );
		//TO DO: handle error case (if !$rs ... )

		//error handling
		//if (!rsl || !rsr) $this->_restoreAllLimits(); (TO DO ...)

		//insert the new node
		$query = "INSERT into ". $this->table." ( ". $this->_listFields().") VALUES ("
		. "NULL,'". (int)$idParent ."','". $path. "','". (int)$level ."','". $new_limits['iLeft'] ."','". ($new_limits['iRight']+1) ."' "
		. $this->_getOtherValues()
		.")";
		$id = $this->_executeInsert( $query ) or $this->_printSQLError( '_addFolder: '. $query );

		return $id;
	}

	function _deleteTree( $folder, $onlyLeaf = false ) {
		if( $folder === NULL )
			return FALSE;
		if( trim($folder->path) == '' ) // this remove all!!
			return FALSE;

		$limits = $this->_GetFolderLimits($folder->id);
		if ($onlyLeaf) {
			if ( ((int)$limits['iRight'] - (int)$limits['iLeft']) > 1 ) return FALSE;
		}

		$query = "DELETE FROM ". $this->table
			." WHERE ". $this->fields['iLeft'] ." >= " .$limits['iLeft']. " "
			."    AND ". $this->fields['iRight'] ." <= " .$limits['iRight']. " "
			.$this->_getFilter();
		$this->_executeQuery( $query ) or $this->_printSQLError( '_deleteTree' );

		$shift = $limits['iRight'] - $limits['iLeft'] + 1; //or -1 ??

		$query = "UPDATE ".$this->table." SET ".$this->fields['iLeft']."=".$this->fields['iLeft']."-".$shift." WHERE ".$this->fields['iLeft'].">=".$limits['iLeft'];
		$this->_executeQuery( $query );
		$query = "UPDATE ".$this->table." SET ".$this->fields['iRight']."=".$this->fields['iRight']."-".$shift." WHERE ".$this->fields['iRight'].">=".$limits['iRight'];
		$this->_executeQuery( $query );
		//handle error ....
		//...

		return TRUE;
	}

	function _getFolderLimits( $idFolder, $lv = false ) {
		if($idFolder == 0) {

			$fields = $this->_getArrBaseFields( $this->table );
			$query = "SELECT COUNT(*)"
			." FROM ". $this->table.$this->_getOtherTables()
			.$this->_outJoinFilter($this->table)
			." WHERE 1 "
			.$this->_getFilter($this->table);
			$rs = $this->_executeQuery( $query )
				or $this->_printSQLError( 'getFolderById' );
			if( sql_num_rows($rs) == 0 ) {
				$false_var = NULL;
				return $false_var;
			}
			list($num_row) = sql_fetch_row($rs);
			$result = array('iLeft'=> 1, 'iRight'=> $num_row * 2 );
			return $result;
		}
		$fields = $this->_getArrBaseFields( $this->table );
		$query = "SELECT ".$fields['iLeft'].", ".$fields['iRight'].($lv ? ", ".$fields['lev'] : "")." FROM ". $this->table." WHERE ".$fields['id']." = '".$idFolder."'";
		$rs = $this->_executeQuery( $query );
		if (!$rs) return FALSE;
		if (sql_num_rows($rs) === 0) {
			return FALSE;
		} else {
			$result = sql_fetch_array($rs);
			return $result;
		}
	}

	function getDescendantsById( $idFolder ) {
		$limits = $this->_getFolderLimits( $idFolder );
		$fields = $this->_getArrBaseFields( $this->table );
		$query = "SELECT ".$this->_getDISTINCT(). $this->_listFields($this->table)
		." FROM ". $this->table.$this->_getOtherTables()
		.$this->_outJoinFilter($this->table)
		." WHERE ".$fields['iLeft'].">=".$limits['iLeft']
		." AND ".$fields['iRight']."<=".$limits['iRight']
		." ORDER BY ". $this->_getOrderBy($this->table);
		$rs = $this->_executeQuery( $query )
		or $this->_printSQLError( 'getDescendantsById' );
		return $rs;
	}

	//------------------------------------------------------------------------------
	// not converted
	//------------------------------------------------------------------------------


	/**
	 * return a record set with all childrens of given folder
	 * @param $idFolder id of parent folder
	 * @return ResultSet of all childrens
	 **/
	function getChildrensById( $idFolder ) {
		$fields = $this->_getArrBaseFields( $this->table );
		$query = "SELECT ".$this->_getDISTINCT(). $this->_listFields($this->table)
		." FROM ". $this->table.$this->_getOtherTables()
		.$this->_outJoinFilter($this->table)
		." WHERE ((". $fields['idParent'] ." = '". (int)$idFolder ."')"
		.$this->_getFilter($this->table)
		.") ORDER BY ". $this->_getOrderBy($this->table);
		$rs = $this->_executeQuery( $query )
		or $this->_printSQLError( 'getChildrensById' );
		return $rs;
	}

	function getJoinedChildrensById( $idFolder ) {
		$fields = $this->_getArrBaseFields( $this->table );
		$query = "SELECT ".$this->_getDISTINCT(). $this->_listFields($this->table).', COUNT(t2.idCourse)'
				." FROM ". $this->table." LEFT JOIN %lms_course as t2"
				." ON ( ". $this->table.".idCategory = t2.idCategory )"
			." WHERE ((". $fields['idParent'] ." = '". (int)$idFolder ."')"
				.$this->_getFilter($this->table)
			.") GROUP BY ". $this->table.".idCategory "
			."ORDER BY ". $this->_getOrderBy($this->table);
		
		$rs = $this->_executeQuery( $query );
		return $rs;
	}

	function getFoldersCollection( &$arrayId ) {
		$query = "SELECT ".$this->_getDISTINCT(). $this->_getBaseFields('t1') .", count(t2.".$this->fields['id'].") "
				. $this->_getOtherSelectFields('t1')
				. $this->_getOtherFields('t1')
				." FROM ". $this->table ." AS t1 LEFT JOIN ". $this->table ." AS t2"
				."		ON (t1.".$this->fields['id']." = t2.".$this->fields['idParent']
				.$this->_getParentJoinFilter( 't1','t2' ).")"
				.$this->_getOtherTables( 't1' )
				.$this->_outJoinFilter( 't1' );
		if( $arrayId === NULL )
			$query .=" WHERE ((1) "
					.$this->_getFilter('t1');
		else
			$query .=" WHERE ((t1.". $this->fields['id'] ." IN (". implode( ',', $arrayId) ."))"
					//."   AND ((t1.".$this->fields['id']." = t2.".$this->fields['idParent'] .")"
					//."    OR  (t1.".$this->fields['id']." = 0 ))"
					.$this->_getFilter('t1');
		$query .=") ". " AND t1.".$this->fields['id']."<>0 "
				."GROUP BY ". $this->_getBaseFields('t1')
				. $this->_getOtherFields('t1')
				." ORDER BY ". $this->_getOrderBy("t1");

		$rs = $this->_executeQuery( $query )
				or $this->_printSQLError( 'getFoldersCollection: '.$query );
		$coll = new FoldersCollection( $this, $rs, TRUE, true );
		return $coll;
	}

	function getDescendantsId( $folder ) {
		$fields = $this->_getArrBaseFields( $this->table );
		$query = "SELECT ".$this->_getDISTINCT(). $fields['id']
		." FROM ". $this->table.$this->_getOtherTables()
		.$this->_outJoinFilter($this->table)
		." WHERE ((path LIKE '".(($folder->id == 0)?"":sql_escape_string($folder->path))."/%')"
		."   AND (".$fields['id']." != '".$folder->id."') "
		.$this->_getFilter($this->table)
		.") ORDER BY ". $this->_getOrderBy($this->table);
		$rs = $this->_executeQuery( $query )
			or die( sql_error() . " [ $query ]");
		// or $this->_printSQLError( 'getChildrensById' );
		if( sql_num_rows( $rs ) === 0 ) {
			return FALSE;
		} else {
			$result = array();
			while( list($id) = sql_fetch_row( $rs ) )
			$result[] = $id;
		}
		return $result;
	}

	function checkAncestor( $folderA, $folderB ) {
		if( strpos( $folderB->path, $folderA->path ) !== FALSE )
			return TRUE;
		else
			return FALSE;
	}

	function moveFolder( &$folder, &$parentFolder, $newfoldername = FALSE ) {
		$oldFolder =((version_compare(phpversion(), '5.0') < 0) ? $folder : clone($folder));

		$folder->idParent = $parentFolder->id;
		$folder->path = (($parentFolder->id == 0)?'/root/':($parentFolder->path . "/"))
		.(($newfoldername!==FALSE)?$newfoldername:$oldFolder->getFolderName());

		$folder->level = $parentFolder->level+1;
		$query = "UPDATE ". $this->table
		." SET "
		. $this->fields['idParent'] ." = '".$folder->idParent ."',"
		. $this->fields['path'] ." = '". $folder->path ."',"
		. $this->fields['lev'] ." = '".$folder->level ."'"
		." WHERE (". $this->fields['id'] ." = '". $folder->id ."')"
		.$this->_getFilter();
		$this->_executeQuery( $query )
		or $this->_printSQLError( 'moveFolder' );
		$this->_propagateChange( $oldFolder, $folder);
	}
	
	function getAllParentId( &$folder, &$tdb ) {

		$path = $folder->getParentPath();
		$arr_ancestors = array();
		while($path != "") {

			$parentFolder =& $tdb->getFolderByPath($path);
			if($parentFolder !== NULL && $parentFolder->id != false) {
				$arr_ancestors[] = $parentFolder->id;
				$path = $parentFolder->getParentPath();
			} else {
				$path = '';
			}
		}
		return $arr_ancestors;
	}

	function getChildrenCount( $idFolder ) {
			$limits = $this->_getFolderLimits( $idFolder );
			return (int)(($limits['iRight'] - $limits['iLeft'] -1)/2);
	}

	function renameFolderById( $idFolder, $newName) {
		return $this->renameFolder( $this->getFolderById($idFolder), $newName );
	}

	/*
	function getChildrensByIdList( $idFolderList ) {
		$fields = $this->_getArrBaseFields( $this->table );

		$query = "SELECT ".$this->_getDISTINCT(). $fields['id']
		." FROM ". $this->table.$this->_getOtherTables()
		.$this->_outJoinFilter($this->table)
		." WHERE ".$fields['id']." IN (".implode(",", $idFolderList).") "
		.$this->_getFilter($this->table)
		.") ORDER BY ". $this->_getOrderBy($this->table);
		$rs = $this->_executeQuery( $query )
			or die( sql_error() . " [ $query ]");

		$query =
	}
	*/

	function getOpenedFolders( $idFolder ) {
		$limits = $this->_getFolderLimits( $idFolder );
		$query = "SELECT idCategory FROM ".$GLOBALS['prefix_lms']."_category ".
				"WHERE iLeft <= ".$limits['iLeft']." AND iRight >= ".$limits['iRight']." AND idCategory > 0 ORDER BY iLeft";
		$res = sql_query($query);
		$folders = array(0);
		while (list($id_cat) = sql_fetch_row($res)) { $folders[] = (int)$id_cat; }

		return  $folders;
	}

	function getCategoryName($id) {
		list($name) = sql_fetch_row( sql_query( "SELECT path FROM ".$this->table." WHERE idCategory=".$id ) );
		return end( explode('/', $name) );
	}


	//*** nested set move folder ***----------------------------------------------

	function _shiftRL($from, $shift) {
		$query1 = "UPDATE ".$this->table." SET iLeft = iLeft + ".$shift." WHERE iLeft >= ".$from;
		$query2 = "UPDATE ".$this->table." SET iRight = iRight + ".$shift." WHERE iRight >= ".$from;
		$res1 = sql_query($query1);
		$res2 = sql_query($query2);
	}

	function _shiftRLSpecific($from, $to, $shift) {
		$query1 = "UPDATE ".$this->table." SET iLeft = iLeft + ".$shift." WHERE iLeft >= ".$from." AND iRight <= ".$to;
		$query2 = "UPDATE ".$this->table." SET iRight = iRight + ".$shift." WHERE iRight >= ".$from." AND iRight <= ".$to;
		$res1 = sql_query($query1);
		$res2 = sql_query($query2);
	}

	function move($src_folder, $dest_folder) {
		if ($src_folder <= 0) return false;
		if ($dest_folder <= 0) return false;
		$output = true;//false;

		list($src_left, $src_right, $lvl_src) = $this->_getFolderLimits($src_folder, true);
		list($dest_left, $dest_right, $lvl_dest) = $this->_getFolderLimits($dest_folder, true);

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
		$query1 = "UPDATE ".$this->table." SET idParent = ".(int)$dest_folder." WHERE idCategory = ".(int)$src_folder;
		$query2 = "UPDATE ".$this->table." SET lev = lev + ".$lvl_gap." WHERE iLeft > ".$src_left." AND iRight < ".$src_right;
		$res1 = sql_query($query1);
		$res2 = sql_query($query2);

		// move the subtree
		$this->_shiftRLSpecific($src_left, $src_right, $dest_left - $src_left);

		// fix values from the gap created
		$this->_shiftRL($src_right + 1, -$gap);

		//update path column for source
		list($parent_level, $parent_path) = sql_fetch_row(sql_query("SELECT lev, path FROM ".$this->table." WHERE idCategory=".(int)$dest_folder));
		list($source_path) = sql_fetch_row(sql_query("SELECT path FROM ".$this->table." WHERE idCategory=".(int)$src_folder));
		$source_name = end(explode("/", $source_path));
		$res = sql_query("UPDATE ".$this->table." SET lev=".(int)($parent_level+1).", path='".$parent_path.'/'.$source_name."' WHERE idCategory=".(int)$src_folder);

		return $output;
	}

}

?>