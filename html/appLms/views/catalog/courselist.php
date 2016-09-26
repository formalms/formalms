 
 <script type="text/javascript">
    YAHOO.util.Event.onDOMReady(function() {
        initialize("<?php echo Lang::t('_UNDO', 'standard'); ?>");
    });
</script>  

<script type="text/javascript">
    var lb = new LightBox();
    lb.back_url = 'index.php?r=lms/catalog/show&sop=unregistercourse';
    
    var Config = {};
    Config.langs = {_CLOSE: '<?php echo Lang::t('_CLOSE', 'standard'); ?>'};
    lb.init(Config);  
</script>


  <?php


       require_once(_lms_ . '/lib/lib.middlearea.php');
       $ma = new Man_MiddleArea();
       
                  echo  '<div class="col-md-12">';


                $category = $this->model->getMinorCategory($std_link, true);
  
           ?>
           
           
<div class="main">
    <div id="cbp-vm" class="cbp-vm-switcher cbp-vm-view-grid">
        <div class="cbp-vm-options">
            <a href="#"  data-view="cbp-vm-view-grid"><span class='glyphicon glyphicon-th'></span></a> 
            <a href="#"  data-view="cbp-vm-view-list"><span class='glyphicon glyphicon-align-justify'></span></a>
        </div>
                 
            <ul >
            
    <?php
