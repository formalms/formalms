<?php if( $use_label ) : ?>
<div class="container-back">
	<a href="index.php?r=elearning/show&id_common_label=-2">
		<span>&lsaquo;&lsaquo; <?php echo Lang::t('_BACK_TO_LABEL', 'course') ?></span>
	</a>
</div>
<?php endif; ?>



        <?php
             $contCourse=0;
         ?>
                   
                   
<fieldset >
  <legend><?php echo Lang::t('_ELEARNING', 'catalogue'); ?></legend>       
                   
                 <div class="row">  
                 <ul class="grid cs-style-3">
                         
                <?php if( empty($courselist) ) : ?>

                    <p><?php echo Lang::t('_NO_CONTENT', 'standard'); ?></p>

                <?php endif; ?>          
         
                 
                 <?php foreach( $courselist as $course ) : ?>
                   <?php
                           
 
                    $nameCategory =  str_replace( "/", " - ", substr($course['nameCategory'],1));
                    if($nameCategory=="") {
                        $nameCategory = Lang::t('_CATEGORY', 'standard').": ".Lang::t('_NO_CATEGORY', 'standard') ;
                    }else{
                        $nameCategory = Lang::t('_CATEGORY', 'standard').": ".substr($course['nameCategory'],6);
                    }                 
                       ?>               
             
                <li>
                    <figure>
                     
                <!-- IMG COURSE -->   
                 <?php if($course['use_logo_in_courselist'] && $course['img_course']) : ?>
                    <img width="100%" 
                        src="<?php echo $path_course.$course['img_course']; ?>"
                        alt="<?php echo Util::purge($course['name']); ?>" />

                
                <?php endif; ?>
                <?php if($course['use_logo_in_courselist'] && !$course['img_course']) : ?>
                            
                            <img width="100%" 
                                 src="<?php echo Get::tmpl_path().'images/course/course_nologo.png'; ?>"
                                alt="<?php echo Util::purge($course['name']); ?>" />
                <?php endif; ?>                        

                        <figcaption>
             
 
                                        <table width='100%' border="0">
                                          <tr>
                                               <td width='55%' valign=middle><?php echo $course['name'];     ?></td>
                                               <td align=right >
                                                           

                                   
                                                 <?php if ($course['can_enter']['can']) { ?>
                                                    <a title="<?php echo Util::purge($course['name']); ?>" href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>"<?php echo ( $course['direct_play'] == 1 && $course['level'] <= 3 && $course['first_lo_type'] == 'scormorg' ? ' rel="lightbox"' :'' ); ?>>
                     
                                                        <?php echo $keyword != "" ? Layout::highlight($course['name'], $keyword) : Lang::t('_USER_STATUS_ENTER', 'catalogue'); ?>
                                                        
                                                    </a>
                                                    
                   
                                                    <?php
                                                        } else {    
                                                        
                                                        echo ''.($keyword != "" ? Layout::highlight($course['name'], $keyword) : $course['name']);
                                                    }
                                                    ?>
 
                                    
                                         </td>
                                         <td align="right" width=10%>
                                           <?php
                              
                                              if($course['user_status']=='2') echo "<img class='no_traform' src='". Get::tmpl_path().'images/lobject/completed.png'."'>"  ;
                                              if($course['user_status']=='1') echo "<img class='no_traform' src='". Get::tmpl_path().'images/lobject/attempted.png'."'>"  ;
                                              if($course['user_status']=='0') echo "<img class='no_traform' src='". Get::tmpl_path().'images/standard/unpublish.png'."'>" ;
                                              if(!$course['can_enter']['can']) echo "<img class='no_traform' src='". Get::tmpl_path().'images/standard/locked.png'."'>" ;
             
                                              
                                            ?>
                                         </td>
                                          </tr>
                                        </table>
                                        <br>
                                    
                                          <br>
                                          <br>

                                        <?php
                                        echo Lang::t($this->ustatus[ $course['user_status'] ], 'course').''
                                            .Lang::t('_USER_LVL', 'course', array('[level]' => '<b>'.$this->levels[ $course['level'] ].'</b>'));
                                        ?>
             
                                <?php 
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
                            //                                echo '<a href="index.php?modname=course&amp;op=viewprofile&amp;id_user='.$id_user.'">' . $acl_man->getConvertedUserName($user_info) . '</a>';
                                                            echo '<b>'.$acl_man->getConvertedUserName($user_info).'</b>';
                                                        } // end while
                                                    } // end if
                                                }
                                            } // end if
                                        } // end while
                                    ?>
                

                    <p class="text-justify-paragrafo">
                             <p class="course_support_info">
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
                                    <p class="course_support_info">
                                        <?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                                    </p>
                                <?php endif; ?>
                                 <?php 
                                 if((!empty($course['can_enter']['expiring_in']) && $course['can_enter']['can']==false && $course['can_enter']['expiring_in'] <= 0) || ($course['can_enter']['expiring_in'] == 0 && $course['can_enter']['can']==false)) : ?>
                                    <p class="course_support_info">
                                        <?php echo Lang::t('_EXPIRED', 'course'); ?>
                                    </p>
                                <?php endif; ?>                    
                                <p class="course_support_info">

                                    <!-- CODE COURSE -->
                                    <!--
                                    <?php if($course['code']) { ?>
                                        <i style="font-size:.88em">[<?php echo $keyword != "" ? Layout::highlight($course['code'], $keyword) : $course['code']; ?>]</i>
                                    <?php } ?>
                                     -->
                                    
                                      <?php if($nameCategory) { ?>
                                        <i style="font-size:.88em"><?php echo $nameCategory ?></i>
                                    <?php } ?>                                    
                                    
    
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
                                        if ($_can_unsubscribe):
                                ?>
                                <p class="course_support_info">
                                    <?php if ($_date_limit !== FALSE && $_date_limit < date("Y-m-d H:i:s")) {
                                        echo '';
                                    } else { ?> 
                                    
                                        <table width='100%' border="0">
                                              <tr>
                                                   <td width='60%' valign=middle>&nbsp;</td>
                                                   <td align=center >
                                                               
                                        <a href="index.php?r=elearning/self_unsubscribe&amp;id_course=<?php echo $course['idCourse']; ?>&amp;back=<?php echo Get::req('r', DOTY_STRING, ""); ?>"
                                             title="<?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>">
                                             <?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>
                                        </a>
                                        </td><td>&nbsp;</td></tr></table>
                                    
                                    
                                    <?php
                                        if ($_date_limit) echo '&nbsp;('.Lang::t('_UNTIL', 'standard').' '.Format::date(substr($_date_limit, 0, 10), 'date').')';
                                    ?>
                                    <?php } //endif ?>
                                </p>
                                
 </p>

                                <?php
                                        endif;
                                    }
                                    unset($smodel);
                                ?>                             
                            
                        
                            
                            </span>    
                            <!--
                                <?php if ($course['can_enter']['can']) { ?>
                                 <a href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>">Apri corso</a>
                                <?php } else {    
                                        echo Get::img('standard/locked.png', Lang::t('_'.strtoupper($course['can_enter']['reason']), 'standard'));
                                        echo ' '.($keyword != "" ? Layout::highlight($course['name'], $keyword) : $course['name']);
                                    }
                                    ?>
                           -->         
                                    
                        </figcaption>
                    </figure>
                </li>

        <?php endforeach; ?>
        
           </ul> 
        </div>    
