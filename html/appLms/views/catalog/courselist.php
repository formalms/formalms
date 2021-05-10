<script type="text/javascript">
    YAHOO.util.Event.onDOMReady(function () {
        initialize("<?php echo Lang::t('_UNDO', 'standard'); ?>");
    });
</script>

<script type="text/javascript">
    var lb = new LightBox();
    lb.back_url = 'index.php?r=lms/catalog/show&sop=unregistercourse';

    var Config = {};
    Config.langs = {_CLOSE: '<?php echo Lang::t('_CLOSE', 'standard'); ?>'};
    lb.init(Config);  
</script>



<?php
require_once(_lms_ . '/lib/lib.middlearea.php');
$ma = new Man_MiddleArea();
//   $category = $this->model->getMinorCategory($std_link, true);
$html = '';
$path_course = $GLOBALS['where_files_relative'] . '/appLms/' . Get::sett('pathcourse') . '/';
$current_catalogue = $smodel->current_catalogue;



function classroomCourse(&$row, &$smodel){
    
    $user_classroom = $smodel->classroom_man->getUserDateForCourse(Docebo::user()->getIdSt(), $row['idCourse']);
    if (count($user_classroom)>0){  // user already enrolled.
        $action .= '<a class="forma-button forma-button--orange-hover" href="index.php?modname=course&op=aula&idCourse=' . $row['idCourse'] . ' "'
                        . ' title="' . $_text . '"><span class="forma-button__label">'
                        . Lang::t('_USER_STATUS_ENTER', 'catalogue') . '</span>'
                        . '</a>';
    }  else {
        // get all editions of a course with status available
        $classrooms = $smodel->classroom_man->getCourseDate($row['idCourse'], false);
        if (count($classrooms) == 0) {
            $action .= '<a class="forma-button forma-button--disabled">
                        <span class="forma-button__label">' . Lang::t('_NO_EDITIONS', 'catalogue') . '</span>
                    </a>';
        }  else {
            if ($row['selling'] == 0) {
                switch ($row['subscribe_method']) {
                    case 2:
                        // free
                        $action .= '<a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><span class="forma-button__label">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</span></a>';
                        break;
                    case 1:
                        // moderate
                        $action .= '<a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><span class="forma-button__label">' . Lang::t('_COURSE_S_MODERATE', 'catalogue') . '</span></a>';
                        break;
                    case 0:
                        // only admin
                        $action .= '<a class="forma-button forma-button--orange-hover">
                                        <span class="forma-button__label">' . Lang::t('_COURSE_S_GODADMIN', 'catalogue') . '</span>
                                    </a>';
                        break;
                }                    
            } else {
                    if (isset($_SESSION['lms_cart'][$row['idCourse']]['classroom'])) {
                        $action .= '<p class="subscribed">' . Lang::t('_ALL_EDITION_BUYED', 'catalogue') . '</p>';
                    }  else {
                        $action .= '<a href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'1\')" title="' . Lang::t('_ADD_TO_CART', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_ADD_TO_CART', 'catalogue') . '</p></a>';
                    }
            }    
        }
    }
    return $action;
 
}


function editionElearning(&$row, &$smodel){
    $editions = $smodel->edition_man->getEditionAvailableForCourse(Docebo::user()->getIdSt(), $row['idCourse']);
    
    if (count($editions) == 0) {
        $action .= '<a href="javascript:void(0);" class="forma-button forma-button--disabled">
                        <p class="forma-button__label">' . Lang::t('_NO_EDITIONS', 'catalogue') . '</p>
                    </a>';
    } else {
        if ($row['selling'] == 0) {
            $action .= '<a class="forma-button forma-button--green forma-button--orange-hover" href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'0\')" title="' . Lang::t('_SUBSCRIBE', 'catalogue') . '"><span class="forma-button__label">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</span></a>';
        } else {
            $edition_in_chart = array();
            if (isset($_SESSION['lms_cart'][$row['idCourse']]['editions']))
                $edition_in_chart = $_SESSION['lms_cart'][$row['idCourse']]['editions'];

            $editions = array_diff($editions, $edition_in_chart);

            if (count($editions) == 0)
                $action .= '<p class="subscribed">' . Lang::t('_ALL_EDITION_BUYED', 'catalogue') . '</p>';
            else
                $action .= '<a href="javascript:;" onclick="courseSelection(\'' . $row['idCourse'] . '\', \'1\')" title="' . Lang::t('_ADD_TO_CART', 'catalogue') . '"><p class="can_subscribe">' . Lang::t('_ADD_TO_CART', 'catalogue') . '</p></a>';
        }
    }
    return $action;    
}




function getEditionInfo(&$row, &$smodel){
    $classrooms = $smodel->classroom_man->getCourseDate($row['idCourse'], false);
    $_edition = array();
    foreach ($classrooms as $classroom_info){
         $_edition['id_date'] = $classroom_info['id_date'];
         $_edition['id_course'] = $classroom_info['id_course'];
         $_edition['code'] = $classroom_info['code'];
         $_edition['name'] = $classroom_info['name'];
         $_edition['startDate']  = $classroom_info['date_begin'];
         $_edition['endDate']  = $classroom_info['date_end'];
         $_edition['unsubscribe_date_limit'] = $classroom_info['unsubscribe_date_limit'];
         $_edition['subscribed'] =  $smodel->classroom_man->controlDateUserSubscriptions(Docebo::user()->getIdSt(),$_edition['id_date']) ;
         $_edition['days'] = $smodel->classroom_man->getDateDayDateDetails($_edition['id_date']) ;
         $ret_array[] = $_edition;
    }                 
    return $ret_array;
    
}


function classroomActionButton($is_enrolled, $unsubscribe_date_limit, &$row){

    if ($is_enrolled ) {
        // 0 only admin
        // 1 moderate 
        // 2 free un_subscribe
        switch ($row['auto_unsubscribe']) {
            case 0:
               return Lang::t('_USER_STATUS_ENTER', 'catalogue');
            case 1:
            case 2:
                if (strtotime("now") < strtotime($unsubscribe_date_limit)) {            
                    return Lang::t('_UNSUBSCRIBE', 'course');
                } else {
                    return Lang::t('_USER_STATUS_ENTER', 'catalogue');    
                } 
        }
    } else {
        return Lang::t('_SUBSCRIBE', 'course');
    }

}


?>

        <script type="text/javascript">
        
                function chooseEdition(id_course){
                    var posting = $.get(
                                            'ajax.server.php',
                                                {
                                                    r: 'catalog/chooseEdition',
                                                    id_course: id_course,
                                                    type_course: $( "#typeCourse" ).val(),
                                                    id_catalogue: <?php echo $id_catalogue ?>,
                                                    id_category: $('#treeview1').treeview('getSelected')[0] ? 
                                                        $('#treeview1').treeview('getSelected')[0].id_cat : null
                                                }
                                            );
                                            posting.done(function (responseText) {
                                                t = JSON.parse(responseText)
                                                $('<div></div>').appendTo('body')
                                                .html("<div>"+t.body +"</div>")
                                                .dialog({
                                                    modal: true, 
                                                     title: "dialog_title", 
                                                     autoOpen: true,
                                                     resizable: false,
                                                     buttons: {
                                                         chiudi: function() {$(this).dialog("close"); }
                                                     },
                                                     width: 500
                                                 })                    
                                                
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