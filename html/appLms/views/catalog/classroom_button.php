<div class="course-box__item" id="action_<?=$course['idCourse']?>">
    <?php if ($course['edition_exists'] || $course['is_enrolled']) { ?>    
            <?php if ($course['is_enrolled']) { ?> 
                    <?php if ( $course['canEnter'] ) {?> 
                            <a class="forma-button forma-button--orange-hover" href="index.php?modname=course&op=aula&idCourse=<?=$course['idCourse']?>"
                                    title="<?=$course['name']?>" <?=$str_rel?>>
                                <span class="forma-button__label">
                                <?=Lang::t('_USER_STATUS_ENTER', 'catalogue')?> 
                                </span>
                           </a>
                    <?php } else {  ?>   
                            <a class="forma-button forma-button--disabled" href="javascript:void(0);">
                                    <span class="forma-button__label">
                                    <?=Lang::t('_DISABLED', 'course')?>
                                    </span>
                            </a>
                    <?php }  ?>

            <?php } else { ?>
                    <?php if ($course['selling'] == 0) { ?> 
                            <?php if ($course['subscribe_method'] == 2) { ?>
                                    <a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" 
                                    onclick ="chooseEdition(<?=$course['idCourse']?>)">
                                        <span class="forma-button__label"><?=Lang::t('_SUBSCRIBE', 'catalogue')?></span>
                                    </a>
                             <?php } elseif ($course['subscribe_method'] == 1) {  ?>      
                                    <a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" 
                                        onclick ="chooseEdition(<?=$course['idCourse']?>)">
                                        <span class="forma-button__label"><?=Lang::t('_COURSE_S_MODERATE', 'catalogue')?></span>
                                    </a>
                             <?php } elseif ($course['subscribe_method'] == 0) {  ?> 
                                    <a href="javascript:void(0);" class="forma-button forma-button--disabled">
                                        <span class="forma-button__label"><?=Lang::t('_COURSE_S_GODADMIN', 'catalogue')?></span>
                                    </a>
                             <?php }  ?>                              
                    <?php } else { ?>     
                            <?php if ($course['in_cart']) { ?>
                                <a href="javascript:void(0);" class="forma-button forma-button--orange-hover">
                                    <p class="forma-button__label"><?=Lang::t('_COURSE_IN_CART', 'catalogue')?> </p>
                                </a>
                            <?php } else {  ?>
                                <a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" 
                                            onclick ="chooseEdition(<?=$course['idCourse']?>)">    
                                    <span class="forma-button__label"><?=Lang::t('_ADD_TO_CART', 'catalogue')?></span>
                                </a>                                
                             <?php }  ?>                                
                    <?php } ?>
            <?php } ?>
    <?php } else {  ?>
            <a class="forma-button forma-button--disabled" href="javascript:void(0);">
                <span class="forma-button__label">
                    <?=Lang::t('_NO_EDITIONS_AVAILABLE', 'classroom')?>
                </span>
            </a>
    <?php } ?>   
</div>








