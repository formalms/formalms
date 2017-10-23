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
        
        // can not disable menu if it is set as home-page in system configuration
        $which_home_page = Get::sett('home_page_option');
        if (($obj_index == 'mo_1' && $which_home_page == 'my_courses' && $c_status == false) || ($obj_index == 'mo_46' && $which_home_page == 'catalogue' && $c_status == false) )
            return true;

        
        if($c_status == 1) $c_status = 0;
        else $c_status = 1;
        
        $query = "UPDATE ".$this->_tableMA()." 
        SET disabled = '".$c_status."' 
        WHERE obj_index = '".$obj_index."' ";
        $re_query = $this->_query($query);
        
        if(!$re_query) return false;
        return true;
    }
    
    
    public function setHomePageMenu($obj_index) {
        if ($obj_index == 'mo_1' || $obj_index == 'mo_46') {
            
            $idst_list = serialize(array());
            $query = "UPDATE ".$this->_tableMA()."
            SET idst_list = '".$idst_list."', disabled = 0 
            WHERE obj_index = '".$obj_index."' ";
            return $this->_query($query);
        }
    }
    
    public function setHomePageTab($obj_index){
        
        $array_tab['tb_classroom']  = 'classroom/show';
        $array_tab['tb_communication']  = 'communication/show';
        $array_tab['tb_coursepath']  = 'coursepath/show';
        $array_tab['tb_elearning']  = 'elearning/show';
        $array_tab['tb_games']  = 'games/show';
        $array_tab['tb_home']  = 'home/show';
        $array_tab['tb_kb']  = 'kb/show';
        $array_tab['tb_label']  = 'label/show';
        $array_tab['tb_videoconference']  = 'videoconference/show'; 
        //  plugin added tab cannot be home 
        if (array_key_exists($obj_index, $array_tab )) {
            $query = "UPDATE ".$this->_tableMA()."
            SET is_home = 0
            WHERE obj_index like 'tb%'";
            $this->_query($query);
            
            $query = "UPDATE ".$this->_tableMA()."
            SET is_home = 1
            WHERE obj_index = '$obj_index'";
            $this->_query($query);
            
            if ($this->isDisabled($obj_index)) {
                $query = "UPDATE ".$this->_tableMA()." 
                SET disabled = 0 
                WHERE obj_index = '".$obj_index."' ";
                $re_query = $this->_query($query);
            }
             
             
             $query = "update ".$GLOBALS['prefix_lms']."_module set mvc_path ='".$array_tab[$obj_index]."' where idModule=1";
             $this->_query($query);         
        }                

    }
    
    public function setObjIdstList($obj_index, $idst_list) {
        
        $idst_list = serialize($idst_list);
        
        $query = "SELECT obj_index 
        FROM ".$this->_tableMA()." 
        WHERE obj_index = '".$obj_index."' ";
        $exists = sql_num_rows($this->_query($query));
        
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