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

require_once dirname(__FILE__) . '/certificate.base.php';

class CertificateSubs_Misc extends CertificateSubstitution
{
    public function getSubstitutionTags()
    {
        $subs = [];
        $subs['[today]'] = Lang::t('_COURSE_TODAY', 'certificate', 'lms');
        $subs['[year]'] = Lang::t('_COURSE_YEAR', 'certificate', 'lms');

        return $subs;
    }

    public function getSubstitution()
    {
        $subs = [];

        $subs['[today]'] = Format::date(date('Y-m-d H:i:s'), 'date');
        $subs['[year]'] = date('Y');

        return $subs;
    }
}
