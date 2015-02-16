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

// redirection
if( !isset($_GET['no_redirect']) && !isset($_POST['no_redirect']) ) {
	if(Docebo::user()->isAnonymous()
		&& (!isset($GLOBALS['modname']) || ($GLOBALS['modname'] != 'login'))
		&& !isset($_GET['login_user']) && !isset($_POST['login_user']) ) {

		require_once(_base_.'/lib/lib.platform.php');
		$pl_man =& PlatformManager::CreateInstance();
		$pl = $pl_man->getHomePlatform();
		
		if($pl != 'cms') {
			// Added by Claudio Redaelli
			$_SESSION['login_requestedURL'] = "?" . $_SERVER['QUERY_STRING'];
			
			$GLOBALS['op'] 		= 'login';
			$GLOBALS['modname'] = 'login';
			Util::jump_to('../index.php');
		}
	}
}

if(Get::sett('stop_concurrent_user') == 'on') {

	if(!Docebo::user()->isAnonymous() && isset($_SESSION['idCourse'])) {

		//two user logged at the same time
		if(!TrackUser::checkSession(getLogUserId())) {
			TrackUser::resetUserSession(getLogUserId());
			$_SESSION = array();
			session_destroy();
			
			Util::jump_to(Get::rel_path('lms').'/index.php?modname=login&op=logout&msg=102');
		}
	}
}

if(isset($_SESSION['must_renew_pwd']) && $_SESSION['must_renew_pwd'] == 1) {
	if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
		if(!Docebo::user()->isAnonymous() && $GLOBALS['modname'] != 'login' && $GLOBALS['op'] != 'logout') {
			$GLOBALS['modname'] = '';
			$GLOBALS['op'] 		= '';
			if(strpos($GLOBALS['req'], 'lms/profile') === false) {
				$GLOBALS['req'] = 'lms/profile/renewalpwd';
			}
		}
	}
}else{
	if($GLOBALS['modname'] == '' && $GLOBALS['op'] == '' && $GLOBALS['req'] == '' && !Docebo::user()->isAnonymous()) {
		if(!isset($_SESSION['idCourse'])) {
			// the user isn't into a course, redirect it to the mycourses area
			$_SESSION['current_main_menu'] = '1';
			$_SESSION['sel_module_id'] = '1';
			$GLOBALS['req'] = _after_login_;
		} else {
			//redirect the user in the leaved module of the course
			if($_SESSION['sel_module_id'] !=0) {
				$query = "SELECT module_name, default_op, mvc_path"
					." FROM %lms_module"
					." WHERE idModule = ".(int)$_SESSION['sel_module_id'];
				list($modname, $op, $mvc_path) = sql_fetch_row(sql_query($query));
				if($mvc_path !== '') $GLOBALS['req'] = $mvc_path;
				$GLOBALS['modname'] = $modname;
				$GLOBALS['op'] 		= $op;
			}
		}
	}
}

/**
 * SSO
 * operation that is needed before loading grafiphs element, menu and so on
 * index.php?login_user=staff&time=200812101752&token=5D93BCEDF500E9759E4870492AF32E7A
 */
