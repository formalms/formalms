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
 * @module scorm_items_track.php
 *
 * @version $Id: scorm_items_track.php 891 2007-01-05 12:09:06Z ema $
 * @copyright 2004 
 **/
/*
 * Org1
 * 		Item1.1 -------------> Res1 -> Track20 [complete]
 *  	Item1.2 
 *  		Item1.2.1
 * 				Item1.2.1.1 -> Res2 -> Track21 [passed]
 * 				Item1.2.1.2 -> Res3 -> Track22 [failed]
 * 			Item1.2.2 -------> Res4 -> Track23 [incomplete]
 * 			Item1.2.3 -------> Res5 -> null [--]
 *  	Item1.3 -------------> Res6 -> null [--]
 * 
 * User50	
 * 
 * Org1 ---------------------> ItemTrack100 [Org1,        null, User50,    null,   incomplete ]
 * 		Item1.1 -------------> ItemTrack101	[Org1,     Item1.1, User50, Track20,     complete ]
 *  	Item1.2 -------------> ItemTrack102 [Org1,     Item1.2, User50,    null,   incomplete ]
 *  		Item1.2.1 -------> ItemTrack103 [Org1,   Item1.2.1, User50,    null,   incomplete ]
 * 				Item1.2.1.1 -> ItemTrack104 [Org1, Item1.2.1.1, User50, Track21,       passed ]
 * 				Item1.2.1.2 -> ItemTrack105 [Org1, Item1.2.1.2, User50, Track22,       failed ]
 * 			Item1.2.2 -------> ItemTrack106 [Org1,   Item1.2.2, User50, Track23,   incomplete ]
 * 			Item1.2.3 -------> ItemTrack107 [Org1,   Item1.2.3, User50,    null,    ab initio ]
 *  	Item1.3 -------------> ItemTrack108 [Org1,     Item1.3, User50,    null,    ab initio ]
 * 
 * At the first open of the organization we must create all needed records (only visible?)
 * All records must be initialized with status = ab initio
 * When a tracking change status we must recalculate the status of all parents
 * 
 * This table as 	a 1:1 relation with _scorm_tracking
 *					a n:1 relation with _scorm_organizations
 *					a m:1 relation with _organizations
 */ 
 
 
require_once(dirname(__FILE__) . '/config.scorm.php');
require_once(dirname(__FILE__) . '/scorm_utils.php');

