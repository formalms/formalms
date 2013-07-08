<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
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
	
	// Print of the menu
	cout('<div class="title_block">'
			.'<h1>'.Docebo::course()->getValue('name').'</h1>'
		.'</div>'
		.'<div id="menu_lat" class="lmsmenu_block">'
		.'<div class="bd">'
		.'<ul class="main-v-ul">'
	, 'menu');
	while(list($id_main, $menu) = each($menu_module)) {

		if(!empty($menu['submenu'])) {

			cout('<li class="main-v main-'.( $_SESSION['current_main_menu'] == $id_main ? 'open' : 'close' ).'">'
				.'<a class="main-av" href="#"
				onclick="( this.parentNode.className != \'main-v main-close\' ? this.parentNode.className = \'main-v main-close\' : this.parentNode.className = \'main-v main-open\' )"
				>'.$menu['main']['name'].'</a>'
				.'<div class="bd">'
				.'<ul>'
			, 'menu');
			while(list($id_sub, $sub) = each($menu['submenu'])) {

				cout('<li class="sub-v"><a href="'.$sub['link'].'">'.$sub['name'].'</a></li>', 'menu');
			}
			cout('</ul>'
				.'</div>'
				.'</li>', 'menu');
		} // endif

	}
	if($course_type === 'assessment' && Docebo::user()->getUserLevelId() === ADMIN_GROUP_GODADMIN)
		cout(	'<li class="main-v">'
				.'<a class="main-av" href="'.Get::rel_path('adm').'/index.php?modname=preassessment&op=assesmentlist&of_platform=lms">'.Lang::t('_BACK_TO_ADMINISTRATION', 'course').'</a></li>', 'menu');
	cout('</ul>'
		.'</div>'
		.'</div>'
	, 'menu');
	
	// todo: redo the following 
	$info_panel = '';
	if(isset($_SESSION['idCourse'])) {
		
		$path = $GLOBALS['where_files_relative'].'/doceboLms/'.Get::sett('pathcourse');
		$GLOBALS['page']->add('<li><a href="#your_info">'.Lang::t('_BLIND_YOUR_INFO', 'menu_over').'</a></li>', 'blind_navigation');
	
		$userid 		= Docebo::user()->getUserId();
		$course_name 	= Docebo::course()->getValue('name');
		$sponsor_link 	= Docebo::course()->getValue('linkSponsor');
		$sponsor_img 	= Docebo::course()->getValue('imgSponsor');
		$course_img 	= Docebo::course()->getValue('img_course');
		
		$info_panel .= '<div class="lmsmenu_block">'."\n";

		if($course_img != '') {
			
			$info_panel .= '<p class="align-center">'
				.'<img class="boxed" src="'.$path.$course_img.'" alt="'.Lang::t('_COURSE_LOGO', 'course').' : '.$course_name.'" />'
				.'</p>'
				.'<br />'."\n";
		}
		// welcome in_course
		/*$info_panel .= '<p>'
				.'<span>'.Lang::t('_IN_COURSE', 'menu_course').':</span> <b>'.$course_name.'</b>'
				.'</p>'."\n";
		*/
		
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
		
		// who is online ---------------------------------------------------------

		if(Docebo::course()->getValue('show_who_online') == _SHOW_INSTMSG) {
			
			addCss('instmsg');
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
		// print first pannel
		if(!empty($user_stats['head'])) {
			
			$info_panel .= '<table id="user_stats" class="quick_table">'
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
		}
		
		// print progress bar -------------------------------------------------
		if(Docebo::course()->getValue('show_progress') == 1) {
			
			require_once( $GLOBALS['where_lms'].'/lib/lib.stats.php' );
			$total = getNumCourseItems( $_SESSION['idCourse'], 
										FALSE, 
										getLogUserId(), 
										FALSE );
			$tot_complete = getStatStatusCount(	getLogUserId(), 
												$_SESSION['idCourse'],
												array( 'completed', 'passed' ) );
			$tot_failed = getStatStatusCount(	getLogUserId(), 
												$_SESSION['idCourse'],
												array( 'failed' ) );
			
			
			$info_panel .= '<table id="course_stats" class="quick_table">'
			.'<thead><tr>'
				.'<th scope="col">'.Lang::t('_PROGRESS_ALL', 'course').'</th>'
				.'<th scope="col">'.Lang::t('_COMPLETED', 'course').'</th>'
				.'<th scope="col">'.Lang::t('_PROGRESS_FAILED', 'course').'</th>'
			.'</tr></thead><tbody><tr>'
				.'<td>'.$total.'</td>'
				.'<td>'.$tot_complete.'</td>'
				.'<td>'.$tot_failed.'</td>'
			.'</tr></tbody>'
			.'</table>';
			
			$info_panel .= '<p class="course_progress">'
				.'<span>'.Lang::t('_PROGRESS', 'course').'</span>'
				.'</p>'
				.'<div class="nofloat"></div>'
				.renderProgress($tot_complete, $tot_failed, $total, false)."\n";
		}
		
		$info_panel .= '</div>'."\n";
		
		// Sponsor  ---------------------------------------------------
		if($sponsor_img != '') {
			
			$link_arg = '<img class="boxed" src="'.$path.$sponsor_img.'" alt="'.Lang::t('_SPONSORED_BY', 'course').'" />';
		} else $link_arg = Lang::t('_SPONSORED_BY', 'course');
		
		if($sponsor_link != '' && trim($sponsor_link) != 'http://') {
			
			$GLOBALS['page']->add('<div class="lmsmenu_block align-center">'
				.'<a href="'.$sponsor_link.'" title="'.$sponsor_link.'">'.$link_arg.'</a>'
				.'</div>'
			, 'menu');
		} elseif($sponsor_img != '') {
			
			$GLOBALS['page']->add('<p class="align-center">'.$link_arg.'</p>', 'menu');
		}
		
	} // end if course
	if ($counter == 1) {
		$GLOBALS['page']->clean('menu', false);
		$GLOBALS['page']->clean('content', false);
		$GLOBALS['page']->addStart('');
		$GLOBALS['page']->addEnd('');
	}
	
	$GLOBALS['page']->add($info_panel, 'menu');
	
	if((Get::sett('use_tag', 'off') == 'on') && checkPerm('view', true, 'forum')) {
	
		YuiLib::load(array('tabview'=>'tabview-min.js')
			, array('tabview/assets/skins/sam/' => 'tabview.css'));
			
		require_once($GLOBALS['where_framework'].'/lib/lib.tags.php');
		$tags = new Tags('*');

		$GLOBALS['page']->add('<div id="tag_cloud" class="yui-navset"></div>', 'menu');
		$GLOBALS['page']->add(''
			.'<script type="text/javascript">'."\n"
			."	(function() {"."\n"
			."		var cloud_tab = new YAHOO.widget.TabView();"."\n"
			."		cloud_tab.addTab( new YAHOO.widget.Tab({"."\n"
			."			label: '". Lang::t('_POPULAR', 'tags', 'framework')."',"."\n"
			."			dataSrc: '".$GLOBALS['where_framework_relative']."/ajax.adm_server.php?plf=framework&file=tags&op=get_platform_cloud',"."\n"
			."			cacheData: true, "."\n"
			."			active: true "."\n"
			."		}));"."\n"
			."		cloud_tab.addTab( new YAHOO.widget.Tab({"."\n"
			."			label: '". Lang::t('_COURSE', 'tags', 'framework')."',"."\n"
			."			dataSrc: '".$GLOBALS['where_framework_relative']."/ajax.adm_server.php?plf=framework&file=tags&op=get_course_cloud',"."\n"
			."			cacheData: true"."\n"
			."		}));"."\n"
			."		cloud_tab.addTab( new YAHOO.widget.Tab({"."\n"
			."			label: '". Lang::t('_YOURS', 'tags', 'framework')."',"."\n"
			."			dataSrc: '".$GLOBALS['where_framework_relative']."/ajax.adm_server.php?plf=framework&file=tags&op=get_user_cloud',"."\n"
			."			cacheData: true"."\n"
			."		}));"."\n"
			."		cloud_tab.appendTo('tag_cloud');"."\n"
			."	})();"."\n"
			.'</script>'."\n"
		, 'scripts');
	}
	
}
