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

	$ma = new Man_MiddleArea();

	$user_level = Docebo::user()->getUserLevelId();

	$query_menu = "
	SELECT mo.idModule, mo.module_name, mo.default_op, mo.mvc_path, mo.default_name, mo.token_associated, mo.module_info
	FROM ".$GLOBALS['prefix_lms']."_module AS mo
		JOIN ".$GLOBALS['prefix_lms']."_menucourse_under AS under
			ON ( mo.idModule = under.idModule)
	WHERE module_info IN ('all', 'user', 'public_admin')
	ORDER BY module_info, under.sequence ";

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
				false
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
	if($ma->currentCanAccessObj('mo_message')) {
		require_once($GLOBALS['where_framework'].'/lib/lib.message.php');
		$msg = new Man_Message();
		$unread_num = $msg->getCountUnreaded(getLogUserId(), array(), '', true);
		$menu['all'][] = array(
			'index.php?modname=message&amp;op=message&amp;sop=unregistercourse',
			Lang::t('_MESSAGES', 'menu_over').( $unread_num ? ' <b class="num_notify">'.$unread_num.'</b>' : '' ),
			false
		);
		$menu_i++;
	}

	// Customer help
	if ($ma->currentCanAccessObj('mo_help')) {

		$help_email = trim( Get::sett('customer_help_email', '') );
		$can_send_emails = !empty( $help_email ) ? true : false;
		$can_admin_settings = checkRole('/framework/admin/setting/view', true);

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
				Lang::t('_CUSTOMER_HELP', 'customer_help'),
				false
			);
			$customer_help = ++$menu_i;
			$setup_menu .= " oMenuBar.getItem($customer_help).subscribe('click', CustomerHelpShowPopUp);";

		} else {

			if ($can_admin_settings) {
				$menu['all'][] = array(
					'../appCore/index.php?r=adm/setting/show',
					'<i>('.Lang::t('_CUSTOMER_HELP', 'customer_help').': '.Lang::t('_SET', 'standard').')</i>',
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


    
      <!-- Static navbar -->
      <nav class="navbar navbar-default">   

        <div class="container-fluid" id="lms_menu_container" >

        
          <div class="navbar-header" >

            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                      
              <span  class="glyphicon glyphicon-align-justify"></span>
            </button>
            <a class="navbar-brand" href="#"><img class="left_logo" width="150" src="'. Layout::path().'/images/company_logo.png" alt="Left logo"/></a>   
          </div>        
        
          <div id="navbar" class="navbar-collapse collapse" >   
            
                    
                ','menu_over');         
         
         
         
         cout('<ul class="pager" ><br><br><br><br>','menu_over');
         
            foreach ($menu['all'] as $row) {
                cout( '<li ><a href="'.$row[0].'" >'.$row[1].'</a></li> &nbsp; ','menu_over');

                
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
          
        
        
        
        cout('         <ul class="nav navbar-nav navbar-right">
                                                    <li class="dropdown">
                                                    
                                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">'.Docebo::user()->getUserName().'<b class="caret"></b></a>
                                                        
                                                        
                                                        <ul class="dropdown-menu">
                                                           
                                                           
                                                           
                                                           
                                                            <li>
                                                                
                                                               
                                                       
                                                                        <div class="col-md-12">
                                    
                                                                                   '.Lang::t('_WELCOME', 'profile').', <b>'.Docebo::user()->getUserName().'&nbsp; &nbsp; &nbsp; </b>                                     
                                                                                                               <br>
                                                                                                                    '. Format::date(date("Y-m-d H:i:s")).'<br />
                                                                                                                    <span class="select-language">'. Layout::change_lang().'</span>
                                                                                                                                                                      
                                                                                        
                                                                                                
                                                                                            <div class="divider">  _____________________________________
                                                                                            </div>
                                                                               
                                                                               
                                                                                           <div>
                                                                                                    <a href="index.php?r=profile/show" class="btn btn-primary btn-sm pull-left">'.Lang::t('_PROFILE', 'profile').' </a>
                                                                                      
                                                                                             
                                                                                           
                                                                                                    <a href="index.php?modname=login&amp;op=logout" class="btn btn-primary btn-sm pull-right">'.Lang::t('_LOGOUT', 'standard').'</a>
                                                                                           </div>                                                               
                                                               
                                                                        </div>
                                                              
                                           
                                                                    
                                                          
                                                                
                                                            </li>

                                                            
                                                        </ul>
                                                    </li>
                                                </ul>        

                                        ','menu_over')   ; 
                                                
                                                
                                                
                                                
                                                    
        
        
        
                     
          cout('</ul>','menu_over'); 
                                             
          
                                 

         
         
         
         
          cout('</div><!--/.nav-collapse -->
        </div><!--/.container-fluid -->    
        

        
      </nav>

      
      
      

      
      
      
      
      
      
      
','menu_over');        
    
         


       

       
    
    
}    
     
?>