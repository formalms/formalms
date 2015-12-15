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

if(!Docebo::user()->isAnonymous()) {
	YuiLib::load('base,menu');
	require_once(_lms_.'/lib/lib.middlearea.php');
    
   require_once('../widget/lms_block/lib.lms_block_menu.php');
   require_once(_lms_.'/lib/lib.course.php');
   $widget = new  Lms_BlockWidget_menu() ;

   //** GESTIONE AREA PROFILO UTENTE **
   require_once (_lib_ . '/lib.user_profile.php');
   $profile = new UserProfile(getLogUserId());
   $profile->init('profile', 'framework', 'index.php?r='._after_login_, 'ap');
   $profile_box  = $profile->homeUserProfile('normal', false, false);
   $photo = $profile->homePhotoProfile('normal', false, false);
   
   $credits = $widget->credits();
   $career = $widget->career();
   $subscribe_course = $widget->subscribe_course();
   $news = $widget->news();
   
   
	$ma = new Man_MiddleArea();

	$user_level = Docebo::user()->getUserLevelId();

	$query_menu = "
	SELECT mo.idModule, mo.module_name, mo.default_op, mo.mvc_path, mo.default_name, mo.token_associated, mo.module_info
	FROM ".$GLOBALS['prefix_lms']."_module AS mo
		JOIN ".$GLOBALS['prefix_lms']."_menucourse_under AS under
			ON ( mo.idModule = under.idModule)
	WHERE module_info IN ('all', 'user', 'public_admin')   and mo.idModule not in(7,34)
	ORDER BY module_info, under.sequence ";

    
   // echo $query_menu;
   //  die();

	$menu = array();
	$re_menu_voice = sql_query($query_menu);
	while(list($id_m, $module_name, $def_op, $mvc_path, $default_name, $token, $m_info) = sql_fetch_row($re_menu_voice)) {

		
        if($ma->currentCanAccessObj('mo_'.$id_m) && checkPerm($token, true, $module_name,  true)) {


            // if e-learning tab disabled, show classroom courses
            if ($module_name ==='course' && !$ma->currentCanAccessObj('tb_elearning'))
                $mvc_path = 'lms/classroom/show';
            
			$menu[$m_info][$id_m] = array(
				'index.php?'.( $mvc_path ? 'r='.$mvc_path : 'modname='.$module_name.'&amp;op='.$def_op ).'&amp;sop=unregistercourse',
				Lang::t($default_name, 'menu_over'),
				false ,
                $id_m
			);
		}
	}
	if(isset($menu['all'])) $menu_i = count($menu['all'])-1;
	else $menu_i = -1;
	$setup_menu = '';
	// Menu for the public admin
	/*if(!empty($menu['user'])) {
		$menu['all'][] = array(
			'#',
			Lang::t('_MY_AREA', 'menu_over'),
			'user'
		);
		$menu_i++;
	}*/

	// Menu for messages
      /*
	if($ma->currentCanAccessObj('mo_47')) {
		require_once($GLOBALS['where_framework'].'/lib/lib.message.php');
		$menu['all'][] = array(
			'index.php?r=lms/catalog/show',
			Lang::t('_CATALOGUE', 'menu_over').( $unread_num ? '' : '' ),
			false
		);
		$menu_i++;
	}
     */
     
	// Customer help
	if ($ma->currentCanAccessObj('mo_help')) {

		$help_email = trim( Get::sett('customer_help_email', '') );
		$can_send_emails = !empty( $help_email ) ? true : false;
		$can_admin_settings = checkRole('/framework/admin/setting/view', true);

        $strTxtHelp = Lang::t('_CUSTOMER_HELP', 'customer_help')."";
        $strHelp = "<span class='glyphicon glyphicon-question-sign'></span>";
        
		if ($can_send_emails) {

			cout(Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true), 'scripts');
			cout(Util::get_js(Get::rel_path('lms').'/modules/customer_help/customer_help.js', true), 'scripts');

			cout('<script type="text/javascript">'.
				' var CUSTOMER_HELP_AJAX_URL = "ajax.server.php?mn=customer_help&plf=lms&op=getdialog"; '.
				' var ICON_LOADING = "'.Get::tmpl_path().'images/standard/loadbar.gif"; '.
				' var LANG = new LanguageManager({'.
				'	_CONFIRM: "'.Lang::t('_CONFIRM').'",'.
				'	_UNDO: "'.Lang::t('_UNDO').'",'.
				'	_COURSE_NAME: "'.Lang::t('_COURSE_NAME', 'course').'",'.
				'	_VAL_COURSE_NAME: "'.(isset($GLOBALS['course_descriptor']) ? $GLOBALS['course_descriptor']->getValue('name') : "").'",'.
				'	_DLG_TITLE: "'.Lang::t('_CUSTOMER_HELP', 'customer_help').'",'.
				'	_LOADING: "'.Lang::t('_LOADING').'"'.
				'}); '
				.'</script>'
			, 'scripts');

			$menu['all'][] = array(
				'#',
				$strHelp,
				false
			);
			$customer_help = ++$menu_i;
			$setup_menu .= " oMenuBar.getItem($customer_help).subscribe('click', CustomerHelpShowPopUp);";

		} else {

			if ($can_admin_settings) {
				$menu['all'][] = array(
					'../appCore/index.php?r=adm/setting/show',
					'<i>('.$strHelp.': '.Lang::t('_SET', 'standard').')</i>',
					false
				);
			}

		}
	}

	// Menu for the public admin
	if($user_level == ADMIN_GROUP_PUBLICADMIN && !empty($menu['public_admin'])) {
		$menu['all'][] = array(
			'#',
			Lang::t('_PUBLIC_ADMIN_AREA', 'menu_over'),
			'public_admin'
		);
		$menu_i++;
	}

	// Link for the administration
	if($user_level == ADMIN_GROUP_GODADMIN || $user_level == ADMIN_GROUP_ADMIN ) {
		$menu['all'][] = array(
			Get::rel_path('adm'),
			Lang::t('_GO_TO_FRAMEWORK', 'menu_over'),
			false
		);
		$menu_i++;
	}

    
