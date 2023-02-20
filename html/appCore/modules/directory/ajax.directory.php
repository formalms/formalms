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

/*
 * @package course management
 * @subpackage course catalogue
 * @category ajax server
 * @author Giovanni Derks
 * @version $Id:$
 *
 */

if (Docebo::user()->isAnonymous()) {
    exit('You can\'t access');
}

require_once _adm_ . '/lib/lib.permission.php';

$op = FormaLms\lib\Get::req('op', DOTY_ALPHANUM, '');
switch ($op) {
    case 'getuserprofile':
        $lang = &DoceboLanguage::createInstance('standard', 'framework');
        $lang->setGlobal();

        require_once _base_ . '/lib/lib.user_profile.php';

        $id_user = importVar('id_user', true, 0);

        $profile = new UserProfile($id_user);
        $profile->init('profile', 'framework', 'modname=directory&op=org_manageuser&id_user=' . $id_user, 'ap');
        $profile->enableGodMode();
        $profile->disableModViewerPolicy();
        $value = ['content' => $profile->getUserInfo()
                // teacher profile, if the user is a teacher
                //.$profile->getUserTeacherProfile()

                //.$profile->getUserLmsStat()  .'<br />'
                //.$profile->getUserCompetencesList()
                ,
                'id_user' => $id_user,
        ];

        require_once _base_ . '/lib/lib.json.php';

        $json = new Services_JSON();
        $output = $json->encode($value);
        aout($output);
     break;
}
