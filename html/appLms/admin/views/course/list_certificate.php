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
        .'&nbsp;&nbsp;&nbsp;<a id="download_selected_button_1" href="javascript:download_all_certificate();">'
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
            rowId: 'id_user',
            deferRender: true,
            data:  <?php echo json_encode($data_certificate) ?>,
            select: {
                style: 'multi',
                all: true   
            },            
            columns:[
             { title: 'id_user', sortable: false, visible: false, searchable: false },
             { title: 'id_certificate', sortable: false, visible: false, searchable: false },
             { title: '<?php echo Lang::t('_EDITION', 'standard'); ?>', sortable: true, visible: <?php echo (($course_type == 'classroom') ? 'true' :'false') ?>  },              
             { title: '<?php echo Lang::t('_USERNAME', 'standard'); ?>', sortable: true },
             { title: '<?php echo Lang::t('_LASTNAME', 'standard'); ?>', sortable: true },
             { title: '<?php echo Lang::t('_NAME', 'standard'); ?>', sortable: true },
             <?php
               $hidden_fields_n = 6;
               foreach($custom_fields as $key=>$value) {
                   $hidden_fields_n++;
                   $hidden_fields_array[] = $hidden_fields_n;
                   echo "{title:'".$value."', sortable:true, visible: false},".PHP_EOL;
               }
             ?>               
             { title: '<?php echo Lang::t('_STATUS', 'standard'); ?>', sortable: true,  
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
             { title: '<?php echo Lang::t('_CERTIFICATE_REPORT', 'certificate'); ?>', sortable: true },
             { title: '<?php echo Lang::t('_DATE_END', 'standard'); ?>', sortable: true, type: 'date' },  // TBD converting to local time                      
             { title: '<?php echo Lang::t('_RELASE_DATE', 'certificate'); ?>', sortable: true, type: 'date' }, // TBD converting to local time
             { title: '<?php echo Get::sprite('subs_pdf', Lang::t('_TITLE_VIEW_CERT', 'certificate')) ?>', sortable: true, searchable: false },
             { title: '<?php echo Get::sprite('subs_del', Lang::t('_DEL', 'certificate')); ?>', sortable: false, searchable: false }
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
            // if search bar is visible force redraw
           if (cert_table.searchBar.isvisible())
                cert_table.searchBar.redraw() 
        });
        



          function print_certificate(id_user, id_course, id_certificate){
              var posting = $.get(
                        'index.php',
                        {
                            modname:'certificate',
                            of_platform:'lms',
                            op:'print_certificate',
                            certificate_id: id_certificate,
                            course_id: id_course,
                            user_id: id_user
                        }
                    );
                    posting.done(function (responseText) { 
                        location.reload();    
                    });
                    posting.fail(function () {
                        alert("Error generating certificate");
                    })      
          }
          
          
           function getRowsSelected(){
                the_table = $('#table_certificate').DataTable();
                var data = the_table.rows('.selected').data();
                var newarray=[];       
                for (var i=0; i < data.length ;i++){
                   newarray.push(data[i][1] + "-" + data[i][2]+"-"+id_course);          
                }
                var sData = newarray.join();                    
                return sData;      
           }
          // generate  selected certificates
          function generate_all_certificate(){
          
            var all_selected_Array =  getRowsSelected().split(',');
            if(all_selected_Array.length==0) return 
            $.each(all_selected_Array, function( index, value ) {
                
                var this_user = value.split("-");
                var id_user = this_user[0];
                var id_certificate = this_user[1];
                var the_course = this_user[2]
                print_certificate(id_user, the_course, id_certificate) ;
                
            });
          }
          
          function download_all_certificate(){
              
            strRows =   getRowsSelected();
            if(strRows=="") return 
            var arr_users = strRows.split(',');
            document.location.href = "index.php?modname=certificate&of_platform=lms&op=download_all&str_rows=" + strRows;
              
          }          
          
                     
                    
          
          
          
</script>

<?php

    require_once(_base_.'/lib/lib.dialog.php');
    setupHrefDialogBox('a[href*=del_report_certificate]',Lang::t('_CONFIRM_DELETION', 'iotask'),Lang::t('_YES', 'standard'),Lang::t('_NO', 'standard'));    



    echo $print_button.'<br />';    

    echo "</div>";

    
    
?>
