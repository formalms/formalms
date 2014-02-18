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

require_once(dirname(__FILE__).'/certificate.base.php');

class CertificateSubs_User extends CertificateSubstitution {

	function getSubstitutionTags() {
		
		$lang =& DoceboLanguage::createInstance('certificate', 'lms');
		
		$subs = array();
		$subs['[display_name]'] = $lang->def('_DISPLAY_NAME');
		$subs['[username]'] 	= $lang->def('_USERNAME');
		$subs['[firstname]'] 	= $lang->def('_FIRSTNAME');
		$subs['[lastname]'] 	= $lang->def('_LASTNAME');
		
		//variable fields
		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		$temp = new FieldList();
    $fields = $temp->getFlatAllFields();
		foreach ($fields as $key=>$value) {
      $subs['[userfield_'.$key.']'] = $lang->def('_USERFIELD').' "'.$value.'"';
    }
		
		return $subs;
	}
	
	function getSubstitution() {
		
		$subs = array();
		
		$aclman =& Docebo::user()->getAclManager();
		$user = $aclman->getUser($this->id_user, false);
		
		$subs['[display_name]'] =  ( $user[ACL_INFO_LASTNAME].$user[ACL_INFO_FIRSTNAME]
			? $user[ACL_INFO_LASTNAME].' '.$user[ACL_INFO_FIRSTNAME]
			: $aclman->relativeId($user[ACL_INFO_USERID]) );
		
		$subs['[username]'] 	= $aclman->relativeId($user[ACL_INFO_USERID]);
		$subs['[firstname]'] 	= $user[ACL_INFO_FIRSTNAME];
		$subs['[lastname]'] 	= $user[ACL_INFO_LASTNAME];
		
		//variable fields
		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		
		$temp = new FieldList();
		$fields = $temp->getFlatAllFields();
		foreach ($fields as $key=>$value)
	    	$subs['[userfield_'.$key.']'] = $temp->showFieldForUser($this->id_user, $key);
	    
		return $subs;
	}
}

?>