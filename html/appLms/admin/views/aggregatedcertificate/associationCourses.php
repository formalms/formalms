<?php

Util::get_js(Get::rel_path('lms').'/admin/views/'.$controller_name.'/tabbedcontent.min.js', true, true);
Util::get_css(Get::rel_path('base').'/addons/jquery/bootstrap-treeview/bootstrap-treeview.min.css', true, true);

include Forma::inc(_lib_ . '/formatable/include.php');
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
    getTitleArea(Lang::t('_AGGRETATE_CERTIFICATES_ASSOCIATION_CAPTION'), 'certificate')
);

?>

<div class="std_block">

<?php cout(
        Form::openForm('new_assign_step_2',
                    'index.php?r=alms/'.$this->controller_name.'/'.$opsArr['associationusers'])
); ?>

    <script>

    var nodesIdArr = [];
    var data = '';
    
<?php
    switch($type_assoc){
        case COURSE:
            echo "var idsCourseArr = " . json_encode($idsCourses)   . ";";
            break;
        case COURSE_PATH:
            echo "var idsCoursePathArr = " .(isset($idsCoursePath) ? json_encode($idsCoursePath) : "[]")  . ";";
            break;
    }
?>

    $(document).ready(function () {    
           
        if(typeof idsCourseArr !== 'undefined') {
         

            var course_ft = $('#course_ft').FormaTable({
                // processing: true,
                // serverSide: true, 
                'ajax': {
                     type: 'POST',                  
                     data: {
                        "nodesArr" : "0",
                     },
                     url: "<?= 'ajax.adm_server.php?r=alms/' . $controller_name . '/getCourseList'?>",
                     "dataSrc": "",
                }, 
                language: {       //TODO: Aggiungere lingue
                    "emptyTable": "<?= Lang::t('_NO_COURSE_FOUND','catalogue'); ?>",
                    "info": "<?= Lang::t('_INFO','datatable'); ?>",
                    "infoEmpty": "<?= Lang::t('_INFOEMPTY','datatable'); ?>",
                    select: {
                        rows: {
                            0: "<?= Lang::t('_NO_ROWS_SELECTED','datatable'); ?>",
                            1: "<?= Lang::t('_ONE_ROW_SELECTED','datatable'); ?>",
                            _: "<?= Lang::t('_ROWS_SELECTED','datatable'); ?>",
                        }
                    }
                },
                rowId: function(row) {
                  return row.idCourse;
                },


                columnDefs: [ 
                    {  
                        className: 'select-checkbox',
                        targets:   0,
                     /*   'createdCell':  function (td, cellData, rowData, row, col){
                       /* if( idsCourseArr.includes(rowData[0]) ){
                            
                        }*/
                    }
                ],
                columns: [   
                    {
                        data: 'idCourse',
                        sortable: false,
                        visible: false,
                        searchable: false,
                    },
                    {
                        data: 'codeCourse',
                        title: "<?= Lang::t('_CODE') ?>",
                    },
                    {
                         data: 'nameCourse',
                        title: "<?= Lang::t('_COURSE_NAME') ?>",
                    },
                    {
                        data: 'pathCourse',
                        title: "<?= Lang::t('_CATEGORY') ?>",
                    },
                    {
                        data: 'stateCourse',
                        title: "<?= Lang::t('_STATUS') ?>",
                    } 
                ],
                select: {
                        style: 'multi',

                },
                'order': [[1, 'asc']],
                dom: 'Bfrtip',
                buttons: [
                    'selectAll',
                    'selectNone'
                ],
            });   
            
                
            course_ft._datatable.on( 'select.dt', function ( e, dt, type, indexes ) {
                 if ( type === 'row' ) {
                 
                       // getting data from the row selected
                       var rowData = course_ft._datatable.rows( indexes ).data().toArray();

                       if (rowData.lenght != 0){
                           rowData.forEach(function (row) {
                               var index = idsCourseArr.indexOf(parseInt(row['idCourse']));
                               if (index === -1)
                                   idsCourseArr.push(parseInt(row['idCourse']));
                           });


                       }
                 }

            } );


            course_ft._datatable.on( 'deselect.dt', function ( e, dt, type, indexes ) {
                
                // getting data from the row selected
                var rowData = course_ft._datatable.rows( indexes ).data().toArray();

                if (rowData.lenght != 0){
                    rowData.forEach(function (row) {
                        var index = idsCourseArr.indexOf(parseInt(row['idCourse']));
                        if (index !== -1) // Found
                            idsCourseArr.splice(index, 1); // Deleting
                    });
                }
            });

            course_ft._datatable.on('draw', function () {

 //                if(!course_ft._datatable.data().any()) console.log("non ci sono dati.");
                course_ft._datatable.button(0).enable(course_ft._datatable.data().any());

            });

       var treecat = $('#treecategory').treeview({
            data: <?php echo json_encode($treeCat); ?>,
            levels: 1,
            expandIcon: 'glyphicon glyphicon-folder-close',
            collapseIcon: ' glyphicon glyphicon-folder-open',
            selectedBackColor: "#73b3ff",
            selectedColor: "black",
            onNodeSelected: function (event,node) {

                // Retrieving the node info and all the other under-nodes
            nodesIdArr = [];

            getChildsId(node);

            $.ajax({
                type: 'POST',                  
                data: {
                    "nodesArr" : nodesIdArr.join(","),
                },
                "dataType": "json",
                url: "<?= 'ajax.adm_server.php?r=alms/' . $controller_name . '/getCourseList'?>",
                success: function(res){

                  if(res !== null) {
                      
                      course_ft._datatable.clear().rows.add(res).draw(); 
                      course_ft._datatable.rows( function (idx, data, node){
                            
                            return idsCourseArr.indexOf(parseInt(data.idCourse)) !== -1;
                      
                      }).select();   
                      
                  } else
                      course_ft._datatable.clear().draw();

                },
                
                    });
                }
        });

      } else if(typeof idsCoursePathArr !== 'undefined') {

        // Course Path
        var coursepath_ft = $('#coursepath_ft').FormaTable({
            
            processing: true,
            serverSide: true,
            'ajax': {
                type: 'POST',
                url: "<?= 'ajax.adm_server.php?r=alms/' . $controller_name . '/getCoursePathList'?>",
            },
            rowId: function(row) {
              return row.idCoursePath;
            },
            columnDefs: [ 
                {   
                    "width": "5%",
                    className: 'select-checkbox',
                    targets:   0,
                 /*   'createdCell':  function (td, cellData, rowData, row, col){
                   /* if( idsCourseArr.includes(rowData[0]) ){
                        
                    }*/
                }
            ],
            rowCallback: function( row, data) {
            
                // if (idsCoursePathArr.indexOf(parseInt(data.idCoursePath)) !== -1){
                //    coursepath_ft._datatable.row.select();
                // }

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
                    title: "<?= Lang::t('_NAME') ?>",
                },
                { 
                    data: 'descriptionCoursePath',
                    title: "<?= Lang::t('_DESCRIPTION') ?>",
                    searchable: false 
                },

            ],                    
            select: {
                    style: 'multi',
            },
            'order': [[1, 'asc']],
            language: {       //TODO: Aggiungere lingue

                "emptyTable": "<?= Lang::t('_NO_COURSEPATH_FOUND','coursepath'); ?>",
                "info": "<?= Lang::t('_INFO','datatable'); ?>",
                "infoEmpty": "<?= Lang::t('_INFOEMPTY','datatable'); ?>",
                select: {
                    rows: {
                        0: "<?= Lang::t('_NO_COURSEPATH_SELECTED','coursepath'); ?>",
                        1: "<?= Lang::t('_ONE_ROW_SELECTED','datatable'); ?>",
                        _: "<?= Lang::t('_ROWS_SELECTED','datatable'); ?>",

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

        });

        coursepath_ft._datatable.on('draw', function () {

            //                if(!course_ft._datatable.data().any()) console.log("non ci sono dati.");
            coursepath_ft._datatable.button(0).enable(coursepath_ft._datatable.data().any());

        });

      }

        $('#new_assign_step_2').on('submit', function(e){
             
              var form = this;
              
              if(typeof idsCourseArr !== 'undefined'){
                 $(form).append(
                     $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'idsCourse')
                        .val(idsCourseArr)
                 );
                 $(form).append(
                     $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'alreadySelCourses')
                        .val(<?= json_encode($idsCourses) ?>)
                 ); 
              } else if(typeof idsCoursePathArr !== 'undefined') {

                  $(form).append(
                      $('<input>')
                          .attr('type', 'hidden')
                          .attr('name', 'idsCoursePath')
                          .val(idsCoursePathArr)
                  );
                  $(form).append(
                      $('<input>')
                          .attr('type', 'hidden')
                          .attr('name', 'alreadySelCoursepaths')
                          .val(<?= json_encode($idsCoursePath) ?>)
                  );
              }
                         
           });


    });


    function getChildsId(node) {

        nodesIdArr.push(node["idCategory"]);

        var i = 0;
        if(node["nodes"]){
            while( i < node["nodes"].length ){

                getChildsId(node["nodes"][i]);

                i += 1;
            }
        }
    }


