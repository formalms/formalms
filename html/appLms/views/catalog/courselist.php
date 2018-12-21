<script type="text/javascript">
    YAHOO.util.Event.onDOMReady(function () {
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
//   $category = $this->model->getMinorCategory($std_link, true);
$html = '';
$path_course = $GLOBALS['where_files_relative'] . '/appLms/' . Get::sett('pathcourse') . '/';
$current_catalogue = $smodel->current_catalogue;


function TruncateText($the_text, $size)
{
    if (strlen($the_text) > $size)
        return substr($the_text, 0, $size) . '...';
    return $the_text;
}


function classroomCourse(&$row, &$smodel){
    
    $user_classroom = $smodel->classroom_man->getUserDateForCourse(Docebo::user()->getIdSt(), $row['idCourse']);
    if (count($user_classroom)>0){  // user already enrolled.
        $action .= '<a class="forma-button forma-button--orange-hover" href="index.php?modname=course&op=aula&idCourse=' . $row['idCourse'] . ' "'
                        . ' title="' . $_text . '"><span class="forma-button__label">'
                        . Lang::t('_USER_STATUS_ENTER', 'catalogue') . '</span>'
                        . '</a>';
    }  else {
        // get all editions of a course with status available
        $classrooms = $smodel->classroom_man->getCourseDate($row['idCourse'], false);
        if (count($classrooms) == 0) {
            $action .= '<a class="forma-button forma-button--disabled">
                        <span class="forma-button__label">' . Lang::t('_NO_EDITIONS', 'catalogue') . '</span>
                    </a>';
        }  else {
            if ($row['selling'] == 0) {
                switch ($row['subscribe_method']) {
                    case 2:
                        // free
                        $action .= '<a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><span class="forma-button__label">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</span></a>';
                        break;
                    case 1:
                        // moderate
                        $action .= '<a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><span class="forma-button__label">' . Lang::t('_COURSE_S_MODERATE', 'catalogue') . '</span></a>';
                        break;
                    case 0:
                        // only admin
                        $action .= '<a class="forma-button forma-button--orange-hover">
                                        <span class="forma-button__label">' . Lang::t('_COURSE_S_GODADMIN', 'catalogue') . '</span>
                                    </a>';
                        break;
                }                    
            } else {
                    if (isset($_SESSION['lms_cart'][$row['idCourse']]['classroom'])) {
                        $action .= '<p class="subscribed">' . Lang::t('_ALL_EDITION_BUYED', 'catalogue') . '</p>';
                    }  else {
                        $action .= '<a href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'1\')" title="' . Lang::t('_ADD_TO_CART', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_ADD_TO_CART', 'catalogue') . '</p></a>';
                    }
            }    
        }
    }
    return $action;
 
}


function editionElearning(&$row, &$smodel){
    $editions = $smodel->edition_man->getEditionAvailableForCourse(Docebo::user()->getIdSt(), $row['idCourse']);
    
    if (count($editions) == 0) {
        $action .= '<a href="javascript:void(0);" class="forma-button forma-button--disabled">
                        <p class="forma-button__label">' . Lang::t('_NO_EDITIONS', 'catalogue') . '</p>
                    </a>';
    } else {
        if ($row['selling'] == 0) {
            $action .= '<a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><span class="forma-button__label">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</span></a>';
        } else {
            $edition_in_chart = array();
            if (isset($_SESSION['lms_cart'][$row['idCourse']]['editions']))
                $edition_in_chart = $_SESSION['lms_cart'][$row['idCourse']]['editions'];

            $editions = array_diff($editions, $edition_in_chart);

            if (count($editions) == 0)
                $action .= '<p class="subscribed">' . Lang::t('_ALL_EDITION_BUYED', 'catalogue') . '</p>';
            else
                $action .= '<a href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'1\')" title="' . Lang::t('_ADD_TO_CART', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_ADD_TO_CART', 'catalogue') . '</p></a>';
        }
    }
    return $action;    
}


function elearningCourse(&$row, &$smodel){
   
    $result_control = $smodel->getInfoEnroll($row['idCourse'], Docebo::user()->getIdSt());
    if (sql_num_rows($result_control) > 0) {
        // the user is enrolled in some way
        list($status, $waiting, $level) = sql_fetch_row($result_control);

        if ($waiting) {
            $action .= '<a href="javascript;void(0);" class="forma-button">
                            <p class="forma-button__label">' . Lang::t('_WAITING', 'catalogue') . '</p>
                        </a>';
        } else {
            $result_lo = $smodel->getInfoLO($row['idCourse']);
            list($id_org, $id_course, $obj_type) = sql_fetch_row($result_lo);
            $str_rel = "";
            if ($obj_type == "scormorg" && $level <= 3 && $row['direct_play'] == 1) $str_rel = " rel='lightbox'";


            $canEnter = $smodel->canEnterCoursecatalog($row['idCourse'] );

            if($canEnter==1){
                $action .= '<a class="forma-button forma-button--orange-hover" href="index.php?modname=course&op=aula&idCourse=' . $row['idCourse'] . ' "'
                    . ' title="' . $row['name'] . '"   ' . $str_rel . '><span class="forma-button__label">'
                    . Lang::t('_USER_STATUS_ENTER', 'catalogue') . '</span>'
                    . '</a>';
            }else{

               $action .= '<a class="forma-button forma-button--disabled" href="javascript:void(0);">
                            <span class="forma-button__label">
                            '. Lang::t('_DISABLED', 'course') .'
                            </span>
                        </a>';
            }
        }

    } else {
        // course is not enrolled
        $course_full = false;

        if ($row['max_num_subscribe'] != 0) {
            $control = $smodel->enrolledStudent($row['idCourse']);
            if ($control >= $row['max_num_subscribe']) {
                // the course have reached the maximum number of subscription
                $action .= '<a href="javascript:void(0);" class="forma-button forma-button--disabled">
                                <span class="forma-button__label">' . Lang::t('_MAX_NUM_SUBSCRIBE', 'catalogue') . ' - ' . $row['max_num_subscribe'] . '</span>
                            </a>';
                $course_full = true;
            }
        }

        if (!$course_full) {

            if ($row['selling'] == 0) {

                switch ($row['subscribe_method']) {
                    case 2:
                        // free
                        $action .= '<a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" onclick="subscriptionPopUp(\'' . $row['idCourse'] . '\', \'0\', \'0\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><span class="forma-button__label">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</span></a>';
                        break;
                    case 1:
                        // moderate
                        $action .= '<a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" onclick="subscriptionPopUp(\'' . $row['idCourse'] . '\', \'0\', \'0\', \'0\')" title="' . Lang::t('_COURSE_S_MODERATE', 'course') . '"><span class="forma-button__label">' . Lang::t('_COURSE_S_MODERATE', 'catalogue') . '</span></a>';
                        break;
                    case 0:
                        // only admin
                        $action .= '<a href="javascript:void(0);" class="forma-button forma-button--disabled">
                                        <span class="forma-button__label">' . Lang::t('_COURSE_S_GODADMIN', 'catalogue') . '</span>
                                    </a>';
                        break;
                }
            } else {
                $date_in_chart = array();

                if (isset($_SESSION['lms_cart'][$row['idCourse']]))
                    $action .= '<a href="javascript:void(0);" class="forma-button forma-button--orange-hover">
                                    <p class="forma-button__label">' . Lang::t('_COURSE_IN_CART', 'catalogue') . '</p>
                                </a>';
                else
                    $action .= '<a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" onclick="subscriptionPopUp(\'' . $row['idCourse'] . '\', \'0\', \'0\', \'1\')" title="' . Lang::t('_ADD_TO_CART', 'catalogue') . '"><span class="forma-button__label">' . Lang::t('_ADD_TO_CART', 'catalogue') . '</span></a>';
            }
        }
    }
    return $action;
}



function getEditionInfo(&$row, &$smodel){
    $classrooms = $smodel->classroom_man->getCourseDate($row['idCourse'], false);
    $_edition = array();
    foreach ($classrooms as $classroom_info){
         $_edition['id_date'] = $classroom_info['id_date'];
         $_edition['id_course'] = $classroom_info['id_course'];
         $_edition['code'] = $classroom_info['code'];
         $_edition['name'] = $classroom_info['name'];
         $_edition['startDate']  = $classroom_info['date_begin'];
         $_edition['endDate']  = $classroom_info['date_end'];
         $_edition['unsubscribe_date_limit'] = $classroom_info['unsubscribe_date_limit'];
         $_edition['subscribed'] =  $smodel->classroom_man->controlDateUserSubscriptions(Docebo::user()->getIdSt(),$_edition['id_date']) ;
         $_edition['days'] = $smodel->classroom_man->getDateDayDateDetails($_edition['id_date']) ;
         $ret_array[] = $_edition;
    }                 
    return $ret_array;
    
}


function classroomActionButton($is_enrolled, $unsubscribe_date_limit, &$row){

    if ($is_enrolled ) {
        // 0 only admin
        // 1 moderate 
        // 2 free un_subscribe
        switch ($row['auto_unsubscribe']) {
            case 0:
               return Lang::t('_USER_STATUS_ENTER', 'catalogue');
            case 1:
            case 2:
                if (strtotime("now") < strtotime($unsubscribe_date_limit)) {            
                    return Lang::t('_UNSUBSCRIBE', 'course');
                } else {
                    return Lang::t('_USER_STATUS_ENTER', 'catalogue');    
                } 
        }
    } else {
        return Lang::t('_SUBSCRIBE', 'course');
    }

}


function InsertOption(&$row, $smodel){

    
    $result_control = $smodel->getInfoEnroll($row['idCourse'], Docebo::user()->getIdSt());
    $not_enrolled = sql_num_rows($result_control) > 0;
    $html = '';
    if( $not_enrolled && $row['selling'] == 0 && ($row['auto_unsubscribe']==2 || $row['auto_unsubscribe']==1 || $row["course_demo"] ) ){
    
        $html .= '<div class="course-box__options dropdown pull-right">
                    <div class="dropdown-toggle" id="courseBoxOptions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <i class="glyphicon glyphicon-option-horizontal"></i> 
                    </div>   
                    <ul class="dropdown-menu" aria-labelledby="courseBoxOptions">';
                        if(($row['auto_unsubscribe']==2 || $row['auto_unsubscribe']==1)  && $not_enrolled) {
                            $html .= '<li><a href="javascript:confirmDialog(\''.$row['name'].'\','.$row['idCourse'].')">'.Lang::t('_SELF_UNSUBSCRIBE', 'course').'</a></li>';    
                            //$html .= "<li><a href='javascript:void(0);'>option1</a></li>";
                        }    
                        if ($row["course_demo"]) {
                            $html .= '<li><a href="index.php?r=catalog/downloadDemoMaterial&amp;course_id='.$row['idCourse'].'">'.Lang::t('_COURSE_DEMO', 'course').'</a></li>';
                        }                                
         $html .= '</ul></div>';
    }     
    return $html;
    
}

?>

        <script type="text/javascript">
                function confirmDialog(title, id_course, id_date ){
                        $('<div></div>').appendTo('body')                    
                        .html("<div><h6><?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course')?></h6></div>")
                        .dialog({
                                modal: true, 
                                title: title, 
                                autoOpen: true,
                                width: '200',
                                height: '150', 
                                resizable: false,
                                buttons: {
                                     <?php echo Lang::t('_CONFIRM', 'standard')?>: function () {
                                        var posting = $.get(
                                            'ajax.server.php',
                                                {
                                                    r: 'catalog/self_unsubscribe',
                                                    id_course: id_course,
                                                    id_date: id_date,
                                                    type_course: $( "#typeCourse" ).val(),
                                                    id_catalogue: <?php echo $current_catalogue ?>,
                                                    id_category: $('#treeview1').treeview('getSelected')[0] ? 
                                                        $('#treeview1').treeview('getSelected')[0].id_cat : null
                                                }
                                            );
                                            posting.done(function (responseText) {
                                                $("#div_course").html(responseText);
                                            });
                                            posting.fail(function () {
                                                alert('unsubscribe failed')
                                            })                                
                                        $(this).dialog("close");
                                     },
                                     <?php echo Lang::t('_UNDO', 'standard')?>: function () { $(this).dialog("close");}
                                    
                                },
                            open: function(event, ui) {
                                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                            }, 
                            close: function (event, ui) {
                                $(this).remove();
                            }
                        });                      
                }


        </script>        
        <div id="cbp-vm" class="" style="margin-top: 15px;">
                <?php
                while ($row = sql_fetch_assoc($result)) {
                    $action = '';
                    $editions = array();
                    $img_type = "<span class='elearning'><i class='fa fa-graduation-cap'></i>&nbsp;" . Lang::t('_LEARNING_COURSE', 'cart') . "</span>";
                    if ($row['course_type'] === 'classroom') {
                        $action = '<div class="course-box__item" id="action_' . $row['idCourse'] . '">'.classroomCourse($row, $smodel).'</div>';
                        $img_type = "<span class='classroom'><i class='fa fa-university'></i>&nbsp;" . Lang::t('_CLASSROOM_COURSE', 'cart') . "</span>";
                        $editions = getEditionInfo($row, $smodel);
                    } elseif ($row['course_edition'] == 1) { // edition e-learning courses
                        $action = '<div class="course-box__item" id="action_' . $row['idCourse'] . '">'.editionElearning($row, $smodel).'</div>';
                        $editions = getEditionInfo($row, $smodel);
                    } else { // e-learning course
                        $action = '<div class="course-box__item" id="action_' . $row['idCourse'] . '">'.elearningCourse($row,$smodel).'</div>';
                    }

                    // BUG TRACKER - LR #5669
                    $data_inizio = $row['date_begin'];
                    $data_end = $row['date_end'];

                    $str_lock_start = "";
                    $str_lock_end = "";

                    if ($row['hour_begin'] != "-1") $str_h_begin = $row['hour_begin'];
                    if ($row['hour_end'] != "-1") $str_h_end = $row['hour_end'];

                    $can_enter_star = true;
                    $can_enter_end = true;
                    if ($data_inizio != "0000-00-00") $str_lock_start = "<b><i style='font-size:.68em'>" . Lang::t('_COURSE_BEGIN', 'certificate') . "</b>: " . Format::date($data_inizio, 'date') . " " . $str_h_begin . "</i>";
                    if ($data_end != "0000-00-00") $str_lock_end = "<br><b><i style='font-size:.68em'>" . Lang::t('_COURSE_END', 'certificate') . "</b>: " . Format::date($data_end, 'date') . " " . $str_h_end . "</i>";

                    if ($data_inizio != "0000-00-00" && $data_inizio > date('Y-m-d')) $can_enter_star = false;
                    if ($data_end != "0000-00-00" && $data_end < date('Y-m-d')) $can_enter_end = false;

                    if ($data_inizio != "0000-00-00" || $data_end != "0000-00-00") $str_can_enter = ($can_enter_star && $can_enter_end);
                    if ($data_inizio == "0000-00-00" && $data_end == "0000-00-00") $str_can_enter = true;
                    $data_inizio_format = Format::date($data_inizio, 'date');
                    $data_end_format = Format::date($data_end, 'date');

                    //here begins the course box
                    
                    $html .= '
                    <div class="col-xs-offset-1 col-xs-10 col-md-offset-0 col-md-6">
                        <div class="course-box">
                            <div class="course-box__item">
                                <div class="course-box__title">' . $row['name'] . '</div>
                            </div>
                            <div class="course-box__item course-box__item--no-padding">';

                    if ($row['use_logo_in_courselist'] && $row['img_course']) { //check per img
                        $html .= '<div class="course-box__img" style="background-image: url(' . $path_course . $row['img_course'] . ');">';
                    } else {
                        $html .= '<div class="course-box__img">';
                    }
                    $html .= '<div class="course-box__img-title">' . $img_type . '</div>
                             </div>    
                            </div>
                            <div class="course-box__item">
                                <div class="course-box__desc">
                                    ' . TruncateText($row['box_description'], 120).' 
                                </div>'.InsertOption($row, $smodel).'                            
                            </div>';
                    $html .= $action;
                    $html .= '</div>';
					
                    if (count($editions)> 0 && false) {
						$html .= '
							<div class="course-box__item course-box__item--half">
								<p class="course-box__show-dates js-course-box-open-dates-modal">
								    <i class="glyphicon glyphicon-play"></i> &nbsp; ' . Lang::t('_SHOW_ALL_DATES', 'course') . '
								</p>
								<br><br>
								<div class="course-box__modal">
									<div class="course-box__modal__header">
										<p class="course-box__modal__title">Course dates list</p>
										<button type="button" class="close-button js-course-box-close-dates-modal">
											<span class="close-button__icon"></span>
											<span class="close-button__label">close</span>
										</button>
									</div>
									<div class="course-box__modal__content">';

								for ($i = 0; $i < count($editions); $i++) {
									$html .= '
										<div class="course-box__modal__entry">
											<p class="course-box__modal__title"></p>
											<table class="course-box__modal__lesson">
												<tr>
													<td>Name:</td>
													<td>' . $editions[$i]['name'] . '</td>
													<td rowspan="5">
														<a class="forma-button forma-button--' . ($editions[$i]['subscribed'] ? 'border' : 'green-border') . '" href="javascript:void(0)">
                                                        ' . classroomActionButton($editions[$i]['subscribed'], $editions[$i]['unsubscribe_date_limit'], $row). '
                                                        </a>
													</td>
												</tr>
												<tr>
													<td>Code:</td>
													<td>' . $editions[$i]['code'] . '</td>
												</tr>
												<tr>
													<td>Date begin:</td>
													<td>' . $editions[$i]['startDate'] . '</td>
												</tr>
												<tr>
													<td>Date end:</td>
													<td>' . $editions[$i]['endDate'] . '</td>
												</tr>';
                    
                                            for ($j = 0; $j < count($editions[$i]['days'] ); $j++) {
                                                 $html .= '   
                                                                                                                           
                                                                  
                                                    <table class="course-box__modal__lesson" id="table_days">
                                                    <tr>
                                                        <td>Giornata</td>
                                                        <td><b>' .($j+1) . '</b></td>
                                                    </tr>
                                                 
                                                     <tr>
                                                        <td>Ora inizio:</td>
                                                        <td>' . $editions[$i]['days'][$j]['date_begin'] . '</td>
                                                    </tr>                                                    
                                                 
                                                    <tr>
                                                        <td>Ora fine:</td>
                                                        <td>' . $editions[$i]['days'][$j]['date_end'] . '</td>
                                                    </tr>                                                 
                                                 
                                                 
                                                     <tr>
                                                        <td>Location:</td>
                                                        <td>' . $editions[$i]['days'][$j]['classroom'] . '</td>
                                                    </tr>                                                 
                                                 
                                                    </table>
                                                          
                                                              
                                                 ';   
                                                
                                                
                                            }    
                
                                                
                
										 $html.='</table>
										</div>';
								}
						$html .= '
									</div>
									<div class="course-box__modal__footer">
										<button type="button" class="forma-button">Discard</button>
									</div>
								</div>
							</div>';
					}

                  /*    need checking
                    if ($row["course_demo"]) {
                        $html .= '
                        <div class="course-box__item course-box__item--half">
                            <a class="course-box__dl-course-supply" href="index.php?r=catalog/downloadDemoMaterial&amp;course_id=' . $row['idCourse'] . '" class="course-box__show-dates">
                                <i class="glyphicon glyphicon-download-alt"></i>&nbsp;' . Lang::t('_COURSE_DEMO', 'course') . '
                            </a>
                        </div>';
                    } */


                                            
                    /*    need checking
                    if ($str_can_enter == true && $row['status'] != CST_CONCLUDED) {
                        $html .= $action;
                    }

                    // in caso di corso a tempo, l utente deve potersi iscrivere, se non iscritto
                    if (($row['subscribe_method'] == 2 || $row['subscribe_method'] == 1) && $str_can_enter == false && strrpos($action, "subscribed") == false) {
                        $html .= $action;
                    }
                    */

                    $html .= '</div>'; //closes course-box__item
                     

                } //end  while

                if (sql_num_rows($result) <= 0) {
                    $html = '<p>' . Lang::t('_NO_CONTENT', 'standard') . '</p>';
                }

                echo $html; //returns course-box

                ?>


        </div> <!--  /forma-grid - /row-->
</div>





