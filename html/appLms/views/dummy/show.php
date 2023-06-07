<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

    $languages = [
        '_ROOT' => Lang::t('_CATEGORY'),
        '_YES' => Lang::t('_CONFIRM'),
        '_NO' => Lang::t('_UNDO'),
        '_NEW_FOLDER_NAME' => Lang::t('_NEW_CATEGORY'),
        '_MOD' => Lang::t('_MOD'),
        '_AREYOUSURE' => Lang::t('_AREYOUSURE'),
        '_NAME' => Lang::t('_NAME'),
        '_MOD' => Lang::t('_MOD'),
        '_DEL' => Lang::t('_DEL'),
    ];

    $arguments = [
        'id' => 'tree',
        'ajaxUrl' => 'ajax.adm_server.php?plf=lms&file=category_tree&sf=folder_tree', //'ajax.server.php?r=dummy/testdata',
        'treeClass' => 'CourseFolderTree',
        'treeFile' => FormaLms\lib\Get::rel_path('base') . '/widget/tree/coursefoldertree.js',
        'languages' => $languages,
        'show' => 'tree',
    ];

    $this->widget('tree', $arguments);
