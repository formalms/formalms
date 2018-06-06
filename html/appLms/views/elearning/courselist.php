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

function getStringPresence($presence){
    $strPresence = Lang::t('_NO', 'standard');
    if($presence==1) $strPresence = Lang::t('_YES', 'standard') ;
    
    return $strPresence;
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


<script language="javascript">
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
                                                r: 'elearning/self_unsubscribe',
                                                id_course: id_course,
                                                id_date: id_date 
                                            }
                                        );
                                        posting.done(function (responseText) {
                                            var ft = $("#course_search_filter_text").val();
                                            var ctype = $("#course_search_filter_type").selectpicker().val();
                                            var category = $('#course_search_filter_cat').selectpicker().val();
                                            var cyear = $("#course_search_filter_year").selectpicker().val();
                                            var json_status = $('.js-label-menu-filter.selected').attr('data-value');
                                            $("#div_course").html("<br><p align='center'><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>");
                                            var posting = $.get( 'ajax.server.php?r=elearning/all&rnd=<?php echo time(); ?>&filter_text=' + ft + '&filter_type=' + ctype + '&filter_cat=' + category + '&filter_status=' + json_status + '&filter_year=' + cyear, {});
                                            posting.done(function(responseText){
                                                    $("#div_course").html(responseText);
                                            });                                    
                                        });
                                        posting.fail(function () {
                                            alert('unsubscribe failed')
                                        })                                
                                        $(this).dialog("close");
                                    },
                            <?php echo Lang::t('_UNDO', 'standard')?>: function () {                                                                 
                                $(this).dialog("close");
                            }
                        },
                        close: function (event, ui) {
                            $(this).remove();
                        }
                    });
    }


