<?php

use FormaLms\lib\Userselector\Userselector;
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


class UserselectorAdmController extends AdmController
{
    protected $userSelector;

    public function __construct(){

        $this->userSelector = new Userselector();
        $this->_mvc_name = 'userselector';
    }

    public function list() {
        $this->render('show',[]);
    }

}
