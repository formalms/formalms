<?php
require_once(_lms_ . '/lib/lib.middlearea.php');
$ma = new Man_MiddleArea();
// $path_course = $GLOBALS['where_files_relative'] . '/appLms/' . Get::sett('pathcourse') . '/';

$files_dir = str_replace('../', '', $GLOBALS['where_files_relative']);

$path_course =  Get::site_url() . $files_dir . '/appLms/' . Get::sett('pathcourse');
$smodel = new CatalogLms();
$html = '';

function TruncateText($the_text, $size)
{
    if (strlen($the_text) > $size)
        return substr($the_text, 0, $size) . '...';
    return $the_text;
}
?>


<script type="text/javascript">
    YAHOO.util.Event.onDOMReady(function () {
        initialize("<?php echo Lang::t('_UNDO', 'standard'); ?>");
    });
</script>


<div class="col-md-12">
    <div class="main">
        <div id="cbp-vm" class="cbp-vm-switcher cbp-vm-view-grid" style="padding: 0;">
<!--            <ul>-->
<!--    <pre>--><?php //var_dump($row); ?><!--</pre>-->
    <div class="row">
        <?php

        while ($row = sql_fetch_assoc($result)) {
//            echo '<pre>'. json_encode($row) . '</pre>';
//            echo '<pre>'. var_dump($result) . '</pre>';
            $action = '';
            if ($row['course_type'] === 'classroom') {
                $classrooms = $smodel->classroom_man->getCourseDate($row['idCourse'], false);
                if (count($classrooms) > 0) {
//                    $action = '<div class="catalog_action" style="top:5px;" id="action_' . $row['idCourse'] . '">'
//                        . '<a href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SHOW_EDITIONS', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_SHOW_EDITIONS', 'catalogue') . '</p></a></div>';


                    $action = '<div class="course-box__item" id="action_' . $row['idCourse'] . '">'
                        . '<a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SHOW_EDITIONS', 'catalogue') . '">'
                        . '<span class="forma-button__label">' . Lang::t('_SHOW_EDITIONS', 'catalogue') . '</span>'
                        . '</a>'
                        . '</div>';
                } else {
                    $action = '
                        <div class="course-box__item">
                            <a class="forma-button forma-button--disabled">
                                <span class="forma-button__label">' . Lang::t('_NO_AVAILABLE_EDITIONS', 'catalogue') . '</span>
                            </a>
                        </div>';
                }
            } else {
                // testo login
                $linkTitle = str_replace(array('[login]', '[signin]'), array($login_link, $signin_link), ($register_type === 'self' || $register_type === 'self_optin' || $register_type === 'moderate' ? Lang::t('_REGISTER_FOR_COURSE', 'login') : Lang::t('_REGISTER_FOR_COURSE_NO_REG', 'login')));

                $login_link = '<a class="forma-button forma-button--green forma-button--orange-hover" href="index.php" title="login">Login</a>';
//                $action = '<div class="can_subscribe" style="top:5px;" id="action_' . $row['idCourse'] . '">'
//                    . str_replace(array('[login]', '[signin]'), array($login_link, $signin_link), ($register_type === 'self' || $register_type === 'self_optin' || $register_type === 'moderate' ? Lang::t('_REGISTER_FOR_COURSE', 'login') : Lang::t('_REGISTER_FOR_COURSE_NO_REG', 'login')))
//                    . '</div>';

                $action = '
                    <div class="course-box__item" id="action_' . $row['idCourse'] . '">'
//                    . str_replace(array('[login]', '[signin]'), array($login_link, $signin_link), ($register_type === 'self' || $register_type === 'self_optin' || $register_type === 'moderate' ? Lang::t('_REGISTER_FOR_COURSE', 'login') : Lang::t('_REGISTER_FOR_COURSE_NO_REG', 'login')))
                    . $login_link
                    . '</div>';
            }

            $arr_cat = $smodel->getMinorCategoryTree((int)$row['idCategory']);

            if ($row['course_type'] == "elearning") $img_type_ico = "<span class='elearning'><i class='fa fa-graduation-cap'></i>" . Lang::t('_LEARNING_COURSE', 'cart') . "</span>";
            if ($row['course_type'] == "classroom") $img_type_ico = "<span class='classroom'><i class='fa fa-university'></i>" . Lang::t('_CLASSROOM_COURSE', 'cart') . "</span>";
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

            //here begins the course box