//	    echo   $html
  
  

        $html = '';
        $path_course = $GLOBALS['where_files_relative'].'/appLms/'.Get::sett('pathcourse').'/';

        $smodel = new CatalogLms();
        
        while($row = sql_fetch_assoc($result))
        {
            $action = '';

            if($row['course_type'] === 'classroom')
            {
                $additional_info = '';

                $classrooms = $smodel->classroom_man->getCourseDate($row['idCourse'], false);

                $action = '<div class="catalog_action" id="action_'.$row['idCourse'].'">';
                if(count($classrooms) == 0)
                    $action .= '<p class="cannot_subscribe">'.Lang::t('_NO_EDITIONS', 'catalogue').'</p>';
                else
                {
                    //Controllo che l'utente non sia iscritto a tutte le edizioni future
                    $date_id = array();

                                        $user_classroom = $smodel->classroom_man->getUserDateForCourse(Docebo::user()->getIdSt(), $row['idCourse']);
                    $classroom_full = $smodel->classroom_man->getFullDateForCourse($row['idCourse']);
                    $classroom_not_confirmed = $smodel->classroom_man->getNotConfirmetDateForCourse($row['idCourse']);

                    $overbooking_classroom = $smodel->classroom_man->getOverbookingDateForCourse($row['idCourse']);

                    foreach($classrooms as $classroom_info)
                        $date_id[] = $classroom_info['id_date'];

                    reset($classrooms);
                    // for all the dates we will remove the one in which the user is subscribed and the classroom not confirmed
                    $control = array_diff($date_id, $user_classroom, $classroom_not_confirmed);

                    if(count($control) == 0)
                    {
                        if (!empty($overbooking_classroom)) {
                            $_text = ($row['selling'] == 0
                                ? Lang::t('_SUBSCRIBE', 'catalogue')
                                : Lang::t('_ADD_TO_CART', 'catalogue'));
                            $action .= '<a href="javascript:;" onclick="courseSelection(\''.$row['idCourse'].'\', \''.($row['selling'] == 0 ? '0' : '1').'\')" '
                                .' title="'.$_text.'"><p class="can_subscribe">'.$_text.'<br />'
                                .'('.Lang::t('_SUBSCRIBE_WITH_OVERBOOKING', 'catalogue').': '.count($overbooking_classroom).')</p>'
                                .'</a>';
                        } else {
                            //$action .= '<p class="cannot_subscribe">'.Lang::t('_NO_EDITIONS', 'catalogue').'</p>';
/* FORMA - INSERITO BOTTONE ENTRA
                            if (count($user_classroom) > 0) {
                                $action .= '<p class="subscribed">'.Lang::t('_USER_STATUS_SUBS', 'catalogue').'</p>';
                            } else {
                                $action .= '<p class="cannot_subscribe">'.Lang::t('_NO_AVAILABLE_EDITIONS', 'catalogue').'</p>';
                            }
*/
                            if (count($user_classroom) > 0) {
                                $action .= '<a href="index.php?modname=course&op=aula&idCourse='.$row['idCourse'].' "'
                                    .' title="'.$_text.'"><p class="subscribed">'
                                    .Lang::t('_USER_STATUS_ENTER', 'catalogue').'</p>'
                                    .'</a>';
                            } else {
                                $action .= '<p class="cannot_subscribe">'.Lang::t('_NO_AVAILABLE_EDITIONS', 'catalogue').'</p>';
                            }
                        }            
                        
                    }
                    else
                    {
                        if($row['selling'] == 0)

                           switch ($row['subscribe_method']) {
                                case 2:
                                    // free
                                    $action .= '<a href="javascript:;" onclick="courseSelection(\''.$row['idCourse'].'\', \'0\')" title="'.Lang::t('_SUBSCRIBE', 'catalogue').'"><p class="can_subscribe">'.Lang::t('_SUBSCRIBE', 'catalogue').'</p></a>';                                    
                                break;
                                case 1:
                                    // moderate
                                     $action .=  '<a href="javascript:;" onclick="courseSelection(\''.$row['idCourse'].'\', \'0\')" title="'.Lang::t('_SUBSCRIBE', 'catalogue').'"><p class="can_subscribe">'.Lang::t('_COURSE_S_MODERATE', 'catalogue').'</p></a>';
                                break;
                                case 0:
                                    // only admin
                                    $action .= '<p class="cannot_subscribe">'.Lang::t('_COURSE_S_GODADMIN', 'catalogue').'</p>';
                                break; 
                            }                               
                            
                            
                        else
                        {
                            $classroom_in_chart = array();

                            if(isset($_SESSION['lms_cart'][$row['idCourse']]['classroom']))
                                $classroom_in_chart = $_SESSION['lms_cart'][$row['idCourse']]['classroom'];

                            $control = array_diff($control, $classroom_in_chart);

                            if(count($control) == 0)
                                $action .= '<p class="subscribed">'.Lang::t('_ALL_EDITION_BUYED', 'catalogue').'</p>';
                            else
                                $action .= '<a href="javascript:;" onclick="courseSelection(\''.$row['idCourse'].'\', \'1\')" title="'.Lang::t('_ADD_TO_CART', 'catalogue').'"><p class="can_subscribe">'.Lang::t('_ADD_TO_CART', 'catalogue').'</p></a>';
                        }
                    }
                }
                $action .= '</div>';
            }
            elseif($row['course_edition'] == 1)
            {
                $additional_info = '';

                $editions = $smodel->edition_man->getEditionAvailableForCourse(Docebo::user()->getIdSt(), $row['idCourse']);

                $action = '<div class="catalog_action" id="action_'.$row['idCourse'].'">';
                if(count($editions) == 0)
                    $action .= '<p class="cannot_subscribe">'.Lang::t('_NO_EDITIONS', 'catalogue').'</p>';
                else
                {
                    if($row['selling'] == 0)
                        $action .= '<a href="javascript:;" onclick="courseSelection(\''.$row['idCourse'].'\', \'0\')" title="'.Lang::t('_SUBSCRIBE', 'catalogue').'"><p class="can_subscribe">'.Lang::t('_SUBSCRIBE', 'catalogue').'</p></a>';
                    else
                    {
                        $edition_in_chart = array();

                        if(isset($_SESSION['lms_cart'][$row['idCourse']]['editions']))
                            $edition_in_chart = $_SESSION['lms_cart'][$row['idCourse']]['editions'];

                        $editions = array_diff($editions, $edition_in_chart);

                        if(count($editions) == 0)
                            $action .= '<p class="subscribed">'.Lang::t('_ALL_EDITION_BUYED', 'catalogue').'</p>';
                        else
                            $action .= '<a href="javascript:;" onclick="courseSelection(\''.$row['idCourse'].'\', \'1\')" title="'.Lang::t('_ADD_TO_CART', 'catalogue').'"><p class="can_subscribe">'.Lang::t('_ADD_TO_CART', 'catalogue').'</p></a>';
                    }
                }
                $action .= '</div>';
            }
            else
            {
                // standard elearning course without editions
                /*
                $query =    "SELECT COUNT(*)"
                            ." FROM %lms_courseuser"
                            ." WHERE idCourse = '".$row['idCourse']."'";
                            
                list($enrolled) = sql_fetch_row(sql_query($query));
                 */
                 
                 $enrolled = $smodel->enrolledStudent($row['idCourse']);
                 
                $row['enrolled'] = $enrolled;
                $row['create_date'] = Format::date($row['create_date'], 'date');
                $additional_info =    '<p class="course_support_info">'.Lang::t('_COURSE_INTRO', 'course', array(
                                        '[course_type]'        => $row['course_type'],
                                        '[create_date]'        => $row['create_date'],
                                        '[enrolled]'        => $row['enrolled'],
                                        '[course_status]'    => Lang::t($this->cstatus[$row['status']], 'course')))
                                    .'</p>';

                                    
                 // da mettere nel model 
                /*
                $query =    "SELECT status, waiting, level"
                            ." FROM %lms_courseuser"
                            ." WHERE idCourse = ".$row['idCourse']
                            ." AND idUser = ".Docebo::user()->getIdSt();
                $result_control = sql_query($query);
                */
                $result_control = $smodel->getInfoEnroll($row['idCourse'],Docebo::user()->getIdSt());
                
                
                $action = '<div class="catalog_action" id="action_'.$row['idCourse'].'">';
                if(sql_num_rows($result_control) > 0)
                {
                    // the user is enrolled in some way
                    list($status, $waiting, $level) = sql_fetch_row($result_control);

                    if($waiting)
                        $action .= '<p class="subscribed">'.Lang::t('_WAITING', 'catalogue').'</p>';
                    else {
                        
              // #1995 Grifo multimedia (da togliere????????)                               
              // da mettere nel model           
              /*
              $query_lo =    "select org.idOrg, org.idCourse, org.objectType from (SELECT o.idOrg, o.idCourse, o.objectType 
              FROM %lms_organization AS o WHERE o.objectType != '' AND o.idCourse IN (".$row['idCourse'].") ORDER BY o.path) as org 
              GROUP BY org.idCourse";
              $result_lo = sql_query($query_lo);            
              */   
              $result_lo = $smodel->getInfoLO($row['idCourse']);
                 
                 list($id_org, $id_course, $obj_type) = sql_fetch_row($result_lo);
               $str_rel = "";
           
               if($obj_type == "scormorg" && $level<=3 && $row['direct_play']==1 ) $str_rel = " rel='lightbox'";
                  $action .= '<a href="index.php?modname=course&op=aula&idCourse='.$row['idCourse'].' "'
                    .' title="'.$_text.'"   '.$str_rel.'><p class="subscribed">'
                    .Lang::t('_USER_STATUS_ENTER', 'catalogue').'</p>'
                    .'</a><br>';
                                
                    }
                        
                }
                else
                {
                    // course is not enrolled
                    $course_full = false;

                    if($row['max_num_subscribe'] != 0)
                    {
                        // DA INSERIRE NEL MODEL
                        /*
                        $query = "SELECT COUNT(*)"
                            ." FROM %lms_courseuser"
                            ." WHERE idCourse = ".$row['idCourse'];
                        list($control) = sql_fetch_row(sql_query($query));
                        */
                        $control = $smodel->enrolledStudent($row['idCourse']);
                        
                        if($control >= $row['max_num_subscribe'])
                        {
                            // the course have reached the maximum number of subscription
                            $action .= '<p class="cannot_subscribe">'.Lang::t('_MAX_NUM_SUBSCRIBE', 'catalogue').' - '.$row['max_num_subscribe'].'</p>';
                            $course_full = true;
                        }
                    }

                    if(!$course_full)
                    {

                        if($row['selling'] == 0) {

                            switch ($row['subscribe_method']) {
                                case 2:
                                    // free
                                    $action .= '<a href="javascript:;" onclick="subscriptionPopUp(\''.$row['idCourse'].'\', \'0\', \'0\', \'0\')" title="'.Lang::t('_SUBSCRIBE', 'catalogue').'"><p class="can_subscribe">'.Lang::t('_SUBSCRIBE', 'catalogue').'</p></a>';
                                   // $action .= '<a class="cbp-vm-add" href="javascript:;" onclick="subscriptionPopUp(\''.$row['idCourse'].'\', \'0\', \'0\', \'0\')" title="'.Lang::t('_SUBSCRIBE', 'catalogue').'">'.Lang::t('_SUBSCRIBE', 'catalogue').'</a>';
                                break;
                                case 1:
                                    // moderate
                                    $action .= '<a href="javascript:;" onclick="subscriptionPopUp(\''.$row['idCourse'].'\', \'0\', \'0\', \'0\')" title="'.Lang::t('_COURSE_S_MODERATE', 'course').'"><p class="can_subscribe">'.Lang::t('_COURSE_S_MODERATE', 'catalogue').'</p></a>';
                                break;
                                case 0:
                                    // only admin
                                    $action .= '<p class="cannot_subscribe">'.Lang::t('_COURSE_S_GODADMIN', 'catalogue').'</p>';
                                break;
                            }


                        } else {
                            $date_in_chart = array();

                            if(isset($_SESSION['lms_cart'][$row['idCourse']]))
                                $action .= '<p class="subscribed">'.Lang::t('_COURSE_IN_CART', 'catalogue').'</p>';
                            else
                                $action .= '<a href="javascript:;" onclick="subscriptionPopUp(\''.$row['idCourse'].'\', \'0\', \'0\', \'1\')" title="'.Lang::t('_ADD_TO_CART', 'catalogue').'"><p class="can_subscribe">'.Lang::t('_ADD_TO_CART', 'catalogue').'</p></a>';
                        }
                    }
                }
                $action .= '</div>';
            }

        $arr_cat = $smodel->getMinorCategoryTree((int)$row['idCategory']);
        
        
        if($row['course_type']=="elearning") $img_type =  "<img src='".Layout::path()."images/lobject/scormorg.gif'>";
        if($row['course_type']=="classroom") $img_type = "<img src='".Layout::path()."images/course/classroom-cal.png'>";
        // start - end 
        
        $str_start_end ="";
        if($row['date_begin']!="0000-00-00"){
            $str_start_end = Lang::t('_SUBSCRIPTION_DATE_BEGIN', 'course'). " <b>". $row['date_begin'].'</b>  '.Lang::t('_SUBSCRIPTION_DATE_END', 'course').' <b>'.$row['date_end']."</b>";
        }
        
        
        
        
    // BUG TRACKER - LR #5669
        $data_inizio = $row['date_begin'];
        $data_end = $row['date_end'];
        
        $str_lock_start = "";
        $str_lock_end = "";
        
        if($row['hour_begin'] != "-1") $str_h_begin = $row['hour_begin']; 
        if($row['hour_end'] != "-1") $str_h_end = $row['hour_end']; 
        
        $can_enter_star = true;
        $can_enter_end = true  ;  
        if($data_inizio != "0000-00-00") $str_lock_start = "<b><i style='font-size:.68em'>".Lang::t('_COURSE_BEGIN', 'certificate')."</b>: ".Format::date($data_inizio, 'date')." ".$str_h_begin."</i>" ;
        if($data_end  != "0000-00-00") $str_lock_end= "<br><b><i style='font-size:.68em'>".Lang::t('_COURSE_END', 'certificate')."</b>: ".Format::date($data_end, 'date')." ".$str_h_end."</i>";

        if($data_inizio != "0000-00-00" && $data_inizio > date('Y-m-d')  ) $can_enter_star = false;
        if($data_end != "0000-00-00" &&  $data_end      < date('Y-m-d') ) $can_enter_end = false;

        if($data_inizio != "0000-00-00"  || $data_fine != "0000-00-00" ) $str_can_enter = ($can_enter_star && $can_enter_end);
        if($data_inizio == "0000-00-00"  && $data_fine == "0000-00-00" ) $str_can_enter = true;        
                
        
        
        $html .= '
                  
                        <li>
                                        <div class="cbp-vm-image" >
                                                            '
                                                            .($row['use_logo_in_courselist'] && $row['img_course'] ? '<div class="logo_container"><img class="group list-group-image" src="'.$path_course.$row['img_course'].'" alt="'.Util::purge($row['name']).'" /></div>' : '')
                                                            .($row['use_logo_in_courselist'] && !$row['img_course'] ? '<div class="logo_container"><img class="group list-group-image" src="'.Get::tmpl_path().'images/course/course_nologo.png'.'" alt="'.Util::purge($row['name']).'" /></div>' : '')                         
                                                            .
                                                           '
                                                           
                                                    <!--         
                                                    '.($row['code'] ? '<i style="font-size:.68em">['.$row['code'].']</i>' : '&nbsp;').'                  
                                                     -->
                                                    
                                                    
                                        </div>
                                        '.$img_type.'
                                    
                                        <h3 class="cbp-vm-title">'.$row['name'].'<br><br> <font style="color:#FFFFFF;background-color:#C84000;">'.$arr_cat[$row['idCategory']]['name'].'</font> </h3> 
                                        '.$str_lock_start.' 
                                        '.$str_lock_end.'  
                 
                                         <!-- DATE START - DATE END  -->
                                         
                                         
                                          
                                        <div class="cbp-vm-details">  &nbsp
                                                     '.$row['description'].' 
                                        '.
                                            ($row["course_demo"] ? '<a   href="index.php?r=catalog/downloadDemoMaterial&amp;course_id='.$row['idCourse'].'" class="ico-wt-sprite subs_download"><span>'.Lang::t('_COURSE_DEMO', 'course').'</span></a>' : '')
                                        .'                                       
                                                         
                                        <br>
                                        
                                                            
                                       
                                       
                                        </div>
                                            
                                         
                                          <div class="cbp-vm-add">                                   
                                                <table   border=0 align=center  >
                                                      <tr><td>  <br>';
                                                      
                                                      
      if($str_can_enter==true &&  $row['status']!=CST_CONCLUDED)  $html .=    $action;
          if($str_can_enter==false || $row['status']==CST_CONCLUDED)  $html .= "<img class='no_traform' src='". Get::tmpl_path().'images/standard/locked.png'."'>" ;   
              
                                          
             $html .= ' </td></tr>
                             </table>     
                     </div>                             
                        </li>';
                                                              
                                                      
                        
        }

        if(sql_num_rows($result) <= 0)
            $html = '<p>'.Lang::t('_NO_CONTENT', 'standard').'</p>';

        echo $html;  
  
        
        
     ?>      
          </ul>
         
            
    </div>
