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

class Man_MiddleArea {

	public $_cache = NULL;

	public function __construct() {}
	
	public function _tableMA() { return $GLOBALS['prefix_lms'].'_middlearea'; }
	
	public function _query($query) {
		
		$re = sql_query($query);
		return $re;
	}
		
	public function getObjIdstList($obj_index) {
	
		$query = "SELECT idst_list 
		FROM ".$this->_tableMA()." 
		WHERE obj_index = '".$obj_index."' ";
		$re_query = $this->_query($query);
		if(!$re_query) return false;
		
		list($idst_list) = sql_fetch_row($re_query);
		
		if($idst_list && is_string($idst_list)) return unserialize($idst_list);
		return array();
	} 	
	
	public function isDisabled($obj_index) {
	
		$query = "SELECT disabled 
		FROM ".$this->_tableMA()." 
		WHERE obj_index = '".$obj_index."' ";
		$re_query = $this->_query($query);
		if(!$re_query) return false;
		
		list($disabled) = sql_fetch_row($re_query);
		
		return $disabled;
	}
	
	public function changeDisableStatus($obj_index) {
		
		$c_status = $this->isDisabled($obj_index);
		
		if($c_status == 1) $c_status = 0;
		else $c_status = 1;
		
		$query = "UPDATE ".$this->_tableMA()." 
		SET disabled = '".$c_status."' 
		WHERE obj_index = '".$obj_index."' ";
		$re_query = $this->_query($query);
		
		if(!$re_query) return false;
		return true;
	}
	
	public function setObjIdstList($obj_index, $idst_list) {
		
		$idst_list = serialize($idst_list);
		
		$query = "SELECT obj_index 
		FROM ".$this->_tableMA()." 
		WHERE obj_index = '".$obj_index."' ";
		$exists = mysql_num_rows($this->_query($query));
		
		if(!$exists) {
			
			$query = "INSERT INTO ".$this->_tableMA()."
			( idst_list, obj_index ) VALUES ( '".$idst_list."', '".$obj_index."' ) ";
		} else {
		
			$query = "UPDATE ".$this->_tableMA()."
			SET idst_list = '".$idst_list."'
			WHERE obj_index = '".$obj_index."' ";
		}
		$this->_cache[$obj_index] = $idst_list;
		return $this->_query($query);
	}
	
	public function getDisabledList() {
	
		$disabled = array();
				
		$query = "SELECT obj_index 
		FROM ".$this->_tableMA()." as t
		WHERE t.disabled = '1' ";
		$re_query = $this->_query($query);
		
		while(list($obj_i) = sql_fetch_row($re_query)) {
			
			$disabled[$obj_i] = $obj_i;
		}
		
		return $disabled;
	}
	
	public function currentCanAccessObj($obj_index) {
		if($this->_cache === NULL) {
				
			$query = "SELECT obj_index, disabled, idst_list 
			FROM ".$this->_tableMA()." ";
			$re_query = $this->_query($query);
			
			while(list($obj_i, $disabled, $idst_list) = sql_fetch_row($re_query)) {
				
				$this->_cache[$obj_i]['list'] = unserialize($idst_list);
				$this->_cache[$obj_i]['disabled'] =$disabled;
			}
		}
		if(isset($this->_cache[$obj_index]) && ($this->_cache[$obj_index]['disabled'] == 1)) {
			return false;	
		}
		$user_level = Docebo::user()->getUserLevelId();
		if($user_level == ADMIN_GROUP_GODADMIN) return true;
		
		$user_assigned = Docebo::user()->getArrSt();
		if(isset($this->_cache[$obj_index])) {
			if($this->_cache[$obj_index]['list'] == '' || empty($this->_cache[$obj_index]['list'])) return true;
			
			$intersect = array_intersect($user_assigned, $this->_cache[$obj_index]['list']);
		} else {
			return true;
		}
		
		return !empty($intersect);
	}
	
}

?>