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

    cout(getTitleArea($cert_name . ':&nbsp;' . Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION', 'certificate'))
                . '<div class="std_block">');

    cout($tb->getTable()
                . $tb->getNavBar($ini, $countAssociations)
                . getBackUi('index.php?r=alms/' . $controller_name . '/' . array_key_exists('home', $arrOps) ?  '':$arrOps['home'] , Lang::t('_BACK'))
                . '</div>');
