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

if(!Docebo::user()->isAnonymous() && isset($_SESSION['idCourse'])) {
	$query =	"SELECT course_type"
				." FROM %lms_course"
				." WHERE idCourse = ".(int)$_SESSION['idCourse'];

	list($course_type) = sql_fetch_row(sql_query($query));

	YuiLib::load('base');
	$db = DbConn::getInstance();

	$id_main_sel 	= Get::req('id_main_sel', DOTY_INT, 0);
	$id_module_sel 	= Get::req('id_module_sel', DOTY_INT, 0);

	if(isset($_GET['id_main_sel'])) 	$_SESSION['current_main_menu'] = $id_main_sel;
	if(isset($_GET['id_module_sel'])) 	$_SESSION['sel_module_id'] = $id_module_sel;

	// recover main menu --------------------------------------------------------------------------------
	$counter = 0;
	$id_list = array();
	$menu_module = array();

	$query = "SELECT idMain AS id, name FROM %lms_menucourse_main WHERE idCourse = ".(int)$_SESSION['idCourse']." ORDER BY sequence";
	$re_main = $db->query($query);
	while($main = $db->fetch_obj($re_main)) {

		$menu_module[$main->id] = array(
			'submenu'=> array(),
			'main'=> array(
				'name' => Lang::t($main->name, 'menu_course', false, false, $main->name ),
				'link' => 'index.php?id_module_sel=0&amp;id_main_sel='.$main->id
			)
		);
		$id_list[] = '"menu_lat_'.$main->id.'"';
	}

	$query_menu = "
	SELECT mo.idModule AS id, mo.module_name, mo.default_op, mo.default_name, mo.token_associated AS token, mo.mvc_path, under.idMain AS id_main, under.my_name
	FROM %lms_module AS mo JOIN %lms_menucourse_under AS under ON (mo.idModule = under.idModule)
	WHERE under.idCourse = ".(int)$_SESSION ['idCourse']."
	ORDER BY under.idMain, under.sequence";
	$re_menu_voice = $db->query($query_menu);
	while($obj = $db->fetch_obj($re_menu_voice)) {

		// checkmodule module
		if(checkPerm($obj->token, true, $obj->module_name)) {

			$GLOBALS['module_assigned_name'][$obj->module_name] = ( $obj->my_name != '' ? $obj->my_name : Lang::t($obj->default_name, 'menu_course') );

			$menu_module[$obj->id_main]['submenu'][$obj->id] = array(
				'name' => $GLOBALS['module_assigned_name'][$obj->module_name],
				'link' => ( $obj->mvc_path != ''
					? 'index.php?r='.$obj->mvc_path.'&amp;id_module_sel='.$obj->id.'&amp;id_main_sel='.$obj->id_main
					: 'index.php?modname='.$obj->module_name.'&amp;op='.$obj->default_op.'&amp;id_module_sel='.$obj->id.'&amp;id_main_sel='.$obj->id_main
				)
			);
			$counter++;
		} // end if checkPerm

	} // end while


	// NUOVO MENU
	if(isset($_SESSION['idCourse'])) {
            $path = $GLOBALS['where_files_relative'].'/appLms/'.Get::sett('pathcourse');
            $course_img 	= Docebo::course()->getValue('img_course');
			$sponsor_link 	= Docebo::course()->getValue('linkSponsor');
			$sponsor_img 	= Docebo::course()->getValue('imgSponsor');

            if($course_img != '') {
                $logo = '<img class="course_logo" src="'.$path.$course_img.'" alt="'.Lang::t('_COURSE_LOGO', 'course').' : '.$course_name.'" />';
            }
			// sponsor logo
			if($sponsor_img != '') {
                $sponsor_img = '<img class="course_logo sponsor" src="'.$path.$sponsor_img.'" />';
            }
        }
		$menu_reborn = $logo.'<h1>'.Docebo::course()->getValue('name').'</h1>';
		$menu_bottom = '<div id="menu-oriz">';

		$menu_right = '<ul id="float-right">';

		while(list($id_main, $menu) = each($menu_module)) {
			if(!empty($menu['submenu'])) {

				$menu_right .= '<li><div class="menu-area'.( $_SESSION['current_main_menu'] == $id_main ? ' menu-selected' : '' ).'">';
				$menu_right .= '<a href="#" rel="'.  str_replace(" ","_",strtolower($menu['main']['name'])).'">'.$menu['main']['name'].'</a></div></li>';

                $menu_left .= '<ul id="'.  str_replace(" ","_",strtolower($menu['main']['name'])).'" class="float-left '.( $_SESSION['current_main_menu'] == $id_main ? 'sub_visible' : 'sub_hide' ).'">';

				$count = count($menu['submenu']);
				$i = 1;
				while(list($id_sub, $sub) = each($menu['submenu'])) {
					$active = '';
					if ($id_sub == $_GET['id_module_sel'])
						$active = ' class="active"';
					$menu_left .= '<li><a href="'.$sub['link'].'"'.$active.'>'.$sub['name'];
					$i == $count ? $menu_left .= '</a></li>' : $menu_left .= '</a> | </li>';
					$i++;
	            }
                $menu_left  .= '</ul>';
			}
        }
		$menu_right .= '</ul>';

        $user_stats = array('head'=>array(),'body'=>array());
            if(!isset($_SESSION['is_ghost']) || $_SESSION['is_ghost'] !== true) {

                if(Docebo::course()->getValue('show_time') == 1) {

                    $tot_time_sec 		= TrackUser::getUserPreviousSessionCourseTime(getLogUserId(), $_SESSION['idCourse']);
                    $partial_time_sec 	= TrackUser::getUserCurrentSessionCourseTime($_SESSION['idCourse']);
                    $tot_time_sec  		+= $partial_time_sec;

                    $hours 		= (int)($partial_time_sec / 3600);
                    $minutes 	= (int)(($partial_time_sec % 3600) / 60);
                    $seconds 	= (int)($partial_time_sec % 60);
                    if($minutes < 10) $minutes = '0'.$minutes;
                    if($seconds < 10) $seconds = '0'.$seconds;
                    $partial_time = ( $hours != 0 ? $hours.'h ' : '' ).$minutes.'m ';//.$seconds.'s ';

                    $hours 		= (int)($tot_time_sec/3600);
                    $minutes 	= (int)(($tot_time_sec%3600)/60);
                    $seconds 	= (int)($tot_time_sec%60);
                    if($minutes < 10) $minutes = '0'.$minutes;
                    if($seconds < 10) $seconds = '0'.$seconds;
                    $tot_time = ( $hours != 0 ? $hours.'h ' : '' ).$minutes.'m ';//.$seconds.'s ';

                    Util::get_js(Get::rel_path('lms').'/lib/lib.track_user.js', true, true);
                    $GLOBALS['page']->add(
                        '<script type="text/javascript">'
                        .'	userCounterStart('.(int)$partial_time_sec.', '.(int)$tot_time_sec.');'
                        .'</script>'."\n"
                    , 'page_head');


                    $user_stats['head'][0] = Lang::t('_PARTIAL_TIME', 'course');
                    $user_stats['body'][0] = '<span id="partial_time">'.$partial_time.'</span>';

                    $user_stats['head'][1] = Lang::t('_TOTAL_TIME', 'course');
                    $user_stats['body'][1] = '<span id="total_time">'.$tot_time.'</span>';
                }

            }
            if(Docebo::course()->getValue('show_who_online') == _SHOW_INSTMSG) {

                addCss('instmsg');
                addJs('addons/yui/my_window/','windows.js');
                addJs($GLOBALS['where_lms_relative'].'/modules/instmsg/','instmsg.js');

                $GLOBALS['page']->add(
                    '<script type="text/javascript">'
                    ." setup_instmsg( '".Docebo::user()->getIdSt()."', "
                    ."'".$userid."', "
                    ."'".getPathImage('fw')."' ); "
                    .'</script>'."\n", 'page_head');

                $user_stats['head'][2] = Lang::t('_WHOIS_ONLINE', 'course');
                $user_stats['body'][2] = '<b id="user_online_n">'
                    .'<a id="open_users_list" href="javascript:void(0)">'
                        .TrackUser::getWhoIsOnline($_SESSION['idCourse'])
                    .'</a></b>';

            } elseif(Docebo::course()->getValue('show_who_online') == _SHOW_COUNT) {

                $user_stats['head'][2] = Lang::t('_WHOIS_ONLINE', 'course');
                $user_stats['body'][2] = '<b id="user_online_n">'
                        .TrackUser::getWhoIsOnline($_SESSION['idCourse'])
                    .'</b>';
            }

        $all_stats = '<div id="accordion"><h3>';
		$all_stats .='<span class="tempo">'.Lang::t('_TOTAL_TIME', 'course').': '.$tot_time.' ';
		

		// get status count value
        if(Docebo::course()->getValue('show_progress') == 1 ) {

		    require_once( $GLOBALS['where_lms'].'/lib/lib.stats.php' );
            $total = getNumCourseItems( $_SESSION['idCourse'],
                                        FALSE,
                                        getLogUserId(),
                                        FALSE );
            $tot_complete = getStatStatusCount(	getLogUserId(),
                                                $_SESSION['idCourse'],
                                                array( 'completed', 'passed' ) );
			$tot_incomplete = $total - $tot_complete;

            $tot_passed = getStatStatusCount(	getLogUserId(),
                                                $_SESSION['idCourse'],
                                                array( 'passed' ) );
            $tot_failed = getStatStatusCount(	getLogUserId(),
                                                $_SESSION['idCourse'],
                                                array( 'failed' ) );
		}

        // print progress bar -------------------------------------------------
		$user_stats_table = "";
		if(Docebo::course()->getValue('show_progress') == 1  && true ) {

           $user_stats_table = '<table id="user_stats" class="quick_table">'
            .'<thead><tr>'
            .( isset($user_stats['head'][0]) ? '<th scope="col">'.$user_stats['head'][0].'</th>' : '' )
            .( isset($user_stats['head'][1]) ? '<th scope="col">'.$user_stats['head'][1].'</th>' : '' )
            .( isset($user_stats['head'][2]) ? '<th scope="col">'.$user_stats['head'][2].'</th>' : '' )
            .'</tr></thead><tbody><tr>'
            .( isset($user_stats['body'][0]) ? '<td>'.$user_stats['body'][0].'</td>' : '' )
            .( isset($user_stats['body'][1]) ? '<td>'.$user_stats['body'][1].'</td>' : '' )
            .( isset($user_stats['body'][2]) ? '<td>'.$user_stats['body'][2].'</td>' : '' )
            .'</tr></tbody>'
            .'</table>';

            $all_stats .="</br>Progress:</span>".renderProgress($tot_complete, $tot_failed, $total, false);
        }
		$course_stats_table = "";
        if(Docebo::course()->getValue('show_progress') == 1 && true ) {


            $course_stats_table .= '<table id="course_stats" class="quick_table">'
            .'<thead><tr>'
                .'<th scope="col">'.Lang::t('_PROGRESS_ALL', 'course').'</th>'
                .'<th scope="col">'.Lang::t('_PROGRESS_COMPLETED', 'course').'</th>'
				.'<th scope="col">'.Lang::t('_PROGRESS_INCOMPLETE', 'course').'</th>'
            .'</tr></thead><tbody><tr>'
                .'<td>'.$total.'</td>'
                .'<td>'.$tot_complete.'</td>'
                .'<td>'.$tot_incomplete.'</td>'
            .'</tr></tbody>'
            .'</table>';

	        $course_stats_table .= "\n";
		}

		$course_ex_stats_table = "";
		if(Docebo::course()->getValue('show_progress') == 1  && true ) {

            $course_ex_stats_table .= '<table id="course_exstats" class="quick_table">'
            .'<thead><tr>'
                .'<th scope="col">'.Lang::t('_PROGRESS_PASSED', 'course').'</th>'
                .'<th scope="col">'.Lang::t('_PROGRESS_FAILED', 'course').'</th>'
            .'</tr></thead><tbody><tr>'
                .'<td>'.$tot_passed.'</td>'
                .'<td>'.$tot_failed.'</td>'
            .'</tr></tbody>'
            .'</table>';
	        $course_ex_stats_table .= "\n";
        }

		$all_stats .= '<div id="arrow"></div><div class="clear"></div>';
        $all_stats .= '</h3><div>'.$user_stats_table.''.$course_stats_table.''.$course_ex_stats_table.$sponsor_img.'</div>';

        $all_stats .='</div>';

		$menu_bottom .= $menu_left.$menu_right.'</div>';

		$menu_reborn .= $all_stats.'<div class="clear"></div>'.$menu_bottom;

        cout($menu_reborn,'menu');

}
