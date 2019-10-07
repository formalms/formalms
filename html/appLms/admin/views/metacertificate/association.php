<?php


    cout(
        getTitleArea(Lang::t('_TITLE_META_CERTIFICATE_ASSIGN','certificate'), 'certificate')
        .'<div class="std_block">'
            .Form::openForm('new_assign_step_0', 'index.php?r=alms/'.$controller_name.'/'. $arrOps['saveMetadataAssoc'])
                .Form::getHidden('id_certificate', 'id_certificate',  $id_certificate)
                .(isset($edit) ? Form::getHidden('edit', 'edit',  $edit) : '')
                .(isset($id_metacertificate) ? Form::getHidden('id_metacertificate', 'id_metacertificate',  $id_metacertificate) : '')
                .Form::openElementSpace()
                    .Form::getTextfield(Lang::t('_NAME'), 'title', 'title', '255', isset($associationMetadataArr['title']) ? $associationMetadataArr['title'] : '')
                    .Form::getSimpleTextarea(Lang::t('_DESCRIPTION'), 'description', 'description', isset($associationMetadataArr['description']) ? $associationMetadataArr['description'] : '')
                    .Form::getDropdown(Lang::t('_COURSE_TYPE','catalogue'),
                                         'type_course',
                                         'type_course', 
                                         $assoc_types,
                                         $type_course,
                                         '',
                                         '',
                                         $html_before_select)
                .Form::closeElementSpace()
                .Form::openButtonSpace()
                    .Form::getButton('next', 'next', Lang::t('_NEXT'))
                    .Form::getButton('undo', 'undo_assign', Lang::t('_UNDO'))
                .Form::closeButtonSpace()
            .Form::closeForm()
        .'</div>'
    );

