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

class AlmsController extends Controller
{
    protected $_mvc_name = 'almscontroller';

    public function viewPath()
    {
        return _lms_ . '/admin/views';
    }

    public function templatePath()
    {
        return _templates_ . '/' . getTemplate() . '/layout';
    }

    public function init()
    {
        parent::init();
        checkPerm('view', false, $this->_mvc_name, 'lms');
    }
}
