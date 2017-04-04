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
$smodel = new CatalogLms();

?>


<div class="col-md-12">
    <div class="main">
        <div id="cbp-vm" class="cbp-vm-switcher">

            <div class="course-box">
                <div class="course-box__item">
                    <div class="course-box__title">Lorem ipsum dolor sit amet</div>
                </div>
                <div class="course-box__item course-box__item--no-padding">
                    <div class="course-box__img">
                        <img src="" alt="">
                        <div class="course-box__img-title">lorem ipsum</div>
                    </div>
                </div>
                <div class="course-box__item">
                    <div class="course-box__desc">
                        Lorem Ipsum dolor sit amet, consectetur adipiscing elit. Facilis ponatur
                        infinito oderis obruamus. Effectices, terroribus cognosci elegans totam
                        atilli arare p minuendas.
                    </div>
                </div>
                <div class="course-box__item">
                    <div class="course-box__date-box calendar-icon--check">31 agosto 2016</div>
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                    <div class="course-box__date-box course-box__date-box--end calendar-icon--green-cross">30 novembre
                        2017
                    </div>
                </div>
                <div class="course-box__item">
                    <a class="button button--orange-hover">
                            <span class="button__label">
                                Entra nel corso
                            </span>
                    </a>
                </div>
            </div>

            <ul>
                <?php
                while ($row = sql_fetch_assoc($result)) {
//                    echo '<pre>'. json_encode($row) . '</pre>';
                    $action = '';
                    if ($row['course_type'] === 'classroom') {
                        $additional_info = '';
                        $classrooms = $smodel->classroom_man->getCourseDate($row['idCourse'], false);
                        $action = '<div class="catalog_action" id="action_' . $row['idCourse'] . '">';
                        if (count($classrooms) == 0)
                            $action .= '<p class="cannot_subscribe">' . Lang::t('_NO_EDITIONS', 'catalogue') . '</p>';
                        else {
                            //Controllo che l'utente non sia iscritto a tutte le edizioni future
                            $date_id = array();
                            $user_classroom = $smodel->classroom_man->getUserDateForCourse(Docebo::user()->getIdSt(), $row['idCourse']);
                            $classroom_full = $smodel->classroom_man->getFullDateForCourse($row['idCourse']);
                            $classroom_not_confirmed = $smodel->classroom_man->getNotConfirmetDateForCourse($row['idCourse']);
                            $overbooking_classroom = $smodel->classroom_man->getOverbookingDateForCourse($row['idCourse']);
                            foreach ($classrooms as $classroom_info)
                                $date_id[] = $classroom_info['id_date'];

                            reset($classrooms);
                            // for all the dates we will remove the one in which the user is subscribed and the classroom not confirmed
                            $control = array_diff($date_id, $user_classroom, $classroom_not_confirmed);
                            if (count($control) == 0) {
                                if (!empty($overbooking_classroom)) {
                                    $_text = ($row['selling'] == 0
                                        ? Lang::t('_SUBSCRIBE', 'catalogue')
                                        : Lang::t('_ADD_TO_CART', 'catalogue'));
                                    $action .= '<a href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'' . ($row['selling'] == 0 ? '0' : '1') . '\')" '
                                        . ' title="' . $_text . '"><p class="can_subscribe">' . $_text . '<br />'
                                        . '(' . Lang::t('_SUBSCRIBE_WITH_OVERBOOKING', 'catalogue') . ': ' . count($overbooking_classroom) . ')</p>'
                                        . '</a>';
                                } else {
                                    if (count($user_classroom) > 0) {
                                        $action .= '<a href="index.php?modname=course&op=aula&idCourse=' . $row['idCourse'] . ' "'
                                            . ' title="' . $_text . '"><p class="subscribed">'
                                            . Lang::t('_USER_STATUS_ENTER', 'catalogue') . '</p>'
                                            . '</a>';
                                    } else {
                                        $action .= '<p class="cannot_subscribe">' . Lang::t('_NO_AVAILABLE_EDITIONS', 'catalogue') . '</p>';
                                    }
                                }
                            } else {
                                if ($row['selling'] == 0)
                                    switch ($row['subscribe_method']) {
                                        case 2:
                                            // free
                                            $action .= '<a href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</p></a>';
                                            break;
                                        case 1:
                                            // moderate
                                            $action .= '<a href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_COURSE_S_MODERATE', 'catalogue') . '</p></a>';
                                            break;
                                        case 0:
                                            // only admin
                                            $action .= '<p class="cannot_subscribe">' . Lang::t('_COURSE_S_GODADMIN', 'catalogue') . '</p>';
                                            break;
                                    }

                                else {
                                    $classroom_in_chart = array();
                                    if (isset($_SESSION['lms_cart'][$row['idCourse']]['classroom']))
                                        $classroom_in_chart = $_SESSION['lms_cart'][$row['idCourse']]['classroom'];
                                    $control = array_diff($control, $classroom_in_chart);
                                    if (count($control) == 0)
                                        $action .= '<p class="subscribed">' . Lang::t('_ALL_EDITION_BUYED', 'catalogue') . '</p>';
                                    else
                                        $action .= '<a href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'1\')" title="' . Lang::t('_ADD_TO_CART', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_ADD_TO_CART', 'catalogue') . '</p></a>';
                                }
                            }
                        }
                        $action .= '</div>';
                    } elseif ($row['course_edition'] == 1) {
                        $additional_info = '';

                        $editions = $smodel->edition_man->getEditionAvailableForCourse(Docebo::user()->getIdSt(), $row['idCourse']);

                        $action = '<div class="catalog_action" id="action_' . $row['idCourse'] . '">';
                        if (count($editions) == 0)
                            $action .= '<p class="cannot_subscribe">' . Lang::t('_NO_EDITIONS', 'catalogue') . '</p>';
                        else {
                            if ($row['selling'] == 0)
                                $action .= '<a href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</p></a>';
                            else {
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
                        $action .= '</div>';
                    } else {

                        $enrolled = $smodel->enrolledStudent($row['idCourse']);
                        $row['enrolled'] = $enrolled;
                        $row['create_date'] = Format::date($row['create_date'], 'date');
                        $additional_info = '<p class="course_support_info">' . Lang::t('_COURSE_INTRO', 'course', array(
                                '[course_type]' => $row['course_type'],
                                '[create_date]' => $row['create_date'],
                                '[enrolled]' => $row['enrolled'],
                                '[course_status]' => Lang::t($this->cstatus[$row['status']], 'course')))
                            . '</p>';

                        $result_control = $smodel->getInfoEnroll($row['idCourse'], Docebo::user()->getIdSt());


                        $action = '<div class="catalog_action" id="action_' . $row['idCourse'] . '">';
                        if (sql_num_rows($result_control) > 0) {
                            // the user is enrolled in some way
                            list($status, $waiting, $level) = sql_fetch_row($result_control);

                            if ($waiting)
                                $action .= '<p class="subscribed">' . Lang::t('_WAITING', 'catalogue') . '</p>';
                            else {

                                $result_lo = $smodel->getInfoLO($row['idCourse']);

                                list($id_org, $id_course, $obj_type) = sql_fetch_row($result_lo);
                                $str_rel = "";

                                if ($obj_type == "scormorg" && $level <= 3 && $row['direct_play'] == 1) $str_rel = " rel='lightbox'";
                                $action .= '<a href="index.php?modname=course&op=aula&idCourse=' . $row['idCourse'] . ' "'
                                    . ' title="' . $_text . '"   ' . $str_rel . '><p class="subscribed">'
                                    . Lang::t('_USER_STATUS_ENTER', 'catalogue') . '</p>'
                                    . '</a>';

                            }

                        } else {
                            // course is not enrolled
                            $course_full = false;

                            if ($row['max_num_subscribe'] != 0) {
                                $control = $smodel->enrolledStudent($row['idCourse']);
                                if ($control >= $row['max_num_subscribe']) {
                                    // the course have reached the maximum number of subscription
                                    $action .= '<p class="cannot_subscribe">' . Lang::t('_MAX_NUM_SUBSCRIBE', 'catalogue') . ' - ' . $row['max_num_subscribe'] . '</p>';
                                    $course_full = true;
                                }
                            }

                            if (!$course_full) {

                                if ($row['selling'] == 0) {

                                    switch ($row['subscribe_method']) {
                                        case 2:
                                            // free
                                            $action .= '<a href="javascript:;" onclick="subscriptionPopUp(\'' . $row['idCourse'] . '\', \'0\', \'0\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</p></a>';
                                            break;
                                        case 1:
                                            // moderate
                                            $action .= '<a href="javascript:;" onclick="subscriptionPopUp(\'' . $row['idCourse'] . '\', \'0\', \'0\', \'0\')" title="' . Lang::t('_COURSE_S_MODERATE', 'course') . '"><p class="can_subscribe">' . Lang::t('_COURSE_S_MODERATE', 'catalogue') . '</p></a>';
                                            break;
                                        case 0:
                                            // only admin
                                            $action .= '<p class="cannot_subscribe">' . Lang::t('_COURSE_S_GODADMIN', 'catalogue') . '</p>';
                                            break;
                                    }


                                } else {
                                    $date_in_chart = array();

                                    if (isset($_SESSION['lms_cart'][$row['idCourse']]))
                                        $action .= '<p class="subscribed">' . Lang::t('_COURSE_IN_CART', 'catalogue') . '</p>';
                                    else
                                        $action .= '<a href="javascript:;" onclick="subscriptionPopUp(\'' . $row['idCourse'] . '\', \'0\', \'0\', \'1\')" title="' . Lang::t('_ADD_TO_CART', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_ADD_TO_CART', 'catalogue') . '</p></a>';
                                }
                            }
                        }
                        $action .= '</div>';
                    }

                    $arr_cat = $smodel->getMinorCategoryTree((int)$row['idCategory']);


                    if ($row['course_type'] == "elearning") $img_type = "<span class='elearning'><i class='fa fa-graduation-cap'></i>&nbsp;" . Lang::t('_LEARNING_COURSE', 'cart') . "</span>";
                    if ($row['course_type'] == "classroom") $img_type = "<span class='classroom'><i class='fa fa-university'></i>&nbsp;" . Lang::t('_CLASSROOM_COURSE', 'cart') . "</span>";
                    // start - end

                    $str_start_end = "";
                    if ($row['date_begin'] != "0000-00-00") {
                        $str_start_end = Lang::t('_SUBSCRIPTION_DATE_BEGIN', 'course') . " <b>" . $row['date_begin'] . '</b>  ' . Lang::t('_SUBSCRIPTION_DATE_END', 'course') . ' <b>' . $row['date_end'] . "</b>";
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

                    //here begins the <li> element
                    $html .= '
                        <div class="course-box">
                            <div class="course-box__item">
                                <div class="course-box__title">' . $row['name'] . '</div>
                            </div>
                            <div class="course-box__item course-box__item--no-padding">
                                <div class="course-box__img"> 
                                    <div class="course-box__img-title"></div>
                                    <div class="area2 cat" >
                                        <p class="course_support_info1">
                                            <p class="descrizione_corso cat">' . $row['description'] . '</p>
                                            <p class="tipo_corso">' . $img_type . '</p>
                                        </p>    
                                    </div>
                                    <div class=""image_cat>'
                        . ($row['use_logo_in_courselist'] && $row['img_course'] ? '<img class="group list-group-image" src="' . $path_course . $row['img_course'] . '" alt="' . Util::purge($row['name']) . '" />' : '')
                        . ($row['use_logo_in_courselist'] && !$row['img_course'] ? '<img class="group list-group-image" src="' . Get::tmpl_path() . 'images/course/course_nologo.png' . '" alt="' . Util::purge($row['name']) . '" />' : '')
                        . '         </div>
                                </div>    
                            </div> ';

                    $strClassStyle = 'style="background-color: transparent;"';
                    if ($data_inizio != "0000-00-00" && $data_end != "0000-00-00") $strClassStyle = "";

                    $html .= '<div class="edizioni_cal cat" ' . $strClassStyle . '>'
                        . ($data_inizio != "0000-00-00" && $data_end != "0000-00-00" ?
                            '<a href="#" class="tooltips" id="classe_data_start" title="INIZIO"><div class="edizioni_start cat"><i class="fa  fa-2x fa-calendar-check-o" aria-hidden="true"></i>' . $data_inizio_format . ' </a></div>
                                <a href="#" class="tooltips" id="classe_data_end" title="FINE"><div class="edizioni_end cat"><i class="fa fa-2x fa-calendar-times-o" aria-hidden="true"></i>' . $data_end_format . ' </a></div>
                                <div style="clear:both"></div>' : '')
                        . ($data_inizio == "0000-00-00" && $data_end == "0000-00-00" ? '' : '')
                        . ($data_inizio != "0000-00-00" && $data_end == "0000-00-00" ? '
                                <a href="#" class="tooltips" id="classe_data_start" title="INIZIO"><div class="edizioni_start cat"><i class="fa  fa-2x fa-calendar-check-o" aria-hidden="true"></i>' . $data_inizio_format . ' </a></div><div style="clear:both"></div>' : '')
                        . ($data_inizio == "0000-00-00" && $data_end != "0000-00-00" ? '
                                <a href="#" class="tooltips" id="classe_data_end" title="FINE"><div class="edizioni_end cat"><i class="fa  fa-2x fa-calendar-check-o" aria-hidden="true"></i>' . $data_end_format . ' </a></div><div style="clear:both"></div>' : '') .
                        '</div>';

                    if ($row["course_demo"]) {
                        $html .= '<!-- DATE START - DATE END  -->
                                         <div class="box_edizioni cat">
                                               <div class="edizioni cat">
                                                     <div class="edizioni_nome cat">
                                                     ' . Lang::t('_COURSE_DEMO', 'course') . '
                                                     </div>  
                                                 <div class="luogo cat">  
                                                    <a  href="index.php?r=catalog/downloadDemoMaterial&amp;course_id=' . $row['idCourse'] . '>
                                                    <i class="fa fa-2x fa-download" aria-hidden="true"></i>&nbsp<span>' . Lang::t('_DOWNLOAD') . '</a>
                                                 </div>                                     
                                               </div>
                                         </div>';
                    }
                    $html .= '<div class="cbp-vm-add">                                   
                        <div>';
                    if ($str_can_enter == true && $row['status'] != CST_CONCLUDED) $html .= $action;
                    if ($str_can_enter == false || $row['status'] == CST_CONCLUDED) $html .= "<div class='lock cat'><i class='fa fa-3x fa-lock' aria-hidden='true'></i></div>";

                    // in caso di corso a tempo, l utente deve potersi iscrivere, se non iscritto
                    if (($row['subscribe_method'] == 2 || $row['subscribe_method'] == 1) && $str_can_enter == false && strrpos($action, "subscribed") == false) $html .= $action;

                    $html .= ' </div>    
                     </div>                             
                        </div>';


                }

                if (sql_num_rows($result) <= 0)
                    $html = '<p>' . Lang::t('_NO_CONTENT', 'standard') . '</p>';

                echo $html;


                ?>
            </ul>


        </div>
    </div><!-- /main -->

</div>





