<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */


    $mytitle = $cert_name . ':&nbsp' . Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION', 'certificate');

    cout(
        getTitleArea($mytitle)
        . '<div class="std_block">'
            . Form::openForm('new_assign_step_0', 'index.php?r=alms/aggregatedcertificate/' . $operation)
                . Form::getHidden('id_certificate', 'id_certificate', $id_certificate)
                . (isset($id_association) ? Form::getHidden('id_association', 'id_association', $id_association) : '')
                . (isset($type_assoc) ? Form::getHidden('type_assoc', 'type_assoc', $type_assoc) : '')
                . Form::openElementSpace()
                    . Form::getTextfield(Lang::t('_NAME'), 'title', 'title', '255', isset($associationMetadataArr['title']) ? $associationMetadataArr['title'] : '')
                    . Form::getSimpleTextarea(Lang::t('_DESCRIPTION'), 'description', 'description', isset($associationMetadataArr['description']) ? $associationMetadataArr['description'] : '')
                    . Form::getDropdown(Lang::t('_COURSE_TYPE', 'catalogue'),
                                         'type_assoc',
                                         'type_assoc',
                                         $assoc_types,
                                         isset($type_assoc) ?? null,
                                         '',
                                         '',
                                         $html_before_select)
                . Form::closeElementSpace()
                . Form::openButtonSpace()
                    . Form::getButton('nextOperation', 'nextOperation', Lang::t('_NEXT'))
                    . (isset($id_association) &&  $id_association != 0 ? Form::getButton('nextOperation', 'nextOperation', Lang::t('_SAVE')) : '')
                    . Form::getButton('undo', 'undo_assign', Lang::t('_UNDO'))
                . Form::closeButtonSpace()
            . Form::closeForm()
        . '</div>'
    );
