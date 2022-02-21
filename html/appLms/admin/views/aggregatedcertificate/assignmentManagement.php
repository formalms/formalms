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

      cout(
        getTitleArea(Lang::t('_TITLE_META_CERTIFICATE_CREATE', 'certificate'), 'certificate')
        . '<div class="std_block">'
        . getBackUi('index.php?r=alms/' . $controller_name . '/' . $opsArr['home'], Lang::t('_BACK'))
        . $tb->getTable()
        . getBackUi('index.php?r=alms/' . $controller_name . '/' . $opsArr['home'], Lang::t('_BACK'))

        . '</div>'
      );
