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
    'index.php?r=alms/games/show' => Lang::t('_CONTEST', 'games'),
    Lang::t('_ASSIGN_USERS', 'games'),
];

$user_selector->loadSelector('index.php?r=alms/games/mod_user&id_game=' . $id_game,
                                $title_area,
                                '',
                                true);
