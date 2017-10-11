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

/**
 * @package  admin-library
 * @subpackage interaction
 * @version 	$Id: lib.treedb.php 974 2007-02-17 00:25:06Z giovanni $
 */

class Folder {
	// TreeDb object
	var $tdb;
	var $id;
	var $idParent;
	var $path;
	var $level;
	var $ileft;
	var $iRight;
	var $otherValues;
	var $countChildrens;
	var $nested;

	function Folder( &$tdb, $arrayValues, $childInfo = FALSE, $nested = false ) {
		$this->tdb = $tdb;
		$this->id = $arrayValues[0];
		$this->idParent = $arrayValues[1];
		$this->path = $arrayValues[2];
		$this->level = $arrayValues[3];
		$this->nested = $nested;

		if($nested) {
			$this->iLeft = $arrayValues[4];
			$this->iRight = $arrayValues[5];

			if( $childInfo ) {
				$this->otherValues = array_slice($arrayValues, 7);
				$this->countChildrens = $arrayValues[6];
			} else {
				if( is_array( $arrayValues ) ) {
					$this->otherValues = array_slice($arrayValues, 6);
					$this->hasChildrens = NULL;
				}
			}
		} else {
			if( $childInfo ) {
				$this->otherValues = array_slice($arrayValues, 5);
				$this->countChildrens = $arrayValues[4];
			} else {
				if( is_array( $arrayValues ) ) {
					$this->otherValues = array_slice($arrayValues, 4);
					$this->hasChildrens = NULL;
				}
			}
		}
	}

	function countChildrens() {
		return $this->countChildrens;
	}

	function getFolderName() {
		if( ($pos = strrpos($this->path, '/')) === FALSE )
			return $this->path;
		return substr( $this->path, $pos+1 );
	}

	function getFolderPath() {
		return $this->path;
	}

	function setFolderPath($path) {
		$this->path =$path;
	}

	function getParentPath() {
		if( ($pos = strrpos($this->path, '/')) === FALSE )
			return "";
		return substr( $this->path, 0, $pos );
	}

	function getChildrens() {
		$rs = $this->tdb->getChildrens( $this );
		$coll = new FoldersCollection( $this->tdb, $rs, false, $this->nested );
		return $coll;
	}

	function rename( $newFolderName ) {
		$this->tdb->renameFolder( $this, $newFolderName );
	}

	function delete() {
		$this->tdb->deleteTreeByPath( $this->path );
	}

	function move( $newParentFolder ) {
		$this->tdb->moveFolder( $this, $newParentFolder );
	}
}

class TreeDb {
	// table name
	var $table;
	// associative array of field's names
	// id -> id of the record
	// idParent -> id of the parent
	// path -> full path
	// lev -> level starting from 1; 0 is root
	var $fields;

	// database connection
	var $dbconn = NULL;

	function _listFields($tname = FALSE) {
		return $this->_getBaseFields($tname)
			 . $this->_getOtherFields($tname);
	}

	function isDISTINCT() { return FALSE; }
	function _getDISTINCT() { if( $this->isDISTINCT() ) return " DISTINCT "; }

	function _getBaseFields($tname = FALSE) {
		if( $tname === FALSE )
			return $this->fields['id'] 			.', '
				 . $this->fields['idParent'] 	.', '
				 . $this->fields['path'] 		.', '
				 . $this->fields['lev'];
		else
			return $tname.'.'.$this->fields['id'] 		.', '
				 . $tname.'.'.$this->fields['idParent'] .', '
				 . $tname.'.'.$this->fields['path'] 	.', '
				 . $tname.'.'.$this->fields['lev'];
	}

	function _getOtherTables() { return "";	}

	function _getJoinFilter($table = FALSE) { return FALSE; }

	function _outJoinFilter($table = FALSE ) {
		$jf = $this->_getJoinFilter($table);
		if( $jf === FALSE )
			return "";
		else
			return ' ON ('.$jf.')';
	}

