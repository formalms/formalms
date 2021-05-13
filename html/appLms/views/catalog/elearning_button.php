<div class="course-box__item" id="action_<?=$course['idCourse']?>">
    <?php if ($course['is_enrolled']) { ?> 
            <?php if ($course['waiting']) { ?> 
                    <a href="javascript:void(0);" class="forma-button forma-button--disabled">
                        <p class="forma-button__label"><?=Lang::t('_WAITING', 'catalogue')?></p>
                    </a>
            <?php } else { ?>  
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
            <?php } ?>
    <?php } else { ?>
            <?php if ($course['course_full']) { ?> 
                    <?php if (!$course['allow_overbooking']) { ?> 
                        <a href="javascript:void(0);" class="forma-button forma-button--disabled">
                            <span class="forma-button__label"><?=Lang::t('_MAX_NUM_SUBSCRIBE', 'course')?> (<?=$course['max_num_subscribe']?>)</span>
                        </a>
                    <?php } else { ?>    
                        <a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" 
                            onclick ="confirmDialog('<?=Lang::t('_SUBSCRIBE', 'catalogue')?>',
                                       '<?=$course['escaped_name']?>', 'catalog/subscribeToCourse', '<?=$course['idCourse']?>', '0','0','1')">
                            <span class="forma-button__label"><?=Lang::t('_USER_STATUS_OVERBOOKING', 'subscribe')?></span>
                        </a>
                    <?php } ?>    
                    
            <?php } else {  ?> 
                    <?php if ($course['selling'] == 0) { ?> 
                                <?php if ($course['subscribe_method'] == 2) { ?>
                                        <a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" 
                                        onclick ="confirmDialog('<?=Lang::t('_SUBSCRIBE', 'catalogue')?>',
                                                 '<?=$course['escaped_name']?>', 'catalog/subscribeToCourse', '<?=$course['idCourse']?>', '0','0','0')">
                                            <span class="forma-button__label"><?=Lang::t('_SUBSCRIBE', 'catalogue')?></span>
                                        </a>
                                 <?php } elseif ($course['subscribe_method'] == 1) {  ?>      
                                        <a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" 
                                            onclick ="confirmDialog('<?=Lang::t('_SUBSCRIBE', 'catalogue')?>',
                                                     '<?=$course['escaped_name']?>', 'catalog/subscribeToCourse', '<?=$course['idCourse']?>', '0','0','0')">
                                            <span class="forma-button__label"><?=Lang::t('_COURSE_S_MODERATE', 'catalogue')?></span>
                                        </a>
                                 <?php } elseif ($course['subscribe_method'] == 0) {  ?> 
                                        <a href="javascript:void(0);" class="forma-button forma-button--disabled">
                                            <span class="forma-button__label"><?=Lang::t('_COURSE_S_GODADMIN', 'catalogue')?></span>
                                        </a>
                                 <?php }  ?>                              

                    <?php } else {  ?>
                                <?php if ($course['in_cart']) { ?>
                                    <a href="javascript:void(0);" class="forma-button forma-button--orange-hover">
                                        <p class="forma-button__label"><?=Lang::t('_COURSE_IN_CART', 'catalogue')?> </p>
                                    </a>
                                <?php } else {  ?>
                                    <a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" 
                                                onclick ="confirmDialog('<?=Lang::t('_CONFIRM_ADD_TO_CART', 'catalogue')?>',
                                                 '<?=$course['escaped_name']?>'+'<br><br>'+
                                                 '<?=Get::sett('currency_symbol', '&euro;')?>
                                                 <?=$course['prize']?>','catalog/addToCart', '<?=$course['idCourse']?>', '0','0','0')">
                                        <span class="forma-button__label"><?=Lang::t('_ADD_TO_CART', 'catalogue')?></span>
                                    </a>                                
                                 <?php }  ?>                                
                
                    <?php }  ?>                              
            <?php } ?>

    <?php } ?>    

</div>