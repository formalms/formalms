<?php
function GetCategory($the_course)
{
    $ret_val = str_replace("/", " - ", substr($the_course['nameCategory'], 1));
    if ($ret_val == "") {
        $ret_val = Lang::t('_NO_CATEGORY', 'standard');
    } else {
        $ret_val = substr($the_course['nameCategory'], 6);
    }
    return $ret_val;
}


function DataExists($the_course)
{
    $date = Format::date($the_course['date_end'], 'date');
    return ($date != "00-00-0000");
}

function GetCourseYear($the_course)
{
    $date = Format::date($the_course['date_end'], 'date');
    $date_split = explode('-', $date);
    return $date_split[2];
}

function GetCourseMonth($the_course)
{
    setlocale(LC_ALL, "IT");// TBD: setting to platform locale
    $date = Format::date($the_course['date_end'], 'date');
    $month_name = ucfirst(strftime("%B", strtotime($date)));
    return substr($month_name, 0, 3);
}

function GetCourseDay($the_course)
{
    $date = Format::date($the_course['date_end'], 'date');
    $date_split = explode('-', $date);
    return $date_split[0];
}

function GetCourseImage($the_course, $path_image)
{

    if ($the_course['img_course']) {
        return $path_image . $the_course['img_course'];
    } else {
        return Get::tmpl_path() . 'images/course/course_nologo.png';
    }
}

function TruncateText($the_text, $size)
{
    if (strlen($the_text) > $size)
        return substr($the_text, 0, $size) . '...';
    return $the_text;
}

?>

<link rel="shortcut icon" href="../favicon.ico">

<?php if ($use_label) : ?>
    <div class="container-back">
        <a href="index.php?r=elearning/show&id_common_label=-2">
            <span>&lsaquo;&lsaquo; <?php echo Lang::t('_BACK_TO_LABEL', 'course') ?></span>
        </a>
    </div>
<?php endif; ?>


