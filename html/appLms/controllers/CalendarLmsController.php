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

defined('IN_FORMA') or exit('Direct access is forbidden.');

class CalendarLmsController extends LmsController
{
    public $name = 'calendar';

    protected $_default_action = 'show';

    public function isTabActive($tab_name)
    {
        return true;
    }

    public function init()
    {
        YuiLib::load('base,tabview');
        Lang::init('course');
    }

    public function showTask()
    {
        $this->render('_tabs', []);
    }

    public function allTask()
    {
        $this->render('calendar', []);
    }

    public function courseTask()
    {
        $this->render('calendar', []);
    }

    public function communicationTask()
    {
        $this->render('calendar', []);
    }

    public function videoconferenceTask()
    {
        $this->render('calendar', []);
    }
}