	function _getArrBaseFields( $tname ) {
		return array( 	'id' => $tname.'.'.$this->fields['id'],
						'idParent' => $tname.'.'.$this->fields['idParent'],
						'path' => $tname.'.'.$this->fields['path'],
						'lev' => $tname.'.'.$this->fields['lev'] );
	}

	function _getOtherFields($tname = FALSE) { return ""; }
	// As the previous one, but used only in the select fields list:
	function _getOtherSelectFields($tname = FALSE) { return ""; }
	function _getOtherValues() { return ""; }
	function _getOtherUpdates() { return ""; }

	function _getParentJoinFilter( $t1name, $t2name ) { return ""; }

	function _getFilter() { return ""; }
	function _getOrderBy( $tname ) {
		$fields = $this->_getArrBaseFields( $tname );
		return $fields['path'];
	}


	function _executeQuery( $query ) {
		if( $this->dbconn === NULL )
			$rs = sql_query( $query );
		else
			$rs = sql_query( $query, $this->dbconn );
		return $rs;
	}

	function _executeInsert( $query ) {
		$res = null;
		if( $this->dbconn === NULL ) {
			$res = sql_query( $query );
		} else {
			$res = sql_query( $query, $this->dbconn );
		}

		if (!$res) return FALSE;
		if( $this->dbconn === NULL )
			return sql_insert_id();
		else
			return sql_insert_id($this->dbconn);
	}

	function _printSQLError( $funcname ) {
		if( $this->dbconn === NULL )
			die( "Error on $funcname " . sql_error() );
		else
			die( "Error on $funcname " . sql_error($this->dbconn) );
	}

	function getChildrensIdById( $idFolder ) {
		$fields = $this->_getArrBaseFields( $this->table );
		$query = "SELECT ".$this->_getDISTINCT(). $fields['id']
				." FROM ". $this->table.$this->_getOtherTables()
				.$this->_outJoinFilter($this->table)
				." WHERE ((". $fields['idParent'] ." = '". (int)$idFolder ."')"
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

	/**
	 * return a record set with all childrens of given folder
	 * @param $foder parent folder
	 * @return ResultSet of all childrens
	 **/
	function getChildrensByFolder( &$folder ) {
		return $this->getChildrensById( $folder->id );
	}

	/**
	 * @param Folder $folder il folder di cui si vogliono tutti gli avi
	 * @param TreeDb $tdb    l'albero cui $folder appartiene
	 * @return array un array contenente tutti gli id degli avi di $folder
	 *               nella posizione 0 c'e' il padre di $folder, nella
	 *               posizione 1 c'e' il nonno etc etc.
	 **/
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

	/**
	 * Propagate change in a folder in all chidrens
	 * The changes to propagate are path and level
	 * @param $prevFolder previous folder
	 * @param $newFolder new folder
	 **/
	function _propagateChange( $prevFolder, $newFolder ) {
		$len = strlen( $prevFolder->path )+1;
		$levDiff = $newFolder->level - $prevFolder->level;
		$query = "UPDATE ". $this->table
				." SET "
				. $this->fields['path']
				." = CONCAT('". $newFolder->path ."', SUBSTRING( path, ". $len ." )),"
				. $this->fields['lev'] ." = ( ".$this->fields['lev']." + (".$levDiff.")) "
				." WHERE ((path LIKE '".sql_escape_string($prevFolder->path)."/%')"
				."   AND (".$this->fields['id']." != '".$prevFolder->id."')) "
				.$this->_getFilter();
		return $this->_executeQuery( $query );
	}

	function &getRootFolder() {
		$folder = new Folder( $this, array( 0, 0, "/root", 0) );
		return $folder;
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
		$coll = new FoldersCollection( $this, $rs, TRUE );
		return $coll;
	}

	/**
	 * Get folder by id
	 * @param $id id of the folder to retrieve
	 * @return Folder object or NULL if not found
	 **/
	function &getFolderById( $id ) {

		if( $id == 0 ) {
			$folder =& $this->getRootFolder();
		} else {
			$fields = $this->_getArrBaseFields( $this->table );
			$query = "SELECT ".$this->_getDISTINCT(). $this->_listFields($this->table)
					." FROM ". $this->table.$this->_getOtherTables()
					.$this->_outJoinFilter($this->table)
					." WHERE (". $fields['id'] ." = '". (int)$id ."')"
					.$this->_getFilter($this->table);
			$rs = $this->_executeQuery( $query )
					or $this->_printSQLError( 'getFolderById' );
			if( sql_num_rows($rs) == 0 ) {
				$false_var = NULL;
				return $false_var;
			}
			$folder = new Folder( $this, sql_fetch_row($rs) );
		}
		return $folder;
	}

	/**
	 * Get folder by path
	 * @param int $id id of the folder to retrieve
	 * @return Folder object or NULL if not found
	 **/
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
		$folder = new Folder( $this, sql_fetch_row($rs) );
		return $folder;
	}

