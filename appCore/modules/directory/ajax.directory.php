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
 * @package course management
 * @subpackage course catalogue
 * @category ajax server
 * @author Giovanni Derks
 * @version $Id:$
 *
 */

if(Docebo::user()->isAnonymous()) die('You can\'t access');

require_once($GLOBALS['where_framework'].'/lib/lib.permission.php');

$op = Get::req('op', DOTY_ALPHANUM, '');
switch($op) {
	case "getuserprofile" : {
		$lang =& DoceboLanguage::createInstance( 'standard', 'framework');
		$lang->setGlobal();
		
		require_once(_base_.'/lib/lib.user_profile.php');
		
		$id_user = importVar('id_user', true, 0);
		
		$profile = new UserProfile( $id_user );
		$profile->init('profile', 'framework', 'modname=directory&op=org_manageuser&id_user='.$id_user, 'ap');
		$profile->enableGodMode();
		$profile->disableModViewerPolicy();
		$value = array("content" 	=> $profile->getUserInfo()
		 		
			 	// teacher profile, if the user is a teacher
			 	//.$profile->getUserTeacherProfile()
		 		
				//.$profile->getUserLmsStat()  .'<br />'
				//.$profile->getUserCompetencesList()
				,
				"id_user" => $id_user
		);
  
		require_once(_base_.'/lib/lib.json.php');
		
		$json = new Services_JSON();
		$output = $json->encode($value);
  		aout($output);
	};break;
}

?>