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

Util::get_js('../addons/jquery/datatables/Buttons-1.5.4/js/buttons.colVis.min.js',true, true);
Util::get_js('../appLms/admin/modules/certificate/certificate.js',true, true);

$vars = 'var ajax_url="ajax.adm_server.php?plf=lms&mn=certificate"; var _CLOSE="'.Lang::t('_CLOSE').'"; var _STOP="'.Lang::t('_STOP').'"; '
    .'var glob_id_certificate = 0, glob_id_course = '.(int)$id_course.';'
    .'var single_list = ['.(count($downloadables) ? '"'.implode('","', $downloadables).'"' : '').']; '
    .'var reload_url = "'.str_replace('&amp;', '&', (isset(/*$form_url*/$submit_url) ? /*$form_url*/$submit_url : '')).'", '
    .'_ERROR_PARSE = "'.Lang::t('_OPERATION_FAILURE').'", _SUCCESS = "'.Lang::t('_OPERATION_SUCCESSFUL').'", '
    .'_AREYOUSURE="'.Lang::t('_AREYOUSURE', 'standard').'";';
cout('<script type="text/javascript">'.$vars.'</script>',"page_head");
    
$back_label =  Lang::t('_CERTIFICATE_ASSIGN_STATUS', 'course');   
    
echo getTitleArea(array(
	'index.php?r=alms/course/show' => $back_label,
	$course_name
));


  $print_button = '<div>'
						.'<a id="print_selected_button_1" href="javascript:generate_all_certificate();">'
							.Get::img('course/certificate.png', Lang::t('_GENERATE_ALL_SELECTED', 'certificate'))
							.Lang::t('_GENERATE_ALL_SELECTED', 'certificate')
						.'</a>'
						.'&nbsp;&nbsp;&nbsp;'.
						'<a id="download_selected_button_1" href="javascript:download_all_certificate();">'
							.Get::img('course/certificate.png', Lang::t('_DOWNLOAD_ALL_SELECTED', 'certificate'))
							.Lang::t('_DOWNLOAD_ALL_SELECTED', 'certificate')
						.'</a>'
				.'</div>';

  echo $print_button.'<br />';
  echo "<div class='container-back'>";
  switch  ($from) {
      case "course":
        echo "<a href='index.php?r=alms/course/certificate&amp;id_course=".$id_course."'>".Lang::t('_BACK', 'standard')."</a>";      
        break;
      case "manage":
        echo "<a href='index.php?modname=certificate&op=report_certificate&of_platform=lms&id_certificate=".$id_certificate."'>".Lang::t('_BACK', 'standard')."</a>";
        break;
      default:
        echo "<a href='index.php?r=alms/course/show'>".Lang::t('_BACK', 'standard')."</a>";
  }
  
  echo "</div>"; 
 
 

?>
    
<input type='hidden' id='show_search' value='false'>
<input type='hidden' id='sel_all' value='false'>

<div class='std_block'>
           <table id='table_certificate'  data-id_course='<?=$id_course?>' data-id_certificate='<?php echo $id_certificate ?>' class='table table-striped table-bordered' style='width:100%'></table>            
