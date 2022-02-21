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

/**
 * @version  $Id: class.cf.php 601 2006-09-01 10:50:52Z giovanni $
 *
 * @category Field
 *
 * @author   Claudio Cherubino <claudio.cherubino@docebo.com>
 */
require_once Forma::inc(_adm_ . '/modules/field/class.field.php');

/**
 * class for IM fields.
 */
class Field_Contact extends Field
{
    /**
     * this function is useful for field recognize.
     *
     * @return string return the identifier of the field
     */
    public function getFieldType()
    {
        return 'contact_field';
    }

    public function getIMBrowserHref($id_user, $field_value)
    {
        return '';
    }

    public function getIMBrowserImageSrc($id_user, $field_value)
    {
        return '';
    }
}
