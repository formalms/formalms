<?php

Util::get_js(FormaLms\lib\Get::rel_path('lms') . '/admin/views/aggregatedcertificate/tabbedcontent.min.js', true, true);
Util::get_css(FormaLms\lib\Get::rel_path('base') . '/addons/jquery/bootstrap-treeview/bootstrap-treeview.min.css', true, true);

include \FormaLms\lib\Forma::inc(_lib_ . '/formatable/include.php');
?>

<style>
    #coursepath_ft_wrapper { 
        margin: 15px 0;
    }
    #coursecatalog_ft_wrapper { 
        margin: 15px 0;
    }
</style>
<?php

cout(
     getTitleArea($cert_name . ':&nbsp;' . Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION', 'certificate'))
);

?>



<?php cout(
        Form::openForm('new_assign_step_2',
                    'index.php?r=adm/userselector/show&id=' . $id_association .'_' . $type_assoc . '&instance=aggregated_certificate&load=1&tab_filters[]=user&tab_filters[]=group&tab_filters[]=org')
); ?>

    <script>

    var nodesIdArr = [];
    var data = '';
    var selected_one = false;
    
<?php
    echo 'var idsCoursePathArr = ' . $idsCoursePath . ';';
?>

    $(document).ready(function () {    
            // Course Path
            var coursepath_ft = $('#coursepath_ft').FormaTable({
            
                processing: true,
                serverSide: true,
                'ajax': {
                    type: 'POST',
                    url: "<?php echo 'ajax.adm_server.php?r=alms/aggregatedcertificate/getCoursePathList'; ?>",
                },
                rowId: function(row) {
                  return row.idCoursePath;
                },
                columnDefs: [ 
                    {   
                        "width": "5%",
                        className: 'select-checkbox',
                        targets:   0,
                    }
                ],
                rowCallback: function( row, data) {

                    coursepath_ft._datatable.rows( function (idx, data, node){

                        return idsCoursePathArr.indexOf(parseInt(data.idCoursePath)) !== -1;

                    }).select()
                
                },
                columns:[
                    {
                        data: 'idCoursePath',
                        sortable: false,
                        visible: false,
                        searchable: false,

                    },
                    { 
                        data: 'nameCoursePath',
                        title: "<?php echo Lang::t('_NAME'); ?>",
                    },
                    { 
                        data: 'descriptionCoursePath',
                        title: "<?php echo Lang::t('_DESCRIPTION'); ?>",
                        searchable: false 
                    },

                ],                    
                select: {
                        style: 'multi',
                },
                'order': [[1, 'asc']],
                language: {       //TODO: Aggiungere lingue

                    "emptyTable": "<?php echo Lang::t('_NO_COURSEPATH_FOUND', 'coursepath'); ?>",
                    "info": "<?php echo Lang::t('_INFO', 'datatable'); ?>",
                    "infoEmpty": "<?php echo Lang::t('_INFOEMPTY', 'datatable'); ?>",
                    select: {
                        rows: {
                            0: "<?php echo Lang::t('_NO_COURSEPATH_SELECTED', 'coursepath'); ?>",
                            1: "<?php echo Lang::t('_ONE_ROW_SELECTED', 'datatable'); ?>",
                            _: "<?php echo Lang::t('_ROWS_SELECTED', 'datatable'); ?>",

                        }
                    }
                },
                dom: 'Bfrtip',
                buttons: [
                    'selectAll',
                    'selectNone'
                ],
            });

               
            coursepath_ft._datatable.on( 'select.dt', function ( e, dt, type, indexes ) {
                 if ( type === 'row' ) {
                        $('#ok_filter').prop("disabled", false);
                       // getting data from the row selected
                        var rowData = coursepath_ft._datatable.rows( indexes ).data().toArray();
                        if (rowData.lenght != 0){
                            rowData.forEach(function (row) {
                                var index = idsCoursePathArr.indexOf(parseInt(row['idCoursePath']));
                                if (index === -1)
                                    idsCoursePathArr.push(parseInt(row['idCoursePath']));
                            });
                        }
                 }
        } );    
             
        coursepath_ft._datatable.on( 'deselect.dt', function ( e, dt, type, indexes ) {
            
            // getting data from the row selected
            var rowData = coursepath_ft._datatable.rows( indexes ).data().toArray();

            if (rowData.lenght != 0){
                rowData.forEach(function (row) {
                    var index = idsCoursePathArr.indexOf(parseInt(row['idCoursePath']));
                    if (index !== -1) // Found
                        idsCoursePathArr.splice(index, 1); // Deleting
                });
            }
            if (idsCoursePathArr.length == 0) $('#ok_filter').prop("disabled", true);
        });

        coursepath_ft._datatable.on('draw', function () {
            coursepath_ft._datatable.button(0).enable(coursepath_ft._datatable.data().any());

        });

        $('#new_assign_step_2').on('submit', function(e){
              var form = this;
                  $(form).append(
                      $('<input>')
                          .attr('type', 'hidden')
                          .attr('name', 'idsCoursePath')
                          .val(idsCoursePathArr)
                  );
        });
      }

    );
    
    $(window).on("load", function(){
        $('#ok_filter').prop("disabled", !selected_one);
        
    })    


</script>

<?php

    require 'tab.php';
    require_once _base_ . '/lib/lib.table.php';

?>

    <input type="hidden" value="" id="nodesArr" />

<?php

    // Setting static table with all courses
    if ($id_association > 0) {
        $tb_courses = new Table();
        $cont_h = [Lang::t('_CODE'), Lang::t('_COURSE_PATH_NAME'), Lang::t('_COURSE_PATH_DESCR')];
        $type_h = ['', '', ''];
        $tb_courses->addHead($cont_h);
        $tb_courses->setColsStyle($type_h);
        require_once _lms_ . '/lib/lib.coursepath.php';
        foreach ($coursePathsArr as $coursePathInfo) {
            $tb_courses->addBody([$coursePathInfo[COURSEPATH_CODE],  $coursePathInfo[COURSEPATH_NAME], $coursePathInfo[COURSEPATH_DESCR]]);
        }
    }

    $mtitle = Lang::t('_COURSEPATH');
    $id_table = 'coursepath_ft';
    $arrTab = [
              [
              'title' => $title,
              'content' => ($id_association > 0 ? $tb_courses->getTable() : '')
              . "<table class='table table-striped table-bordered' style='width:100%' id='{$id_table}'>
                </table>
             ",
              ],
    ];

    TabContainer::printStartHeader();
    TabContainer::printNewTabHeader($arrTab);
    TabContainer::printEndHeader();

    Tab::printStartContent();
    Tab::printTab($arrTab);
    Tab::printEndContent();

   cout(
    Form::getHidden('id_certificate', 'id_certificate', $id_certificate)
    . Form::getHidden('id_association', 'id_association', $id_association)
    . Form::getHidden('type_assoc', 'type_assoc', $type_assoc)
    . Form::getHidden('title', 'title', $title)
    . Form::getHidden('description', 'description', $description)
    );

    cout(
        Form::openButtonSpace()
        . Form::getBreakRow()
        . Form::getButton('ok_filter', 'import_filter', Lang::t('_NEXT'))
        . Form::getButton('undo_filter', 'undo_filter', Lang::t('_UNDO'))
        . Form::closeButtonSpace()
        . Form::closeForm()
    );
?>