</script>

<?php

    require_once($GLOBALS["where_lms"].'/lib/tab/lib.tab.php');
    require_once(_base_.'/lib/lib.table.php');

?>

    <input type="hidden" value="" id="nodesArr" />

<?php

    // Setting static table with all courses
    if($edit){
    
        $tb_courses = new Table();
    
        switch($type_assoc){
            
            case COURSE:
            
                $cont_h = array( Lang::t('_CODE'), Lang::t('_COURSE_NAME'), Lang::t('_CATEGORY') );
                $type_h = array('', '', '');

                break;
            
            case COURSE_PATH:
                
                $cont_h = array( Lang::t('_CODE'), Lang::t('_COURSE_PATH_NAME'), Lang::t('_COURSE_PATH_DESCR') );
                $type_h = array('', '', '');

                
                break;
            
            default:
                break;
            
        }
        
        $tb_courses->addHead($cont_h);
        $tb_courses->setColsStyle($type_h);
     
        switch($type_assoc){
                
                case COURSE:
                
                     foreach($coursesArr as $course) 
                        $tb_courses->addBody($course);

                    break;
                
                case COURSE_PATH:

                    require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');

                    
                    foreach($coursePathsArr as $coursePathInfo)
                        $tb_courses->addBody(
                           [ 
                            $coursePathInfo[COURSEPATH_CODE], 
                            $coursePathInfo[COURSEPATH_NAME],
                            $coursePathInfo[COURSEPATH_DESCR],
                             ]
                             );    
                    
                    break;
                

                
        }
        
       
    } 

    switch ($type_assoc) {

        case COURSE:
            $title =  Lang::t("_COURSES");
            $id_table = "course_ft";
            break;

        case COURSE_PATH:
            $title =  Lang::t("_COURSEPATH");
            $id_table = "coursepath_ft";
            break;

        default:
            $title = "Type_assoc_not_valid";
            break;
    }
   
    $arrTab = array(
              array(
              "title"     => $title,
              "content"   => 
              
              ($edit ? $tb_courses->getTable() : '')
              . ($type_assoc == COURSE ? " <div id='treecategory'></div> " : "")
              . "<table class='table table-striped table-bordered' style='width:100%' id='{$id_table}'>
                </table>
             "             
          )
    );

    TabContainer::printStartHeader();
    TabContainer::printNewTabHeader($arrTab);
    TabContainer::printEndHeader();

    Tab::printStartContent();
    Tab::printTab($arrTab);
    Tab::printEndContent();

   cout(
    Form::getHidden("id_certificate","id_certificate",$id_certificate)
    .Form::getHidden("id_association","id_association",$id_association)
    .Form::getHidden("type_assoc","type_assoc",$type_assoc)
    .Form::getHidden("edit","edit",$edit)
    );

    cout(
        Form::openButtonSpace()
        .Form::getBreakRow()
        .Form::getButton('ok_filter', 'import_filter', Lang::t('_NEXT'))
        .Form::getButton('undo_filter', 'undo_filter', Lang::t('_UNDO'))
        .Form::closeButtonSpace()
        .Form::closeForm()
    );
?>

</div>