	/**
	 * Get folder path array from id array
	 * @param array $arr_id array of id
	 * @return array the array of path
	 * @access public
	**/
	function getPathFromFolderId( $arr_id ) {
		$query = "SELECT ".$this->fields['id'].", ".$this->fields['path']
				." FROM ".$this->table
				." WHERE ".$this->fields['id']." IN ('".implode("','",$arr_id)."')";
		$rs = $this->_executeQuery( $query )
				or $this->_printSQLError( 'getPathFromFolderId: '. $query );
		$arr_result = array();
		while( list( $idFolder, $path ) = sql_fetch_row($rs) ) {
			$arr_result[$idFolder] = $path;
		}
		return $arr_result;
	}

	/**
	 * Add a folder children of folder identified by id
	 * @param idParent id of parent folder
	 * @param folderName name of folder to add
	 **/
	function _addFolder( $idParent, $path, $level ) {
		$query = "INSERT into ". $this->table
			."( ". $this->_listFields()
			.") VALUES ("
			. "NULL,'". (int)$idParent ."','". $path. "','". (int)$level ."'"
			. $this->_getOtherValues()
			.")";
		$id = $this->_executeInsert( $query )
				or $this->_printSQLError( '_addFolder: '. $query );
		return $id;
		//$folder = new Folder( $this, array( $id, $idParent, $path, $level) );
	}

	/**
	 * Add a folder children of folder identified by id
	 * @param idParent id of parent folder
	 * @param folderName name of folder to add
	 **/
	function addFolderById( $idParent, $folderName ) {
		$parent = $this->getFolderById( $idParent );
		$path = sql_escape_string($parent->path). "/" .$folderName;
		$level = $parent->level + 1;
		return $this->_addFolder( $idParent, $path, $level );
	}

	function addFolderByPath( $fullPath, $createAll ) {
		$fields = $this->_getArrBaseFields( $this->table );
		// search most near folder
		$query = "SELECT ".$this->_getDISTINCT(). $this->_listFields($this->table)
				." FROM ". $this->table
				." WHERE (('". $fullPath . "' LIKE CONCAT(". $fields['path']. ",'%'))"
				.$this->_getFilter($this->table)
				.") ORDER BY ". $this->_getOrderBy($this->table)
				." LIMIT 0,1";
		$rs = $this->_executeQuery( $query )
				or $this->_printSQLError( 'addFolderByPath' );
		$parentFolder = new Folder( $this, sql_fetch_row($rs) );

		// get path tokens
		$pathTokens = explode( '/', $fullPath );

		// verify level
		if( count($pathTokens) <= $parentFolder->level )	// directory exist
			return FALSE;
		if( count($pathTokens) > ($parentFolder->level+1) && !$createAll ) // only one level
			return FALSE;

		$newFolder = $parentFolder;
		for( $index = $parentFolder->level+1; $index < count($pathTokens); $index++ ) {
			$newFolder = $this->_addFolder( $newFolder->id,
											sql_escape_string($newFolder->path) . $pathTokens[$index],
											$newFolder->level +1);
		}

		return $newFolder;
	}

