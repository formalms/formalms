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

if(!defined('IN_FORMA')) die('You cannot access this file directly');

require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

$json = new Services_JSON();
$op = importVar('op', false, '');

switch ($op) {
	case "getpopup": {
		$output = array('success' => true);

		$lang =& DoceboLanguage::createInstance('certificate', 'lms');
		$head = Lang::t('_CERTIFICATES_GENERATION', 'certificate');
		$body = '<div><p>'.Lang::t('_PROGRESS', 'standard').'</p><div class="box_progress_bar" id="load_line">'
			.'<div id="print_progressbar" class="bar_complete" style="width:0%"></div>'
			.'<div class="no_float"></div></div>'
			.'<p>'.Lang::t('_GENERATE', 'certificate').'&nbsp;<span id="actual_num">'
			.'</span>&nbsp;'.Lang::t('_OF', 'certificate').'&nbsp;<span id="total_num">'.'</span>'
			.'</p></div><div id="print_result" class="error_frame"></div>';

		$output['head'] = $head;
		$output['body'] = $body;

		aout($json->encode($output));
	} break;

	case "print": {
		$output = array('success' => false);
		$id_certificate = Get::req('id_certificate', DOTY_INT, -1);
		$id_course = Get::req('id_course', DOTY_INT, -1);
		$id_user = Get::req('id_user', DOTY_INT, -1);
		ob_start();
		if ($id_user>0 && $id_course>0 && $id_certificate>0)
		{
			$cert = new Certificate();
			$subs = $cert->getSubstitutionArray($id_user, $id_course);
			$cert->send_certificate($id_certificate, $id_user, $id_course, $subs, false, true);
			
			$output['success'] = true;
			$output['printed'] = $id_user;
		} else {
			$acl_man =& $GLOBALS['current_user']->getAclManager();
			$user_info = $acl_man->getUser($id_user, false);

			if($user_info[ACL_INFO_FIRSTNAME] !== '' && $user_info[ACL_INFO_LASTNAME] !== '')
				$username = $user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
			elseif($user_info[ACL_INFO_LASTNAME] !== '')
				$username = $user_info[ACL_INFO_LASTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
			elseif($user_info[ACL_INFO_FIRSTNAME] !== '')
				$username = $user_info[ACL_INFO_FIRSTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
			else
				$username = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

			$output['message'] = $username.' - '.Lang::t('_CERTIFICATE_PRINT_ERROR', 'certificate');
		}
		ob_clean();
		ob_start();
		aout($json->encode($output));
	} break;

	default: {}
}

?>