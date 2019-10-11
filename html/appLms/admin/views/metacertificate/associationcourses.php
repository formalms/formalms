<?php

Util::get_js(Get::rel_path('lms').'/admin/views/metacertificate/tabbedcontent.min.js', true, true);
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
    .'<div class="std_block">'
    .Form::openForm('new_assign_step_2', 'index.php?r=alms/'.$this->controller_name.'/'.$opsArr['associationUsersCourses'])
);

?>

<script>

    var nodesIdArr = [];
    var data = '';
    
    <?php 
        // Printing initially all the courses if i'm editing the assoc.
        if(isset($idsCourses)) 
            echo "var idsCourseArr = " . json_encode($idsCourses); 
        else if(isset($idsCoursePath))
            echo "var idsCoursePathArr = " . json_encode($idsCoursePath);
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
                     url: 'ajax.adm_server.php?r=alms/metacertificate/getCourseList',
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
                        all: true   
                },     
                'order': [[1, 'asc']]
            });   
            
                
            course_ft._datatable.on( 'select.dt', function ( e, dt, type, indexes ) {
                 if ( type === 'row' ) {
                 
                       // getting data from the row selected
                       var rowData = course_ft._datatable.rows( indexes ).data().toArray();
                 
                       var index = idsCourseArr.indexOf(parseInt(rowData[0]['idCourse']));
                       if (index === -1)
                            
                            idsCourseArr.push(parseInt(rowData[0]['idCourse']));

                 }
            } );    
            course_ft._datatable.on( 'deselect.dt', function ( e, dt, type, indexes ) {
                
                // getting data from the row selected
                var rowData = course_ft._datatable.rows( indexes ).data().toArray();
                 
                var index = idsCourseArr.indexOf(parseInt(rowData[0]['idCourse']));
                if (index !== -1) // Found
                    idsCourseArr.splice(index, 1); // Deleting
      
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
                url: 'ajax.adm_server.php?r=alms/metacertificate/getCourseList',
                success: function(res){

                    // Iterate over all selected checkboxes
                
          /*      var rows_selected = course_ft._datatable.rows( { selected: true } ).data();  // FORMATABLE
                  //var rows_selected = course_ft.rows( { selected: true } ).data();

                $.each(rows_selected, function(index, rowId) {
                  
                        if( idsCourseArr.indexOf(rowId) === -1 )
                            idsCourseArr.push(rowId[0]);
 
                });*/
                   
                  
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
            ajax: "ajax.adm_server.php?r=alms/metacertificate/getCoursePathList",
          
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
                    all: true   
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
        });

           
        coursepath_ft._datatable.on( 'select.dt', function ( e, dt, type, indexes ) {
             if ( type === 'row' ) {
             
                   // getting data from the row selected
                   var rowData = coursepath_ft._datatable.rows( indexes ).data().toArray();
             
                   var index = idsCoursePathArr.indexOf(parseInt(rowData[0]['idCoursePath']));
                   if (index === -1)
                        idsCoursePathArr.push(parseInt(rowData[0]['idCoursePath']));

             }
        } );    
             
        coursepath_ft._datatable.on( 'deselect.dt', function ( e, dt, type, indexes ) {
            
            // getting data from the row selected
            var rowData = coursepath_ft._datatable.rows( indexes ).data().toArray();
             
            var index = idsCoursePathArr.indexOf(parseInt(rowData[0]['idCourse']));
            if (index !== -1) // Found
                idsCoursePathArr.splice(index, 1); // Deleting
  
        });
          
      }  
      /*
        // Course catalog
        coursecatalog_ft = $('#coursecatalog_ft').FormaTable({
             
            processing: true,
            serverSide: true,
            ajax: "ajax.adm_server.php?r=alms/metacertificate/getCatalogCourseList",
            
            rowId: function(row) {
                return row.idCatalogue;
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

            columns:[
                {
                    data: 'idCatalogue',
                    sortable: false,
                    visible: false,
                    searchable: false,

                },
                { 
                    data: 'nameCatalog',
                    title: "<?= Lang::t('_NAME') ?>",
                },
                { 
                    data: 'descriptionCatalog',
                    title: "<?= Lang::t('_DESCRIPTION') ?>",
                    searchable: false 
                }
            ],
            columnDefs: [ {
                className: 'select-checkbox',
                
                targets:   0,

            } ],
            select: {
                    style: 'multi',
                    all: true   
            }, 
            'order': [[1, 'asc']]

        });*/
   
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
                        .attr('name', 'oldestCourses')
                        .val(<?= json_encode($idsCourses) ?>)
                 ); 
              } else if(typeof idsCoursePathArr !== 'undefined'){
                
                 $(form).append(
                     $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'idsCoursePath')
                        .val(idsCoursePathArr)
                 );
                  
              }
              
                 
             /*
              $(form).append(
                     $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'idsCourseCatalogArr')
                        .val(idsCourseCatalogArr)
                 );  */
                         
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

   /* $tb = new Table(Get::sett("visuItem"), Lang::t("_COURSE_LIST"));
    $tb->addHead(
            array('',
                Lang::t("_CODE"),
                Lang::t("_COURSE_NAME"),
                Lang::t("_STATUS"),
                )
    );
    $tb->addBody(array("Test","test 2", "test 3", "test 4")); */

    ?>

    <input type="hidden" value="" id="nodesArr" />

<?php

    // Setting static table with all courses
    if(isset($edit)){
    
        $tb_courses = new Table();
    
        $cont_h = array( Lang::t('_CODE'), Lang::t('_COURSE_NAME'), Lang::t('_CATEGORY') );
        $type_h = array('', '', '');
        
        $tb_courses->addHead($cont_h);
        $tb_courses->setColsStyle($type_h);
        
        foreach($coursesArr as $course){
            $tb_courses->addBody($course);
        }
    } 
   
   
    $arrTab = array(
              array(
              "title"     => (isset($idsCourses) ? Lang::t("_COURSES") : Lang::t("_COURSEPATH")),
              "content"   => 
              
              (isset($edit) ? $tb_courses->getTable() : '')
              . " <div id='treecategory'></div>
                <table class='table table-striped table-bordered' style='width:100%' id='".(isset($idsCourses) ? 'course_ft' : 'coursepath_ft')."'>
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
    .Form::getHidden("id_metacertificate","id_metacertificate",$id_metacertificate)
    );

    cout(
        Form::openButtonSpace()
        .Form::getBreakRow()
        .Form::getButton('ok_filter', 'import_filter', Lang::t('_NEXT'))
        .Form::getButton('undo_filter', 'undo_filter', Lang::t('_UNDO'))
        .Form::closeButtonSpace()
        .Form::closeForm()
        .'</div>'
    );