</div><!-- /main -->


</div>


<script type="text/javascript">
    var lb = new LightBox();
    lb.back_url = 'index.php?r=lms/catalog/show&sop=unregistercourse';
    
    var Config = {};
    Config.langs = {_CLOSE: '<?php echo Lang::t('_CLOSE', 'standard'); ?>'};
    lb.init(Config);  
</script>


<script type="text/javascript">

/* Modernizr 2.6.2 (Custom Build) | MIT & BSD
 * Build: http://modernizr.com/download/#-generatedcontent-touch-shiv-cssclasses-teststyles-prefixes-load
 */
;window.Modernizr=function(a,b,c){function x(a){j.cssText=a}function y(a,b){return x(n.join(a+";")+(b||""))}function z(a,b){return typeof a===b}function A(a,b){return!!~(""+a).indexOf(b)}function B(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:z(f,"function")?f.bind(d||b):f}return!1}var d="2.6.2",e={},f=!0,g=b.documentElement,h="modernizr",i=b.createElement(h),j=i.style,k,l=":)",m={}.toString,n=" -webkit- -moz- -o- -ms- ".split(" "),o={},p={},q={},r=[],s=r.slice,t,u=function(a,c,d,e){var f,i,j,k,l=b.createElement("div"),m=b.body,n=m||b.createElement("body");if(parseInt(d,10))while(d--)j=b.createElement("div"),j.id=e?e[d]:h+(d+1),l.appendChild(j);return f=["&#173;",'<style id="s',h,'">',a,"</style>"].join(""),l.id=h,(m?l:n).innerHTML+=f,n.appendChild(l),m||(n.style.background="",n.style.overflow="hidden",k=g.style.overflow,g.style.overflow="hidden",g.appendChild(n)),i=c(l,a),m?l.parentNode.removeChild(l):(n.parentNode.removeChild(n),g.style.overflow=k),!!i},v={}.hasOwnProperty,w;!z(v,"undefined")&&!z(v.call,"undefined")?w=function(a,b){return v.call(a,b)}:w=function(a,b){return b in a&&z(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=s.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(s.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(s.call(arguments)))};return e}),o.touch=function(){var c;return"ontouchstart"in a||a.DocumentTouch&&b instanceof DocumentTouch?c=!0:u(["@media (",n.join("touch-enabled),("),h,")","{#modernizr{top:9px;position:absolute}}"].join(""),function(a){c=a.offsetTop===9}),c},o.generatedcontent=function(){var a;return u(["#",h,"{font:0/0 a}#",h,':after{content:"',l,'";visibility:hidden;font:3px/1 a}'].join(""),function(b){a=b.offsetHeight>=3}),a};for(var C in o)w(o,C)&&(t=C.toLowerCase(),e[t]=o[C](),r.push((e[t]?"":"no-")+t));return e.addTest=function(a,b){if(typeof a=="object")for(var d in a)w(a,d)&&e.addTest(d,a[d]);else{a=a.toLowerCase();if(e[a]!==c)return e;b=typeof b=="function"?b():b,typeof f!="undefined"&&f&&(g.className+=" "+(b?"":"no-")+a),e[a]=b}return e},x(""),i=k=null,function(a,b){function k(a,b){var c=a.createElement("p"),d=a.getElementsByTagName("head")[0]||a.documentElement;return c.innerHTML="x<style>"+b+"</style>",d.insertBefore(c.lastChild,d.firstChild)}function l(){var a=r.elements;return typeof a=="string"?a.split(" "):a}function m(a){var b=i[a[g]];return b||(b={},h++,a[g]=h,i[h]=b),b}function n(a,c,f){c||(c=b);if(j)return c.createElement(a);f||(f=m(c));var g;return f.cache[a]?g=f.cache[a].cloneNode():e.test(a)?g=(f.cache[a]=f.createElem(a)).cloneNode():g=f.createElem(a),g.canHaveChildren&&!d.test(a)?f.frag.appendChild(g):g}function o(a,c){a||(a=b);if(j)return a.createDocumentFragment();c=c||m(a);var d=c.frag.cloneNode(),e=0,f=l(),g=f.length;for(;e<g;e++)d.createElement(f[e]);return d}function p(a,b){b.cache||(b.cache={},b.createElem=a.createElement,b.createFrag=a.createDocumentFragment,b.frag=b.createFrag()),a.createElement=function(c){return r.shivMethods?n(c,a,b):b.createElem(c)},a.createDocumentFragment=Function("h,f","return function(){var n=f.cloneNode(),c=n.createElement;h.shivMethods&&("+l().join().replace(/\w+/g,function(a){return b.createElem(a),b.frag.createElement(a),'c("'+a+'")'})+");return n}")(r,b.frag)}function q(a){a||(a=b);var c=m(a);return r.shivCSS&&!f&&!c.hasCSS&&(c.hasCSS=!!k(a,"article,aside,figcaption,figure,footer,header,hgroup,nav,section{display:block}mark{background:#FF0;color:#000}")),j||p(a,c),a}var c=a.html5||{},d=/^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i,e=/^(?:a|b|code|div|fieldset|h1|h2|h3|h4|h5|h6|i|label|li|ol|p|q|span|strong|style|table|tbody|td|th|tr|ul)$/i,f,g="_html5shiv",h=0,i={},j;(function(){try{var a=b.createElement("a");a.innerHTML="<xyz></xyz>",f="hidden"in a,j=a.childNodes.length==1||function(){b.createElement("a");var a=b.createDocumentFragment();return typeof a.cloneNode=="undefined"||typeof a.createDocumentFragment=="undefined"||typeof a.createElement=="undefined"}()}catch(c){f=!0,j=!0}})();var r={elements:c.elements||"abbr article aside audio bdi canvas data datalist details figcaption figure footer header hgroup mark meter nav output progress section summary time video",shivCSS:c.shivCSS!==!1,supportsUnknownElements:j,shivMethods:c.shivMethods!==!1,type:"default",shivDocument:q,createElement:n,createDocumentFragment:o};a.html5=r,q(b)}(this,b),e._version=d,e._prefixes=n,e.testStyles=u,g.className=g.className.replace(/(^|\s)no-js(\s|$)/,"$1$2")+(f?" js "+r.join(" "):""),e}(this,this.document),function(a,b,c){function d(a){return"[object Function]"==o.call(a)}function e(a){return"string"==typeof a}function f(){}function g(a){return!a||"loaded"==a||"complete"==a||"uninitialized"==a}function h(){var a=p.shift();q=1,a?a.t?m(function(){("c"==a.t?B.injectCss:B.injectJs)(a.s,0,a.a,a.x,a.e,1)},0):(a(),h()):q=0}function i(a,c,d,e,f,i,j){function k(b){if(!o&&g(l.readyState)&&(u.r=o=1,!q&&h(),l.onload=l.onreadystatechange=null,b)){"img"!=a&&m(function(){t.removeChild(l)},50);for(var d in y[c])y[c].hasOwnProperty(d)&&y[c][d].onload()}}var j=j||B.errorTimeout,l=b.createElement(a),o=0,r=0,u={t:d,s:c,e:f,a:i,x:j};1===y[c]&&(r=1,y[c]=[]),"object"==a?l.data=c:(l.src=c,l.type=a),l.width=l.height="0",l.onerror=l.onload=l.onreadystatechange=function(){k.call(this,r)},p.splice(e,0,u),"img"!=a&&(r||2===y[c]?(t.insertBefore(l,s?null:n),m(k,j)):y[c].push(l))}function j(a,b,c,d,f){return q=0,b=b||"j",e(a)?i("c"==b?v:u,a,b,this.i++,c,d,f):(p.splice(this.i++,0,a),1==p.length&&h()),this}function k(){var a=B;return a.loader={load:j,i:0},a}var l=b.documentElement,m=a.setTimeout,n=b.getElementsByTagName("script")[0],o={}.toString,p=[],q=0,r="MozAppearance"in l.style,s=r&&!!b.createRange().compareNode,t=s?l:n.parentNode,l=a.opera&&"[object Opera]"==o.call(a.opera),l=!!b.attachEvent&&!l,u=r?"object":l?"script":"img",v=l?"script":u,w=Array.isArray||function(a){return"[object Array]"==o.call(a)},x=[],y={},z={timeout:function(a,b){return b.length&&(a.timeout=b[0]),a}},A,B;B=function(a){function b(a){var a=a.split("!"),b=x.length,c=a.pop(),d=a.length,c={url:c,origUrl:c,prefixes:a},e,f,g;for(f=0;f<d;f++)g=a[f].split("="),(e=z[g.shift()])&&(c=e(c,g));for(f=0;f<b;f++)c=x[f](c);return c}function g(a,e,f,g,h){var i=b(a),j=i.autoCallback;i.url.split(".").pop().split("?").shift(),i.bypass||(e&&(e=d(e)?e:e[a]||e[g]||e[a.split("/").pop().split("?")[0]]),i.instead?i.instead(a,e,f,g,h):(y[i.url]?i.noexec=!0:y[i.url]=1,f.load(i.url,i.forceCSS||!i.forceJS&&"css"==i.url.split(".").pop().split("?").shift()?"c":c,i.noexec,i.attrs,i.timeout),(d(e)||d(j))&&f.load(function(){k(),e&&e(i.origUrl,h,g),j&&j(i.origUrl,h,g),y[i.url]=2})))}function h(a,b){function c(a,c){if(a){if(e(a))c||(j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}),g(a,j,b,0,h);else if(Object(a)===a)for(n in m=function(){var b=0,c;for(c in a)a.hasOwnProperty(c)&&b++;return b}(),a)a.hasOwnProperty(n)&&(!c&&!--m&&(d(j)?j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}:j[n]=function(a){return function(){var b=[].slice.call(arguments);a&&a.apply(this,b),l()}}(k[n])),g(a[n],j,b,n,h))}else!c&&l()}var h=!!a.test,i=a.load||a.both,j=a.callback||f,k=j,l=a.complete||f,m,n;c(h?a.yep:a.nope,!!i),i&&c(i)}var i,j,l=this.yepnope.loader;if(e(a))g(a,0,l,0);else if(w(a))for(i=0;i<a.length;i++)j=a[i],e(j)?g(j,0,l,0):w(j)?B(j):Object(j)===j&&h(j,l);else Object(a)===a&&h(a,l)},B.addPrefix=function(a,b){z[a]=b},B.addFilter=function(a){x.push(a)},B.errorTimeout=1e4,null==b.readyState&&b.addEventListener&&(b.readyState="loading",b.addEventListener("DOMContentLoaded",A=function(){b.removeEventListener("DOMContentLoaded",A,0),b.readyState="complete"},0)),a.yepnope=k(),a.yepnope.executeStack=h,a.yepnope.injectJs=function(a,c,d,e,i,j){var k=b.createElement("script"),l,o,e=e||B.errorTimeout;k.src=a;for(o in d)k.setAttribute(o,d[o]);c=j?h:c||f,k.onreadystatechange=k.onload=function(){!l&&g(k.readyState)&&(l=1,c(),k.onload=k.onreadystatechange=null)},m(function(){l||(l=1,c(1))},e),i?k.onload():n.parentNode.insertBefore(k,n)},a.yepnope.injectCss=function(a,c,d,e,g,i){var e=b.createElement("link"),j,c=i?h:c||f;e.href=a,e.rel="stylesheet",e.type="text/css";for(j in d)e.setAttribute(j,d[j]);g||(n.parentNode.insertBefore(e,n),m(c,0))}}(this,document),Modernizr.load=function(){yepnope.apply(window,[].slice.call(arguments,0))};

