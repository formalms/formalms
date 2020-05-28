

<?php


     cout(    getTitleArea($cert_name.':&nbsp;'.Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION', 'certificate'))
                    .'<div class="std_block">'
                    .$form->openForm('new_assign_step_3', 'index.php?r=alms/'.$this->controller_name.'/'.$opsArr['saveAssignment'])
                    .$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
                    .$form->getHidden('id_assoc', 'id_assoc', $id_association)
                    .$form->getHidden('type_assoc', 'type_assoc', $type_assoc)
                    .$form->getHidden('selected_courses', 'selected_courses', $selected_courses)
                    .$form->getHidden('selected_users', 'selected_users', $selected_users)
                    .$form->getHidden('selected_users', 'selected_idsCoursePath', $selected_idsCoursePath)
                    .$form->getHidden('title', 'title', $title)
                    .$form->getHidden('description', 'description', $description)
                    
                    
     );
             
   cout(    
                $form->openElementSpace()
                .$tb->getTable()
                .$form->closeElementSpace()
                .$form->openButtonSpace()
                .Form::getButton('select_all', 'select_all', Lang::t('_SELECT_ALL'),false,'',true,false)
                .$form->getButton('insert', 'insert', Lang::t('_INSERT'))
                .$form->getButton('undo_assign', 'undo_assign', Lang::t('_UNDO'))
                .$form->closeButtonSpace()
                .$form->closeForm()
    );

        ?>


<script>
    $("#select_all").click(function () {

        $("#tb_AssocLinks input:checkbox").prop('checked',true);

    });
</script>
