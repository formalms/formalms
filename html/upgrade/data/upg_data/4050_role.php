<?php if (!defined('IN_FORMA')) { die('You can\'t access!'); }

// if this file is not needed for a specific version,
// just don't create it.


// Create this function only if needed, else you can remove it
// (we check it with function_exists)
function upgradeUsersRoles4050() {
	$res ='';
	
	/* $res ='/lms/course/public/course/view
/lms/course/public/coursecatalogue/view
/lms/course/public/course_autoregistration/view
/lms/course/public/message/view'; */
	
	return $res;
}


// Create this function only if needed, else you can remove it
// (we check it with function_exists)
function upgradeGodAdminRoles4050() {
	$res ='';
	
	/* $res ='/framework/admin/adminmanager/mod
/framework/admin/adminmanager/view
/framework/admin/adminrules/view
/framework/admin/code/view'; */
	
	return $res;
}