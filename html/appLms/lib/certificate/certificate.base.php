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

class CertificateSubstitution
{
    public $id_user;
    public $id_course;
    public $id_meta;

    public function __construct($id_user, $id_course, $id_meta = 0)
    {
        $this->id_user = $id_user;
        $this->id_course = $id_course;
        $this->id_meta = $id_meta;
    }

    public function getSubstitution()
    {
        return [];
    }

    public function getSubstitutionTags()
    {
        return [];
    }
}