//                    $html = '<li>
//                                <div class="cbp-vm-image" >
//                                    <div class="area1 course-cover cat">
//                                        <a href="#">
//                                            <div class="area2 cat" style="z-index:1" >
//                                                <h1>' . $row['name'] . '</h1>
//                                                <div style="clear:both;"></div>
//                                                <p class="course_support_info1">
//                                                    <p class="descrizione_corso cat">' . $row['description'] . '</p>
//                                                    <p class="tipo_corso">' . $img_type_ico . '</p>
//                                                </p>
//                                             </div>
//                                            <div class=""image_cat>'
//                                . ($row['use_logo_in_courselist'] && $row['img_course'] ? '<img class="group list-group-image" src="' . Get::site_url() . $path_course . $row['img_course'] . '" alt="' . Util::purge($row['name']) . '" />' : '')
//                                . ($row['use_logo_in_courselist'] && !$row['img_course'] ? '<img class="group list-group-image" src="' . Get::site_url() . '/templates/' . Get::sett('defaultTemplate') . '/images/course/course_nologo.png' . '" alt="' . Util::purge($row['name']) . '" />' : '')
//                                . '</div>
//                                        </a>
//                                    </div>
//                                </div> ';

            $html .= '
                <div class="col-xs-12 col-md-6">
                    <div class="course-box">
                        <div class="course-box__item">
                            <div class="course-box__title">' . $row['name'] . '</div>
                        </div>
                        <div class="course-box__item course-box__item--no-padding">
            ';

            if ($row['use_logo_in_courselist'] && $row['img_course']) { //check per img
                $html .= '<div class="course-box__img" style="background-image: url(' . $path_course . $row['img_course'] . ');">';
            } else {
                $html .= '<div class="course-box__img">';
            }

            $html .= '
                                <div class="course-box__img-title">' . $img_type_ico . '</div>
                            </div>
                        </div>
                        <div class="course-box__item">
                            <div class="course-box__desc">
                                ' . TruncateText($row['box_description'], 120) . '
                            </div>
                        </div>';

//            $strClassStyle = 'style="background-color: transparent;"';
//            if ($data_inizio != "0000-00-00" && $data_end != "0000-00-00") $strClassStyle = "";
//
//            $html .= '<div class="edizioni_cal cat" ' . $strClassStyle . '>'
//                . ($data_inizio != "0000-00-00" && $data_end != "0000-00-00" ?
//                    '<a href="#" class="tooltips" id="classe_data_start" title="INIZIO"><div class="edizioni_start cat"><i class="fa  fa-2x fa-calendar-check-o" aria-hidden="true"></i>' . $data_inizio_format . ' </a></div>
//                        <a href="#" class="tooltips" id="classe_data_end" title="FINE"><div class="edizioni_end cat"><i class="fa fa-2x fa-calendar-times-o" aria-hidden="true"></i>' . $data_end_format . ' </a></div>
//                        <div style="clear:both"></div>' : '')
//                . ($data_inizio == "0000-00-00" && $data_end == "0000-00-00" ? '' : '')
//                . ($data_inizio != "0000-00-00" && $data_end == "0000-00-00" ? '
//                        <a href="#" class="tooltips" id="classe_data_start" title="INIZIO"><div class="edizioni_start cat"><i class="fa  fa-2x fa-calendar-check-o" aria-hidden="true"></i>' . $data_inizio_format . ' </a></div><div style="clear:both"></div>' : '')
//                . ($data_inizio == "0000-00-00" && $data_end != "0000-00-00" ? '
//                        <a href="#" class="tooltips" id="classe_data_end" title="FINE"><div class="edizioni_end cat"><i class="fa  fa-2x fa-calendar-check-o" aria-hidden="true"></i>' . $data_end_format . ' </a></div><div style="clear:both"></div>' : '') .
//                '</div>';

            $startDate = $data_inizio == '0000-00-00' ? false : true;
            $endDate = $data_end == '0000-00-00' ? false : true;

            if ($startDate && $endDate) { //ci sono le date di inizio e fine
                $html .= '
                            <div class="course-box__item">
                                <div class="course-box__date-box calendar-icon--check">' . $data_inizio_format . '</div>
                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                <div class="course-box__date-box course-box__date-box--end calendar-icon--green-cross">' . $data_end_format . '</div>
                            </div>
                       ';
            } elseif ($startDate && !$endDate) { //c'è solo la data di inizio
                $html .= '
                            <div class="course-box__item">
                                <div class="course-box__date-box calendar-icon--check">' . $data_inizio_format . '</div>
                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                <div class="course-box__date-box course-box__date-box--no-date calendar-icon--gray-cross"></div>
                            </div>
                       ';
            } elseif (!$startDate && $endDate) { //c'è solo la data di fine
                $html .= '
                            <div class="course-box__item">
                                <div class="course-box__date-box course-box__date-box--no-date calendar-icon--check"></div>
                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                <div class="course-box__date-box course-box__date-box--no-date calendar-icon--green-cross">' . $data_end_format . '</div>
                            </div>
                       ';
            } else { // non ci sono date, mostro i box vuoti
                $html .= '
                            <div class="course-box__item">
                                <div class="course-box__date-box course-box__date-box--no-date calendar-icon--check"></div>
                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                <div class="course-box__date-box course-box__date-box--no-date calendar-icon--gray-cross"></div>
                            </div>
                       ';
            }

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
//            $html .= '<div class="cbp-vm-add">
//                <div>';
            if ($str_can_enter == true && $row['status'] != CST_CONCLUDED) $html .= $action;
            if ($str_can_enter == false || $row['status'] == CST_CONCLUDED) $html .= "<div class='lock cat'><i class='fa fa-3x fa-lock' aria-hidden='true'></i></div>";

            // in caso di corso a tempo, l utente deve potersi iscrivere, se non iscritto
            if (($row['subscribe_method'] == 2 || $row['subscribe_method'] == 1) && $str_can_enter == false && strrpos($action, "subscribed") == false) $html .= $action;

                    $html .= '</div>
                           </div>
                                         
                      '; //</li>


        }


        if (sql_num_rows($result) <= 0) {
            $html = '<p>' . Lang::t('_NO_CONTENT', 'standard') . '</p>';
        }

        echo $html;

        ?>
    </div>
<!--            </ul>-->


<!--        </div>-->
<!--    </div><!-- /main-->


</div>
</div>
</div>