//** DEV: LR - creato un menu_over  responsive  attraverso bootstrap **
cout('
           
           <header class="header white-bg">

      <!-- Static navbar -->
      <nav class="navbar navbar-default">   

        <div class="container-fluid" id="lms_menu_container" >

        
          <div class="navbar-header" >

            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                      
              <span  class="glyphicon glyphicon-align-justify"></span>
            </button>

          </div>        
        
          <div id="navbar" class="navbar-collapse collapse " >   
            
                    
                ','menu_over');         
         
                
         
         cout('
         
         <div class=col-md-2>                 
             
             <!--logo start-->
            <a class="navbar-brand" href="#"><img class="left_logo" width="120" src="'. Layout::path().'/images/company_logo.png" alt="logo di sinistra"/></a>   
            </div>
         
         
            <div class="col-md-8">
         
            <ul class="nav navbar-nav" >','menu_over');
         
                foreach ($menu['all'] as $row) {
                    
                    $active = "";
                    if(strrpos($row[0], $_GET['r'])>0 || strrpos($row[0], $_GET['modname'])>0) $active = " class='active'";
                    
                    if( isset($_GET['id_cat']) && strpos($row[0], "catalog")>0)  $active = " class='active'";
         
                    
                     if(strrpos($row[0], 'appCore')>0 ){
                        cout( '<li  ><a href="'.$row[0].'" title="'.$row[1].'"><span class="glyphicon glyphicon-cog"></span></a></li> ','menu_over'); 
                     } else{
                        cout( '<li '.$active.'  ><a href="'.$row[0].'" >'.$row[1].'</a></li>','menu_over');
                     }
                            
                        if($row[2] !== false) {

                                cout('<div id="submenu_'.$id_m.'" >'
                                    .'<div class="bd"><ul class="first-of-type">', 'menu_over');
                                while(list($id_m, $s_voice) = each($menu[ $row[2] ])) {

                                    cout(''
                                        .'<a  href="'.$s_voice[0].'"">'
                                        .''.$s_voice[1].''
                                        .'</a> &nbsp; '
                                        .'', 'menu_over');
                                }
                                cout('</div>'
                                    .'</div>', 'menu_over');
                            }             
      
                }  
          
               
               cout('</ul></div>
        
        
              <div class="col-md-2">
             <ul class="nav pull-right top-menu">
                                                    
                                                    
                                                    
                                                    <li class="dropdown">

                                                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                                        <table><tr><td >'. $photo.'  &nbsp;</td><td><span class="username"> '.Docebo::user()->getUserName().'</span><b class="caret"></b></td></tr></table>
                                                         
                                                     </a>                                             
                                                        
                                                        <ul class="dropdown-menu">
           
                                                            <li>
                                                                
                                                               
                                                       
                                                                        <div class="col-md-12">
                                                                        
                                                                                  <!--
                                                                                   '.Lang::t('_WELCOME', 'profile').', <b>'.Docebo::user()->getUserName().'&nbsp;  </b>                                     
                                                                                                             
                                                                                                                     <i style="font-size:.88em">'. Format::date(date("Y-m-d H:i:s")).'</i><br />
                                                                                  -->                                   
                                                                                                                    <span class="select-language">'. Layout::change_lang().'</span>
                               
                                                                                 <div class="pull-right logout-holder">                            
                                                                                             <!--
                                                                                             <a href="index.php?r=profile/show"><img title="'.Lang::t('_PROFILE', 'profile').'"  src="'. Layout::path().'/images/chat/user2.gif" alt="'.Lang::t('_PROFILE', 'profile').'"/></a>
                                                                                             -->
                                                                                            <a href="index.php?modname=login&amp;op=logout"  ><img title="'.Lang::t('_LOGOUT', 'standard').'"  src="'. Layout::path().'/images/standard/exit.png" alt="'.Lang::t('_LOGOUT', 'standard').'"/></a>
                                                                                 </div>                                 
                               
                                                                                
                                                                                                    '.$profile_box.'
                                                                                                     <div >&nbsp;</div>   
                                                                                                    '.$subscribe_course.'
                                                                                                    '.$news.'
                                                                                                    '.$credits.'
                                                                  
                                                                                            </div>
                                                                                                        
                                                                                    
                                                                                                                                                              
                                                                                    <br>
                                                                                    <!-- 
                                                                           <div class="col-md-12"> 
                                                                                    <a href="index.php?r=profile/show" class="glyphicon glyphicon-user">'.Lang::t('_PROFILE', 'profile').' </a>
                                                                      
                                         
                                                                           </div>                                                               
                                                                            -->
                                                                        
                                               
                                                               
                                                               
                                                                        </div>
                                                              
                                           
                                                                    
                                                          
                                                                
                                                            </li>
        
                                                            
                                                        </ul>
                                                    </li>
                                                 

                                                 </ul>
                </div>                                                
                                                
                                                
                                        ','menu_over')   ; 
                                                
                                                
                                                
                                                
                              
        
        
        
                     
          cout('
                
                
          </div>','menu_over'); 
          cout('<!--/.nav-collapse -->
          
          
          
        </div><!--/.container-fluid -->    

        
      </nav>

          </header>
          <br><br><br><br>
      
','menu_over');        
    
    
    

}    


     


     
?>