	function _deleteTree( $folder ) {
		if( $folder === NULL )
			return FALSE;
		if( trim($folder->path) == '' ) // this remove all!!
			return FALSE;
		$query = "DELETE FROM ". $this->table
				." WHERE ((". $this->fields['path'] ." LIKE '" . addslashes($folder->path) . "/%')"
				."    OR  (". $this->fields['id'] ." = '".(int)$folder->id ."'))"
				.$this->_getFilter();
		$this->_executeQuery( $query )
			or $this->_printSQLError( '_deleteTree' );
		return TRUE;
	}

	function deleteAllTree( ) {
		$query = "DELETE FROM ". $this->table
				." WHERE 1 ".$this->_getFilter();
		$this->_executeQuery( $query )
			or $this->_printSQLError( 'deleteAllTree' );
		return TRUE;
	}

	function deleteTreeById( $id ) {
		$folder = $this->getFolderById( $id );
		return $this->_deleteTree( $folder );
	}

	function deleteTreeByPath( $path ) {
		$folder = $this->getFolderByPath( $path );
		return $this->_deleteTree( $folder );
	}

	/**
	 * Return TRUE if a folderA is ancestor of folderB
	 * NOTE: this function don't query the db
	 * NOTE: if folderA === folderB return is TRUE
	 * @param folderA possible ancestor folder
	 * @param folderB possible descentant
	 * @return 	- TRUE if folderA is ancestr of folderB
	 *			- FALSE otherwise
	 **/
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
				. $this->fields['path']	." = '". addslashes($folder->path) ."',"
				. $this->fields['lev'] ." = '".$folder->level ."'"
				." WHERE (". $this->fields['id'] ." = '". $folder->id ."')"
				.$this->_getFilter();
		$this->_executeQuery( $query )
			or $this->_printSQLError( 'moveFolder' );
		$this->_propagateChange( $oldFolder, $folder);
	}

	function renameFolder( &$folder, $newName ) {
		$oldFolder =((version_compare(phpversion(), '5.0') < 0) ? $folder : clone($folder));

		$folder->path = $oldFolder->getParentPath() . "/" . $newName;
		$query = "UPDATE ". $this->table
				." SET "
				. $this->fields['path']	." = '". addslashes($folder->path) ."'"
				." WHERE ((". $this->fields['id'] ." = '". $folder->id ."')"
				.$this->_getFilter() .")";
		$res = $this->_executeQuery( $query );
		if (!$res) $this->_printSQLError( 'renameFolder ['.$query.']' );
		$this->_propagateChange( $oldFolder, $folder);
		return $res;
	}

	function changeOtherData( &$folder ) {
		$query = "UPDATE ". $this->table
				." SET "
				. $this->_getOtherUpdates()
				." WHERE (". $this->fields['id'] ." = '". $folder->id ."')"
				.$this->_getFilter();
		$this->_executeQuery( $query )
			or $this->_printSQLError( 'changeOtherData' );
	}
}

class FoldersCollection {
	var $rs;
	var $tdb;
	var $childInfo;
    var $nested;

	function FoldersCollection( &$tdb, $rs, $childInfo = FALSE, $nested = false ) {
		$this->tdb = $tdb;
		$this->rs = $rs;
		$this->childInfo = $childInfo;
		$this->nested = $nested;
	}

	function count() {
		return sql_num_rows( $this->rs );
	}

	function getFirst() {
		if( !sql_data_seek ( $this->rs, 0 ) )
			return FALSE;

		return $this->getNext();
	}

	function getNext() {

		$array = sql_fetch_row( $this->rs );
		if( $array === FALSE )
			return FALSE;
		$folder = new Folder( $this->tdb, $array, $this->childInfo, $this->nested );
		return $folder;
	}
}

?>
