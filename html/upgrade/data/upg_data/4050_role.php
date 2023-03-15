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

if (!defined('IN_FORMA')) {
    exit('You can\'t access!');
}

// if this file is not needed for a specific version,
// just don't create it.

// Create this function only if needed, else you can remove it
// (we check it with function_exists)
function upgradeUsersRoles4050()
{
    $res = '';

    /* $res ='/lms/course/public/course/view
/lms/course/public/coursecatalogue/view
/lms/course/public/course_autoregistration/view
/lms/course/public/message/view'; */

    return $res;
}

// Create this function only if needed, else you can remove it
// (we check it with function_exists)
function upgradeGodAdminRoles4050()
{
    $res = '';

    /* $res ='/framework/admin/adminmanager/mod
/framework/admin/adminmanager/view
/framework/admin/adminrules/view
/framework/admin/code/view'; */

    return $res;
}
