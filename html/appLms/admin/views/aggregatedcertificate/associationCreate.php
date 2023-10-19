

<?php

     cout(getTitleArea($cert_name . ':&nbsp;' . Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION', 'certificate'))
                    . '<div class="std_block">'
                    . Form::openForm('new_assign_step_3', 'index.php?r=alms/' . $this->controller_name . '/' . $opsArr['saveAssignment'])
                    . Form::getHidden('id_certificate', 'id_certificate', $id_certificate)
                    . Form::getHidden('id_assoc', 'id_assoc', $id_association)
                    . Form::getHidden('type_assoc', 'type_assoc', $type_assoc)
                    . Form::getHidden('selected_courses', 'selected_courses', $selected_courses)
                    . Form::getHidden('selected_users', 'selected_users', $selected_users)
                    . Form::getHidden('selected_users', 'selected_idsCoursePath', $selected_idsCoursePath)
                    . Form::getHidden('title', 'title', $title)
                    . Form::getHidden('description', 'description', $description)
     );

   cout(
                Form::openElementSpace()
                . $tb->getTable()
                . Form::closeElementSpace()
                . Form::openButtonSpace()
                . Form::getButton('select_all', 'select_all', Lang::t('_SELECT_ALL'), false, '', true, false)
                . Form::getButton('insert', 'insert', Lang::t('_INSERT'))
                . Form::getButton('undo_assign', 'undo_assign', Lang::t('_UNDO'))
                . Form::closeButtonSpace()
                . Form::closeForm()
    );

        ?>


<script>
    $("#select_all").click(function () {

        $("#tb_AssocLinks input:checkbox").prop('checked',true);

    });
</script>
