<style>
  
thead input {
        width: 100%;
    }
 
 
.custom{
  background-color:yellow;
} 

</style>      


<?php



require_once(Forma::inc(_lms_.'/lib/lib.subscribe.php'));
require_once(Forma::inc(_lib_ . '/formatable/include.php'));

//Util::get_js(Get::rel_path('appLms') . '/appLms/admin/views/course/certificate_tab.js', true, true);
Util::get_js('../addons/jquery/datatables/Buttons-1.5.4/js/buttons.colVis.min.js',true, true);

      
    $arr_status = array(
        _CUS_CONFIRMED         => "Confermato",//, 'subscribe', 'lms'),
        _CUS_SUBSCRIBED        => "Iscritto",//, 'subscribe', 'lms'),//_USER_STATUS_SUBS(?)
        _CUS_BEGIN             => "Iniziato",//, 'subscribe', 'lms'),
        _CUS_END               => "Finito",//, 'subscribe', 'lms'),
        _CUS_SUSPEND           => "Sospeso"//, 'subscribe', 'lms')
    );

    
$back_label =  Lang::t('_CERTIFICATE_ASSIGN_STATUS', 'course');   
    
echo getTitleArea(array(
	'index.php?r=alms/course/show' => $back_label,
	$info_course['name']
));


  $print_button = '<div>'
        .'<a id="print_selected_button_1" href="javascript:generate_all_certificate();">'
        .Get::img('course/certificate.png', Lang::t('_GENERATE_ALL_SELECTED', 'certificate'))
        .Lang::t('_GENERATE_ALL_SELECTED', 'certificate')
        .'</a>'
        .'&nbsp;&nbsp;&nbsp;<a id="download_selected_button_1" href="javascript:download_all_certificate();">'
        .Get::img('course/certificate.png', Lang::t('_DOWNLOAD_ALL_SELECTED', 'certificate'))
        .Lang::t('_DOWNLOAD_ALL_SELECTED', 'certificate')
        .'</a>'
        .'</div>';


  echo $print_button.'<br />';
 
 
         
    if($from=="course"){
        echo "<div class='container-back'>";
        echo "<a href='index.php?r=alms/course/certificate&amp;id_course=".$id_course."'>".Lang::t('_BACK', 'standard')."</a>";
        echo "</div>";
    } 
          
            
    if($from=="courselist"){ 
        echo "<div class='container-back'>";
        echo "<a href='index.php?r=alms/course/show'>".Lang::t('_BACK', 'standard')."</a>";
        echo "</div>";
    }
    
    if($from=="manage"){
        echo "<div class='container-back'>";
        echo "<a href='index.php?modname=certificate&op=report_certificate&of_platform=lms&id_certificate=".$id_certificate."'>".Lang::t('_BACK', 'standard')."</a>";
        echo "</div>";        
    }

?>
    
<input type='hidden' id='show_search' value='false'>
<input type='hidden' id='sel_all' value='false'>

<div class='std_block'>
           <table id='table_certificate'  data-id_course='<?php echo $id_course ?>' data-id_certificate='<?php echo $id_certificate ?>' class='table table-striped table-bordered display' style='width:100%'></table>            
<script type="text/javascript">           
        var table = $('#table_certificate').FormaTable({
            rowId: 'id_user',
            data:  <?php echo json_encode($data_certificate) ?>,
            select: true,            
            columns:[
             { title: 'id_user', sortable: false, visible: false },
             { title: 'id_certificate', sortable: false, visible: false },
             { title: '<?php echo Lang::t('_USERNAME', 'standard'); ?>', sortable: true },
             { title: '<?php echo Lang::t('_LASTNAME', 'standard'); ?>', sortable: true },
             { title: '<?php echo Lang::t('_NAME', 'standard'); ?>', sortable: true },
             { title: '<?php echo Lang::t('_STATUS', 'standard'); ?>', sortable: true,  
                render: function ( data, type, row ) { 
                    
                    switch (data){
                        case '0':
                        return '<?php echo Lang::t('_NOT_STARTED', 'standard'); ?>'
                        case '1':
                        return '<?php echo Lang::t('_STARTED', 'standard'); ?>'
                        case '2':
                        return '<?php echo Lang::t('_USER_STATUS_END', 'standard'); ?>'
                        default:
                        return ''
                    }
                
                
                }
             },
             { title: '<?php echo Lang::t('_CERTIFICATE_REPORT', 'certificate'); ?>', sortable: true },
             { title: '<?php echo Lang::t('_EDITION', 'standard'); ?>', sortable: true, visible: ('classroom' == '<?php echo $course_type ?>')  },
             { title: '<?php echo Lang::t('_DATE_END', 'standard'); ?>', sortable: true },             
             { title: '<?php echo Lang::t('_RELASE_DATE', 'certificate'); ?>', sortable: true },
             { title: '<?php echo Lang::t('_TITLE_VIEW_CERT', 'standard'); ?>', sortable: true },
             { title: '<?php echo Lang::t('_DEL', 'standard'); ?>', sortable: true }
            ],
            pagingType: 'full_numbers',
            language : {
                 'sInfo'  : '<?php echo Lang::t('_FROM', 'standard'); ?>  _START_  <?php echo Lang::t('_TO', 'standard'); ?> _END_ <?php echo Lang::t('_OF', 'standard'); ?>   _TOTAL_ <?php echo Lang::t('_CERTIFICATE', 'menu'); ?> <?php echo Lang::t('_TOTAL', 'standard'); ?> ',
                 'infoEmpty': '',
                 'sEmptyTable' : '<?php echo Lang::t('_NO_CERTIFICATE_AVAILABLE', 'certificate'); ?> '
            }
        })
</script>

<?php

    require_once(_base_.'/lib/lib.dialog.php');
    setupHrefDialogBox('a[href*=del_report_certificate]',Lang::t('_CONFIRM_DELETION', 'iotask'),Lang::t('_YES', 'standard'),Lang::t('_NO', 'standard'));    



    echo $print_button.'<br />';    

    echo "</div>";

    
    
?>
