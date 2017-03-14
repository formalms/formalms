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

if(Docebo::user()->isLoggedIn()) {
	
	$lang 	=& DoceboLanguage::createInstance('menu', 'framework');
	$p_man 	=& PlatformManager::createInstance();
	$platforms 	= $p_man->getPlatformList();

    
	cout('<li><a href="#main_menu">'.$lang->def('_BLIND_MAIN_MENU').'</a></li>', 'blind_navigation');
//******************************
    

    
    
$strPanelUser = "<div class='dropdown-grid-wrapper' role='menu'>";
$strPanelUser = $strPanelUser ."<table width=100% class='hidden-sm hidden-md'>
                                <tr><td>
                                    <b><label class='hidden-sm hidden-md'>".Lang::t('_WELCOME', 'profile').", ".Docebo::user()->getUserName()."</b></label><br /><br>";
$strPanelUser = "".$strPanelUser.Format::date(date("Y-m-d H:i:s"))."";
$strPanelUser = $strPanelUser." </td><td>" ;

$strPanelUser = $strPanelUser."<a   href='index.php'>                
                                    <span class='glyphicon glyphicon-th'>&nbsp;".Lang::t('_DASHBOARD', 'dashboard')."</span>
                                </a>                                 
                                <br> <br> ";  

$strPanelUser = $strPanelUser."<span class='glyphicon glyphicon-globe' > <span class='select-language'>".Layout::change_lang()."</span> </span> 
                                  <br><br>
                                    <a class='area_user' href='index.php?r=lms/profile/show'>
                                            <span class='glyphicon glyphicon-user'>&nbsp;".Lang::t('_PROFILE', 'profile')."</span>
                                        </a>    <br><br>
                                    ";              
               
$strPanelUser = $strPanelUser."<a id='logout'  href='index.php?modname=login&amp;op=logout'>                
                                    <span class='glyphicon glyphicon-off'>&nbsp;".Lang::t('_LOGOUT', 'standard')."</span>
                                </a>                                 
                                <br>
                                </td></tr></table>";
$strPanelUser = $strPanelUser."</div>";              
              
           
 cout('

        <style>
  
.container {
    position: fixed;
    left: 0;
    right: 0;
    z-index: 1002;
    width: 100%;
    height: 50px;
    background: #ffffff;
    border: 0px solid #CCC;
    margin: 0px auto;
    padding: 0px 0% 0px 0%;
}  
  
</style>


    <div class="container">
        <nav class="navbar navbar-inverse no-border-radius" id="main_navbar" role="navigation">
            <div class="container-fluid">
                
          <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.html"><img width=100px class="left_logo" src="'.Layout::path().'images/company_logo.png" alt="Left logo" /></a>
                </div>
          
          
          
          
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="navbar-collapse-1">
          
          
                      <ul class="nav navbar-nav navbar-right">
                    
                        
                        <!-- divider -->
                        <li class="divider"></li>
                        
              
                     <!-- account -->
                            <li class="dropdown-grid">
                                <a data-toggle="dropdown" href="javascript:;" class="dropdown-toggle"><i class="fa fa-lock"></i>&nbsp;<span class="hidden-sm">Account</span><span class="caret"></span></a>
                                <div class="dropdown-grid-wrapper" role="menu">
                                    <ul class="dropdown-menu">
                                        <li>
                                            '.$strPanelUser.'
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>          
          
          
          <ul class="nav navbar-nav navbar-left">
                    
                        
                        <!-- divider -->
                        <li class="divider"></li>
          
                    <!-- text -->
                    <p class="navbar-text navbar-left">
                    
                    <a href="'.$GLOBALS['where_lms_relative'].'"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>'.
                                ' <span class="admmenu_goto"><b>'.$lang->def('_JUMP_TO_PLATFORM', 'menu', 'framework')
                                .' '.$lang->def('_LMS', 'platform').'</b></span>'.
                            '</a>                  
                    </p>
                        
                    </ul>        
                          
                    <ul class="nav navbar-nav navbar-left">
                        <!-- divider -->
                        <li class="divider"></li>  ','menu_over');
              


       //** ciclo su tuttii platform **

       
       

       

    foreach($platforms as $p_code => $p_name) {
        
        if($p_code=='lms'){
            $menu_man =& $p_man->getPlatofmMenuInstance($p_code);
        }else{
            $menu_man =& $p_man->getPlatofmMenuInstanceFramework($p_code);          
       }
       
  
       $strHeader = $lang->def('_FIRST_LINE_'.$p_code);
       
       if($p_code == 'menu_user') {$strHeader = $lang->def('_USER_MANAGMENT', 'menu', 'framework'); $strIco = '<i class="fa fa-users fa-fw"></i>';}
       if($p_code == 'menu_elearning') {$strHeader = $lang->def('_FIRST_LINE_lms', 'menu', 'framework'); $strIco = '<span class="glyphicon glyphicon glyphicon-education" aria-hidden="true"></span>';}
       if($p_code == 'menu_content') {$strHeader = $lang->def('_CONTENTS', 'standard', 'framework')   ;  $strIco = '<i class="fa fa-clipboard fa-fw"></i>';}
       if($p_code == 'menu_report') {$strHeader = $lang->def('_REPORT', 'standard', 'framework'); $strIco = '<i class="fa fa-bar-chart-o fa-fw"></i>';}
       if($p_code == 'menu_config') {$strHeader = $lang->def('_CONFIGURATION', 'menu', 'framework');  $strIco = '<i class="fa fa-cogs fa-fw"></i>';}
       

        if($menu_man !== false) {
            
            $main_voice = $menu_man->getLevelOne();

            if(!empty($main_voice)) {       
       
       
      cout(' <!-- dropdown default -->
                        <li class="dropdown-short">
                            <a data-toggle="dropdown" href="javascript:;"  class="dropdown-toggle" aria-expanded="true">
                           <span class="hidden-sm">'.$strIco.' '.$strHeader.'</span><span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <!-- li class="dropdown-header">'.$strHeader.'</li-->
                                
                                <!-- li class="divider"></li-->','menu_over');

            
                          foreach($main_voice as $id_m => $v_main) {

                                            $under_voice = $menu_man->getLevelTwo($id_m);
  
                                            
                                            if($v_main['collapse']==true){
                                                foreach($under_voice as $id_m => $voice) {
                                            
                                                   cout('<li class="dropdown-right-onhover no-fix">
                                                                <!-- Menu item with submenu -->
                                                                <a href="'.Util::str_replace_once('&', '&amp;',  $voice['link']).'" >'.$voice['name'].'</a></li>','menu_over');  
                                                            
                                                }               
                                            }       
                                                
                                              
                                                
                                                
                                                
                                           if(!isset($v_main['collapse']) || $v_main['collapse'] === false) {         
                                                            
                                         cout('<li class="dropdown-right-onhover no-fix">
                                                                    <!-- Menu item with submenu -->
                                                                    <a href="javascript:;" data-toggle="collapse" class="dropdown-toggle collapsed">'.$v_main['name'].'</a>

                                                                    <!-- start submenu -->
                                                                    <ul class="dropdown-menu collapse" >
                                                                        <!--li class="dropdown-header">'.$v_main['name'].'</li-->
                                                                        <!--li class="divider"></li-->','menu_over');
                                            
                                            //** sottomenu ** 
                                            foreach($under_voice as $id_m => $voice) {         
                                         cout('   <li><a href="'.Util::str_replace_once('&', '&amp;',  $voice['link']).'" ><span class="desc">'.$voice['name'].'</span></a></li>
                                                   ','menu_over');                    
                                            }                          
                                                                    
                                          cout(' </ul>
                                                    <!-- end submenu -->           
                                                              </li>
                                                                
                                                
                                                        <!-- divider -->
                                                        
                                                      ','menu_over');  
                                      
                                                          
                                               }       
                                                
                          }                    
                 
                 

                      
               
               cout('   </ul>','menu_over') ;      
                      
                        
            }
        }
    }
                        
//******** FINE CICLO SULLE PLATFORM ***********                        
                        
                        
                  
     cout('         
     
     
                     </ul>
                </div>
            </div>
        </nav>
    </div>
        
          <br><br><br><br>
        
   
','menu_over')  ; 



    
    
    
}

?>