Class Scorm_ItemsTrack {
	var $dbconn;
	var $prefix;
	var $main_table;
	
	
	/**
	 * Scorm_ItemsTrack::Scorm_ItemsTrack()
	 * Constructor
	 * @param $connection connection to the database
	 * @param $prefix the prefix to prepend to all tables names
	 **/
	function Scorm_ItemsTrack( $connection, $prefix ) {
		$this->dbconn = $connection;
		$this->prefix = $prefix;
		$this->main_table = $prefix."_scorm_items_track";
	}
	
	function getTrackingScore( $idscorm_tracking ) {
		$query = "SELECT score_raw FROM ".$this->prefix."_scorm_tracking WHERE idscorm_tracking='".(int)$idscorm_tracking."'";
		$rs = sql_query($query, $this->dbconn) or die( "Scorm_ItemsTrack::getTrackingScore error in select [$query] ". sql_error($this->dbconn) );
		list( $score ) = sql_fetch_row($rs);
		return $score;
	}
	
	/**
	 *	Scorm_ItemsTrack::getItemTrackById
	 *  Return a recordset with the item_track record
	 *	The entry is identified by idscorm_item_track
	 *
	 *	@param $idscorm_item_track
	 *	@return record set resource or FALSE
	 **/
	function getItemTrackById( $idscorm_item_track ) {
		$query = "SELECT idscorm_item_track, idscorm_item, idReference, idscorm_tracking,"
				." status, nChild, nChildCompleted, nDescendant, nDescendantCompleted"
				." FROM ".$this->main_table
				." WHERE idscorm_item_track='".(int)$idscorm_item_track."'";
		$rs = sql_query($query, $this->dbconn);
		if( $rs === FALSE ) 
			die( "Scorm_ItemsTrack::getItemTrackById error in select [$query] ". sql_error($this->dbconn) );
		if( sql_num_rows($rs) == 0 )
			return FALSE;
		return $rs;		
	}	

	/**
	 *	Scorm_ItemsTrack::getItemTrack
	 *  Return a recordset with the item_track record
	 *	The entry is identified by idUser, idReference, (idscorm_item|idscorm_organization)
	 *
	 *	@param $idUser user id of the record
	 *	@param $idReference id of the related item in lesson area
	 *	@param $idscorm_item id of the item of the record to retrieve
	 *						if is null we can search the entry of the 
	 *						scorm organization
	 *	@param $idscorm_organization id of the organization to get
	 *	@return record set resource or FALSE
	 **/
	function getItemTrack( $idUser, $idReference, $idscorm_item, $idscorm_organization = null ) {
		if( $idscorm_item === null ) {
			$query = "SELECT idscorm_item_track, idscorm_item, idReference, idscorm_tracking,"
					." status, nChild, nChildCompleted, nDescendant, nDescendantCompleted"
					." FROM ".$this->main_table
					." WHERE idUser='".(int)$idUser."'"
					." AND idscorm_item IS NULL"
					//." AND idReference = '".(int)$idReference."'"
					." AND idscorm_organization = '".(int)$idscorm_organization."'";
		} else {
			$query = "SELECT idscorm_item_track, idscorm_item, idReference, idscorm_tracking,"
					." status, nChild, nChildCompleted, nDescendant, nDescendantCompleted"
					." FROM ".$this->main_table
					." WHERE idUser='".(int)$idUser."'"
					//." AND idReference = '".(int)$idReference."'"
					." AND idscorm_item= '".(int)$idscorm_item."'";
		}
		$rs = sql_query($query, $this->dbconn);
		if( $rs === FALSE ) 
			die( "Scorm_ItemsTrack::getItemsTrack error in select [$query] ". sql_error($this->dbconn) );
		if( sql_num_rows($rs) == 0 )
			return FALSE;
		return $rs;		
	}	
	
	/**
	 *	Set the idscorm_tracking field of the record identified 
	 *		by idscorm_item_track
	 *	@param $idscorm_item_track id (primary key) of the record
	 *	@param $idscorm_tracking id of the scorm_tracking
	 *	@return TRUE if success, die otherwise
	 **/
	function setTracking( $idscorm_item_track, $idscorm_tracking ) {
		$query = "UPDATE ".$this->main_table
				." SET idscorm_tracking='".(int)$idscorm_tracking."'"
				." WHERE idscorm_item_track='".(int)$idscorm_item_track."'";
		if( sql_query($query,$this->dbconn) ) {
			return TRUE;
		} else {
			die( "Scorm_ItemsTrack::setTracking error in select [$query] ". sql_error($this->dbconn) );
		}
	}
	
	/**
	 * Scorm_ItemsTrack::getChildItemTrack()
	 * Return a record set with items_track child of a $idscorm_item
	 * 
	 * @param $idUser id of the user to search
	 * @param $idReference id of the record in lesson area
	 * @param $idscorm_item id of the start item or null, 
	 * 			in this case idscorm_organization must be set
	 * @return all items childs of a specified item as a array of idscorm_item
	 * 			return FALSE in case of error
	 * 			return an empty array if there isn't no child items
	 **/
	function getChildItemTrack( $idUser, $idReference, $idscorm_item, $idscorm_organization = null ) {
		$items_array = $this->getChildItems( $idReference, $idscorm_item, $idscorm_organization );
		if ($items_array === FALSE ) {
		    return FALSE;
		}
		if( count($items_array) > 0 ) {
			$query = "SELECT idscorm_item_track, idscorm_item, idscorm_tracking, status, nChild, nChildCompleted, nDescendant, nDescendantCompleted"
					." FROM ".$this->main_table
					." WHERE idUser='".(int)$idUser."'"
					." AND idReference='".(int)$idReference."'"
					." AND idscorm_item IN (".implode(',', $items_array).")";
			$rs = sql_query($query,$this->dbconn);
			return $rs;
		} else {
			return FALSE;
		}		
	}

	/**
	 * Scorm_ItemsTrack::getDescendantItemTrack()
	 * Return a record set with items_track descendant of a $idscorm_item
	 * 
	 * @param $idUser id of the user to search
	 * @param $idReference id of the record in lesson area
	 * @param $idscorm_item id of the start item or null, 
	 * 			in this case idscorm_organization must be set
	 * @return all items descendant of a specified item as a array of idscorm_item
	 * 			return FALSE in case of error
	 * 			return an empty array if there isn't no descendant items
	 **/
	function getDescendantItemTrack( $idUser, $idReference, $idscorm_item, $idscorm_organization = null ) {
		$items_array = $this->getDescendantItems( $idReference, $idscorm_item, $idscorm_organization );
		if ($items_array === FALSE ) {
		    return FALSE;
		}
		if( count($items_array) > 0 ) {
			$query = "SELECT idscorm_item_track, idscorm_item, idscorm_tracking, status, nChild, nChildCompleted, nDescendant, nDescendantCompleted"
					." FROM ".$this->main_table
					." WHERE idUser='".(int)$idUser."'"
					." AND idReference='".(int)$idReference."'"
					." AND idscorm_item IN (".implode(',', $items_array).")";
			$rs = sql_query($query,$this->dbconn);
			return $rs;
		} else {
			return FALSE;
		}		
	}
		
	/**
	 * Scorm_ItemsTrack::getParentItemTrack()
	 * Return a record set with items_track parent of a $idscorm_item
	 * 
	 * @param $idUser id of the user to search
	 * @param $idReference id of the record in lesson area
	 * @param $idscorm_item id of the start item, 
	 * @return the record set from items_track of the parent of a specified item 
	 * 			return FALSE in case of error
	 * 			return an empty array if there isn't no child items
	 **/
	function getParentItemTrack( $idUser, $idReference, $idscorm_item ) {
		soap__dbgOut( "+getParentItemTrack( $idUser, $idReference, $idscorm_item )" );		
		$items_array = $this->getParentItem( $idscorm_item );
		if ($items_array === FALSE ) {
		    return FALSE;
		}
		if( count($items_array) > 0 ) {
			$query = "SELECT idscorm_item_track, idscorm_item, idscorm_tracking, status, nChild, nChildCompleted, nDescendant, nDescendantCompleted"
					." FROM ".$this->main_table
					." WHERE idUser='".(int)$idUser."'";
			if( !isset($items_array[0]) || $items_array[0] === NULL ) {
				$query .= " AND idscorm_organization='".(int)$items_array[1]."'"
						 ." AND idReference='".(int)$idReference."'"
						 ." AND idscorm_item IS NULL";
			} else {
				$query .= " AND idscorm_item='".(int)$items_array[0]."'"
						 ." AND idReference='".(int)$idReference."'";
			}
					
			soap__dbgOut( $query );		
			$rs = sql_query($query,$this->dbconn);
			if( $rs === FALSE )
				die("Scorm_ItemsTrack::getParentItemTrack error in select [$query] ". sql_error($this->dbconn)
				." - ".print_r($items_array) );				
			return $rs;
		} else {
			return FALSE;
		}		
	}
		
	/**
	 * Scorm_ItemsTrack::getSiblingsItemTrack()
	 * Return a record set with items_track parent of a $idscorm_item
	 * 
	 * @param $idUser id of the user to search
	 * @param $idReference id of the record in lesson area
	 * @param $idscorm_item id of the start item, 
	 * @return the record set from items_track of the parent of a specified item 
	 * 			return FALSE in case of error
	 * 			return an empty array if there isn't no child items
	 **/
	function getSiblingsItemTrack( $idUser, $idReference, $idscorm_item ) {
		$items_array = $this->getSiblingsItems( $idReference, $idscorm_item );
		if ($items_array === FALSE ) {
		    return FALSE;
		}
		if( count($items_array) > 0 ) {
			$query = "SELECT idscorm_item_track, idscorm_item, idscorm_tracking, status, nChild, nChildCompleted, nDescendant, nDescendantCompleted"
					." FROM ".$this->main_table
					." WHERE idUser='".(int)$idUser."'"
					." AND idReference='".(int)$idReference."'"
					." AND idscorm_item IN (".implode(',', $items_array).")";
			$rs = sql_query($query,$this->dbconn);
			return $rs;
		} else {
			return FALSE;
		}		
	}
			
	/**
	 * Scorm_ItemsTrack::getChildItems()
	 * Return all items childs of a specified item or organization
	 * @param $idReference
	 * @param $idscorm_item
	 * @param $idscorm_organization
	 * @return all items childs of a specified item as a array of idscorm_item
	 * 			return FALSE in case of error
	 * 			return an empty array if there isn't no child items
	 * FIXME: This function return only the first child!!!!
	 **/
	function getChildItems( $idReference, $idscorm_item, $idscorm_organization = null ) {
		if( $idscorm_organization === null ) {
			$query = "SELECT idscorm_item"
					." FROM ".$this->prefix."_scorm_items"
					." WHERE idscorm_parentitem='".(int)$idscorm_item."'"
					."   AND idReference='".(int)$idReference."'";
		} else {
			$query = "SELECT idscorm_item FROM ".$this->prefix."_scorm_items"
					." WHERE idscorm_organization='".(int)$idscorm_organization."'"
					."   AND idReference='".(int)$idReference."'"
					."   AND idscorm_parentitem IS NULL";
		}
		$rs = sql_query($query, $this->dbconn );
		if( $rs == FALSE ) {
			return FALSE;
		} 
		if ( sql_num_rows($rs) == 0) {
			$result = array();
		    return $result;
		} else {
			$result = sql_fetch_row($rs);
			return $result;
		}
	}
	
	/**
	 * Scorm_ItemsTrack::getDescendantItems()
	 * Generate an array tath contains all the idscorm_item descendant from a given 
	 * idscorm_item or idscorm_organization
	 * @parma $idReference id of item in the lesson area
	 * @param $idscorm_item id of the start item or null, 
	 * 			in this case idscorm_organization must be set
	 * @param $idscorm_organization id of the organization to be searched for idscorm_items
	 * @return An array with the idscorm_item of all the items descendant; 
	 * 			if error return FALSE; if there isn't no descendants items return an 
	 * 			empty array
	 * FIXME: This function return only the first child!!!!
	 **/
	function getDescendantItems( $idReference, $idscorm_item, $idscorm_organization = null ) {
		$result = array();
		$childs = $this->getChildItems( $idReference, $idscorm_item, $idscorm_organization);
		if($childs !== FALSE) {
			foreach( $childs as $item ) {
				$tmpArr = $this->getDescendantItems( $idReference, $item);
				if( $tmpArr !== FALSE )
					$result = array_merge($result, $tmpArr);
			}
			
		} else {
			return FALSE;
		}
		return $result;
	}
	
	/**
	 * Scorm_ItemsTrack::getParentItem
	 *	Return an array with idscorm_item and iscorm_organization of a given 
	 *	$idReference $idscorm_item
	 *	@param $idReference
	 *	@param $idscorm_item
	 *	@return array with (0=>idscorm_item, 1=>idscorm_organization) of the parent 
	 **/
	function getParentItem( $idscorm_item) {
		$query = "SELECT idscorm_parentitem, idscorm_organization FROM ".$this->prefix."_scorm_items"
				." WHERE idscorm_item='".(int)$idscorm_item."'";
				//."   AND idReference='".(int)$idReference."'";
		$rs = sql_query($query,$this->dbconn);
		if( $rs === FALSE ) {
			$textOut = "Scorm_ItemsTrack::getParentItem error in select [$query] ". sql_error($this->dbconn);
			soap__dbgOut( $textOut, 1 );
			die( $textOut );
		}			
		if( sql_num_rows( $rs ) == 0 )
			return FALSE;
		else
			return sql_fetch_row($rs);
	}
	
	/**
	 * Scorm_ItemsTrack::getSiblingItem
	 *	Return an array with all siblings
	 * FIXME: this function return only the first sibling!!!!
	 **/
	function getSiblingsItems( $idReference, $idscorm_item) {
		$resultArray = $this->getParentItem($idscorm_item);
		if( $resultArray === FALSE )
			return FALSE;
		list($idscorm_parentitem) = sql_fetch_row($resultArray);
		if( $idscorm_parentitem != NULL ) 
			$idscorm_organization = NULL;
		
		return $this->getChildItems($idReference, $idscorm_parentitem, $idscorm_organization);
	}
	
	/**
	 * Scorm_ItemsTrack::getItensInfo
	 *	return infos (nChild and nDescendant) of a given item
	 * @param $idReference 
	 * @param $idscorm_item
	 * @param $idscorm_organization
	 * @return associative array with 'nChild'=>nChild and 'nDescendant'=>nDescendant
	 *			die on error
	 **/
	function getItemsInfo( $idReference, $idscorm_item, $idscorm_organization = NULL ) {
		if( $idscorm_organization != null ) {
			$query = "SELECT nChild, nDescendant "
					." FROM ".$this->prefix."_scorm_organizations"
					." WHERE idscorm_organization='".(int)$idscorm_organization."'";
					//."   AND idReference='".(int)$idReference."'";
		} else {
			$query = "SELECT nChild, nDescendant "
					." FROM ".$this->prefix."_scorm_items"
					." WHERE idscorm_item='".(int)$idscorm_item."'";
					//."   AND idReference='".(int)$idReference."'";
		}
		$rs = sql_query($query,$this->dbconn);
		if( $rs === FALSE ) 
			die("Scorm_ItemsTrack::getParentItem error in select [$query] ". sql_error($this->dbconn));
		else
			return sql_fetch_assoc($rs);
	}
	
	/**
	 * 	Scorm_ItemsTrack::createItemsTrack
	 *	This function generate all the records for track the user results in single 
	 *		socorm items. 
	 *	@param $idUser 
	 *	@param $idReference
	 *	@idscorm_organization
	 *	@return
	 **/
	function createItemsTrack( $idUser, $idReference, $idscorm_organization ) {
		$query = "INSERT INTO ".$this->main_table
				." (idscorm_organization,idUser,idReference,status,nChild,nChildCompleted,nDescendant,nDescendantCompleted)"
				." SELECT '".(int)$idscorm_organization."','"
							.(int)$idUser."','"
							.(int)$idReference."','not attempted',nChild,0,nDescendant,0"
				." FROM ".$this->prefix."_scorm_organizations"
				." WHERE idscorm_organization=".$idscorm_organization;
		//echo "a --> " . $query;
		$result = sql_query($query, $this->dbconn);
		if($result === FALSE) {
			$textOut = "Scorm_ItemsTrack::createItemsTrak error in insert [$query] ". sql_error($this->dbconn);
			soap__dbgOut( $textOut, 1 );
			die( $textOut );
		}
		
		$query = "INSERT INTO ".$this->main_table
				." (idscorm_organization,idscorm_item,idReference,idUser,status,nChild,nChildCompleted,nDescendant,nDescendantCompleted)"
				." SELECT '".(int)$idscorm_organization."',idscorm_item,'"
							.(int)$idReference."','"
							.(int)$idUser."','not attempted',nChild,0,nDescendant,0"
				." FROM ".$this->prefix."_scorm_items"
				." WHERE idscorm_organization='".(int)$idscorm_organization."'"
				." ORDER BY idscorm_item";
		$result = sql_query($query, $this->dbconn);
		if($result === FALSE) {
			$textOut = "Scorm_ItemsTrack::createItemsTrak error in insert [$query] ". sql_error($this->dbconn);
			soap__dbgOut( $textOut, 1 );
			die( $textOut );
		}
		return $result;
	}
        
        
        function getIdTrack( $idReference, $idUser, $idResource, $createOnFail = FALSE ) {
                
                $rsItemTrack = $this->getItemTrack($idUser,$idReference, NULL, $idResource);
                if( $rsItemTrack !== FALSE ) {
                    $arrItemTrack = sql_fetch_assoc( $rsItemTrack );
                    return array( TRUE, $arrItemTrack['idscorm_item_track'] );
                } else if ($createOnFail) {
                        $this->createItemsTrack( $idUser, $idReference, $idResource );
                        $rsItemTrack = $this->getItemTrack( $idUser, $idReference, NULL, $idResource );
                        $arrItemTrack = sql_fetch_assoc( $rsItemTrack );
                        return array( FALSE, $arrItemTrack['idscorm_item_track'] );
                }
		return FALSE;
	}
	
	/**
	 *	Scorm_ItemsTrack::setStatus
	 *	Set the status of a given scorm_item 
	 *	@param $idUser
	 *	@param $idReference
	 *	@param $idscorm_item
	 *	@param $status
	 **/
	function setStatus( $idUser, $idReference, $idscorm_item, $status, $environment = 'course_lo' ) {
		$rs = $this->getItemTrack( $idUser, $idReference, $idscorm_item);
		if( $rs === FALSE ) {
			$textOut = "Scorm_ItemsTrack::setStatus error:  getItemTrack return FALSE";
			soap__dbgOut( $textOut, 1 );
			die( $textOut );
		}
		$record = sql_fetch_assoc($rs);
		if( strcmp($record['status'], $status) != 0 ) {
			$query = "UPDATE ".$this->main_table
					." SET status='$status'"
					." WHERE idscorm_item_track=".$record['idscorm_item_track'];

			if( sql_query($query, $this->dbconn) === FALSE ) {
				$textOut = "Scorm_ItemsTrack::setStatus error in update [$query] ". sql_error($this->dbconn);
				soap__dbgOut( $textOut, 1 );		
				die( $textOut );			
			}
			if( strcmp($status,'completed') == 0 || strcmp($status, 'passed') == 0 ) {
				$this->forwardCompleted($idUser, $idReference, $idscorm_item, TRUE, $environment);
			}
		}
	}
	
	/**
	 * Scorm_ItemsTrack::forwardCompleted
	 *	forward completition back to ancestors items.
	 *	change the nChildCompleted and nDescendantCompleted fields
	 *	
	 *	@param $idUser
	 *	@param $idReference
	 *	@param $idscorm_item
	 *	@param $isChild
	 **/
	function forwardCompleted( $idUser, $idReference, $idscorm_item, $isChild, $environment ) {
		$rs = $this->getParentItemTrack($idUser, $idReference, $idscorm_item);
		if( $rs === FALSE ) 
			return FALSE;
		$record = sql_fetch_assoc($rs);
		
		$status = 'incomplete';
		if( $isChild ) {
			$record['nChildCompleted']++;
			$updates[] = "nChildCompleted='".(int)$record['nChildCompleted']."'";
			
			if( $record['nChildCompleted'] >= $record['nChild'] ) {
				$status = 'completed';
				if( $record['nChildCompleted'] > $record['nChild'] ) { // is possible?
					$record['nChildCompleted'] = $record['nChild'];
				}
			}			
		}
		$record['nDescendantCompleted']++;
		if( $record['nDescendantCompleted'] > $record['nDescendant'] ) 
			$record['nDescendantCompleted'] = $record['nDescendant'];
		
		$updates[] = "nDescendantCompleted='".(int)$record['nDescendantCompleted']."'";
		if( strcmp($status,$record['status']) != 0)
			$updates[] = "status='$status'";
		
		$query = "UPDATE ".$this->main_table
				." SET ".implode(', ', $updates)
				." WHERE idscorm_item_track=".$record['idscorm_item_track'];
		
		if( sql_query($query, $this->dbconn) === FALSE ) {
			$textOut = "Scorm_ItemsTrack::forwardCompleted error in update [$query] ". sql_error($this->dbconn)
						.print_r($record);
			soap__dbgOut( $textOut, 1 );		
			die( $textOut );
		}
		if( isset($record['idscorm_item']) ) {
			if( $record['idscorm_item'] !== NULL ) {
				if( strcmp($status,'completed') == 0 || strcmp($status, 'passed') == 0 ) 
					$this->forwardCompleted($idUser, $idReference, $record['idscorm_item'], TRUE, $environment);
				else 
					$this->forwardCompleted($idUser, $idReference, $record['idscorm_item'], FALSE, $environment);
			} 
		} else {
			// org item! set commontrack
			//print_r($record);
			if( strcmp($status,'completed') == 0 || strcmp($status, 'passed') == 0 ) {
				soap__dbgOut( "update commontrack" );
				require_once( _lms_ ."/class.module/track.object.php" );
				require_once( _lms_ ."/class.module/track.scorm.php" );
				soap__dbgOut( "create Track_ScormOrg object" );		
				$track_so = new Track_ScormOrg( $record['idscorm_item_track'], false, false, NULL, $environment );
				soap__dbgOut( "idscorm_item_track" .$record['idscorm_item_track'] );
				soap__dbgOut( "idReference = " .$track_so->idReference );
				$track_so->setStatus( 'completed' );
				$track_so->setDate( date("Y-m-d H:i:s") );
				$track_so->update();
				soap__dbgOut( "mysql error = " .sql_error() );
			}
		}
	}
	
}

?>
