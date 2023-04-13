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

/*
 * @package course management
 * @subpackage course catalogue
 * @category ajax server
 * @version $Id:$
 *
 */

if (Forma::user()->isAnonymous()) {
    exit('You can\'t access');
}

require_once _adm_ . '/lib/lib.tags.php';

$op = FormaLms\lib\Get::req('op', DOTY_ALPHANUM, '');
switch ($op) {
    case 'get_platform_cloud':
        $tags = new Tags('*');

        $cloud = $tags->getPlatformTagCloud();
        aout($cloud);
     break;
    case 'get_course_cloud':
        $tags = new Tags('*');
        $cloud = $tags->getCourseTagCloud();
        aout($cloud);
     break;
    case 'get_user_cloud':
        $tags = new Tags('*');

        $cloud = $tags->getUserTagCloud(getLogUserId());
        aout($cloud);
     break;
    case 'save_tag':
        $compiled_tags = FormaLms\lib\Get::req('tags', DOTY_STRING, '');
        $id_resource = FormaLms\lib\Get::req('id_resource', DOTY_INT, '');
        $resource_type = FormaLms\lib\Get::req('resource_type', DOTY_ALPHANUM, '');

        $title = FormaLms\lib\Get::req('title', DOTY_STRING, '');
        $sample = FormaLms\lib\Get::req('sample_text', DOTY_STRING, '');
        $permalink = FormaLms\lib\Get::req('permalink', DOTY_STRING, '');

        $private = false;
        $req_private = FormaLms\lib\Get::req('private', DOTY_INT, '0');
        if ($req_private) {
            $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
            // requested to save as private, check if the user can do this operation
            if ($session->has('levelCourse') && $session->get('levelCourse') > 3) {
                $private = true;
            }
            if (Forma::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN) {
                $private = true;
            }
        }

        $tags = new Tags($resource_type);
        $updated_tags = $tags->updateTagResource($id_resource, getLogUserId(), $compiled_tags, $title, $sample, $permalink, $private);

        aout($updated_tags);
     break;
    default:
        $query = FormaLms\lib\Get::req('query', DOTY_STRING, '');

        $tags = new Tags('*');
        $suggestion = $tags->getAutoComplete($query);

        $output = implode("\n", $suggestion);

        aout($output);
     break;
}