<div id="container1_<?php echo $stato_corso; ?>">

    <div id="cbp-vm" style="padding: 0">

        <?php
        if ($filter_type == "all" || $filter_type == 'elearning') { ?>
            <h1 class="page-header"><strong><?php echo Lang::t('_ELEARNING', 'catalogue'); ?></strong></h1>

            <div class="clearfix" id='mia_area_<?php echo $stato_corso; ?>'>

                <div class="forma-grid">
                <?php foreach ($courselist as $course) : ?>

                    <div class="forma-grid__item">
<!--                    <div class="col-xs-10 col-sm-5 col-lg-4 course-block">//TODO add box description-->
<!--                    <div class="col col--10 col--5--tablet col--4--desk course-block">-->
                        <div class="course-box"> <!-- NEW BLOCK -->
                            <div class="course-box__item">
                                <div class="course-box__title course-icon--active"><?php echo TruncateText($course['name'], 100); ?></div>
                            </div>
                            <div class="course-box__item course-box__item--no-padding">

                                <?php if ($course['use_logo_in_courselist']) { ?>
                                <div class="course-box__img" style="background-image: url(<?php echo GetCourseImage($course, $path_course) ?>)">
                                <?php } else { ?>
                                <div class="course-box__img">
                                <?php } ?>
                                    <div class="course-box__img-title">
                                        <?php echo GetCategory($course) ?>
                                    </div>
                                </div>

                            </div>
                            <div class="course-box__item">
                                <div class="course-box__owner course-box__owner--<?php echo $course['level']; ?>">
                                    <?php echo $this->levels[$course['level']]; ?>
                                </div>
                                <div class="course-box__desc">
<!--                                    --><?php //echo TruncateText($course['description'], 150); ?>
                                    <?php echo TruncateText($course['box_description'], 120); ?>
                                </div>
                            </div>
                            <div class="course-box__item course-box__item--half">
                                <div class="course-box__date-text">
                                    <?php echo GetCourseDay($course) ?>
                                    <?php echo GetCourseMonth($course) ?>
                                    <?php echo GetCourseYear($course) ?>
                                </div>
                            </div>
                            <div class="course-box__item course-box__item--half">
                                <?php if ($course['can_enter']['can']) { ?>
                                    <a class="forma-button forma-button--orange-hover" title="<?php echo Util::purge($course['name']); ?>"
                                       href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>"<?php echo($course['direct_play'] == 1 && $course['level'] <= 3 && $course['first_lo_type'] == 'scormorg' ? ' rel="lightbox"' : ''); ?>>
                                   <span class="forma-button__label">
                                     <?php echo Lang::t('_USER_STATUS_ENTER', 'catalogue'); ?>
                                   </span>
                                    </a>
                                <?php } else { ?>
                                    <a class="forma-button forma-button--disabled" href="javascript:void(0);">
                                       <span class="forma-button__label">
                                         DISABLED
                                       </span>
                                    </a>
                                <?php } ?>
                            </div>
                        </div> <!-- END NEW BLOCK -->

                        <?php /*</li>*/ ?>
                    </div>
                <?php endforeach; ?>
                </div>

                </div>
                <?php /*</ul>*/ ?>
            <br>
            <?php 
            // END ELEARNING
        }
        ?>


        <?php if ($filter_type == "all" || $filter_type == "classroom"){ ?>
        <h1 class="page-header"><strong><?php echo Lang::t('_CLASSROOM_COURSE', 'cart'); ?></strong></h1>
        <div class="clearfix" id="mia_area_class_<?php echo $stato_corso; ?>">
            <?php if (empty($courselistClassroom)) : ?>
                <p><?php echo Lang::t('_NO_CONTENT', 'standard'); ?></p>
            <?php endif; ?>
            <?php $unsubscribe_call_arr = array(); ?>
            <?php foreach ($courselistClassroom as $course) : ?>

                <div
                    class="col-xs-12 col-sm-6 col-lg-3 course-block completed_course_<?php echo $course['user_status']; ?>">
                    <div class="course-block-content classe">
                        <div class="area1 course-cover">
                            <!-- IMG COURSE -->
                            <a href="#">
                                <?php if (DataExists($course)): ?>
                                    <div class="data_chiusura">
                                        <div class="corso_scaduto2">
                                        <span class="fa-stack fa-lg">
                                            <i class="fa fa-calendar  fa-stack-1x"></i>
                                            <i class="fa fa-ban  fa-stack-2x text-danger"></i>
                                        </span>
                                        </div>
                                        <div class="giorno"><?php echo GetCourseDay($course) ?></div>
                                        <div class="mese"><?php echo GetCourseMonth($course) ?></div>
                                        <div class="anno"><?php echo GetCourseYear($course) ?></div>
                                    </div>
                                <?php endif; ?>
                                <div class="area2">
                                    <h1><?php echo $course['name']; ?></h1>
                                    <div class="icon">&raquo;</div>
                                    <div style="clear:both;"></div>
                                    <p class="course_support_info1">
                                    <p class="descrizione_corso"><?php echo TruncateText($course['description'], 150); ?></p>
                                    <p class="extra">
                                    <span class="utenti">
                                        <i class="fa fa-users" aria-hidden="true"></i>
                                        <span
                                            class="utenti_numero">&nbsp<?php echo $course['enrolled'] . '&nbsp;' . Lang::t('_COURSE_USERISCR', 'course') . '&nbsp;' ?></span>
                                    </span>
                                        <span class="stato_corso"><i class="fa fa-chevron-circle-right"
                                                                     aria-hidden="true"></i>
                                        <span
                                            class="stato_corso_stato">&nbsp<?php echo Lang::t($this->cstatus[$course['course_status']], 'course') ?></span>
                                    </span>
                                    </p>
                                    <?php if (!empty($access['expiring_in']) && $access['expiring_in'] < 5) : ?>
                                        <p class="scadenza">
                                            <i class="fa fa-hourglass-half"
                                               aria-hidden="true"></i>&nbsp<?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <!-- COURSE IMAGE -->
                                <?php if ($course['use_logo_in_courselist']) : ?>
                                    <img class="portrait" width="100%"
                                         src="<?php echo GetCourseImage($course, $path_course) ?>" alt=""/>
                                <?php endif; ?>
                            </a>
                        </div>

                        <div class="area3 classe">
                            <div class="categoria_corso">
                                <a href="#" class="tooltips" title="CATEGORIA DEL CORSO">
                                    <i class="fa fa-folder-open-o" aria-hidden="true"></i> </a>
                                <span class="categoria_corso_nome"> <?php echo GetCategory($course) ?></span>
                            </div>
                            <div class="studente">
                                <a href='#' class='tooltips' id='livello' title='LIVELLO'>
                                    <i class="fa fa-graduation-cap" aria-hidden="true"></i></a>
                                <?php echo $this->levels[$course['level']] ?>
                                <?php if (!empty($access['expiring_in']) && $access['expiring_in'] < 30) : ?>
                                    <?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                                <?php endif; ?>
                            </div>
                            <div style="clear:both"></div>
                            <div class="linea">
                                <hr class="style7">
                            </div>
                            <div class="box_edizioni">
                                <?php
                                if (!empty($display_info) && isset($display_info[$course['idCourse']])) {

                                    foreach ($display_info[$course['idCourse']] as $key => $info) {
                                        echo '<div class="edizioni">';
                                        echo '<div class="edizioni_nome">';
                                        echo '<span>Edizione:</span>' . $info->name;
                                        echo '</div>';
                                        $start_date = $info->date_info['date_begin'];
                                        $end_date = $info->date_info['date_end'];
                                        $_start_time = $start_date != "" && $start_date != "0000-00-00 00:00:00" ? Format::date($start_date, 'date') : "";
                                        $_clock_time = $start_date != "" && $start_date != "0000-00-00 00:00:00" ? Format::date($start_date, 'time') : "";
                                        $_end_time = $end_date != "" && $end_date != "0000-00-00 00:00:00" ? Format::date($end_date, 'date') : "";
                                        $_clockend_time = $end_date != "" && $end_date != "0000-00-00 00:00:00" ? Format::date($end_date, 'time') : "";
                                        echo '<div class="edizioni_cal">';
                                        echo '<a href="#" class="tooltips" id="classe_data_start" title="DATA INIZIO"><div class="edizioni_start"><i class="fa  fa-2x fa-calendar-check-o" aria-hidden="true"></i>&nbsp;<span class="text_time">';
                                        echo $_start_time . '&nbsp;<i class="fa fa-2x fa-clock-o" aria-hidden="true"></i>&nbsp;' . $_clock_time;
                                        echo '</span></a></div>';
                                        echo '<a href="#" class="tooltips" id="classe_data_end" title="DATA FINE"><div class="edizioni_end"><i class="fa fa-2x fa-calendar-times-o" aria-hidden="true"></i>&nbsp;<span class="text_time_end">';
                                        echo $_end_time . '&nbsp;<i class="fa fa-2x fa-clock-o" aria-hidden="true"></i>&nbsp;' . $_clockend_time;
                                        echo '</a></div>';
                                        echo '<div style="clear:both"></div>';
                                        echo '</span></div>';

                                        echo '<a href="#" class="tooltips" id="luogo" title="LUOGO"><div class="luogo"><i class="fa fa-2x fa-map-marker" aria-hidden="true"></i>&nbsp;';
                                        echo $info->date_info['location'];
                                        echo '</a></div>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="linea">
                                <hr class="style7">
                            </div>
                            <div class="stato">
                                <?php
                                if ($course['user_status'] == '2') echo "<p class='completo'><a href='#' class='tooltips' class='statoclass' title='STATO:COMPLETATO'><i class='fa fa-check-circle fa-2x' aria-hidden='true'></i></p></a>";
                                if ($course['user_status'] == '1') echo "<p class='attesa'><a href='#' class='tooltips' id='statoclass' title='STATO:IN CORSO'><i class='fa fa-check-circle fa-2x'  aria-hidden='true'></i></a></p>";
                                if ($course['user_status'] == '0') echo "<p class='non_pubblicato'><a href='#' class='tooltips' id='statoclass' title='STATO: NON PUBBLICATO'><i class='fa fa-check-circle fa-2x'  aria-hidden='true'></i></a></p>"

                                ?>
                            </div>
                        </div>

                        <div class="area4">
                            <?php if ($course['can_enter']['can']) { ?>
                                <a class="enter_course"
                                   href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>">
                                    <?php echo $keyword != "" ? Layout::highlight($course['name'], $keyword) : Lang::t('_USER_STATUS_ENTER', 'catalogue'); ?>
                                </a>
                            <?php } else {
                                echo Get::img('standard/locked.png', Lang::t('_' . strtoupper($course['can_enter']['reason']), 'standard'));
                                echo ' ' . ($keyword != "" ? Layout::highlight($course['name'], $keyword) : $course['name']);
                            }
                            ?>
                        </div>
                    </div>
                    <?php /*</li>*/ ?>
                </div>
            <?php endforeach; ?>
            <?php /*</ul>*/ ?>
            <?php /*</ul>*/ ?>


            <?php
            // fine classroom
            }
            ?>

        </div>
    </div>
</div>