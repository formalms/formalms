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

defined('IN_FORMA') or exit('Direct access is forbidden.');

if (Docebo::user()->isAnonymous()) {
    exit('You can\'t access!');
}

if (!defined('IN_LMS')) {
    define('IN_LMS', true);
}

require_once _adm_ . '/modules/newsletter/newsletter.php';
