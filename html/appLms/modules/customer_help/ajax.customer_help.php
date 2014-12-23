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

$msg_find = "";

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
        $result = parse_user_agent();

        $msg .= $br_char . "---------- CLIENT INFO -----------" . $br_char;
        $msg .= "IP: " . $_SERVER['REMOTE_ADDR'] . $br_char;
        $msg .= "USER AGENT: " . $_SERVER['HTTP_USER_AGENT'] . $br_char;
        $msg .= "OS: " . $result['platform'] . $br_char;
        $msg .= "BROWSER: " . $result['browser'] . " " . $result['version'] . $br_char;
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

    /**
     * Parses a user agent string into its important parts
     *
     * @author Jesse G. Donat <donatj@gmail.com>
     * @link https://github.com/donatj/PhpUserAgent
     * @link http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
     * @param string|null $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL
     * @return array an array with browser, version and platform keys
     */
    function parse_user_agent($u_agent = null) {
        if (is_null($u_agent)) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $u_agent = $_SERVER['HTTP_USER_AGENT'];
            } else {
                throw new InvalidArgumentException('parse_user_agent requires a user agent');
            }
        }

        $platform = null;
        $browser = null;
        $version = null;

        $empty = array('platform' => $platform, 'browser' => $browser, 'version' => $version);

        if (!$u_agent)
            return $empty;

        if (preg_match('/\((.*?)\)/im', $u_agent, $parent_matches)) {

            preg_match_all('/(?P<platform>BB\d+;|Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|Nintendo\ (WiiU?|3DS)|Xbox(\ One)?)
    (?:\ [^;]*)?
    (?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

            $priority = array('Android', 'Xbox One', 'Xbox');
            $result['platform'] = array_unique($result['platform']);
            if (count($result['platform']) > 1) {
                if ($keys = array_intersect($priority, $result['platform'])) {
                    $platform = reset($keys);
                } else {
                    $platform = $result['platform'][0];
                }
            } elseif (isset($result['platform'][0])) {
                $platform = $result['platform'][0];
            }
        }

        if ($platform == 'linux-gnu') {
            $platform = 'Linux';
        } elseif ($platform == 'CrOS') {
            $platform = 'Chrome OS';
        }

        preg_match_all('%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Iceweasel|Safari|MSIE|Trident/.*rv|AppleWebKit|Chrome|CriOS|IEMobile|Opera|OPR|Silk|Lynx|Midori|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
    (?:\)?;?)
    (?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix', $u_agent, $result, PREG_PATTERN_ORDER);


    // If nothing matched, return null (to avoid undefined index errors)
        if (!isset($result['browser'][0]) || !isset($result['version'][0])) {
            return $empty;
        }

        $browser = $result['browser'][0];
        $version = $result['version'][0];

		$find = "_parse_ua_find";

        $key = 0;
        if ($browser == 'Iceweasel') {
            $browser = 'Firefox';
        } elseif ($find('Playstation Vita', $key, $result)) {
            $platform = 'PlayStation Vita';
            $browser = 'Browser';
        } elseif ($find('Kindle Fire Build', $key, $result) || $find('Silk', $key, $result)) {
            $browser = $result['browser'][$key] == 'Silk' ? 'Silk' : 'Kindle';
            $platform = 'Kindle Fire';
            if (!($version = $result['version'][$key]) || !is_numeric($version[0])) {
                $version = $result['version'][array_search('Version', $result['browser'])];
            }
        } elseif ($find('NintendoBrowser', $key, $result) || $platform == 'Nintendo 3DS') {
            $browser = 'NintendoBrowser';
            $version = $result['version'][$key];
        } elseif ($find('Kindle', $key, $result)) {
            $browser = $result['browser'][$key];
            $platform = 'Kindle';
            $version = $result['version'][$key];
        } elseif ($find('OPR', $key, $result)) {
            $browser = 'Opera Next';
            $version = $result['version'][$key];
        } elseif ($find('Opera', $key, $result)) {
            $browser = 'Opera';
            $find('Version', $key, $result);
            $version = $result['version'][$key];
        } elseif ($find('Midori', $key, $result)) {
            $browser = 'Midori';
            $version = $result['version'][$key];
        } elseif ($find('Chrome', $key, $result) || $find('CriOS', $key, $result)) {
            $browser = 'Chrome';
            $version = $result['version'][$key];
        } elseif ($browser == 'AppleWebKit') {
            if (($platform == 'Android' && !($key = 0))) {
                $browser = 'Android Browser';
            } elseif (strpos($platform, 'BB') === 0) {
                $browser = 'BlackBerry Browser';
                $platform = 'BlackBerry';
            } elseif ($platform == 'BlackBerry' || $platform == 'PlayBook') {
                $browser = 'BlackBerry Browser';
            } elseif ($find('Safari', $key, $result)) {
                $browser = 'Safari';
            }

            $find('Version', $key, $result);

            $version = $result['version'][$key];
        } elseif ($browser == 'MSIE' || strpos($browser, 'Trident') !== false) {
            if ($find('IEMobile', $key, $result)) {
                $browser = 'IEMobile';
            } else {
                $browser = 'MSIE';
                $key = 0;
            }
            $version = $result['version'][$key];
        } elseif ($key = preg_grep('/playstation \d/i', array_map('strtolower', $result['browser']))) {
            $key = reset($key);

            $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $key);
            $browser = 'NetFront';
        }

        return array('platform' => $platform, 'browser' => $browser, 'version' => $version);
    }

	function _parse_ua_find( $search, &$key, &$result ) {
		$xkey = array_search(strtolower($search), array_map('strtolower', $result['browser']));
		if ($xkey !== false) {
			$key = $xkey;
			return true;
		}

		return false;
	}



?>