</script>
<script type="text/javascript">

/*!
 * classie - class helper functions
 * from bonzo https://github.com/ded/bonzo
 * 
 * classie.has( elem, 'my-class' ) -> true/false
 * classie.add( elem, 'my-new-class' )
 * classie.remove( elem, 'my-unwanted-class' )
 * classie.toggle( elem, 'my-class' )
 */

/*jshint browser: true, strict: true, undef: true */
/*global define: false */

( function( window ) {

'use strict';

// class helper functions from bonzo https://github.com/ded/bonzo

function classReg( className ) {
  return new RegExp("(^|\\s+)" + className + "(\\s+|$)");
}

// classList support for class management
// altho to be fair, the api sucks because it won't accept multiple classes at once
var hasClass, addClass, removeClass;

if ( 'classList' in document.documentElement ) {
  hasClass = function( elem, c ) {
    return elem.classList.contains( c );
  };
  addClass = function( elem, c ) {
    elem.classList.add( c );
  };
  removeClass = function( elem, c ) {
    elem.classList.remove( c );
  };
}
else {
  hasClass = function( elem, c ) {
    return classReg( c ).test( elem.className );
  };
  addClass = function( elem, c ) {
    if ( !hasClass( elem, c ) ) {
      elem.className = elem.className + ' ' + c;
    }
  };
  removeClass = function( elem, c ) {
    elem.className = elem.className.replace( classReg( c ), ' ' );
  };
}

function toggleClass( elem, c ) {
  var fn = hasClass( elem, c ) ? removeClass : addClass;
  fn( elem, c );
}

var classie = {
  // full names
  hasClass: hasClass,
  addClass: addClass,
  removeClass: removeClass,
  toggleClass: toggleClass,
  // short names
  has: hasClass,
  add: addClass,
  remove: removeClass,
  toggle: toggleClass
};

// transport
if ( typeof define === 'function' && define.amd ) {
  // AMD
  define( classie );
} else {
  // browser global
  window.classie = classie;
}

})( window );


