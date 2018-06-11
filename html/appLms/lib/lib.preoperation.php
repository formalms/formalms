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

// access granted only if user is logged in
if(Docebo::user()->isAnonymous()) { // !isset($_GET['no_redirect']) && !isset($_POST['no_redirect']) XXX: redirection???
    
    // save requested page in session to call it after login
    $_SESSION["login_redirect"] = $_SERVER[REQUEST_URI];
    
    // redirect to index
    Util::jump_to(Get::rel_path("base"));
}

// get maintenence setting
$query  = " SELECT param_value FROM %adm_setting"
        . " WHERE param_name = 'maintenance'"
        . " ORDER BY pack, sequence";
$maintenance = $db->fetch_row($db->query($query))[0];

// handling maintenece
if($maintenance == "on" && Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
    
    // only god admins can access maintenence - logout the user    
    Util::jump_to(Get::rel_path("base") . "/index.php?r=" . _logout_);
}

// handling access from multiple sessions
if(Get::sett('stop_concurrent_user') == 'on' && isset($_SESSION['idCourse'])) {

    // two user logged at the same time
    if(!TrackUser::checkSession(getLogUserId())) {
        
        TrackUser::resetUserSession(getLogUserId());
        Util::jump_to(Get::rel_path("base") . "/index.php?r=" . _stopconcurrency_);
    }
}

if(isset($_SESSION['must_renew_pwd']) && $_SESSION['must_renew_pwd'] == 1 ) {
    
    // handling required password renewal
    
    $GLOBALS['modname'] = '';
    $GLOBALS['op']      = '';
    $GLOBALS['req'] = 'lms/profile/renewalpwd';
} elseif(isset($_SESSION['request_mandatory_fields_compilation']) && $_SESSION['request_mandatory_fields_compilation'] == 1 && $GLOBALS['req'] != 'precompile/set') {
    
    // handling required mandatory fields compilation
    
    $GLOBALS['modname'] = '';
    $GLOBALS['op']      = '';
    $GLOBALS['req'] = 'lms/precompile/show';
} elseif($GLOBALS['modname'] == "" && $GLOBALS['op'] == "" && $GLOBALS['req'] == "") {
    
    // setting default action
    
    // if course is in session, enter the course
    if(isset($_SESSION['idCourse'])) {
                
        // TODO: in corso
        if($_SESSION['sel_module_id'] != 0) {
            
            $query  = " SELECT module_name, default_op, mvc_path"
                    . " FROM %lms_module"
                    . " WHERE idModule = ".(int)$_SESSION['sel_module_id'];
            list($modname, $op, $mvc_path) = sql_fetch_row(sql_query($query));
            if($mvc_path !== '') $GLOBALS['req'] = $mvc_path;
            $GLOBALS['modname'] = $modname;
            $GLOBALS['op']      = $op;
        }
        
    } else {
        
        // select default home page
        
        $array_tab['tb_classroom']  = 'classroom/show';
        $array_tab['tb_communication']  = 'communication/show';
        $array_tab['tb_coursepath']  = 'coursepath/show';
        $array_tab['tb_elearning']  = 'elearning/show';
        $array_tab['tb_games']  = 'games/show';
        $array_tab['tb_home']  = 'home/show';
        $array_tab['tb_kb']  = 'kb/show';
        $array_tab['tb_label']  = 'label/show';
        $array_tab['tb_videoconference']  = 'videoconference/show';        
        
        $query = " SELECT obj_index from %lms_middlearea where is_home=1";
        list($tb_home) = sql_fetch_row(sql_query($query));
       if (Get::sett('home_page_option') == 'catalogue') {
           $GLOBALS['req'] = 'lms/catalog/show';
       } else{
           if (Get::sett('on_usercourse_empty')=='off'){
              $GLOBALS['req'] = $array_tab[$tb_home]; 
           } else {
                $a= Docebo::user()->getIdSt();
                $q = 'Select count(\'x\') from learning_courseuser where idUser ='.$a;
                list($n) = sql_fetch_row(sql_query($q));
                if ($n == 0) { //showing catalogue if no enrollment
                    $GLOBALS['req'] = 'lms/catalog/show'; 
                }  else { 
                    $GLOBALS['req'] =  $array_tab[$tb_home];
                }
           }
          
       }
       
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

//operation that is needed before loading grafiphs element, menu and so on
switch($GLOBALS['op']) {
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