$login_user = Get::req('login_user', DOTY_MIXED, '');
$login_user_use_idst = Get::req('use_user_idst', DOTY_MIXED, false);
if($login_user != '' && Get::sett('sso_token', 'off') == 'on') {

	$time			= Get::req('time', DOTY_MIXED, '');
	$secret			= Get::sett('sso_secret', '8ca0f69afeacc7022d1e589221072d6bcf87e39c');
	$token			= strtoupper(Get::req('token', DOTY_MIXED, ''));
	$recalc_token	= strtoupper(md5(strtolower(stripslashes($login_user)).','.$time.','.$secret));

	$lifetime = Get::sett('rest_auth_lifetime', 1);
	
	if($recalc_token == $token && $time+$lifetime >= time()) {
		//login		
		$user_manager =& $GLOBALS['current_user']->getAclManager();		
		if (!$login_user_use_idst) {
			$username = '/'.$login_user;
			$user_info = $user_manager->getUser(false, $username);
		}
		else { // use idst instead of username
			$user_info = $user_manager->getUser($login_user);
			if (!empty($user_info)) {
				$username =$user_info[ACL_INFO_USERID];
			}
		}
		if($user_info != false) {

			$du = new DoceboUser( $username, 'public_area' );
			Lang::set($du->preference->getLanguage());
			
			$du->setLastEnter(date("Y-m-d H:i:s"));
			$_SESSION['user_enter_mark'] = time();
			$du->loadUserSectionST();
			$du->SaveInSession();
			$GLOBALS['current_user'] = $du;
			
			$id_course		= Get::req('id_course', DOTY_INT, 0);
			$next_action	= Get::req('act', DOTY_STRING, 'none');
			$id_item		= Get::req('id_item', DOTY_INT, '');
			$chapter		= Get::req('chapter', DOTY_MIXED, false);

			if($id_course) {
				// if we have a id_course setted we will log the user into the course,
				// if no specific action are required we will redirect the user into the first page
				// otherwise we will continue to another option
				require_once(_lms_.'/lib/lib.course.php');
				logIntoCourse($id_course, ( $next_action == false || $next_action == 'none' ? true : false ));

				// specific action required
				switch($next_action) {
					case "playsco" : {

						$linkto = 'index.php?modname=organization&op=custom_playitem&id_course='.$id_course.'&courseid='.$id_course.'&id_item='.$id_item.'&start_from_chapter='.$chapter.'&collapse_menu=1';
						Util::jump_to($linkto);
					};break;
				}
			}

			Util::jump_to( 'index.php?r='. _after_login_ );
		} else {
			Util::jump_to('../index.php?access_fail=1');
		}
	} else {

		Util::jump_to('../index.php?access_fail=1');
	}

}

$next_action = Get::req('act', DOTY_STRING, false);
if($next_action != false && Get::sett('sco_direct_play', 'off') == 'on') {

	$id_course		= Get::req('id_course', DOTY_INT, 0);
	$id_item		= Get::req('id_item', DOTY_INT, '');
	$chapter		= Get::req('chapter', DOTY_MIXED, false);
	if($id_course) {
		// if we have a id_course setted we will log the user into the course,
		// if no specific action are required we will redirect the user into the first page
		// otherwise we will continue to another option
		require_once(_lms_.'/lib/lib.course.php');
		logIntoCourse($id_course, ( $next_action == false || $next_action == 'none' ? true : false ));

		// specific action required
		switch($next_action) {
			case "playsco" : {

				$linkto = 'index.php?modname=organization&op=custom_playitem&id_item='.$id_item.'&start_from_chapter='.$chapter.'&collapse_menu=1';
				Util::jump_to($linkto);
			};break;
		}
	}
}

