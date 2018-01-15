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


function dataEndExists($the_course)
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

function typeOfCourse ($t) {
    switch ($t) {
       case "elearning":
         return Lang::t('_ELEARNING', 'catalogue');       
       case "classroom":
            return Lang::t('_CLASSROOM_COURSE', 'cart');       
       case "all":
            return Lang::t('_ALL_COURSES', 'standard');       
    }
    return '';
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


<div id='container1_<?php echo $course_state; ?>'>
    <h1 class="page-header col-xs-12"><strong><?php echo typeOfCourse($filter_type); ?></strong></h1>
    <div class="clearfix" id='mia_area_<?php echo $stato_corso; ?>'>
        <?php if (empty($courselist)) : ?>
            <p><?php echo Lang::t('_NO_CONTENT', 'standard'); ?></p>
        <?php endif; ?>
        <?php foreach ($courselist as $course){  ?>
        <div class="col-xs-12 col-md-4">
            <div class="course-box"> <!-- NEW BLOCK -->
                    <div class="course-box__item">
                        <div class="course-box__title icon--filter-<?php echo $course['user_status']; ?>"><?php echo TruncateText($course['name'], 100); ?></div>
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
                            <?php echo TruncateText($course['box_description'], 120); ?>
                        </div>
                    </div>
                    <?php if (dataEndExists($course)) { // if exists end course, show it ?>
                    <div class="course-box__item course-box__item--half">
                        <div class="course-box__date-text">
                            <span><?php echo Lang::t('_CLOSING_DATA', 'course') ?></span><br>
                            <?php echo GetCourseDay($course)?>&nbsp;<?php echo GetCourseMonth($course)?>&nbsp;<?php echo GetCourseYear($course);?>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="course-box__item course-box__item<?php if (dataEndExists($course)) { echo '--half'; } ?>">
                        <?php if ($course['can_enter']['can']) { ?>
                        <a class="forma-button forma-button--orange-hover forma-button--full" title="<?php echo Util::purge($course['name']); ?>"
                        href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>"<?php echo($course['direct_play'] == 1 && $course['level'] <= 3 && $course['first_lo_type'] == 'scormorg' ? ' rel="lightbox"' : ''); ?>>
                            <span class="forma-button__label"> <?php echo Lang::t('_USER_STATUS_ENTER', 'catalogue'); ?></span>
                        </a>
                        <?php } else { ?>
                        <a class="forma-button forma-button--disabled" href="javascript:void(0);">
                            <span class="forma-button__label">
                                <?php echo Lang::t('_DISABLED', 'course') ?>
                            </span>
                        </a>
                    <?php } ?>
                    </div>
                </div>
            </div>
            <?php } // end foreach ?>
        </div>
    </div>
</div>   