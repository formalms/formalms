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
    
echo "
<input type='hidden' id='show_search' value='false'>
<input type='hidden' id='sel_all' value='false'>

<div class='std_block'>";
            echo "<table id='table_certificate' data-tipocorso='".$course_info['course_type']."' data-id_course='".$id_course."' data-id_certificate='".$id_certificate."' class='table table-striped table-bordered display' style='width:100%'>";            
                    echo "<thead>";
                        echo "<tr>";
                            echo "<th></th>";
                            echo "<th></th>";
                            echo "<th>".Lang::t('_USERNAME', 'standard')."</th>";
                            echo "<th>".Lang::t('_LASTNAME', 'standard')."</th>";
                            echo "<th>".Lang::t('_NAME', 'standard')."</th>";
                            echo "<th>".Lang::t('_STATE', 'classroom')."</th>"; 
                            echo "<th>".Lang::t('_TEMPLATE', 'certificate')."</th>";
                            echo "<th>".Lang::t('_EDITION', 'standard')."</th>";
                            echo "<th>".Lang::t('_DATE_COMPLETE', 'certificate')."</th>";
                            echo "<th>".Lang::t('_CERTIFICATE_RELEASE', 'course')."</th>";
                            echo "<th>".Get::sprite('subs_pdf', Lang::t('_TITLE_VIEW_CERT', 'certificate'))."</th>";
                            echo "<th>". Get::sprite('subs_del', Lang::t('_DEL', 'certificate'))."</th>";
                            echo "<th></th>";
                            
                            foreach($custom_field as $key=>$value)
                            {
                          
                                echo "<th class='custom'>".$value."</th>";
                            }                             
                            
                            
                            
                        echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";        
                    
             foreach($data_certificate['data'] as $key => $user){

                          echo "<tr>";
                          echo "<td>".$user['id_user']."</td>";
                          echo "<td></td>";
                          echo "<td>".$user['username']."</td>";
                          echo "<td>".$user['lastname']."</td>";
                          echo "<td>".$user['firstname']."</td>";
                          echo "<td>".$arr_status[$user['status']]."</td>";
                          echo "<td>".$user['template']."</td>";
                          echo "<td>".$user['edizione'][0]."<br>".$user['edizione'][1]."</td>";
                          echo "<td>".$user['date_complete']."</td>";
                          echo "<td>".$user['date_generate']."</td>";
                          echo "<td>".$user['gen_certificate']."</td>";
                          echo "<td>".$user['action_delete']."</td>";
                          echo "<td>".$user['id_certificate']."</td>";
                          
                          foreach($custom_field as $key=>$value){
                                echo "<td>".$user['custom_field_user'][(int)$key]."</td>"        ;
                          }                             
                          
                          echo "</tr>";

             }
               
        echo "</tbody>";
        
       
    echo "</table>";


    
    require_once(_base_.'/lib/lib.dialog.php');
    setupHrefDialogBox('a[href*=del_report_certificate]',Lang::t('_CONFIRM_DELETION', 'iotask'),Lang::t('_YES', 'standard'),Lang::t('_NO', 'standard'));    



    echo $print_button.'<br />';    

    echo "</div>";
    
    $this->widget('forma_tablefilter', array(
    'id' => 'classlocations_filter',
    'filter_text' => isset($filter_text) ? $filter_text : "",
    'js_callback_set' => 'ClassLocations.setFilter',
    'js_callback_reset' => 'ClassLocations.resetFilter'
    ));
    
    
?>