</script>


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
    <div class="clearfix row" id='mia_area_<?php echo $stato_corso; ?>'>
        <?php if (empty($courselist)) : ?>
		<div class="col-xs-12">
            <p><?php echo Lang::t('_NO_CONTENT', 'standard'); ?></p>
		</div>
        <?php endif; ?>



        <?php foreach ($courselist as $course){  ?>
        <div class="col-xs-12 col-md-4 col-lg-3 mycourses-list">
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

                        <?php if($course['auto_unsubscribe']==2 || $course['auto_unsubscribe']==1 || $course["course_demo"] ): ?>						
                            <div class="course-box__options dropdown pull-right">
                                <div class="dropdown-toggle" id="courseBoxOptions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								    <i class="glyphicon glyphicon-option-horizontal"></i> 
							    </div>   

                                <ul class="dropdown-menu" aria-labelledby="courseBoxOptions">
								      
                                     <?php if($course['auto_unsubscribe']==2 || $course['auto_unsubscribe']==1 ): ?>
								        <li>
                                            <a href='javascript:confirmDialog(<?php echo "\"".$course['name']."\",".$course['idCourse'].",".key($display_info[$course['idCourse']]) ?>)'><?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course') ?></a>    
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php if ($course["course_demo"]) :?>
								        <li>
                                            <a href="index.php?r=catalog/downloadDemoMaterial&amp;course_id=<?php echo $course['idCourse'] ?>"><?php echo Lang::t('_COURSE_DEMO', 'course') ?></a>
                                        </li>
                                    <?php endif; ?>                                    
							    </ul> 
						    </div>
                        <?php endif; ?>
                        
                        <div class="course-box__desc">
                            <?php echo TruncateText($course['box_description'], 120); ?>
                        </div>
                    </div> 
                    
                    <!-- DATA CLOSING ONLY IF SET -->
                    <?php
                        $dataClosing = (int)GetCourseYear($course);
                        if ( $dataClosing > 0 ) {
                    ?>
                        <div class="course-box__item course-box__item--half">
                            <div class="course-box__date-text">
                                <span><?php echo Lang::t('_CLOSING_DATA', 'course') ?></span>:
                                <?php echo GetCourseDay($course)?>&nbsp;<?php echo GetCourseMonth($course)?>&nbsp;<?php echo $dataClosing;?>
                            </div>
                        </div>
                    <?php
                        }
                    ?>

                    <?php 
                        
                        if ($course['course_type']=='classroom') { 
                        // if exists end course, show it ?>
                    <div class="course-box__item course-box__item--half">
     
						<?php
						     
                             $vett_course = array();
                             $day_lessons = array();
                             $next_lesson = array();
                             $vett_course = $display_info[$course['idCourse']];
                             
                             foreach($vett_course as $date){
                                   
                                   foreach($date->date_info_day as $key => $value ){
                                       
                                       $day = array(
                                                       "name" => $date->name,
                                                       "code" => $date->code,
                                                       "startDate" => $value['date_begin'], 
                                                       "endDate" => $value['date_end'],
                                                       "location" => $value['location'] ,
                                                       "teacher" => $value['teacher'] ,
                                                       "presence" => $value['presence']
                                                       );
                                       
                                       if($value['nextMeet']==0){
                                           $day_lessons[] = $day;                                                       
                                       } else{
                                           $next_lesson[] = $day;      
                                       }    
                                   }
                             }
                             
           
						?>
						<?php if ($day_lessons && !empty($day_lessons) || $next_lesson && !empty($next_lesson)) : ?>
						<p class="course-box__show-dates js-course-box-open-dates-modal">
                        <i class="glyphicon glyphicon-play"></i> 
                             &nbsp; <?php echo Lang::t('_MEETING_LESSON', 'standard') ?></p>
						<div class="course-box__modal">
							<div class="course-box__modal__header">
								<p class="course-box__modal__title"><?php echo $course['name']; ?></p>
								<button type="button" class="close-button js-course-box-close-dates-modal">
									<span class="close-button__icon"></span>
									<span class="close-button__label"><?php echo Lang::t('_CLOSE', 'standard') ?></span>
								</button>
							</div>
                            
                            
                           
							<div class="course-box__modal__content">
						
                                <?php if (count($next_lesson) > 0) : ?>
                                
                                <!-- NEXT MEETING -->    
                        		<div class="course-box__modal__entry">
									
                                    <p class="course-box__modal__title"><?php echo Lang::t('_NEXTMEETING', 'standard') ?></p>
									 
                                    <table class="course-box__modal__lesson">
										<tr>
											<td><?php echo Lang::t('_NAME', 'standard') ?>:</td>
											<td><?php echo $next_lesson[0]['name']; ?></td>
										</tr>
										<tr>
											<td><?php echo Lang::t('_CODE', 'standard') ?>:</td>
											<td><?php echo $next_lesson[0]['code']; ?></td>
										</tr>
										<tr>
											<td><?php echo Lang::t('_START', 'standard') ?>:</td>
											<td><?php echo $next_lesson[0]['startDate']; ?></td>
										</tr>
										<tr>
											<td><?php echo Lang::t('_END', 'standard') ?>:</td>
											<td><?php echo $next_lesson[0]['endDate']; ?></td>
										</tr>
										<tr>
											<td><?php echo Lang::t('_CLASSROOM', 'standard') ?>:</td>
											<td><?php echo $next_lesson[0]['location']; ?></td>
										</tr>
                                        
									</table>
								    
                                    
                                </div>
                                <?php endif; ?>
                                
                                <!-- TEACHER -->
                                <?php if (count($day_lessons[0]['teacher']) > 0) : ?>
                                <div class="course-box__modal__entry">
                                    <p class="course-box__modal__title"><?php echo Lang::t('_COURSE_TEACHERS', 'course') ?></p>
                                    <table class="course-box__modal__lesson" border=0>
                                        <?php
                                             for ($j = 0; $j < count($day_lessons[0]['teacher']); $j++){
                                                 echo "<tr width='100%' >
                                                    <td>".Lang::t('_LEVEL_6', 'levels').":</td>
                                                    <td>
                                                        <a href='mailto:".$day_lessons[$j]['teacher'][$j]['email']."'><img src='".Get::tmpl_path() . "images/emoticons/email.gif'></a> &nbsp;
                                                        ".$day_lessons[$j]['teacher'][$j]['firstname']. " 
                                                        ".$day_lessons[$j]['teacher'][$j]['lastname']."</td>
                                         
                                                    </tr>";
                                             }
                           
                                        ?>                                
                                    </table>
                                
                                </div>
                                <?php endif; ?>
                                
                                
                                 <!-- LESSONS PREV -->
								<?php if (count($day_lessons) > 1) : ?>
								<div class="course-box__modal__entry">
									<p class="course-box__modal__title"><?php echo Lang::t('_MEETING_LESSON', 'standard') ?></p>
									<?php for ($i = 0; $i < count($day_lessons); $i++) : ?>
										<table class="course-box__modal__lesson">
											<tr>
												<td><?php echo Lang::t('_NAME', 'standard') ?>:</td>
												<td><?php echo $day_lessons[$i]['name']; ?></td>
											</tr>
											<tr>
												<td><?php echo Lang::t('_CODE', 'standard') ?>:</td>
												<td><?php echo $day_lessons[$i]['code']; ?></td>
											</tr>
											<tr>
												<td><?php echo Lang::t('_START', 'standard') ?>:</td>
												<td><?php echo $day_lessons[$i]['startDate']; ?></td>
											</tr>
											<tr>
												<td><?php echo Lang::t('_END', 'standard') ?>:</td>
												<td><?php echo $day_lessons[$i]['endDate']; ?></td>
											</tr>
											<tr>
												<td><?php echo Lang::t('_CLASSROOM', 'standard') ?>:</td>
												<td><?php echo $day_lessons[$i]['location']; ?></td>
											</tr>
                                            
                                            <?php if ($day_lessons[$i]['presence'] > 0) : ?>
                                            <tr>
                                                <td><?php echo Lang::t('_IS_PRESENCE', 'standard') ?>:</td>
                                                <td><?php echo getStringPresence($day_lessons[$i]['presence']); ?></td>
                                            </tr>                                            
                                            <?php endif; ?>
       
										</table>
									<?php endfor; ?>
								</div>
								<?php endif; ?>
							</div>
			
            
            
                            <div class="close-button js-course-box-close-dates-modal course-box__modal__footer">
                                <button type="button" class="forma-button"><?php echo Lang::t('_CLOSE', 'standard') ?></button>
                            </div>

            
            
						</div>
						<?php endif; ?>
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