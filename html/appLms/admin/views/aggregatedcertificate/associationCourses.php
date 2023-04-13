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

    #treecategory {
        max-height: 400px;
        overflow: auto;
    }
</style>
<?php

cout(
    getTitleArea($cert_name . ':&nbsp;' . Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION', 'certificate'))
);

?>



<?php cout(
    Form::openForm('new_assign_step_2',
        'index.php?r=alms/aggregatedcertificate/associationUsers')
); ?>

<script>

    var nodesIdArr = [];
    var data = '';
    var selected_one = false;

    <?php
    echo 'var idsCourseArr = ' . $idsCourses . ';';
    ?>

    $(document).ready(function () {
        var course_ft = $('#course_ft').FormaTable({
            'ajax': {
                type: 'POST',
                data: {
                    "node": 0,
                },
                url: 'ajax.adm_server.php?r=alms/aggregatedcertificate/getCourseList',
                "dataSrc": "",
            },
            language: {
                "emptyTable": "<?php echo Lang::t('_NO_COURSE_FOUND', 'catalogue'); ?>",
                "info": "<?php echo Lang::t('_INFO', 'datatable'); ?>",
                "infoEmpty": "<?php echo Lang::t('_INFOEMPTY', 'datatable'); ?>",
                select: {
                    rows: {
                        0: "<?php echo Lang::t('_NO_ROWS_SELECTED', 'datatable'); ?>",
                        1: "<?php echo Lang::t('_ONE_ROW_SELECTED', 'datatable'); ?>",
                        _: "<?php echo Lang::t('_ROWS_SELECTED', 'datatable'); ?>",
                    }
                }
            },
            rowId: function (row) {
                return row.idCourse;
            },

            columns: [
                {
                    data: 'idCourse',
                    sortable: false,
                    visible: false,
                    searchable: false,
                },
                {
                    data: 'codeCourse',
                    title: "<?php echo Lang::t('_CODE'); ?>",
                },
                {
                    data: 'nameCourse',
                    title: "<?php echo Lang::t('_COURSE_NAME'); ?>",
                },
                {
                    data: 'pathCourse',
                    title: "<?php echo Lang::t('_CATEGORY'); ?>",
                },
                {
                    data: 'stateCourse',
                    title: "<?php echo Lang::t('_STATUS'); ?>",
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


        course_ft._datatable.on('select.dt', function (e, dt, type, indexes) {
            if (type === 'row') {
                $('#ok_filter').prop("disabled", false);
                // getting data from the row selected
                var rowData = course_ft._datatable.rows(indexes).data().toArray();
                if (rowData.lenght != 0) {
                    rowData.forEach(function (row) {
                        var index = idsCourseArr.indexOf(parseInt(row['idCourse']));
                        if (index === -1)
                            idsCourseArr.push(parseInt(row['idCourse']));
                    });
                }
            }

        });


        course_ft._datatable.on('deselect.dt', function (e, dt, type, indexes) {

            // getting data from the row selected
            var rowData = course_ft._datatable.rows(indexes).data().toArray();
            if (rowData.lenght != 0) {
                rowData.forEach(function (row) {
                    var index = idsCourseArr.indexOf(parseInt(row['idCourse']));
                    if (index !== -1) // Found
                        idsCourseArr.splice(index, 1); // Deleting
                });
            }
            if (idsCourseArr.length == 0) $('#ok_filter').prop("disabled", true);
        });

        course_ft._datatable.on('draw', function () {

            course_ft._datatable.button(0).enable(course_ft._datatable.data().any());
            course_ft._datatable.rows().every(function (rowIdx, tableLoop, rowLoop) {
                    if (idsCourseArr.indexOf(parseInt(this.data().idCourse)) >= 0) {
                        this.select();
                        selected_one = true;
                    }

                }
            );

        });

        var treecat = $('#treecategory').treeview({
            data: <?php echo json_encode($treeCat); ?>,
            expandIcon: 'glyphicon glyphicon-folder-close',
            collapseIcon: ' glyphicon glyphicon-folder-open',
            selectedBackColor: "#73b3ff",
            selectedColor: "black",
            selectable: true,
            levels: 50,
            state: {
                checked: true,
                disabled: true,
                expanded: false,
                selected: true
            },
            onNodeSelected: function (event, node) {
                $.ajax({
                    type: 'POST',
                    data: {
                        "node": node["idCategory"]
                    },
                    dataType: "json",
                    url: "<?php echo 'ajax.adm_server.php?r=alms/aggregatedcertificate/getCourseList'; ?>",
                    success: function (res) {
                        if (res !== null) {
                            course_ft._datatable.clear().rows.add(res).draw();
                            course_ft._datatable.rows(function (idx, data, node) {
                                return idsCourseArr.indexOf(parseInt(data.idCourse)) !== -1;
                            }).select();
                        } else
                            course_ft._datatable.clear().draw();
                    },

                });
            }
        });

        $('#new_assign_step_2').on('submit', function (e) {
            var form = this;
            $(form).append(
                $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'idsCourse')
                    .val(idsCourseArr)
            );
        });
    });

    $(window).on("load", function () {
        $('#ok_filter').prop("disabled", !selected_one);

    })


</script>

<?php

require_once 'tab.php';
require_once _base_ . '/lib/lib.table.php';

?>

<input type="hidden" value="" id="nodesArr"/>

<?php

// Setting static table with all courses
if ($id_association > 0) {
    $tb_courses = new Table();
    $cont_h = [Lang::t('_CODE'), Lang::t('_COURSE_NAME'), Lang::t('_CATEGORY')];
    $type_h = ['', '', ''];
    $tb_courses->addHead($cont_h);
    $tb_courses->setColsStyle($type_h);
    foreach ($coursesArr as $course) {
        $tb_courses->addBody($course);
    }
}
$mtitle = Lang::t('_COURSES');
$id_table = 'course_ft';
$arrTab = [
    [
        'title' => $title,
        'content' => ($id_association > 0 ? $tb_courses->getTable() : '')
            . " <div id='treecategory'></div> "
            . "<table class='table table-striped table-bordered' style='width:100%' id='{$id_table}'></table>",
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
