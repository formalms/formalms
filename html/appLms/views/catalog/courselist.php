<script type="text/javascript">
    YAHOO.util.Event.onDOMReady(function () {
        initialize("<?php echo Lang::t('_UNDO', 'standard'); ?>");
    });



    var lb = new LightBox();
    lb.back_url = 'index.php?r=lms/catalog/show&sop=unregistercourse';

    var Config = {};
    Config.langs = {_CLOSE: '<?php echo Lang::t('_CLOSE', 'standard'); ?>'};
    lb.init(Config);  

        
                function chooseEdition(id_course){
                    
                    var posting = $.get(
                            'ajax.server.php',
                                {
                                    r: 'catalog/chooseEdition',
                                    id_course: id_course,
                                    type_course: $( "#typeCourse" ).val(),
                                    id_catalogue: '<?=$id_catalogue ?>',
                                    id_category: $('#treeview1').treeview('getSelected')[0] ? 
                                        $('#treeview1').treeview('getSelected')[0].id_cat : null
                                }
                            );
                            posting.done(function (r) {
                                $('body').prepend(r)
                                $('#myModal').css("margin-top", $('body').height() / 2 - $('.modal-content').height() / 2 - 300) ;       
                                $('#myModal').modal('show')
                            });
                            posting.fail(function () {
                                alert('unsubscribe failed')
                            })                                
                }
                                    
                function confirmDialog(dialog_title, dialog_body, op, id_course, id_date, id_edition, overbooking){
                        $('<div></div>').appendTo('body')                    
                        .html("<div style='text-align:center'><h6>"+dialog_body+"</h6></div>")
                        .dialog({
                                modal: true, 
                                title: dialog_title, 
                                autoOpen: true,
                                resizable: false,
                                buttons: {
                                     <?php echo Lang::t('_CONFIRM', 'standard')?>: function () {
                                        var posting = $.get(
                                            'ajax.server.php',
                                                {
                                                    r: op,
                                                    id_course: id_course,
                                                    id_date: id_date,
                                                    id_edition: id_edition,
                                                    overbooking: overbooking,
                                                    type_course: $( "#typeCourse" ).val(),
                                                    id_catalogue: <?php echo $id_catalogue ?>,
                                                    id_category: $('#treeview1').treeview('getSelected')[0] ? 
                                                        $('#treeview1').treeview('getSelected')[0].id_cat : null
                                                }
                                            );
                                            posting.done(function (responseText) {
                                                if (op == 'catalog/addToCart') {
                                                    $('#menu_over').load(document.URL +  '  #menu_over');                                                    
                                                };
                                                $("#div_course").html(responseText);
                                            });
                                            posting.fail(function () {
                                                alert('unsubscribe failed')
                                            })                                
                                        $(this).dialog("close");
                                     },
                                     <?php echo Lang::t('_UNDO', 'standard')?>: function () { $(this).dialog("close");}
                                    
                                },
                            open: function(event, ui) {
                                $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                            }, 
                            close: function (event, ui) {
                                $(this).remove();
                            }
                        });                      
                }


        </script>        
        <div id="cbp-vm" class="" style="margin-top: 15px;">
        <?php   foreach ($course_category as $k => $course) { ?>

                    <div class="col-xs-offset-1 col-xs-10 col-md-offset-0 col-md-6">
                        <div class="course-box">
                            <div class="course-box__item">
                                <div class="course-box__title"><?=$course['name']?></div>
                            </div>
                            <div class="course-box__item course-box__item--no-padding">
                            <?php  if ($course['img_course']) {  ?>
                                        <div class="course-box__img" style="background-image: url('<?=$course['img_course']?>);">;
                            <?php  } else { ?>
                                        <div class="course-box__img">
                            <?php  } ?>   
                                            <div class="course-box__img-title">
                                            <?php if ($course['course_type'] == 'elearning') { ?>
                                                    <span class='elearning'><i class='fa fa-graduation-cap'></i>&nbsp;<?=Lang::t('_LEARNING_COURSE', 'cart')?></span>;
                                            <?php } else { ?>
                                                    <span class='classroom'><i class='fa fa-university'></i>&nbsp;<?=Lang::t('_CLASSROOM_COURSE', 'cart')?></span>;
                                            <?php } ?>
                                            
                                            
                                            </div>
                                        </div>                                                                
                            </div>
                            <div class="course-box__item">
                                    <div class="course-box__desc">
                                        <?=$course['box_description']?>
                                    </div>
                                    <?php if ( $course['show_options'] ) { ?>
                                            <div class="course-box__options dropdown pull-right">
                                                <div class="dropdown-toggle" id="courseBoxOptions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                    <i class="glyphicon glyphicon-option-horizontal"></i> 
                                                </div>   
                                                <ul class="dropdown-menu" aria-labelledby="courseBoxOptions">
                                                <?php if($course['userCanUnsubscribe']  && $course['is_enrolled']) { ?>
                                                             <li><a href="javascript:confirmDialog('<?=Lang::t("_SELF_UNSUBSCRIBE", "course")?>',
                                                                                                   '<?=$course['name']?>',
                                                                                                   'catalog/self_unsubscribe',
                                                                                                    '<?=$k?>',)">
                                                                <?=Lang::t('_SELF_UNSUBSCRIBE', 'course')?>
                                                             </a></li> 
                                                <?php } ?>            
                                                <?php  if ($course["course_demo"] && ($course["level"] > 3 || (!$course['waiting']  && $course['canEnter']))) { ?>                          <li><a href="index.php?r=catalog/downloadDemoMaterial&amp;course_id=<?=$row['idCourse']?>">
                                                            <?=Lang::t('_COURSE_DEMO', 'course')?>
                                                            </a></li>
                                                <?php } ?>              
                                                </ul>
                                            </div>
                                  <?php } ?>
                                                           
                            </div>
                            <?php  if ($course['course_type'] == 'elearning')  {
                                require('elearning_button.php');
                            } ?>                                
                            <?php  if ($course['course_type'] == 'classroom')  {
                                require('classroom_button.php');
                            } ?>                                
                            
                            
                        </div> 
                   </div>
                     
<?php           } ?>

                <?php if (count($course_category) == 0) { ?>
                    <p><?=Lang::t('_NO_CONTENT', 'standard')?></p>
                <?php } ?>

        </div> <!--  /forma-grid - /row-->