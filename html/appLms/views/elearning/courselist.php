<?php if( $use_label ) : ?>
<div class="container-back">
	<a href="index.php?r=elearning/show&id_common_label=-2">
		<span>&lsaquo;&lsaquo; <?php echo Lang::t('_BACK_TO_LABEL', 'course') ?></span>
	</a>
</div>
<?php endif; ?>

<?php $contCourse=0; ?>

<?php
// draw search
$_model = new ElearningLms();
$_auxiliary = Form::getInputDropdown('', 'course_search_filter_year', 'filter_year', $_model->getFilterYears(Docebo::user()->getIdst()), 0, '');

$this->widget('tablefilter', array(
    'id' => 'course_search',
    'filter_text' => "",
    // 'auxiliary_filter' => Lang::t('_SEARCH', 'standard').":&nbsp;&nbsp;&nbsp;".$_auxiliary,
    'auxiliary_filter' => '<span class="filter-label">'.Lang::t('_YEAR', 'standard').":</span>&nbsp;&nbsp;&nbsp;".$_auxiliary,
    'js_callback_set' => 'course_search_callback_set',
    'js_callback_reset' => 'course_search_callback_reset',
    'css_class' => 'tabs_filter'
));
?>

<div  id="container1_<?php echo $stato_corso; ?>">
    <?php /*<div id="cbp-vm" class="cbp-vm-switcher cbp-vm-view-grid">*/ ?>
    <div id="cbp-vm">
        <?php /*
        <p align="right" >
            <div class="cbp-vm-options1" align="right">
                <a href="#"   class="grid" onclick="javascript:addGrid_<?php echo $stato_corso; ?>()"><span class='glyphicon glyphicon-th'></span></a> 
                <a href="#"  class="list" onclick="javascript:addList_<?php echo $stato_corso; ?>()"><span class='glyphicon glyphicon-align-justify'></span></a>
            </div>
        </p>
        */ ?>          
        <?php /*<div class="well well-sm"><strong><?php echo Lang::t('_ELEARNING', 'catalogue'); ?></strong></div>*/ ?>

        <h1 class="page-header"><strong><?php echo Lang::t('_ELEARNING', 'catalogue'); ?></strong></h1>

        <?php /*<ul class="grid" id='mia_area_<?php echo $stato_corso; ?>'>*/ ?>
        <div class="clearfix" id='mia_area_<?php echo $stato_corso; ?>'>                       
        <?php foreach( $courselist as $course ) : ?> 
           <?php
                $nameCategory =  str_replace( "/", " - ", substr($course['nameCategory'],1));
                if($nameCategory=="") {
                    $nameCategory = Lang::t('_CATEGORY', 'standard').": ".Lang::t('_NO_CATEGORY', 'standard') ;
                } else {
                    $nameCategory = Lang::t('_CATEGORY', 'standard').": ".substr($course['nameCategory'],6);
                }                 
            ?>            
            <?php /*<li class="completed_course_<?php  echo $course['user_status']; ?>">*/ ?> 
            <div class="col-xs-12 col-sm-6 col-lg-4 course-block completed_course_<?php  echo $course['user_status']; ?>">           
                <div class="course-block-content">
                    <?php if($course['use_logo_in_courselist'] && $course['img_course']) : ?>
                        <div class="area1 course-cover">            
                            <!-- IMG COURSE -->   
                            <img
                                width="100%"
                                src="<?php echo $path_course.$course['img_course']; ?>"
                                alt="<?php echo Util::purge($course['name']); ?>" />
                        </div>
                    <?php endif; ?>
                    <?php if($course['use_logo_in_courselist'] && !$course['img_course']) : ?>
                        <div class="area1">   
                            <img
                                width="70px"
                                height="70px"  
                                src="<?php echo Get::tmpl_path().'images/course/course_nologo.png'; ?>"
                                alt="<?php echo Util::purge($course['name']); ?>" />
                        </div>
                    <?php endif; ?>
                    <div class="area2" >
                        <h1><?php echo $course['name'];     ?></h1>
                    </div>
                    <div  class="area3">
                        <?php
                            echo Lang::t($this->ustatus[ $course['user_status'] ], 'course').''.Lang::t('_USER_LVL', 'course', array('[level]' => '<b>'.$this->levels[ $course['level'] ].'</b>'));
                            $acl_man = Docebo::user()->getAclManager();
                            $levels = CourseLevel::getLevels();
                            
                            while(list($num_lv, $name_level) = each($levels)) {
                                if(CourseLevel::isTeacher($num_lv)) {
                                    
                                }
                                if($course['level_show_user'] & (1 << $num_lv)) {
                                    if(CourseLevel::isTeacher($num_lv)) {
                                        echo "&nbsp;" . $name_level . ":&nbsp;";
                                        $users =& $acl_man->getUsers( Man_Course::getIdUserOfLevel($course['idCourse'], $num_lv, $course['course_edition']) );
                                        if(!empty($users)) {
                                            $first = true;
                                            while(list($id_user, $user_info) = each($users)) {
                                                if($first) $first = false;
                                                else echo(', ');
                                                // echo '<a href="index.php?modname=course&amp;op=viewprofile&amp;id_user='.$id_user.'">' . $acl_man->getConvertedUserName($user_info) . '</a>';
                                                echo '<b>'.$acl_man->getConvertedUserName($user_info).'</b>';
                                            } // end while
                                        } // end if
                                    }
                                } // end if
                            } // end while
                        ?>
                        <p class="text-justify-paragrafo">
                            <p class="course_support_info1">
                                <?php
                                echo Lang::t('_COURSE_INTRO', 'course', array(
                                    '[course_type]'        => $course['course_type'],
                                    '[create_date]'        => $course['create_date'],
                                    '[enrolled]'        => $course['enrolled'],
                                    '[course_status]'    => Lang::t($this->cstatus[$course['course_status']], 'course')
                                ));
                                ?>
                            </p> 
                            <?php if(!empty($access['expiring_in']) && $access['expiring_in'] < 30) : ?>
                                <p class="course_support_info1">
                                    <?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                                </p>
                            <?php endif; ?> 
                            <?php 
                            if((!empty($course['can_enter']['expiring_in']) && $course['can_enter']['can']==false && $course['can_enter']['expiring_in'] <= 0) || ($course['can_enter']['expiring_in'] == 0 && $course['can_enter']['can']==false)) : ?>
                                <p class="course_support_info1">
                                    <?php echo Lang::t('_EXPIRED', 'course'); ?>
                                </p>
                            <?php endif; ?>                    
                            <p class="course_support_info1">
                                <!-- CODE COURSE -->
                                <!--
                                <?php if($course['code']) { ?>
                                    <i style="font-size:.88em">[<?php echo $keyword != "" ? Layout::highlight($course['code'], $keyword) : $course['code']; ?>]</i>
                                <?php } ?>
                                 -->
                                <?php if($nameCategory) { ?>
                                    <i style="font-size:.88em"><?php echo $nameCategory ?></i>
                                <?php } ?>                                    
                                <br>
                                Stato:
                                <?php
                                  if($course['user_status']=='2') echo "<img class='no_traform' src='". Get::tmpl_path().'images/lobject/completed.png'."'>"  ;
                                  if($course['user_status']=='1') echo "<img class='no_traform' src='". Get::tmpl_path().'images/lobject/attempted.png'."'>"  ;
                                  if($course['user_status']=='0') echo "<img class='no_traform' src='". Get::tmpl_path().'images/standard/unpublish.png'."'>" ;
                                  if(!$course['can_enter']['can']) echo "<img class='no_traform' src='". Get::tmpl_path().'images/standard/locked.png'."'>" ;
                                ?>                            
                            </p>
                            <?php
                                $smodel = new SubscriptionAlms();
                                if ($smodel->isUserWaitingForSelfUnsubscribe(Docebo::user()->idst, $course['idCourse'])) {
                                    echo '<p style="padding:.4em">'.Lang::t('_UNSUBSCRIBE_REQUEST_WAITING_FOR_MODERATION', 'course').'</p>';
                                } else {
                                //auto unsubscribe management: create a link for the user in the course block
                                $_can_unsubscribe = ($course['auto_unsubscribe']==1 || $course['auto_unsubscribe']==2);
                                $_date_limit = $course['unsubscribe_date_limit'] != "" && $course['unsubscribe_date_limit'] != "0000-00-00 00:00:00"
                                    ? $course['unsubscribe_date_limit']
                                    : FALSE;
                            ?>
                            <?php if ($_can_unsubscribe): ?>
                                <p class="course_support_info">
                                    <?php if ($_date_limit !== FALSE && $_date_limit < date("Y-m-d H:i:s")) {
                                        echo '';
                                    } else { ?>                     
                                        <a href="index.php?r=elearning/self_unsubscribe&amp;id_course=<?php echo $course['idCourse']; ?>&amp;back=<?php echo Get::req('r', DOTY_STRING, ""); ?>"
                                             title="<?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>">
                                             <?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>
                                        </a>
                                    <?php
                                        if ($_date_limit) echo '&nbsp;('.Lang::t('_UNTIL', 'standard').' '.Format::date(substr($_date_limit, 0, 10), 'date').')';
                                    ?>
                                    <?php } //endif ?>
                                </p>
                        </p>

                        <?php endif;
                            }
                            unset($smodel);
                        ?>                             
                        <!--
                            <?php if ($course['can_enter']['can']) { ?>
                             <a href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>">Apri corso</a>
                            <?php } else {    
                                    echo Get::img('standard/locked.png', Lang::t('_'.strtoupper($course['can_enter']['reason']), 'standard'));
                                    echo ' '.($keyword != "" ? Layout::highlight($course['name'], $keyword) : $course['name']);
                                }
                            ?>
                        -->         
                    </div> 
                    <div class="area4">
                        <?php if ($course['can_enter']['can']) { ?>
                            <a class="enter_course" title="<?php echo Util::purge($course['name']); ?>" href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>"<?php echo ( $course['direct_play'] == 1 && $course['level'] <= 3 && $course['first_lo_type'] == 'scormorg' ? ' rel="lightbox"' :'' ); ?>>
                                <?php echo $keyword != "" ? Layout::highlight($course['name'], $keyword) : Lang::t('_USER_STATUS_ENTER', 'catalogue'); ?>
                            </a>
                        <?php } else {    
                                echo ''.($keyword != "" ? Layout::highlight($course['name'], $keyword) : $course['name']);
                            }
                        ?>                                              
                    </div>
                </div> 
            <?php /*</li>*/ ?>
            </div>
        <?php endforeach; ?>                     
        <?php /*</ul>*/ ?>
        </div>
        <br>

        <?php /*<div class="well well-sm"><strong><?php echo Lang::t('_CLASSROOM', 'standard'); ?></strong></div>*/ ?>

        <h1 class="page-header"><strong><?php echo Lang::t('_CLASSROOM', 'standard'); ?></strong></h1>
                              
        <?php /*<ul class="grid" id='mia_area_class_<?php echo $stato_corso; ?>'>*/ ?>
        <div class="clearfix" id="mia_area_class_<?php echo $stato_corso; ?>">
        <?php if( empty($courselistClassroom) ) : ?>
            <p><?php echo Lang::t('_NO_CONTENT', 'standard'); ?></p>
        <?php endif; ?>
        <?php $unsubscribe_call_arr =array(); ?>
        <?php foreach( $courselistClassroom as $course ) : ?>
            <?php
                $nameCategory =  str_replace( "/", " - ", substr($course['nameCategory'],1));
                if($nameCategory=="") {
                    $nameCategory = Lang::t('_CATEGORY', 'standard').": ".Lang::t('_NO_CATEGORY', 'standard') ;
                } else {
                    $nameCategory = Lang::t('_CATEGORY', 'standard').": ".substr($course['nameCategory'],6);
                }                 
            ?>              
            <?php /*<li class="completed_course_<?php  echo $course['user_status']; ?>">*/ ?>
            <div class="col-xs-12 col-sm-6 col-lg-4 course-block completed_course_<?php  echo $course['user_status']; ?>">
                <div class="course-block-content">
                    <div class="area1">
                        <!-- IMG COURSE -->   
                         <?php if($course['use_logo_in_courselist'] && $course['img_course']) : ?>
                            <img
                                class="img-thumbnail"
                                width="70px"
                                height="70px" 
                                src="<?php echo $path_course.$course['img_course']; ?>"
                                alt="<?php echo Util::purge($course['name']); ?>" />
                        <?php endif; ?>
                        <?php if($course['use_logo_in_courselist'] && !$course['img_course']) : ?>
                            <img
                                width="70px"
                                height="70px"  
                                src="<?php echo Get::tmpl_path().'images/course/course_nologo.png'; ?>"
                                alt="<?php echo Util::purge($course['name']); ?>" />
                        <?php endif; ?>
                    </div>
                    <div class="area2">
                        <h1><?php echo $course['name']; ?></h1>
                    </div>
                    <div class="area3">
                        <p class="course_support_info">
                            <?php echo Lang::t($this->ustatus[ $course['user_status'] ], 'course').''.Lang::t('_USER_LVL', 'course', array('[level]' => '<b>'.$this->levels[ $course['level'] ].'</b>')); ?>
                            <?php if(!empty($access['expiring_in']) && $access['expiring_in'] < 30) : ?>
                                <?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                            <?php endif; ?>
                        </p>
                        <?php
                        if (!empty($display_info) && isset($display_info[$course['idCourse']])) {
                           // echo '<p class="course_support_info">';
                            echo '<br>';
                            foreach ($display_info[$course['idCourse']] as $key => $info) {
                                $_start_time = $info->start_date != "" && $info->start_date != "0000-00-00 00:00:00" ? Format::date($info->start_date, 'datetime') : "";
                                $_end_time = $info->end_date != "" && $info->end_date != "0000-00-00 00:00:00" ? Format::date($info->end_date, 'datetime') : "";
                                // echo '<li style="width: 98%;">';//.($info->code != "" ? '['.$info->code.'] ' : "").$info->name.' '
                                $start_date =$info->date_info['date_begin'];
                                $end_date =$info->date_info['date_end'];
                                $_start_time = $start_date != "" && $start_date != "0000-00-00 00:00:00" ? Format::date($start_date, 'datetime') : "";
                                $_end_time = $end_date != "" && $end_date != "0000-00-00 00:00:00" ? Format::date($end_date, 'datetime') : "";
                                echo '<b>'.Lang::t('_COURSE_BEGIN', 'certificate').'</b>: '.($_start_time ? $_start_time : "- ").'; <br>'
                                    .'<b>'.Lang::t('_COURSE_END', 'certificate').'</b>: '.($_end_time ? $_end_time : "- ").";<br> ";
                                echo ($info->date_info['location'] != "" ? '<b>'.Lang::t('_LOCATION', 'standard').'</b>: '.$info->date_info['location'] : "");
                                $_text_key = property_exists($info, 'max_participants') && $info->max_participants > 0
                                    ? '_COURSE_INTRO_WITH_MAX'
                                    : '_COURSE_INTRO';
                                echo "<br />".Lang::t($_text_key, 'course', array(
                                    '[course_type]'        => $course['course_type'],
                                    '[enrolled]'        => $info->enrolled,
                                    '[max_subscribe]' => property_exists($info, 'max_participants') ? $info->max_participants : 0,
                                    '[course_status]'    => ($info->overbooking > 0 && $info->max_participants > 0 && $info->students >= $info->max_participants)
                                        ? Lang::t('_USER_STATUS_OVERBOOKING', 'subscribe')
                                        : $info->status,
                                ));
                               // echo '</li>';
                            }
                            echo '';
                          //  echo '</p>';
                        }
                        if($nameCategory) { ?>
                            <br><i style="font-size:.88em"><?php echo $nameCategory ?></i>
                        <?php } ?>                           
                        Stato: 
                        <?php
                            if($course['user_status']=='2') echo "<img src='". Get::tmpl_path().'images/lobject/completed.png'."'>"  ;
                            if($course['user_status']=='1') echo "<img src='". Get::tmpl_path().'images/lobject/attempted.png'."'>"  ;
                            if($course['user_status']=='0') echo "<img src='". Get::tmpl_path().'images/lobject/ab-initio.png'."'>"
                        ?>                              
                    </div>
                    <div class="area4">    
                    <?php if ($course['can_enter']['can']) { ?>
                        <a class="enter_course" href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>">
                            <?php echo $keyword != "" ? Layout::highlight($course['name'], $keyword) : Lang::t('_USER_STATUS_ENTER', 'catalogue'); ?>
                        </a>
                        <?php } else {
                            echo Get::img('standard/locked.png', Lang::t('_'.strtoupper($course['can_enter']['reason']), 'standard'));
                            echo ' '.($keyword != "" ? Layout::highlight($course['name'], $keyword) : $course['name']);
                        }
                    ?>                         
                    </div>
                </div>              
            <?php /*</li>*/ ?>
            </div>
            <?php endforeach; ?>     
        <?php /*</ul>*/ ?>
        </div>
    </div>          
</div>
