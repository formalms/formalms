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
	
	if($id_main_sel > 0) 	$_SESSION['current_main_menu'] = $id_main_sel;
	if($id_module_sel > 0) 	$_SESSION['sel_module_id'] = $id_module_sel;

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
	
    cout(
   	'<div id="menu_lat" class="panel panel-default lmsmenu_block">'
		.'<div class="bd">'
		.'<ul class="main-v-ul">'
	, 'menu');
    
	$logo_panel = '';
	if(isset($_SESSION['idCourse'])) {
		
			
			$path = $GLOBALS['where_files_relative'].'/appLms/'.Get::sett('pathcourse');
            $course_name 	= Docebo::course()->getValue('name');
            if (Docebo::course()->getValue('use_logo_in_courselist'))             
			    $course_img  = ( Docebo::course()->getValue('img_course')==''? Get::tmpl_path().'images/course/course_nologo.png': $path.Docebo::course()->getValue('img_course') );
			
			 $img_course = "";
			if($course_img != '') {
	            $logo_panel .= '<div class="lmsmenu_block">'."\n";
				$logo_panel .= '<p class="align-center">'
					.'<img class="boxed" src="'.$course_img.'" />'
					.'</p>'."\n";
					
		        $logo_panel .= '</div>'."\n";
                $img_course = '<br><p align="center"><img class="boxed" src="'.$course_img.'" /></p>';
            }
			
	}			


         
    cout('
                  <div class="sidebar-nav">
                  <div class="navbar navbar-default" role="navigation">
                <div class="navbar-header">
                  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-navbar-collapse">
                 
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  </button>
                  <span class="visible-xs navbar-brand">Menu corso</span>
                </div>
        
                <div class="navbar-collapse collapse sidebar-navbar-collapse">
                  <ul class="nav navbar-nav" id="sidenav01">','menu');
                 
    
    
    
    $cont=0;
    
    //** id_main e' l'indice del menu principale 
    //** menu è il vettore interno 
    $array_menu = array();
    
	while(list($id_main, $menu) = each($menu_module)) {
        $span='';
        if($cont==0)  $span = '<span class="glyphicon glyphicon-cloud"></span> ';
        if($cont==1)  $span = '<span class="glyphicon glyphicon-inbox"></span> ';
        if($cont==2)  $span = '<span class="glyphicon glyphicon-road"></span>';
        if($cont==3)  $span = '<span class="glyphicon glyphicon-stats"></span> ';
        
		if(!empty($menu['submenu'])) {
           /*   
			cout('<li class="main-v main-'.( $_SESSION['current_main_menu'] == $id_main ? 'open' : 'close' ).'">'

				.'<a class="main-av" href="#"
				onclick="( this.parentNode.className != \'main-v main-close\' ? this.parentNode.className = \'main-v main-close\' : this.parentNode.className = \'main-v main-open\' )"
				>'.$menu['main']['name'].'</a>'
				.'<div class="bd">'
				.'<ul>'
			, 'menu'); 
               */
            
            $active = "class='collapse'";  
            $style = 'style="height: 0px;"';
            $li_class = 'class="active"';
            if($_SESSION['current_main_menu'] == $id_main) {
                $active = '';
                $style = 'style="height: auto;"';
            }    
             
           cout('<br><li '.$li_class.'>
                      <a href="#" data-toggle="collapse" data-target="#toggleDemo-'.$id_main.'" data-parent="#sidenav01" class="collapsed">
                        '.$span.'
                     <b>'.$menu['main']['name'].'</b>
                     <span class="caret pull-right">
                      </a>
                      <div  '.$active.' id="toggleDemo-'.$id_main.'" '.$style.' >
                        <ul class="nav nav-list">','menu'); 
                       
            
            
            
			while(list($id_sub, $sub) = each($menu['submenu'])) {

                  $array_menu[$id_main]['nome_area'] = $menu['main']['name'];    
                  $array_menu[$id_main]['menu'] = $sub;
          

					$active_sub = '';
                    $sub_menu_name = $sub['name'];
					if ($id_sub == $_SESSION['sel_module_id']){
                         $active_sub = 'class="active-sub"';                 
                    }
                    
				    //cout('<li class="sub-v '.$active.'"><a href="'.$sub['link'].'" >'.$sub['name'].'</a></li>' , 'menu');
                    cout('<li '.$active_sub.'><a href="'.$sub['link'].'">'.$sub_menu_name.'</a></li>','menu')    ;
                
                
			}
            
             /*
			cout('</ul>'
				.'</div>'
				.'</li>', 'menu');
              */  
                
                
             cout('
                </ul>
                </div> </li> 
             ','menu');   
                
            
  
            
		} // endif

        
        
        $cont++;
        
        
	}    
    
    
    cout('              </ul>
              </div><!--/.nav-collapse -->
    </div>
  </div>','menu');
  
  
        
      //**  horizontal menu **    
      //
  
      

    

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
		
		$path = $GLOBALS['where_files_relative'].'/appLms/'.Get::sett('pathcourse');
		$GLOBALS['page']->add('<li><a href="#your_info">'.Lang::t('_BLIND_YOUR_INFO', 'menu_over').'</a></li>', 'blind_navigation');
	
		$userid 		= Docebo::user()->getUserId();
		$sponsor_link 	= Docebo::course()->getValue('linkSponsor');
		$sponsor_img 	= Docebo::course()->getValue('imgSponsor');
		$info_panel .= '<div class="lmsmenu_block">'."\n";


		
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
			
            $tempo_parziale = Lang::t("_PARTIAL_TIME", "course");
            $tempo_totale =  Lang::t("_TOTAL_TIME", "standard");
            $user_online =  Lang::t("_WHOIS_ONLINE", "course");
             //** LR responsive tabella statistiche **
            $info_panel .='<style>
                            @media
                            only screen and (max-width: 870px),
                            (min-device-width: 870px) and (max-device-width: 1024px)  {            
                                        #user_stats td:nth-of-type(1):before { content: "'.$tempo_parziale.'"; }
                                        #user_stats td:nth-of-type(2):before { content: "'.$tempo_totale.'"; }
                                        #user_stats td:nth-of-type(3):before { content: "'.$user_online.'"; }    
                                        }        
                                        </style>
                                    ';            
            
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
			
			
            $materiali = Lang::t("_PROGRESS_ALL", "course");
            $completato =  Lang::t("_COMPLETED", "standard");
            $sbagliati =  Lang::t("_PROGRESS_FAILED", "course");
             //** LR responsive stats tab **
            $info_panel .='<style>
                            @media
                            only screen and (max-width: 870px),
                            (min-device-width: 870px) and (max-device-width: 1024px)  {            
                                        #course_stats td:nth-of-type(1):before { content: "'.$materiali.'"; }
                                        #course_stats td:nth-of-type(2):before { content: "'.$completato.'"; }
                                        #course_stats td:nth-of-type(3):before { content: "'.$sbagliati.'"; }    
                                        }        
                                        </style>
                                    ';             
            
            
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
			
			$info_panel_progress = '<p class="course_progress">'
				.'<span>'.Lang::t('_PROGRESS', 'course').' </span>'
				.'</p>'
				.'<div class="nofloat"></div>'        
                .renderProgress($tot_complete, $tot_failed, $total, false)."\n";
                
                // MENU OVER
                cout('<div class="row" style="padding-top:80px;">','menu_over');
                  cout('<div class="col-sm-3">'.$logo_panel.'</div>','menu_over'); 
 
                  cout('<div class="col-sm-9" >','menu_over');
                  cout('<div class="col-md-7"><div><h1>'.Docebo::course()->getValue('name').'</h1></div></div>
                        <div class="col-md-4"><div>'.$info_panel_progress.'</div></div>
                        <div class="col-md-1"><div><br> <button type="button" class="btn btn-sm" data-toggle="modal" data-target="#myModal"><span class="glyphicon glyphicon-stats"></span></button></div></div>
                        ' ,'menu_over');  
                  cout('</div></div>&nbsp;','menu_over');
                                
		} else {
			// MENU OVER
			cout('<div class="row" style="padding-top:80px;">','menu_over');
			  cout('<div class="col-sm-3">'.$logo_panel.'</div>','menu_over'); 
			
			  cout('<div class="col-sm-9" >','menu_over');
			      cout('<div class="col-md-7"><div><h1>'.Docebo::course()->getValue('name').'</h1></div></div>' ,'menu_over');  
			      
			  cout('</div></div><br><br>&nbsp;','menu_over');
		}
		
		$info_panel .= '</div>'."\n";
		
		// Sponsor  ---------------------------------------------------
		if($sponsor_img != '') {
			
			$link_arg = '<img class="boxed" src="'.$path.$sponsor_img.'" alt="'.Lang::t('_SPONSORED_BY', 'course').'" />';
		} else $link_arg = Lang::t('_SPONSORED_BY', 'course');
		
		if($sponsor_link != '' && trim($sponsor_link) != 'http://') {
			
            $link_arg = '<div class="lmsmenu_block align-center">'
                .'<a href="'.$sponsor_link.'" title="'.$sponsor_link.'">'.$link_arg.'</a>'
                .'</div>';
            
			//$GLOBALS['page']->add($link_arg, 'menu');
		} elseif($sponsor_img != '') {
			
			//$GLOBALS['page']->add('<p class="align-center">'.$link_arg.'</p>', 'menu');
		}
		
	} // end if course
	if ($counter == 1) {
		$GLOBALS['page']->clean('menu', false);
		$GLOBALS['page']->clean('content', false);
		$GLOBALS['page']->addStart('');
		$GLOBALS['page']->addEnd('');
	}
	
	
    
    $pop_up_modal = '<!-- Trigger the modal with a button -->
                        <!-- Modal -->
                        <div id="myModal" class="modal fade" role="dialog">
                          <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">'.Lang::t('_STATFORUSER', 'stats').'</h4>
                              </div>
                              <div class="modal-body">
                                <p>
                                '.$link_arg.'
                                <br>'.$info_panel.'</p>
                                <br>
                                <div id="tag_cloud" class="yui-navset"></div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">'.Lang::t('_CLOSE', 'standard').'</button>
                              </div>
                            </div>

                          </div>
                        </div>';
        $GLOBALS['page']->add($pop_up_modal, 'menu');                
                        
                        
    
	if((Get::sett('use_tag', 'off') == 'on') && checkPerm('view', true, 'forum')) {
	
		YuiLib::load(array('tabview'=>'tabview-min.js')
			, array('tabview/assets/skins/sam/' => 'tabview.css'));
			
		require_once($GLOBALS['where_framework'].'/lib/lib.tags.php');
		$tags = new Tags('*');

		//$GLOBALS['page']->add('<div id="tag_cloud" class="yui-navset"></div>', 'menu');
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
