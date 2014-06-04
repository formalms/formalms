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

require_once(_base_.'/lib/lib.json.php');
// Loads the class
require_once('Browscap.php');



function chelpCheckField($val) {
	$res = $val;
	if (preg_match("/[\\\r\\\n]/si", $val)) { $res = false; }
	if (preg_match("/.*\\\\0/si", $val)) { $res = false; }
	return $res;
}

$op = Get::req('op', DOTY_STRING, '');
switch ($op) {

	case "getdialog": {
		$idst = getLogUserId();
		$acl_man = Docebo::user()->getAclManager();
		$user_info = $acl_man->getUser($idst, false);
		$user_email = $user_info[ACL_INFO_EMAIL];

		$body = "";
		$body .= '<div class="line_field">'.Lang::t('_README_HELP', 'customer_help').'</div>'
				.'<br />'
				.'<div class="line_field"><b>'.Lang::t('_USERNAME', 'standard').':</b> '.$acl_man->relativeId( Docebo::user()->getUserId()).'</div>';
		if(isset($GLOBALS['course_descriptor'])) {
			$body .= '<div class="line_field"><b>'.Lang::t('_COURSE_NAME', 'admin_course_management').':</b> '
				.$GLOBALS['course_descriptor']->getValue('name').'</div>';
		}
		
		$body .= '<div id="customer_help_error_message" class="align_center"></div>';

		$body .= Form::openForm('customer_help_form', 'ajax.server.php?mn=customer_help&plf=lms&op=send');
		$body .= Form::getTextfield(Lang::t('_TITLE', 'menu').':', 'help_req_subject', 'help_req_subject', 255, '');
		$body .= Form::getTextfield(Lang::t('_EMAIL', 'menu').':', 'help_req_email', 'help_req_email', 255, $user_email);
		$body .= Form::getTextfield(Lang::t('_PHONE', 'classroom').':', 'help_req_tel', 'help_req_tel', 255, '');
		$body .= Form::getSimpleTextarea(Lang::t('_TEXTOF', 'menu').':', 'help_req_text', 'help_req_text', '', false, false, 'textarea_full', 8,40);
        $body .= Form::getHidden('help_req_resolution', 'help_req_resolution', '');
        $body .= Form::getHidden('help_req_flash_installed', 'help_req_flash_installed', '');
		$body .= Form::closeForm();

		$output = array(
			'success' => true,
			'body' => $body
		);

		$json = new Services_JSON();
		aout($json->encode($output));
	} break;


	case "send": {
		require_once(_base_.'/lib/lib.mailer.php');

		$help_email = Get::sett('customer_help_email', '');
		$help_pfx = Get::sett('customer_help_subj_pfx', '');

		$subject = (!empty($help_pfx) ? "[".$help_pfx."] " : "");
		$subject .= chelpCheckField($_POST["help_req_subject"]);

		$idst = getLogUserId();
		$acl_man = Docebo::user()->getAclManager();
		$userid = Docebo::user()->getUserId();
		$user_info = $acl_man->getUser($idst, false);

		//$user_email =$user_info[ACL_INFO_EMAIL];

		$email_text = Get::req("help_req_email", DOTY_STRING, "");
		$user_email = chelpCheckField($email_text);
		$user_name = trim($user_info[ACL_INFO_FIRSTNAME]." ".$user_info[ACL_INFO_LASTNAME]);
		if (empty($user_name)) { $user_name = $userid; }

		$br_char = "<br />";

		$msg = "";
		$msg .= Lang::t('_USER', 'standard').": ".$user_name." (".$userid.")".$br_char.$br_char;

		if (isset($GLOBALS['course_descriptor'])) {
			$msg .= Lang::t('_COURSE', 'standard').": ".$GLOBALS['course_descriptor']->getValue('name').$br_char.$br_char;
		}

		$tel = Get::req("help_req_tel", DOTY_STRING, "");
		$msg .= Lang::t('_PHONE', 'classroom').": ".$tel.$br_char;

		$msg .= $br_char."----------------------------------".$br_char;
		//$msg .= chelpCheckField(Get::req("help_req_txt", DOTY_STRING, ""));
		$msg .= Get::req("help_req_text", DOTY_STRING, "");
		$msg .= $br_char."----------------------------------".$br_char;
        
        /** Getting client info */
        try {
            // Create a new Browscap object (loads or creates the cache)
            $bc = new Browscap(_base_ . '/files');
            // Get information about the current browser's user agent
            $current_browser = $bc->getBrowser();
        } catch (Exception $e) {
            // if can't connect or get another exception
            $current_browser = null;
        }

        $msg .= $br_char . "---------- CLIENT INFO -----------" . $br_char;
        $msg .= "IP: " . $_SERVER['REMOTE_ADDR'] . $br_char;
        if ($current_browser != null) {
            $msg .= "BROWSER: " . $current_browser->Browser . " - " . $current_browser->Version . $br_char;
            $msg .= "OS: " . $current_browser->Platform . " - " . $current_browser->Platform_Description . $br_char;
            $msg .= "JS: " . ($current_browser->JavaScript == 1 ? "true" : "false") . $br_char;
            $msg .= "COOKIES: " . ($current_browser->Cookies == 1 ? "true" : "false") . $br_char;
        } else {
            // print simple USER_AGENT string
            $msg .= "USER AGENT: " . $_SERVER['HTTP_USER_AGENT'] . $br_char;
        }
        $msg .= "RESOLUTION: ".Get::req("help_req_resolution", DOTY_STRING, "") . $br_char;
        $msg .= "FLASH: ".Get::req("help_req_flash_installed", DOTY_STRING, "") . $br_char;

        $mailer = new DoceboMailer();
		$mailer->IsHTML(true);
		$res = $mailer->SendMail($user_email, $help_email, $subject, $msg);
		
		$output = array('success' => $res);
		if (!$res) $output['message'] = UIFeedback::perror(Lang::t('_OPERATION_FAILURE', 'menu'));
		$json = new Services_JSON();
		aout($json->encode($output));
	} break;

	default: {} break;
}

?>