<?php

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
 * @module RendererDb.php
 *
 * @author Emanuele Sandri
 * @version $Id: RendererDb.php 113 2006-03-08 18:08:42Z ema $
 * @copyright 2004
 **/

require_once(dirname(__FILE__) . '/config.scorm.php');
require_once(dirname(__FILE__) . '/RendererBase.php');
require_once(dirname(__FILE__) . '/scorm_organizations.php');
require_once(dirname(__FILE__) . '/scorm_items.php');

class RendererDb extends RendererAbstract {

	// 6 class for any deep
 	var $stack;
 	var $deep = 0;
 	var $classPrefix = "ElemTree_";
	var $imgPrefix = "";
	var $imgOptions = "";
	var $resBase = "";
	var $dbconn;
	var $prefix;
	var $idpackage;
	
	function RendererDb( $connection, $prefix, $idpackage ) {
        $this->dbconn = $connection;
		$this->prefix = $prefix;
		$this->idpackage = $idpackage;
		$this->row = 0;
	}

	/**
	 *  @param $cpm reference to CPManager
	 *  @param $itemInfo info array:
	 *      'identifier'
	 *      'isLast'
	 *      'identifierref'
	 *      'isvisible'
	 *      'parameters'
	 *      'title'
	 *      'isLeaf'
	 */
	function RenderStartItem( $cpm, $itemInfo ){
		// Add some info to $itemInfo hash array
		$itemInfo['isEnd'] = FALSE;                 // the branch is not ended
		$itemInfo['idRow'] = $this->row;            // identifier of row
  		$this->stack[$this->deep]['nChild'] = 0;    // number of renderd child
		$this->stack[$this->deep]['nDescendant'] = 0;    // number of renderd descendant
		if($this->deep > 0) {
            // increase the parent's number of childs
        	$this->stack[$this->deep-1]['nChild']++;
			if ($itemInfo['identifierref']) {
    			// and increase the number of descendant of all ancestors
				for($nUp = $this->deep-1; $nUp >= 0; $nUp-- ) {
					$this->stack[$nUp]['nDescendant']++;
				}
			}
        	// set the sequence id. Progressive number in the set of siblings
        	$itemInfo['idSeq'] = $this->stack[$this->deep-1]['nChild'];
		} else {
            // set the sequence id. The root is always 1
            $itemInfo['idSeq'] = 1;
		}
		/* For debug
		echo "\n<!-- RenderStartItem deep=".$this->deep.", row=".$this->row
			 .",\nnChild=".$this->stack[$this->deep]['nChild'].", nDescendant=".$this->stack[$this->deep]['nDescendant'];
		if( $this->deep > 0) {
			echo ",\nparent_nChild=".$this->stack[$this->deep-1]['nChild'];
		}
		echo "\n-->";
		*/
		// store $itemInfo in a stack (array) for next usage
		$this->stack[$this->deep] = $itemInfo;
		
		if( $itemInfo['identifierref'] ) {
			$resInfo = $cpm->GetResourceInfo($itemInfo['identifierref']);
			$query = "SELECT idscorm_resource"
					." FROM ".$this->prefix."_scorm_resources"
					." WHERE idscorm_package = ". $this->idpackage
					." AND idsco = '". $itemInfo['identifierref'] ."'";
					
			$rs = sql_query( $query ) 
				or die( "Error on RenderStartItem query = $query " . sql_error($this->dbconn));
			if( sql_num_rows( $rs ) == 0 )
				die( "Error on RenderStartItem query = $query record not found" );
			list( $resInfo['uniqueid'] ) = sql_fetch_row( $rs );
		} 
		
		// if this is the last child then the parent is ended
		if( $itemInfo['isLast'] && $this->deep > 0 )
		    $this->stack[$this->deep - 1]['isEnd'] = TRUE;

		/* set of information to be saved
	 	 *      'identifier'
		 * 		idscorm_parentitem
		 * 		idscorm_organization
	 	 *      'isLast'
	 	 *      'identifierref'
		 * 		idscormresource
	 	 *      'isvisible'
	 	 *      'parameters'
	 	 *      'title'
	 	 *      'isLeaf'
		 *		'adlcp_prerequisites'
		 * 		'adlcp_maxtimeallowed'
		 *		'adlcp_timelimitaction'
		 *		'adlcp_datafromlms'
		 * 		'adlcp_masteryscore'
		 */
		$query = "INSERT INTO ";
		if( $this->deep == 0 ) { // there is an organization
			$query .= $this->prefix."_scorm_organizations ("
					."`org_identifier`, `idscorm_package`, `title`, "
					."`nChild`,`nDescendant`) VALUES ( "
					."'". addslashes($itemInfo['identifier']) ."', "
					.$this->idpackage . ", "
					."'".addslashes($itemInfo['title']) . "', "
					."0, 0 )";
		} else {
			if ($this->deep == 1) {
			    $parentid = 'NULL'; 	// null in idscorm_parentitem means 
										// that parent is the organization
			} else {
				$parentid = $this->stack[$this->deep-1]['uniqueid'];
			}
			
			$query .= $this->prefix."_scorm_items ("
					."`idscorm_organization`, `idscorm_parentitem`, "
					."`item_identifier`, `identifierref`, "
					."`idscorm_resource`, `isvisible`, "
					."`parameters`, `title`, "
					."`adlcp_prerequisites`, `adlcp_maxtimeallowed`, "
					."`adlcp_timelimitaction`, `adlcp_datafromlms`, "
					."`adlcp_masteryscore`,`adlcp_completionthreshold`,"
					."`nChild`,`nDescendant`) "
					."VALUES ("
					.$this->stack[0]['uniqueid']. ", "
					.$parentid. ", "
					."'". addslashes($itemInfo['identifier']) ."', "
					."'". addslashes($itemInfo['identifierref']) ."', "
					."'".(isset($resInfo['uniqueid'])?($resInfo['uniqueid']):"NULL")."', "
					."'". $itemInfo['isvisible'] ."', "
					."'". addslashes($itemInfo['parameters']) ."', "
					."'". addslashes($itemInfo['title']) ."', "
					."'". addslashes($itemInfo['adlcp_prerequisites']) ."', "
					."'". addslashes($itemInfo['adlcp_maxtimeallowed']) ."', "
					."'". addslashes($itemInfo['adlcp_timelimitaction']) ."', "
					."'". addslashes($itemInfo['adlcp_datafromlms']) ."', "
					."'". addslashes($itemInfo['adlcp_masteryscore']) ."', "
					."'". addslashes($itemInfo['adlcp_completionthreshold']) ."', "
					."0, 0 )";
		}
		
		if( sql_query($query, $this->dbconn) ) {
			if (sql_affected_rows($this->dbconn) == 1) {
				// get the id of the last insert = idscorm_tracking
				$this->stack[$this->deep]['uniqueid'] = sql_insert_id($this->dbconn);
			} else {
				die( "RendererDb::RenderStartItem Error in insert");
			} 			
		} else {
			die( "RendererDb::RenderStartItem Error in insert [$query] ". sql_error($this->dbconn) );
		}
		
		$this->deep++;
		$this->row++;
	}