</fieldset>           
           <!-- CORSI AULA -->
<fieldset >
  <legend><?php echo Lang::t('_CLASSROOM', 'standard'); ?></legend>       
              <div class="row">  
                 <ul class="grid cs-style-3">       
       
       <?php if( empty($courselistClassroom) ) : ?>

                    <p><?php echo Lang::t('_NO_CONTENT', 'standard'); ?></p>

                <?php endif; ?>

                <?php $unsubscribe_call_arr =array(); ?>

                <?php foreach( $courselistClassroom as $course ) : ?>
                <?php

                    $nameCategory =  str_replace( "/", " - ", substr($course['nameCategory'],1));
                    if($nameCategory=="") {
                        $nameCategory = Lang::t('_CATEGORY', 'standard').": ".Lang::t('_NO_CATEGORY', 'standard') ;
                    }else{
                        $nameCategory = Lang::t('_CATEGORY', 'standard').": ".substr($course['nameCategory'],6);
                    }                 
                       ?>                      
                    
                    <li>

                        <figure>

                            <!-- IMG CORSO -->
                            <?php if($course['use_logo_in_courselist'] && $course['img_course']) : ?>
                                <img width='100%'
                                    src="<?php echo $path_course.$course['img_course']; ?>"
                                    alt="<?php echo Util::purge($course['name']); ?>" />
                            <?php endif; ?>
                            <?php if($course['use_logo_in_courselist'] && !$course['img_course']) : ?>
                                <img width='100%'
                                     src="<?php echo Get::tmpl_path().'images/course/course_nologo.png'; ?>"
                                    alt="<?php echo Util::purge($course['name']); ?>" />
                            <?php endif; ?>                        
                        
                        <figcaption>
                                        <table width='100%' border="0">
                                          <tr>
                                               <td width='89%' valign=middle><?php echo $course['name'];     ?></td>
                                               <td align=right valign=middle>
                                                           

                                   
                           <?php if ($course['can_enter']['can']) { ?>
                            <a href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>">
                                <?php echo $keyword != "" ? Layout::highlight($course['name'], $keyword) : Lang::t('_USER_STATUS_ENTER', 'catalogue'); ?>
                            </a>
                            <?php } else {
                                echo Get::img('standard/locked.png', Lang::t('_'.strtoupper($course['can_enter']['reason']), 'standard'));
                                echo ' '.($keyword != "" ? Layout::highlight($course['name'], $keyword) : $course['name']);
                            }
                            ?>
 
                                    
                             </td>
                             <td align="right" width=10%>
                               <?php
                  
                                  if($course['user_status']=='2') echo "<img src='". Get::tmpl_path().'images/lobject/completed.png'."'>"  ;
                                  if($course['user_status']=='1') echo "<img src='". Get::tmpl_path().'images/lobject/attempted.png'."'>"  ;
                                  if($course['user_status']=='0') echo "<img src='". Get::tmpl_path().'images/lobject/ab-initio.png'."'>"
                                ?>
                             </td>
                              </tr>
                            </table>   <br>                  
                      <p class="text-justify-paragrafo">   
                        <p class="course_support_info">
                            <?php
                            echo Lang::t($this->ustatus[ $course['user_status'] ], 'course').''
                                .Lang::t('_USER_LVL', 'course', array('[level]' => '<b>'.$this->levels[ $course['level'] ].'</b>'));
                            ?>
                        </p>
                    <!-- p class="course_support_info" -->
                        <?php
                        /* echo Lang::t('_COURSE_INTRO', 'course', array(
                            '[course_type]'        => $course['course_type'],
                            '[create_date]'        => $course['create_date'],
                            '[enrolled]'        => $course['enrolled'],
                            '[course_status]'    => Lang::t($this->cstatus[$course['course_status']], 'course')
                        ));*/
                        ?> 
                    <!-- /p -->
                    <p class="course_support_info">
                    <?php if(!empty($access['expiring_in']) && $access['expiring_in'] < 30) : ?>
                        <p class="course_support_info">
                            <?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                        </p>
                    <?php endif; ?>
                    
                     <?php if($nameCategory) { ?>
                            <i style="font-size:.88em"><?php echo $nameCategory ?></i>
                        <?php } ?>  

                     </p>   
                        <?php
                        if (!empty($display_info) && isset($display_info[$course['idCourse']])) {
                           // echo '<p class="course_support_info">';
                            echo '<ul class="action-list">';
                            foreach ($display_info[$course['idCourse']] as $key => $info) {
                                $_start_time = $info->start_date != "" && $info->start_date != "0000-00-00 00:00:00" ? Format::date($info->start_date, 'datetime') : "";
                                $_end_time = $info->end_date != "" && $info->end_date != "0000-00-00 00:00:00" ? Format::date($info->end_date, 'datetime') : "";
                                echo '<li style="width: 98%;">';//.($info->code != "" ? '['.$info->code.'] ' : "").$info->name.' '


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


                                echo '</li>';
                            }
                            echo '</ul>';
                          //  echo '</p>';
                        }
                    ?>
                        
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
                echo '<!-- '.print_r($course['auto_unsubscribe'], true).' -->';
                echo '<!-- '.print_r($course['unsubscribe_date_limit'], true).' -->';
                if ($_can_unsubscribe):
        ?>
        <p class="course_support_info">
            <?php if ($_date_limit !== FALSE && $_date_limit <= date("Y-m-d H:i:s")) {
                echo '';
            } else {

                $unsubscribe_call_arr[]=$course['idCourse'];

                ?>

            <?php if ($dm->checkHasValidUnsubscribePeriod($course['idCourse'], Docebo::user()->getIdSt())): ?>
            <a id="self_unsubscribe_link_<?php echo $course['idCourse']; ?> " href="ajax.server.php?r=classroom/self_unsubscribe_dialog&amp;id_course=<?php echo $course['idCourse']; ?>"
                 title="<?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>">
                 <?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>
            </a>
            <?php
                if ($_date_limit) echo '&nbsp;('.Lang::t('_UNTIL', 'standard').' '.Format::date(substr($_date_limit, 0, 10), 'date').')';
            ?>
            <?php endif; ?>
            <?php } //endif ?>
        </p>
        <?php
                endif;
            }
            unset($smodel);                        
                        
           ?>             
                        
                        
                  </p>      
                        </figcaption>
                        </figure>
                     </li>   
  
        <?php endforeach; ?>     
  
  
           </ul> 
        </div>      
  
  </fieldset>           
           
           
           

 
           
           
           
           
           
           
           
           
           
                                    
   
    









    

