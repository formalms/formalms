<?php

use FormaLms\lib\Forma;

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


require_once Forma::inc(_adm_ . '/modules/field/class.dropdown_complex.php');

class DropdowncomplexAdmController extends AdmController
{
    public function init()
    {
        
        $this->_mvc_name = 'dropdowncomplex';
    }

    public function getDropdownComplex()
    {
        if (!Util::requestIsPost()) {
            Util::jump_to(\FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _logout_);
        }
        $id_common = FormaLms\lib\Get::req('id_common', DOTY_INT, 0);
        $id_user = FormaLms\lib\Get::req('id_user', DOTY_INT, 0);
        $id_son = FormaLms\lib\Get::req('id_son', DOTY_INT, 0);
        $id_common_copy_ref = FormaLms\lib\Get::req('id_common_copy_ref', DOTY_INT, 0);
        $id_selected = FormaLms\lib\Get::req('id_selected', DOTY_INT, 0);
        $value = FormaLms\lib\Get::req('value', DOTY_INT, 0);

        $registrationLayout = FormaLms\lib\Get::req('registration_layout', DOTY_INT, 0);

        $is_mandatory = (\FormaLms\lib\Get::req('mandatory', DOTY_STRING, 0) && FormaLms\lib\Get::req('mandatory', DOTY_STRING, 0) == 'true') ? true : false;

        $dd = new Field_DropdownComplex($id_son, $id_common_copy_ref);

        $output = $dd->play($id_user, null, $is_mandatory, false, $value, $registrationLayout, false, $id_selected, true);

        echo $output;
        exit();
    }
}