	function RenderStopItem( $cpm, $itemInfo ){
		$this->deep--;
		if( $this->stack[$this->deep]['nChild'] > 0 ) {
			// We must update the record to fix the number of childs and descendant
			if ( $this->deep == 0 ) {
			    $query = "UPDATE ".$this->prefix."_scorm_organizations"
						." SET nChild=".$this->stack[$this->deep]['nChild']
						." , nDescendant=".$this->stack[$this->deep]['nDescendant']
						." WHERE idscorm_organization=".$this->stack[$this->deep]['uniqueid'];
			} else {
				$query = "UPDATE ".$this->prefix."_scorm_items"
						." SET nChild=".$this->stack[$this->deep]['nChild']
						." , nDescendant=".$this->stack[$this->deep]['nDescendant']
						." WHERE idscorm_item=".$this->stack[$this->deep]['uniqueid'];
			}
			if( sql_query($query, $this->dbconn) ) {
				if (sql_affected_rows($this->dbconn) != 1) {
					die( "RendererDb::RenderStopItem Error in update [$query]");
				} 			
			} else {
				die( "RendererDb::RenderStopItem Error in update [$query] ". sql_error($this->dbconn) );
			}
		}
		/* For debug
		echo "\n<!-- RenderStopItem deep=".$this->deep.", row=".$this->row
			 .",\nnChild=".$this->stack[$this->deep]['nChild'].", nDescendant=".$this->stack[$this->deep]['nDescendant'];
		if( $this->deep > 0) {
			echo ",\nparent_nChild=".$this->stack[$this->deep-1]['nChild'];
		}
		echo "\n-->";
		*/ 
	}
	
}


?>
