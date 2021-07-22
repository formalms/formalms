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

define("_OTHERFIELD_ID_LANGUAGE", 0);
define("_OTHERFIELD_ID_ADMINLEVELS", 1);

define("_OTHERFIELD_TYPE_LANGUAGE", 'language');
define("_OTHERFIELD_TYPE_ADMINLEVELS", 'adminlevels');

class OtherFieldsTypes {

	protected $db;
	protected $acl_man;

  public function __construct() {
		$this->db = DbConn::getInstance();
		$this->acl_man = Docebo::user()->getAclManager();
	}
  
  
  public function getInitData($js = true) {
    //produces languages' list variable
    $temp1 = array( '{ id: "standard", value: "[ '.addslashes( Lang::t('_DEFAULT_LANGUAGE') ).' ]" }' );
    foreach (Docebo::langManager()->getAllLanguages() as $lang) {
			$temp1[] = '{ id: "'.$lang[0].'", value: "'.addslashes( $lang[0].'  ('.$lang[1].')' ).'" }';
		}

		//produce admin levels list
		$temp2 = array();
		$arr_admin_levels = $this->acl_man->getAdminLevels();
		foreach ($arr_admin_levels as $lev=>$idst) {
			$temp2[] = '{ id: "'.$lev.'", value: "'.addslashes( Lang::t('_DIRECTORY_'.$lev, 'admin_directory') ).'" }';
		}

    if (!$js) {
      $output = array( 
				'languages' => $temp1,
				'levels' => $temp2
			);
    } else {
      $js_langs = "[".implode(",", $temp1)."]";
			$js_levels = "[".implode(",", $temp2)."]";
      $output = array(
				'languages' => $js_langs,
				'levels' => $js_levels
			);
    }
    
    return $output;
  }
  
  
  public function getOtherFieldsList() {
    $list = array(
      array('id'=>'oth_'._OTHERFIELD_ID_LANGUAGE, 'name'=>addslashes(Lang::t('_LANGUAGE', 'standard')), 'type'=>_OTHERFIELD_TYPE_LANGUAGE, 'standard'=>false),
      array('id'=>'oth_'._OTHERFIELD_ID_ADMINLEVELS, 'name'=>addslashes(Lang::t('_LEVEL', 'admin_directory')), 'type'=>_OTHERFIELD_TYPE_ADMINLEVELS, 'standard'=>false)
    );
    
    return $list;
  }
  
  
  
  
  
  public function checkUserField($id_field, $id_user, $filter) {    
		$output = false;
  
    switch ($id_field) {
    
      case _OTHERFIELD_ID_LANGUAGE: {
        if ($filter == "standard") {
          $temp = array();
          $query = "SELECT id_user FROM %adm_setting_user WHERE path_name='ui.language'";
          $res = $this->db->query($query);
          while ( list($idst) = $this->db->fetch_row($res) ) { $temp[] = $idst; }
          $output = !in_array($id_user, $temp);          
        } else {
          $query = "SELECT * FROM %adm_setting_user WHERE path_name='ui.language' AND value='$filter' AND id_user='$id_user'";
          $res = $this->db->query($query);
          $output = ($this->db->num_rows($res)>0);
        }
      }break;

			case _OTHERFIELD_ID_ADMINLEVELS: {
				$groupid = $this->acl_man->relativeId($filter);
				$idst_group = $this->acl_man->getGroupST($groupid);
        $query = "SELECT * FROM %adm_group_members WHERE idst=".(int)$idst_group." AND idstMember=".(int)$id_user;
				$res = $this->db->query($query);
        $output = ($this->db->num_rows($res)>0);
      } break;

      default: { }
      
    }
    
    return $output;
	}
  
  
  
  
  public function getFieldQuery($id_field, $filter) {
  
    $output = '';
  
    switch ($id_field) {
    
      case _OTHERFIELD_ID_LANGUAGE: {
        //$filter = lang_code
        if ($filter == "standard") {
          $output = "SELECT idst ".
            "FROM %adm_user ".
            "WHERE idst NOT IN (SELECT id_user as idst FROM %adm_setting_user  WHERE path_name = 'ui.language')";
        } else {
          $output = "SELECT id_user as idst ".
		        "FROM  %adm_setting_user  ".
            "WHERE path_name = 'ui.language' AND value = '".$filter."'";
        }
      } break;

			case _OTHERFIELD_ID_ADMINLEVELS: {
				//$filter = admin_level path
				$groupid = $this->acl_man->relativeId($filter);
				$idst_group = $this->acl_man->getGroupST($groupid);
				$output = "SELECT idstMember as idst "
					."FROM  %adm_group_members  "
					."WHERE idst = '".(int)$idst_group."'";
      } break;

      default: {
        //...
      }

    }
  
		return $output;
  } 

}





?>