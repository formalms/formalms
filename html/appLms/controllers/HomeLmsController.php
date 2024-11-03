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

class HomeLmsController extends LmsController
{
    public $name = 'home';

    public function show()
    {
        require_once _base_ . '/lib/lib.navbar.php';
        require_once _lms_ . '/lib/lib.middlearea.php';

        $title = '';
        $content = '';
        $query_home = "SELECT title, description FROM %lms_webpages where publish=1 and in_home = 1 AND language = '" . Lang::getDefault() . "' LIMIT 1";
        $re_home = sql_query($query_home);
        list($title, $content) = sql_fetch_row($re_home);

        $this->render('_tabs', []);

        $this->render('home-content', [
            'title' => $title,
            'content' => $content,
        ]);
    }
}
