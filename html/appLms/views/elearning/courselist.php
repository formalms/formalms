<link rel="shortcut icon" href="../favicon.ico"> 
		<script type="text/javascript" src="js/modernizr.custom.04022.js"></script>

        <!--
        <link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/font-awesome/css/font-awesome.min.css" />
        -->

<?php if( $use_label ) : ?>
<div class="container-back">
	<a href="index.php?r=elearning/show&id_common_label=-2">
		<span>&lsaquo;&lsaquo; <?php echo Lang::t('_BACK_TO_LABEL', 'course') ?></span>
	</a>
</div>
<?php endif; ?>

<?php $contCourse=0; ?>

<div  id="container1_<?php echo $stato_corso; ?>">
    <?php /*<div id="cbp-vm" class="cbp-vm-switcher cbp-vm-view-grid">*/ ?>
    <div id="cbp-vm">
    
    <?php if($filter_type=="" || $filter_type=='elearning'){  ?>
    <h1 class="page-header"><strong><?php echo Lang::t('_ELEARNING', 'catalogue'); ?></strong></h1>    

		<div class="clearfix" id='mia_area_<?php echo $stato_corso; ?>'>                       
        <?php foreach( $courselist as $course ) : ?> 
           <?php
                $nameCategory =  str_replace( "/", " - ", substr($course['nameCategory'],1));
                if($nameCategory=="") {
                    $nameCategory = Lang::t('_NO_CATEGORY', 'standard') ;
                } else {
                    $nameCategory = substr($course['nameCategory'],6);
                }                 
            ?>            
            <?php /*<li class="completed_course_<?php  echo $course['user_status']; ?>">*/ ?> 
            <div class="col-xs-12 col-sm-6 col-lg-4 course-block ">           
                <div class="course-block-content completed_course_<?php  echo $course['user_status']; ?>">
                    <?php if($course['use_logo_in_courselist'] && $course['img_course']) : ?>
                        <div class="area1 course-cover">   
                        
                        <?php
                              $date= Format::date($course['date_end'], 'date');
                              $date_split= explode('-', $date);
                              $anno= $date_split[2];
                              $mese= $date_split[1];
                              $giorno=$date_split[0];
                              setlocale(LC_ALL,"IT");//set the date to be converted
                              $date = Format::date($course['date_end'], 'date');
                              $month_name =  ucfirst(strftime("%B", strtotime($date))); 
                              $month_name =  substr($month_name,0,3);
							  
							  


							  
							  
							  ?>
                                                                       
                                 
                            <!-- IMG COURSE -->   
                            <a href="#">
                             <?php
							
							 if($date != "00-00-0000") : ?>
                    
                            <div class="data_chiusura">
                             <div class="corso_scaduto2">
                                    <span class="fa-stack fa-lg">
                                        <i class="fa fa-calendar  fa-stack-1x"></i>
                                        <i class="fa fa-ban  fa-stack-2x text-danger"></i>
                                    </span>
                                </div>
                            <div class="giorno"><?php echo $giorno ?></div>
                            <div class="mese"><?php echo $month_name ?></div>
                            <div class="anno"><?php echo $anno ?></div>
                           
                            </div>
                           <?php endif; ?> 
                            <div class="area2" >
                        <h1><?php echo $course['name'];     ?></h1>
                        <div class="icon">&raquo;</div>
                        <div style="clear:both;"></div>
                         <p class="course_support_info1">
                             <p class="descrizione_corso"><?php echo $course['description']?></p>
                             <p class="utenti"><i class="fa fa-users" aria-hidden="true"></i><span class="utenti_numero">&nbsp<?php echo $course['enrolled']?></span>&nbspUtenti</p>
                             <p class="stato_corso"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i><span class="stato_corso_stato">&nbsp<?php echo Lang::t($this->cstatus[$course['course_status']],'course')?></span></p>
                              
                             
                            </p> 
                            <?php if(!empty($access['expiring_in']) && $access['expiring_in'] < 5) : ?>
                                <p class="scadenza">
                                   <i class="fa fa-hourglass-half" aria-hidden="true"></i>&nbsp<?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                                </p>
                           <?php endif; ?> 
                    </div>
                            <img 
                                class="portrait"
                                width="100%"
                                src="<?php echo $path_course.$course['img_course']; ?>"
                                alt="<?php echo Util::purge($course['name']); ?>" />
                                </a>
                        </div>
                    <?php endif; ?>
                    <?php if($course['use_logo_in_courselist'] && !$course['img_course']) : ?>
                    
                    <div class="area1 course-cover">   
                        
                        <?php
                              $date= Format::date($course['date_end'], 'date');
                              $date_split= explode('-', $date);
                              $anno= $date_split[2];
                              $mese= $date_split[1];
                              $giorno=$date_split[0];
                              setlocale(LC_ALL,"IT");//set the date to be converted
                              $date = Format::date($course['date_end'], 'date');
                              $month_name =  ucfirst(strftime("%B", strtotime($date))); 
                              $month_name =  substr($month_name,0,3);
							  
							  


							  
							  
							  ?>
                                                                       
                                 
                            <!-- IMG COURSE -->   
                            <a href="#">
                            
                            <?php
							
							 if($date != "00-00-0000") : ?>
                    
                            <div class="data_chiusura">
                             <div class="corso_scaduto2">
                                    <span class="fa-stack fa-lg">
                                        <i class="fa fa-calendar  fa-stack-1x"></i>
                                        <i class="fa fa-ban  fa-stack-2x text-danger"></i>
                                    </span>
                                </div>
                            <div class="giorno"><?php echo $giorno ?></div>
                            <div class="mese"><?php echo $month_name ?></div>
                            <div class="anno"><?php echo $anno ?></div>
                           
                            </div>
                           <?php endif; ?> 
                            <div class="area2" >
                        <h1><?php echo $course['name'];     ?></h1>
                        <div class="icon">&raquo;</div>
                        <div style="clear:both;"></div>
                         <p class="course_support_info1">
                             <p class="descrizione_corso"><?php echo $course['description']?></p>
                             <p class="utenti"><i class="fa fa-users" aria-hidden="true"></i><span class="utenti_numero">&nbsp<?php echo $course['enrolled']?></span>&nbspUtenti</p>
                             <p class="stato_corso"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i><span class="stato_corso_stato">&nbsp<?php echo Lang::t($this->cstatus[$course['course_status']],'course')?></span></p>
                              
                             
                            </p> 
                            <?php if(!empty($access['expiring_in']) && $access['expiring_in'] < 30) : ?>
                                <p class="scadenza">
                                   <i class="fa fa-hourglass-half" aria-hidden="true"></i>&n<?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                                </p>
                            <?php endif; ?> 
                    </div>
                            <img
                                src="<?php echo Get::tmpl_path().'images/course/course_nologo.png'; ?>"
                                alt="<?php echo Util::purge($course['name']); ?>" />
                                </a>
                        </div>


                    <?php endif; ?>
                    
                    <div  class="area3">
                    <div class="studente">
                        <a href='#' class='tooltips' id='livello' title='LIVELLO'>
                        <i class="fa fa-graduation-cap" aria-hidden="true"></i></a><?php
                            echo $this->levels[ $course['level'] ];
					
                            $acl_man = Docebo::user()->getAclManager();
                            $levels = CourseLevel::getLevels();
                            
                            while(list($num_lv, $name_level) = each($levels)) {
                                if(CourseLevel::isTeacher($num_lv)) {
                                    
                                }
                                if($course['level_show_user'] & (1 << $num_lv)) {
                                    if(CourseLevel::isTeacher($num_lv)) {
                                        echo "&nbsp;" . $name_level . "";
                                        $users =& $acl_man->getUsers( Man_Course::getIdUserOfLevel($course['idCourse'], $num_lv, $course['course_edition']) );
                                        if(!empty($users)) {
                                            $first = true;
                                            while(list($id_user, $user_info) = each($users)) {
                                                if($first) $first = false;
                                                else echo('');
                                                // echo '<a href="index.php?modname=course&amp;op=viewprofile&amp;id_user='.$id_user.'">' . $acl_man->getConvertedUserName($user_info) . '</a>';
                                                
                                            } // end while
                                        } // end if
                                    }
                                } // end if
                            } // end while
                        ?></div>
                        <p class="text-justify-paragrafo">
                                              
                            <p class="course_support_info1">
                                <!-- CODE COURSE -->
                                <!--
                                <?php if($course['code']) { ?>
                                    <i style="font-size:.88em">[<?php echo $keyword != "" ? Layout::highlight($course['code'], $keyword) : $course['code']; ?>]</i>
                                <?php } ?>
                                 -->
                               <p class="categoria_corso"><a href="#" class="tooltips" title="CATEGORIA DEL CORSO"><i class="fa fa-folder-open-o" aria-hidden="true"></i> </a><span class="categoria_corso_nome"> <?php if($nameCategory) { ?>
                                    <?php echo $nameCategory ?>
                                <?php } ?> </span> </p>  
                                                     
                                <div style="clear:both"></div>                             
                                <div><hr class="style7"></div>
                                <div class="stato">
                                <?php
                                  if($course['user_status']=='2') echo "<p class='completo'><a href='#' class='tooltips' class='stato' title='STATO:COMPLETATO'><i class='fa fa-check-circle fa-2x' aria-hidden='true'></i></p></a>";
                                  if($course['user_status']=='1') echo "<p class='attesa'><a href='#' class='tooltips' id='stato' title='STATO:IN CORSO'><i class='fa fa-check-circle fa-2x'  aria-hidden='true'></i></a></p>";
                                  if($course['user_status']=='0') echo "<p class='non_pubblicato'><a href='#' class='tooltips' id='stato' title='STATO: NON PUBBLICATO'><i class='fa fa-check-circle fa-2x'  aria-hidden='true'></i></a></p>" ;
                                  //if(!$course['can_enter']['can']) echo "<img class='no_traform' src='". Get::tmpl_path().'images/standard/locked.png'."'>" ;
                                ?>     </div>  
                                <?php 
                            if((!empty($course['can_enter']['expiring_in']) && $course['can_enter']['can']==false && $course['can_enter']['expiring_in'] <= 0) || ($course['can_enter']['expiring_in'] == 0 && $course['can_enter']['can']==false)) : ?>
                                <p class="corso_scaduto">
                                    <a href='#' class='tooltips' id="expired" title='CORSO SCADUTO'>
                                    <span class="fa-stack fa-lg">
                                        <i class="fa fa-calendar  fa-stack-1x"></i>
                                        <i class="fa fa-ban  fa-stack-2x text-danger"></i>
                                    </span></a>
                                </p>
                            <?php endif; ?>                      
                            
                            
                            <?php
                                $smodel = new SubscriptionAlms();
                                if ($smodel->isUserWaitingForSelfUnsubscribe(Docebo::user()->idst, $course['idCourse'])) {
                                    echo '<div class="waiting_for">'.Lang::t('_UNSUBSCRIBE_REQUEST_WAITING_FOR_MODERATION', 'course').'</div>';
                                } else {
                                //auto unsubscribe management: create a link for the user in the course block
                                $_can_unsubscribe = ($course['auto_unsubscribe']==1 || $course['auto_unsubscribe']==2);
                                $_date_limit = $course['unsubscribe_date_limit'] != "" && $course['unsubscribe_date_limit'] != "0000-00-00 00:00:00"
                                    ? $course['unsubscribe_date_limit']
                                    : FALSE;
                            ?>
                            <?php if ($_can_unsubscribe): ?>
                            <div class="disiscrivi">
                                <p class="course_support_info">
                                    <?php if ($_date_limit !== FALSE && $_date_limit < date("Y-m-d H:i:s")) {
                                        echo '';
                                    } else { ?>                     
                                        <a class="tooltips" id="unscribe" href="index.php?r=elearning/self_unsubscribe&amp;id_course=<?php echo $course['idCourse']; ?>&amp;back=<?php echo Get::req('r', DOTY_STRING, ""); ?>"
                                             title="<?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>">
                                            <i class="fa fa-user-times fa-2x" aria-hidden="true"></i>
                                        </a>
                                    <?php
                                        if ($_date_limit) echo '&nbsp;('.Lang::t('_UNTIL', 'standard').' '.Format::date(substr($_date_limit, 0, 10), 'date').')';
                                    ?>
                                    <?php } //endif ?>
                                </p>
                        </p></div>

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
                                echo '<div class="lock"><i class="fa fa-3x fa-lock" aria-hidden="true"></i></div>';
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

        <?php
        // fine elearning
        }
        ?>
        
        <?php /*<div class="well well-sm"><strong><?php echo Lang::t('_CLASSROOM', 'standard'); ?></strong></div>*/ ?>

        <?php if($filter_type=="" || $filter_type=='classroom'){  ?>
        <h1 class="page-header"><strong><?php echo Lang::t('_CLASSROOM', 'standard'); ?></strong></h1>
        
		 <div class="clearfix" id="mia_area_class_<?php echo $stato_corso; ?>">
        <?php if( empty($courselistClassroom) ) : ?>
            <p><?php echo Lang::t('_NO_CONTENT', 'standard'); ?></p>
        <?php endif; ?>
        <?php $unsubscribe_call_arr =array(); ?>
        <?php foreach( $courselistClassroom as $course ) : ?>
            <?php
                $nameCategory =  str_replace( "/", " - ", substr($course['nameCategory'],1));
                if($nameCategory=="") {
                    $nameCategory = Lang::t('_NO_CATEGORY', 'standard') ;
                } else {
                    $nameCategory = substr($course['nameCategory'],6);
                }                 
            ?>              
            <?php /*<li class="completed_course_<?php  echo $course['user_status']; ?>">*/ ?>
            <div class="col-xs-12 col-sm-6 col-lg-4 course-block completed_course_<?php  echo $course['user_status']; ?>">
                <div class="course-block-content classe">
                    <?php if($course['use_logo_in_courselist'] && $course['img_course']) : ?>
                        <div class="area1 course-cover">   
                        
                        <?php
                              $date= Format::date($course['date_end'], 'date');
                              $date_split= explode('-', $date);
                              $anno= $date_split[2];
                              $mese= $date_split[1];
                              $giorno=$date_split[0];
                              setlocale(LC_ALL,"IT");//set the date to be converted
                              $date = Format::date($course['date_end'], 'date');
                              $month_name =  ucfirst(strftime("%B", strtotime($date))); 
                              $month_name =  substr($month_name,0,3);
							  
							  


							  
							  
							  ?>
                                                                       
                                 
                            <!-- IMG COURSE -->   
                            <a href="#">
                             <?php
							
							 if($date != "00-00-0000") : ?>
                    
                            <div class="data_chiusura">
                             <div class="corso_scaduto2">
                                    <span class="fa-stack fa-lg">
                                        <i class="fa fa-calendar  fa-stack-1x"></i>
                                        <i class="fa fa-ban  fa-stack-2x text-danger"></i>
                                    </span>
                                </div>
                            <div class="giorno"><?php echo $giorno ?></div>
                            <div class="mese"><?php echo $month_name ?></div>
                            <div class="anno"><?php echo $anno ?></div>
                           
                            </div>
                           <?php endif; ?> 
                            <div class="area2" >
                        <h1><?php echo $course['name'];     ?></h1>
                        <div class="icon">&raquo;</div>
                        <div style="clear:both;"></div>
                         <p class="course_support_info1">
                             <p class="descrizione_corso"><?php echo $course['description']?></p>
                             <p class="utenti"><i class="fa fa-users" aria-hidden="true"></i><span class="utenti_numero">&nbsp<?php echo $course['enrolled']?></span>&nbspUtenti</p>
                             <p class="stato_corso"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i><span class="stato_corso_stato">&nbsp<?php echo Lang::t($this->cstatus[$course['course_status']],'course')?></span></p>
                              
                             
                            </p> 
                            <?php if(!empty($access['expiring_in']) && $access['expiring_in'] < 5) : ?>
                                <p class="scadenza">
                                   <i class="fa fa-hourglass-half" aria-hidden="true"></i>&nbsp<?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                                </p>
                           <?php endif; ?> 
                    </div>
                            <img 
                                class="portrait"
                                width="100%"
                                src="<?php echo $path_course.$course['img_course']; ?>"
                                alt="<?php echo Util::purge($course['name']); ?>" />
                                </a>
                        </div>
                    <?php endif; ?>
                    <?php if($course['use_logo_in_courselist'] && !$course['img_course']) : ?>
                    
                    <div class="area1 course-cover">   
                        
                        <?php
                              $date= Format::date($course['date_end'], 'date');
                              $date_split= explode('-', $date);
                              $anno= $date_split[2];
                              $mese= $date_split[1];
                              $giorno=$date_split[0];
                              setlocale(LC_ALL,"IT");//set the date to be converted
                              $date = Format::date($course['date_end'], 'date');
                              $month_name =  ucfirst(strftime("%B", strtotime($date))); 
                              $month_name =  substr($month_name,0,3);
							  
							  


							  
							  
							  ?>
                                                                       
                                 
                            <!-- IMG COURSE -->   
                            <a href="#">
                            
                            <?php
							
							 if($date != "00-00-0000") : ?>
                    
<!--                            <div class="data_chiusura">
                             <div class="corso_scaduto2">
                                    <span class="fa-stack fa-lg">
                                        <i class="fa fa-calendar  fa-stack-1x"></i>
                                        <i class="fa fa-ban  fa-stack-2x text-danger"></i>
                                    </span>
                                </div>
                            <div class="giorno"><?php echo $giorno ?></div>
                            <div class="mese"><?php echo $month_name ?></div>
                            <div class="anno"><?php echo $anno ?></div>
                           
                            </div>-->
                           <?php endif; ?> 
                            <div class="area2" >
                        <h1><?php echo $course['name'] ?></h1>
                        <div class="icon">&raquo;</div>
                        <div style="clear:both;"></div>
                         <p class="course_support_info1">
                             <p class="descrizione_corso"><?php echo $course['description']?></p>
                             <p class="utenti"><i class="fa fa-users" aria-hidden="true"></i><span class="utenti_numero">&nbsp<?php echo $course['enrolled']?></span>&nbspUtenti</p>
                             <p class="stato_corso"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i><span class="stato_corso_stato">&nbsp<?php echo Lang::t($this->cstatus[$course['course_status']],'course')?></span></p>
                              
                             
                            </p> 
                            <?php if(!empty($access['expiring_in']) && $access['expiring_in'] < 30) : ?>
                                <p class="scadenza">
                                   <i class="fa fa-hourglass-half" aria-hidden="true"></i>&n<?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                                </p>
                            <?php endif; ?> 
                    </div>
                            <img
                                src="<?php echo Get::tmpl_path().'images/course/course_nologo.png'; ?>"
                                alt="<?php echo Util::purge($course['name']); ?>" />
                                </a>
                        </div>


                    <?php endif; ?>
                    <div class="area3 classe">
                         <div class="categoria_corso"><a href="#" class="tooltips" title="CATEGORIA DEL CORSO"><i class="fa fa-folder-open-o" aria-hidden="true"></i> </a><span class="categoria_corso_nome"> <?php if($nameCategory) { ?>
                                    <?php echo $nameCategory ?>
                                <?php } ?> </span> </div> 
                         <div class="studente">
                        <a href='#' class='tooltips' id='livello' title='LIVELLO'>
                        <i class="fa fa-graduation-cap" aria-hidden="true"></i></a>
                            <?php echo $this->levels[ $course['level'] ] ?>
       
  
                            <?php if(!empty($access['expiring_in']) && $access['expiring_in'] < 30) : ?>
                                <?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
                            <?php endif; ?>
                              
                         </div>
                         <div style="clear:both"></div>                             
                                <div class="linea"><hr class="style7"></div>
                         <div class="box_edizioni">
                        <?php
                       
                        if (!empty($display_info) && isset($display_info[$course['idCourse']])) {
                            
                                                        foreach ($display_info[$course['idCourse']] as $key => $info) {
								echo '<div class="edizioni">';
                                echo '<div class="edizioni_nome">';
								echo '<span>Edizione:</span>'.$info->name ;
								echo '</div>';
                                $start_date =$info->date_info['date_begin'];
                                $end_date =$info->date_info['date_end'];
                                $_start_time = $start_date != "" && $start_date != "0000-00-00 00:00:00" ? Format::date($start_date, 'date') : "";
								$_clock_time = $start_date != "" && $start_date != "0000-00-00 00:00:00" ? Format::date($start_date, 'time') : "";
                                $_end_time = $end_date != "" && $end_date != "0000-00-00 00:00:00" ? Format::date($end_date, 'date') : "";
								$_clockend_time = $end_date != "" && $end_date != "0000-00-00 00:00:00" ? Format::date($end_date, 'time') : "";
								echo '<div class="edizioni_cal">';
								echo '<a href="#" class="tooltips" id="classe_data_start" title="DATA INIZIO"><div class="edizioni_start"><i class="fa  fa-2x fa-calendar-check-o" aria-hidden="true"></i>&nbsp;<span class="text_time">';
                                echo $_start_time.'&nbsp;<i class="fa fa-2x fa-clock-o" aria-hidden="true"></i>&nbsp;'.$_clock_time;
								echo '</span></a></div>';
								echo '<a href="#" class="tooltips" id="classe_data_end" title="DATA FINE"><div class="edizioni_end"><i class="fa fa-2x fa-calendar-times-o" aria-hidden="true"></i>&nbsp;<span class="text_time_end">';
                                echo $_end_time.'&nbsp;<i class="fa fa-2x fa-clock-o" aria-hidden="true"></i>&nbsp;'.$_clockend_time;
								echo '</a></div>';
								echo '<div style="clear:both"></div>';
								echo '</span></div>';
								
								echo '<a href="#" class="tooltips" id="luogo" title="LUOGO"><div class="luogo"><i class="fa fa-2x fa-map-marker" aria-hidden="true"></i>&nbsp;';
                                echo $info->date_info['location'];
								echo '</a></div>';
                                echo '</div>';
                            }
                           
                          //  echo '</p>';
                        }
                        ?>  
                        </div> 
                      <div class="linea"><hr class="style7"></div>                        
                     <div class="stato">
                        <?php
                            if($course['user_status']=='2') echo "<p class='completo'><a href='#' class='tooltips' class='statoclass' title='STATO:COMPLETATO'><i class='fa fa-check-circle fa-2x' aria-hidden='true'></i></p></a>"  ;
                            if($course['user_status']=='1') echo "<p class='attesa'><a href='#' class='tooltips' id='statoclass' title='STATO:IN CORSO'><i class='fa fa-check-circle fa-2x'  aria-hidden='true'></i></a></p>"  ;
                            if($course['user_status']=='0') echo "<p class='non_pubblicato'><a href='#' class='tooltips' id='statoclass' title='STATO: NON PUBBLICATO'><i class='fa fa-check-circle fa-2x'  aria-hidden='true'></i></a></p>"
							
                        ?>    
                        </div>                             
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
        <?php /*</ul>*/ ?>
        
        
        <?php
            // fine classroom
            }
        ?>
        
        </div>
    </div>          
</div>