<script type="text/javascript">

        var id_course=$('#table_certificate').data('id_course');
        var cert_table = $('#table_certificate').FormaTable({
            margin: '0 auto',
            scrollX: true,
            rowId: function(row) {
              return row.id_user + '-' + row.id_certificate;
            },
            deferRender: true,
            data:  <?php echo json_encode($data_certificate) ?>,
            select: {
                style: 'multi',
                all: true   
            },            
            columns:[
             { data: 'id_user', title: 'id_user', sortable: false, visible: false, searchable: false },
             { data: 'id_certificate', title: 'id_certificate', sortable: false, visible: false, searchable: false },
             { data: 'edition', title: '<?php echo Lang::t('_EDITION', 'standard'); ?>', sortable: true, visible: <?php echo (($course_type == 'classroom') ? 'true' :'false') ?>  },              
             { data: 'username', title: '<?php echo Lang::t('_USERNAME', 'standard'); ?>', sortable: true },
             { data: 'lastname', title: '<?php echo Lang::t('_LASTNAME', 'standard'); ?>', sortable: true },
             { data: 'firstname', title: '<?php echo Lang::t('_NAME', 'standard'); ?>', sortable: true },
             <?php
               $hidden_fields_n = 6;
               foreach($custom_fields as $key=>$value) {
                   $hidden_fields_n++;
                   $hidden_fields_array[] = $hidden_fields_n;
                   echo "{data:'cf_$key', title:'".$value."', sortable:true, visible: false},".PHP_EOL;
               }
             ?>               
             { data:'status', title: '<?php echo Lang::t('_STATUS', 'standard'); ?>', sortable: true,  
                render: function ( data, type, row ) { 
                    
                    switch (data){
                        case '-2':
                        return '<?php echo Lang::t('_WAITING', 'standard'); ?>'
                        case '-1':
                        return '<?php echo Lang::t('_USER_STATUS_CONFIRMED', 'standard'); ?>'
                        case '0':
                        return '<?php echo Lang::t('_NOT_STARTED', 'standard'); ?>'
                        case '1':
                        return '<?php echo Lang::t('_USER_STATUS_BEGIN', 'standard'); ?>'
                        case '2':
                        return '<?php echo Lang::t('_USER_STATUS_END', 'standard'); ?>'
                        case '3':
                        return '<?php echo Lang::t('_USER_STATUS_SUSPEND', 'standard'); ?>'
                        case '4':
                        return '<?php echo Lang::t('_USER_STATUS_OVERBOOKING', 'standard'); ?>'
                        default:
                        return ''
                    }
                
                
                }
             },
             { data: 'name_certificate', title: '<?php echo Lang::t('_CERTIFICATE_REPORT', 'certificate'); ?>', sortable: true },
             { data: 'date_complete', title: '<?php echo Lang::t('_DATE_END', 'standard'); ?>', sortable: true, type: 'date' },  // TBD converting to local time                      
             { data: 'on_date', title: '<?php echo Lang::t('_RELASE_DATE', 'certificate'); ?>', sortable: true, type: 'date' }, // TBD converting to local time
             { data: 'cell_down_gen', title: '<?php echo Get::sprite('subs_pdf', Lang::t('_TITLE_VIEW_CERT', 'certificate')) ?>', sortable: true, searchable: false },
             { data: 'cell_del_cert', title: '<?php echo Get::sprite('subs_del', Lang::t('_DEL', 'certificate')); ?>', sortable: false, searchable: false }
            ],
            pagingType: 'full_numbers',
            language : {
                 'sInfo'  : '<?php echo Lang::t('_FROM', 'standard'); ?>  _START_  <?php echo Lang::t('_TO', 'standard'); ?> _END_ <?php echo Lang::t('_OF', 'standard'); ?>   _TOTAL_ <?php echo Lang::t('_CERTIFICATE', 'menu'); ?> <?php echo Lang::t('_TOTAL', 'standard'); ?> ',
                 'infoEmpty': '',
                 'sEmptyTable' : '<?php echo Lang::t('_NO_CERTIFICATE_AVAILABLE', 'certificate'); ?> '
            },
            dom: 'Bfrtip',
            buttons:[ {
                        extend: 'colvis',
                        text: '<?=Lang::t('_CHANGEPOLICY', 'profile')?>',
                        columns: '<?=implode(",",$hidden_fields_array)?>', 
                      },
                      {
                        text: '<?=Lang::t('_ADVANCED_SEARCH', 'standard')?>',
                        action: function(e, dt, node, config){
                            cert_table.searchBar.show()
                        } 
                      }
            
            
            ]
        })
        cert_table.searchBar.init('#table_certificate');
        $('#table_certificate').on( 'column-visibility.dt', function ( e, settings, column, state ) {
            // if adding or remove fields, force redraw
            cert_table.searchBar.redraw() 
        });



	
		   function generate_all_certificate() {
        
            if(cert_table.getFlatSelection().length==0) return 
                
               $.each(cert_table.getFlatSelection(), function( index, value ) {
                    
               var this_row = value.split("-");
               push_arr_id_users(this_row[0]);
               push_arr_id_certificates(this_row[1]);
               push_arr_course_id(id_course);
                    
               }); 
             
              
             set_signature('<?php echo Util::getSignature(); ?>');
             send_print(null, { scope: null, type: "total"} );
            
            
        } 
		  
          
          function download_all_certificate(){
            var newarray=[];     
            $.each(cert_table.getFlatSelection(), function( index, value ) {
                    var this_row = value.split("-");
                    var id_user = this_row[0];
                    var id_certificate = this_row[1];
                    newarray.push(id_user + "-" + id_certificate + "-" + id_course);  
            });
            var strRows = newarray.join();
            if(strRows=="") return 
            document.location.href = "index.php?modname=certificate&of_platform=lms&op=download_all&str_rows=" + strRows;
          }          
          
                     
                    
          
          
          
</script>

<?php

    require_once(_base_.'/lib/lib.dialog.php');
    setupHrefDialogBox('a[href*=del_report_certificate]',Lang::t('_CONFIRM_DELETION', 'iotask'),Lang::t('_YES', 'standard'),Lang::t('_NO', 'standard'));    



    echo $print_button.'<br />';    

    echo "</div>";

    
    
?>