</script>

  <script type="text/javascript">
  
/**
 * cbpViewModeSwitch.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */
(function() {

    var container = document.getElementById( 'cbp-vm' ),
        optionSwitch = Array.prototype.slice.call( container.querySelectorAll( 'div.cbp-vm-options > a' ) );

    function init() {
        optionSwitch.forEach( function( el, i ) {
            el.addEventListener( 'click', function( ev ) {
                ev.preventDefault();
                _switch( this );
            }, false );
        } );
    }

    function _switch( opt ) {
        // remove other view classes and any any selected option
        optionSwitch.forEach(function(el) { 
            classie.remove( container, el.getAttribute( 'data-view' ) );
            classie.remove( el, 'cbp-vm-selected' );
        });
        // add the view class for this option
        classie.add( container, opt.getAttribute( 'data-view' ) );
        // this option stays selected
        classie.add( opt, 'cbp-vm-selected' );
    }

    init();

})();  
  
  </script>

      



<script type="text/javascript">

/* Modernizr 2.6.2 (Custom Build) | MIT & BSD
 * Build: http://modernizr.com/download/#-generatedcontent-touch-shiv-cssclasses-teststyles-prefixes-load
 */
;window.Modernizr=function(a,b,c){function x(a){j.cssText=a}function y(a,b){return x(n.join(a+";")+(b||""))}function z(a,b){return typeof a===b}function A(a,b){return!!~(""+a).indexOf(b)}function B(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:z(f,"function")?f.bind(d||b):f}return!1}var d="2.6.2",e={},f=!0,g=b.documentElement,h="modernizr",i=b.createElement(h),j=i.style,k,l=":)",m={}.toString,n=" -webkit- -moz- -o- -ms- ".split(" "),o={},p={},q={},r=[],s=r.slice,t,u=function(a,c,d,e){var f,i,j,k,l=b.createElement("div"),m=b.body,n=m||b.createElement("body");if(parseInt(d,10))while(d--)j=b.createElement("div"),j.id=e?e[d]:h+(d+1),l.appendChild(j);return f=["&#173;",'<style id="s',h,'">',a,"</style>"].join(""),l.id=h,(m?l:n).innerHTML+=f,n.appendChild(l),m||(n.style.background="",n.style.overflow="hidden",k=g.style.overflow,g.style.overflow="hidden",g.appendChild(n)),i=c(l,a),m?l.parentNode.removeChild(l):(n.parentNode.removeChild(n),g.style.overflow=k),!!i},v={}.hasOwnProperty,w;!z(v,"undefined")&&!z(v.call,"undefined")?w=function(a,b){return v.call(a,b)}:w=function(a,b){return b in a&&z(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=s.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(s.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(s.call(arguments)))};return e}),o.touch=function(){var c;return"ontouchstart"in a||a.DocumentTouch&&b instanceof DocumentTouch?c=!0:u(["@media (",n.join("touch-enabled),("),h,")","{#modernizr{top:9px;position:absolute}}"].join(""),function(a){c=a.offsetTop===9}),c},o.generatedcontent=function(){var a;return u(["#",h,"{font:0/0 a}#",h,':after{content:"',l,'";visibility:hidden;font:3px/1 a}'].join(""),function(b){a=b.offsetHeight>=3}),a};for(var C in o)w(o,C)&&(t=C.toLowerCase(),e[t]=o[C](),r.push((e[t]?"":"no-")+t));return e.addTest=function(a,b){if(typeof a=="object")for(var d in a)w(a,d)&&e.addTest(d,a[d]);else{a=a.toLowerCase();if(e[a]!==c)return e;b=typeof b=="function"?b():b,typeof f!="undefined"&&f&&(g.className+=" "+(b?"":"no-")+a),e[a]=b}return e},x(""),i=k=null,function(a,b){function k(a,b){var c=a.createElement("p"),d=a.getElementsByTagName("head")[0]||a.documentElement;return c.innerHTML="x<style>"+b+"</style>",d.insertBefore(c.lastChild,d.firstChild)}function l(){var a=r.elements;return typeof a=="string"?a.split(" "):a}function m(a){var b=i[a[g]];return b||(b={},h++,a[g]=h,i[h]=b),b}function n(a,c,f){c||(c=b);if(j)return c.createElement(a);f||(f=m(c));var g;return f.cache[a]?g=f.cache[a].cloneNode():e.test(a)?g=(f.cache[a]=f.createElem(a)).cloneNode():g=f.createElem(a),g.canHaveChildren&&!d.test(a)?f.frag.appendChild(g):g}function o(a,c){a||(a=b);if(j)return a.createDocumentFragment();c=c||m(a);var d=c.frag.cloneNode(),e=0,f=l(),g=f.length;for(;e<g;e++)d.createElement(f[e]);return d}function p(a,b){b.cache||(b.cache={},b.createElem=a.createElement,b.createFrag=a.createDocumentFragment,b.frag=b.createFrag()),a.createElement=function(c){return r.shivMethods?n(c,a,b):b.createElem(c)},a.createDocumentFragment=Function("h,f","return function(){var n=f.cloneNode(),c=n.createElement;h.shivMethods&&("+l().join().replace(/\w+/g,function(a){return b.createElem(a),b.frag.createElement(a),'c("'+a+'")'})+");return n}")(r,b.frag)}function q(a){a||(a=b);var c=m(a);return r.shivCSS&&!f&&!c.hasCSS&&(c.hasCSS=!!k(a,"article,aside,figcaption,figure,footer,header,hgroup,nav,section{display:block}mark{background:#FF0;color:#000}")),j||p(a,c),a}var c=a.html5||{},d=/^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i,e=/^(?:a|b|code|div|fieldset|h1|h2|h3|h4|h5|h6|i|label|li|ol|p|q|span|strong|style|table|tbody|td|th|tr|ul)$/i,f,g="_html5shiv",h=0,i={},j;(function(){try{var a=b.createElement("a");a.innerHTML="<xyz></xyz>",f="hidden"in a,j=a.childNodes.length==1||function(){b.createElement("a");var a=b.createDocumentFragment();return typeof a.cloneNode=="undefined"||typeof a.createDocumentFragment=="undefined"||typeof a.createElement=="undefined"}()}catch(c){f=!0,j=!0}})();var r={elements:c.elements||"abbr article aside audio bdi canvas data datalist details figcaption figure footer header hgroup mark meter nav output progress section summary time video",shivCSS:c.shivCSS!==!1,supportsUnknownElements:j,shivMethods:c.shivMethods!==!1,type:"default",shivDocument:q,createElement:n,createDocumentFragment:o};a.html5=r,q(b)}(this,b),e._version=d,e._prefixes=n,e.testStyles=u,g.className=g.className.replace(/(^|\s)no-js(\s|$)/,"$1$2")+(f?" js "+r.join(" "):""),e}(this,this.document),function(a,b,c){function d(a){return"[object Function]"==o.call(a)}function e(a){return"string"==typeof a}function f(){}function g(a){return!a||"loaded"==a||"complete"==a||"uninitialized"==a}function h(){var a=p.shift();q=1,a?a.t?m(function(){("c"==a.t?B.injectCss:B.injectJs)(a.s,0,a.a,a.x,a.e,1)},0):(a(),h()):q=0}function i(a,c,d,e,f,i,j){function k(b){if(!o&&g(l.readyState)&&(u.r=o=1,!q&&h(),l.onload=l.onreadystatechange=null,b)){"img"!=a&&m(function(){t.removeChild(l)},50);for(var d in y[c])y[c].hasOwnProperty(d)&&y[c][d].onload()}}var j=j||B.errorTimeout,l=b.createElement(a),o=0,r=0,u={t:d,s:c,e:f,a:i,x:j};1===y[c]&&(r=1,y[c]=[]),"object"==a?l.data=c:(l.src=c,l.type=a),l.width=l.height="0",l.onerror=l.onload=l.onreadystatechange=function(){k.call(this,r)},p.splice(e,0,u),"img"!=a&&(r||2===y[c]?(t.insertBefore(l,s?null:n),m(k,j)):y[c].push(l))}function j(a,b,c,d,f){return q=0,b=b||"j",e(a)?i("c"==b?v:u,a,b,this.i++,c,d,f):(p.splice(this.i++,0,a),1==p.length&&h()),this}function k(){var a=B;return a.loader={load:j,i:0},a}var l=b.documentElement,m=a.setTimeout,n=b.getElementsByTagName("script")[0],o={}.toString,p=[],q=0,r="MozAppearance"in l.style,s=r&&!!b.createRange().compareNode,t=s?l:n.parentNode,l=a.opera&&"[object Opera]"==o.call(a.opera),l=!!b.attachEvent&&!l,u=r?"object":l?"script":"img",v=l?"script":u,w=Array.isArray||function(a){return"[object Array]"==o.call(a)},x=[],y={},z={timeout:function(a,b){return b.length&&(a.timeout=b[0]),a}},A,B;B=function(a){function b(a){var a=a.split("!"),b=x.length,c=a.pop(),d=a.length,c={url:c,origUrl:c,prefixes:a},e,f,g;for(f=0;f<d;f++)g=a[f].split("="),(e=z[g.shift()])&&(c=e(c,g));for(f=0;f<b;f++)c=x[f](c);return c}function g(a,e,f,g,h){var i=b(a),j=i.autoCallback;i.url.split(".").pop().split("?").shift(),i.bypass||(e&&(e=d(e)?e:e[a]||e[g]||e[a.split("/").pop().split("?")[0]]),i.instead?i.instead(a,e,f,g,h):(y[i.url]?i.noexec=!0:y[i.url]=1,f.load(i.url,i.forceCSS||!i.forceJS&&"css"==i.url.split(".").pop().split("?").shift()?"c":c,i.noexec,i.attrs,i.timeout),(d(e)||d(j))&&f.load(function(){k(),e&&e(i.origUrl,h,g),j&&j(i.origUrl,h,g),y[i.url]=2})))}function h(a,b){function c(a,c){if(a){if(e(a))c||(j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}),g(a,j,b,0,h);else if(Object(a)===a)for(n in m=function(){var b=0,c;for(c in a)a.hasOwnProperty(c)&&b++;return b}(),a)a.hasOwnProperty(n)&&(!c&&!--m&&(d(j)?j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}:j[n]=function(a){return function(){var b=[].slice.call(arguments);a&&a.apply(this,b),l()}}(k[n])),g(a[n],j,b,n,h))}else!c&&l()}var h=!!a.test,i=a.load||a.both,j=a.callback||f,k=j,l=a.complete||f,m,n;c(h?a.yep:a.nope,!!i),i&&c(i)}var i,j,l=this.yepnope.loader;if(e(a))g(a,0,l,0);else if(w(a))for(i=0;i<a.length;i++)j=a[i],e(j)?g(j,0,l,0):w(j)?B(j):Object(j)===j&&h(j,l);else Object(a)===a&&h(a,l)},B.addPrefix=function(a,b){z[a]=b},B.addFilter=function(a){x.push(a)},B.errorTimeout=1e4,null==b.readyState&&b.addEventListener&&(b.readyState="loading",b.addEventListener("DOMContentLoaded",A=function(){b.removeEventListener("DOMContentLoaded",A,0),b.readyState="complete"},0)),a.yepnope=k(),a.yepnope.executeStack=h,a.yepnope.injectJs=function(a,c,d,e,i,j){var k=b.createElement("script"),l,o,e=e||B.errorTimeout;k.src=a;for(o in d)k.setAttribute(o,d[o]);c=j?h:c||f,k.onreadystatechange=k.onload=function(){!l&&g(k.readyState)&&(l=1,c(),k.onload=k.onreadystatechange=null)},m(function(){l||(l=1,c(1))},e),i?k.onload():n.parentNode.insertBefore(k,n)},a.yepnope.injectCss=function(a,c,d,e,g,i){var e=b.createElement("link"),j,c=i?h:c||f;e.href=a,e.rel="stylesheet",e.type="text/css";for(j in d)e.setAttribute(j,d[j]);g||(n.parentNode.insertBefore(e,n),m(c,0))}}(this,document),Modernizr.load=function(){yepnope.apply(window,[].slice.call(arguments,0))};