if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
	
	$query = "SELECT param_value FROM core_setting
			WHERE param_name = 'maintenance'
			ORDER BY pack, sequence";

	$mode = $db->fetch_row($db->query($query));
	// Se siamo in modalita' manutenzione
	if($mode[0] == "on") {
//	if(Get::sett('maintenance') == 'on'){ // non posso farlo cosi perche non ancora settato
		if(!Docebo::user()->isAnonymous() && $GLOBALS['modname'] != 'login' && $GLOBALS['op'] != 'logout') {
			TrackUser::resetUserSession(getLogUserId());
			$_SESSION = array();
			session_destroy();
			Util::jump_to(Get::rel_path('lms').'/index.php?modname=login&op=logout');
		}
	}
}
/*
if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
	if(!Docebo::user()->isAnonymous() && $GLOBALS['modname'] != 'login' && $GLOBALS['op'] != 'logout') {
		$pwd_elapsed = Docebo::user()->isPasswordElapsed();
		if($pwd_elapsed > 0) {
			$GLOBALS['modname'] = '';
			$GLOBALS['op'] 		= '';
			$GLOBALS['req'] = 'lms/profile/renewalpwd';
		}
	}
}
*/
//operation that is needed before loading grafiphs element, menu and so on
switch($GLOBALS['op']) {
	
	//login control
	case "confirm" : {
		if($GLOBALS['modname'] == 'login') {

			require_once(_base_.'/lib/lib.usermanager.php');
			$manager = new UserManager();
			$login_data = $manager->getLoginInfo();
			$manager->saveUserLoginData();

			if($login_data['userid'] != ''){

				if(Get::sett('ldap_used') !== 'on')
				{
					require_once(_base_.'/lib/lib.acl.php' );

					$acl = new DoceboACL();
					$acl_man =& $acl->getACLManager();
				}

				$GLOBALS['current_user'] =& DoceboUser::createDoceboUserFromLogin( 	$login_data['userid'],
																					$login_data['password'], 
																					'public_area', 
																					$login_data['lang'] );
				
				if( $GLOBALS['current_user'] === FALSE ) {
					$GLOBALS['current_user'] 	=& DoceboUser::createDoceboUserFromSession('public_area');
					$GLOBALS['access_fail'] 	= true;
					$GLOBALS['op'] 				= 'login';
					
					Util::jump_to('../index.php?access_fail=1');
				} else {
					$_SESSION['logged_in'] = true;
					//loading related ST
					Docebo::user()->loadUserSectionST('/lms/course/public/');

					$pwd_elapsed = Docebo::user()->isPasswordElapsed();
					if($pwd_elapsed > 0) {
						//$GLOBALS['modname'] = 'profile';
						//$GLOBALS['op'] 		= 'renewalpwd';
						$GLOBALS['modname'] = '';
						$GLOBALS['op'] 		= '';
						$GLOBALS['req'] = 'lms/profile/renewalpwd';
						//Util::jump_to('index.php?r=lms/profile/renewalpwd');
					} else {
						
						$_SESSION['current_main_menu'] = '1';
						$_SESSION['sel_module_id'] = '1';
					}
			
					// perform other platforms login operation
					require_once(_base_.'/lib/lib.platform.php');
					$pm =& PlatformManager::createInstance();
					$pm->doCommonOperations("login");
					Docebo::user()->SaveInSession();

					// reset user template:
					resetTemplate();
					
					// end of normal login operation
					
					// check for policy and mandatory fields
					if (Get::sett('request_mandatory_fields_compilation', 'off') == 'on') {
						//if there are field that must be compiled or the privacy policy must be accepted
						$pcm = new PrecompileLms();
						if($pcm->compileRequired()) {
							Util::jump_to('index.php?r=lms/precompile/show');
						}
					}

					// the user must be redirected to a specific use
					if(isset($_SESSION['login_requestedURL']) 
						&& !empty($_SESSION['login_requestedURL']) 
						&& strcmp('id_course', $_SESSION['login_requestedURL'])) {
						
						$url = $_SESSION['login_requestedURL'];
						unset($_SESSION['login_requestedURL']);
						
						$str = parse_url($url);
						parse_str($str['query'], $vars );
						if(isset($vars['id_course'])) {
							
							require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
							if(logIntoCourse($vars['id_course'], false)) Util::jump_to($url);
						}
					}
					//goto welcome page
					$_SESSION['current_main_menu'] = '1';
					$_SESSION['sel_module_id'] = '1';

					if ($pwd_elapsed <= 0) {
						if(Get::sett('first_catalogue') == 'on') Util::jump_to('index.php?r=lms/catalog/show');
                        
                        // if elearning tab disabled, jump to classroom courses
                        require_once(_lms_.'/lib/lib.middlearea.php');                        
                        $ma = new Man_MiddleArea();
                        if (!$ma->currentCanAccessObj('tb_elearning'))  Util::jump_to('index.php?r=lms/classroom/show'); 
                        
						Util::jump_to( 'index.php?r='._after_login_ );
					}

					// end login
				}
			} else {
				
				Util::jump_to('../index.php');
			}
		}
	};break;
	case "logout" : {
		require_once($GLOBALS['where_lms'].'/lib/lib.track_user.php');
		
		if(!Docebo::user()->isAnonymous() && isset($_SESSION['idCourse'])) {
		
			TrackUser::setActionTrack(getLogUserId(), $_SESSION['idCourse'], '', '');
		}
		//i need to save the language of the user in order to use it again after logout
		$language = Lang::get();
		if(!Docebo::user()->isAnonymous()) {
			TrackUser::logoutSessionCourseTrack();
			$_SESSION = array();
			session_destroy();
			
			// load standard language module and put it global
			$glang =& DoceboLanguage::createInstance( 'standard', 'framework');
			
			// Recreate Anonymous user
			$GLOBALS['current_user'] =& DoceboUser::createDoceboUserFromSession('public_area');
			$GLOBALS['logout'] 	= true;

			require_once(_base_.'/lib/lib.platform.php');
			$pm =& PlatformManager::createInstance();
			$pm->doCommonOperations("logout");			
		}
		
		$GLOBALS['op'] 		= 'login';
		$GLOBALS['modname'] = 'login';

		$query = 'logout=1&special=changelang&new_lang='.$language;
		if (isset($_GET['msg']) && !empty($_GET['msg'])) {
			$query ='msg='.(int)$_GET['msg'];
		}

		Util::jump_to('../index.php?'.$query);
	};break;
	case "aula" : {

		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		if(!logIntoCourse($_GET['idCourse'], true)) {
			
			$_SESSION['current_main_menu'] = '1';
			$_SESSION['sel_module_id'] = '1';
			$GLOBALS['modname'] = 'middlearea';
			$GLOBALS['op'] 		= 'show';
		}
	};break;
	//registering menu information
	case "unregistercourse" : {
		
		//if a course is selected the selection is deleted
		if (isset($_SESSION['idCourse'])) {
			
			TrackUser::closeSessionCourseTrack();
			
			unset($_SESSION['idCourse']);
			unset($_SESSION['idEdition']);
		}
		if(isset($_SESSION['test_assessment'])) unset($_SESSION['test_assessment']);
		if(isset($_SESSION['direct_play'])) unset($_SESSION['direct_play']); 
		if(isset($_SESSION['cp_assessment_effect'])) unset($_SESSION['cp_assessment_effect']);
		$_SESSION['current_main_menu'] = '1';
		$_SESSION['sel_module_id'] = '1';
		$_SESSION['is_ghost'] = false;
		$GLOBALS['modname'] = 'middlearea';
		$GLOBALS['op'] 		= 'show';
	};break;
	case "selectMain" : {
		$_SESSION['current_main_menu'] = (int)$_GET['idMain'];
		$first_page = firstPage( $_SESSION['current_main_menu'] );
		
		if($first_page['modulename'] != '') 
			Util::jump_to( 'index.php?modname='.$first_page['modulename'].'&op='.$first_page['op'].'&sel_module='.$first_page['idModule']);
	};break;
	//change language for register user
	case "registerconfirm" : {
		setLanguage($_POST['language']);
	};break;
	case "registerme" : {
		list($language_reg) = sql_fetch_row(sql_query("
		SELECT language
		FROM ".$GLOBALS['prefix_lms']."_user_temp 
		WHERE random_code = '".$_GET['random_code']."'"));
		if($language_reg != '') setLanguage($language_reg);
	};break;
}

// special operation
$sop = importVar('sop', false, '');
if($sop) {
	if(is_array($sop)) $sop = key($sop);
	switch($sop) {

		case "setcourse" : {
			$id_c = Get::req('sop_idc', DOTY_INT, 0);

			if (isset($_SESSION['idCourse']) && $_SESSION['idCourse'] != $id_c) {

				TrackUser::closeSessionCourseTrack();
				unset($_SESSION['idCourse']);
				unset($_SESSION['idEdition']);

				require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
				logIntoCourse($id_c, false);
			} elseif(!isset($_SESSION['idCourse'])) {

				require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
				logIntoCourse($id_c, false);
			}
			if(isset($_SESSION['cp_assessment_effect'])) unset($_SESSION['cp_assessment_effect']);

		};break;
		case "resetselmodule" : {
			unset($_SESSION['sel_module_id']);
		};break;
		case "unregistercourse" : {
			if (isset($_SESSION['idCourse'])) {

				TrackUser::closeSessionCourseTrack();
				unset($_SESSION['idCourse']);
				unset($_SESSION['idEdition']);
			}
			if(isset($_SESSION['cp_assessment_effect'])) unset($_SESSION['cp_assessment_effect']);
		};break;
		case "changelang" : {
			Lang::set(Get::req('new_lang', DOTY_MIXED));
			$_SESSION['changed_lang'] = true;
		};break;
	}
}

// istance the course description class
if(isset($_SESSION['idCourse']) && !isset($GLOBALS['course_descriptor'])) {

	require_once(_lms_.'/lib/lib.course.php');
	$GLOBALS['course_descriptor'] = new DoceboCourse($_SESSION['idCourse']);

}

?>