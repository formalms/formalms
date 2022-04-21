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

if (Docebo::user()->isAnonymous()) {
    exit('You can\'t access');
}

require_once $GLOBALS['where_framework'] . '/lib/lib.tags.php';

$op = Forma\lib\Get::req('op', DOTY_ALPHANUM, '');
switch ($op) {
    case 'get_platform_cloud':
        $tags = new Tags('*');

        $cloud = $tags->getPlatformTagCloud();
        aout($cloud);
    ; break;
    case 'get_course_cloud':
        $tags = new Tags('*');
        $cloud = $tags->getCourseTagCloud();
        aout($cloud);
    ; break;
    case 'get_user_cloud':
        $tags = new Tags('*');

        $cloud = $tags->getUserTagCloud(getLogUserId());
        aout($cloud);
    ; break;
    case 'save_tag':
        $compiled_tags = Forma\lib\Get::req('tags', DOTY_STRING, '');
        $id_resource = Forma\lib\Get::req('id_resource', DOTY_INT, '');
        $resource_type = Forma\lib\Get::req('resource_type', DOTY_ALPHANUM, '');

        $title = Forma\lib\Get::req('title', DOTY_STRING, '');
        $sample = Forma\lib\Get::req('sample_text', DOTY_STRING, '');
        $permalink = Forma\lib\Get::req('permalink', DOTY_STRING, '');

        $private = false;
        $req_private = Forma\lib\Get::req('private', DOTY_INT, '0');
        if ($req_private) {
            // requested to save as private, check if the user can do this operation
            if (isset($_SESSION['levelCourse']) && $_SESSION['levelCourse'] > 3) {
                $private = true;
            }
            if (Docebo::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN) {
                $private = true;
            }
        }

        $tags = new Tags($resource_type);
        $updated_tags = $tags->updateTagResource($id_resource, getLogUserId(), $compiled_tags, $title, $sample, $permalink, $private);

        aout($updated_tags);
    ; break;
    default:
        $query = Forma\lib\Get::req('query', DOTY_STRING, '');

        $tags = new Tags('*');
        $suggestion = $tags->getAutoComplete($query);

        $output = implode("\n", $suggestion);

        aout($output);
    ; break;
}