</script>
<script type="text/javascript">

/*!
 * classie - class helper functions
 * from bonzo https://github.com/ded/bonzo
 * 
 * classie.has( elem, 'my-class' ) -> true/false
 * classie.add( elem, 'my-new-class' )
 * classie.remove( elem, 'my-unwanted-class' )
 * classie.toggle( elem, 'my-class' )
 */

/*jshint browser: true, strict: true, undef: true */
/*global define: false */

( function( window ) {

'use strict';

// class helper functions from bonzo https://github.com/ded/bonzo

function classReg( className ) {
  return new RegExp("(^|\\s+)" + className + "(\\s+|$)");
}
// classList support for class management
// altho to be fair, the api sucks because it won't accept multiple classes at once
var hasClass, addClass, removeClass;

if ( 'classList' in document.documentElement ) {
  hasClass = function( elem, c ) {
    return elem.classList.contains( c );
  };
  addClass = function( elem, c ) {
    elem.classList.add( c );
  };
  removeClass = function( elem, c ) {
    elem.classList.remove( c );
  };
}
else {
  hasClass = function( elem, c ) {
    return classReg( c ).test( elem.className );
  };
  addClass = function( elem, c ) {
    if ( !hasClass( elem, c ) ) {
      elem.className = elem.className + ' ' + c;
    }
  };
  removeClass = function( elem, c ) {
    elem.className = elem.className.replace( classReg( c ), ' ' );
  };
}

function toggleClass( elem, c ) {
  var fn = hasClass( elem, c ) ? removeClass : addClass;
  fn( elem, c );
}

var classie = {
  // full names
  hasClass: hasClass,
  addClass: addClass,
  removeClass: removeClass,
  toggleClass: toggleClass,
  // short names
  has: hasClass,
  add: addClass,
  remove: removeClass,
  toggle: toggleClass
};

// transport
if ( typeof define === 'function' && define.amd ) {
  // AMD
  define( classie );
} else {
  // browser global
  window.classie = classie;
}

})( window );


</script>

  <script type="text/javascript">
  
/**
 * cbpViewModeSwitch.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */
(function() {

    var container = document.getElementById( 'cbp-vm' ),
        optionSwitch = Array.prototype.slice.call( container.querySelectorAll( 'div.cbp-vm-options > a' ) );

    function init() {
        optionSwitch.forEach( function( el, i ) {
            el.addEventListener( 'click', function( ev ) {
                ev.preventDefault();
                _switch( this );
            }, false );
        } );
    }

    function _switch( opt ) {
        // remove other view classes and any any selected option
        optionSwitch.forEach(function(el) { 
            classie.remove( container, el.getAttribute( 'data-view' ) );
            classie.remove( el, 'cbp-vm-selected' );
        });
        // add the view class for this option
        classie.add( container, opt.getAttribute( 'data-view' ) );
        // this option stays selected
        classie.add( opt, 'cbp-vm-selected' );
    }

    init();

})();  
  
  </script>


