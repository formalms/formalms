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

class AdmController extends Controller
{
    protected $_mvc_name = 'admcontroller';

    public function viewPath()
    {
        return _adm_ . '/views';
    }

    public function init()
    {
        parent::init();
        if (!defined('CORE')) {
            checkRole('/framework/admin/' . $this->_mvc_name . '/view', false);
        } else {
            checkPerm('view', false, $this->_mvc_name, 'framework');
        }
    }

    public function templatePath()
    {
        return _templates_ . '/' . getTemplate() . '/layout/appCore';
    }
}
