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

$title_area = [
    'index.php?r=alms/communication/show' => Lang::t('_COMMUNICATIONS', 'communication'),
    Lang::t('_ASSIGN_USERS', 'communication'),
];

$user_selector->loadSelector('index.php?r=alms/communication/mod_user&id_comm=' . $id_comm,
                                $title_area,
                                '',
                                true);
