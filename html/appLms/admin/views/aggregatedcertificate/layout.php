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

   require_once _base_ . '/lib/lib.form.php';
   require_once _base_ . '/lib/lib.table.php';

    $form = new Form();
    cout(
        getTitleArea($page_title, 'certificate')
        . '<div class="std_block">'
        . getBackUi('index.php?r=alms/' . $controller_name . '/' . $this->op['home'], Lang::t('_BACK'))
    );

 cout('<div class="std_block">');

    // cout( getInfoUi(Lang::t('_CERTIFICATE_WARNING')) );

    cout(
    $form->openForm('structure_certificate_form', 'index.php?r=alms/' . $controller_name . '/saveLayout', false, false, 'multipart/form-data')
    . $form->openElementSpace()
    . $form->getTextarea(Lang::t('_STRUCTURE_CERTIFICATE', 'certificate'), 'structure', 'structure', isset($template['cert_structure']) ? $template['cert_structure'] : '')
    . '<p><b>' . Lang::t('_ORIENTATION', 'certificate') . '</b></p>'
    . $form->getRadio(Lang::t('_PORTRAIT', 'certificate'), 'portrait', 'orientation', 'P', isset($template['orientation']) ? $template['orientation'] == 'P' : 1)
    . $form->getRadio(Lang::t('_LANDSCAPE', 'certificate'), 'landscape', 'orientation', 'L', ($template['orientation'] == 'L'))
    . $form->getExtendedFilefield(Lang::t('_BACK_IMAGE', 'certificate'),
                                                'bgimage',
                                                'bgimage',
                                                $template['bgimage'])
    . $form->closeElementSpace()
    . $form->openButtonSpace()
    . $form->getHidden('id_certificate', 'id_certificate', $id_certificate)
    . $form->getButton('structure_certificate', 'structure_certificate', (Lang::t('_SAVE')))
    . $form->getButton('undo', 'undo', Lang::t('_UNDO'))
    . $form->closeButtonSpace()
    . $form->closeForm());

        // Table for tags creation.
    $tb = new Table(0, Lang::t('_TAG_LIST_CAPTION', 'certificate'), Lang::t('_TAG_LIST_SUMMARY', 'certificate'));

    $tb->setColsStyle(['', '']);
    $tb->addHead([Lang::t('_TAG_CODE', 'certificate'), Lang::t('_TAG_DESCRIPTION', 'certificate')]);

    foreach ($certificate_tags as $key => $value) {
        if (file_exists($GLOBALS['where_lms'] . '/lib/certificate/' . $value['file_name'])) {
            require_once $GLOBALS['where_lms'] . '/lib/certificate/' . $value['file_name'];
            $instance = new $value['class_name'](0, 0, 1);
            $this_subs = $instance->getSubstitutionTags();
            foreach ($this_subs as $tag => $description) {
                $tb->addBody([$tag, $description]);
            }
        }
    }

    cout($tb->getTable());

    cout('</div>');
