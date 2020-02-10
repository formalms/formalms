<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

require_once(_base_ . '/lib/lib.json.php');


class CourseLmsController extends LmsController
{
    public function init()
    {
        require_once(_adm_ . '/lib/lib.field.php');

        /** @var Services_JSON json */
        $this->json = new Services_JSON();
        $this->_mvc_name = "course";
        $this->permissions = array(
            'view' => true,
            'mod' => true
        );

        if (!Docebo::user()->isAnonymous()) {

            define('_PATH_COURSE', '/appLms/' . Get::sett('pathcourse'));

            require_once($GLOBALS['where_lms'] . '/lib/lib.levels.php');

        } elseif (!isset($_SESSION['idCourse'])) {
            errorCommunication($lang->def('_FIRSTACOURSE'));

        } else echo "You can't access";
    }

    public function infocourse()
    {
        checkPerm('view_info');

        $lang =& DoceboLanguage::createInstance('course');
        // $course = $GLOBALS['course_descriptor']->getAllInfo();

        $data = [
            'page_title' => $lang->def('_INFO'),
            'course_name' => 'Demo Course',
            'file_img_path' => '../templates/standard/static/images/icons/icon--file.svg',
            'edit_img_path' => '../templates/standard/static/images/icons/icon--edit.svg',
            'delete_img_path' => '../templates/standard/static/images/icons/icon--delete.svg'
        ];

        // var_dump($course);
        $this->render ('infocourse/infocourse' , $data);

        /*require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');

        //finding course information
        $mod_perm = checkPerm('mod', true);
        $lang =& DoceboLanguage::createInstance('course');

        $acl_man = Docebo::user()->getAclManager();
        $course = $GLOBALS['course_descriptor']->getAllInfo();
        $levels = CourseLevel::getLevels();

        $status_lang = array(
            0 => $lang->def('_NOACTIVE'),
            1 => $lang->def('_ACTIVE'),
            2 => $lang->def('_CST_CONFIRMED'),
            3 => $lang->def('_CST_CONCLUDED'),
            4 => $lang->def('_CST_CANCELLED'));

        $difficult_lang = array(
            'veryeasy' => $lang->def('_DIFFICULT_VERYEASY'),
            'easy' => $lang->def('_DIFFICULT_EASY'),
            'medium' => $lang->def('_DIFFICULT_MEDIUM'),
            'difficult' => $lang->def('_DIFFICULT_DIFFICULT'),
            'verydifficult' => $lang->def('_DIFFICULT_VERYDIFFICULT'));

        $subs_lang = array(
            0 => $lang->def('_COURSE_S_GODADMIN'),
            1 => $lang->def('_COURSE_S_MODERATE'),
            2 => $lang->def('_COURSE_S_FREE'),
            3 => $lang->def('_COURSE_S_SECURITY_CODE'));


        $GLOBALS['page']->add(
            getTitleArea($lang->def('_INFO'), 'course')
            . '<div class="std_block">'
            , 'content');


        $GLOBALS['page']->add(
            '<table class="vertical_table">'
            . '<caption class="cd_name">' . $course['name'] . '</caption>'
            . '<tr><th scope="row">' . $lang->def('_CODE') . '</th><td>' . $course['code'] . '</td></tr>'
            . '<tr><th scope="row">' . $lang->def('_COURSE') . '</th><td>' . $course['name'] . '</td></tr>'
            . '<tr><th scope="row">' . $lang->def('_DIFFICULTY') . '</th><td>' . $difficult_lang[$course['difficult']] . '</td></tr>'
            . '<tr><th scope="row">' . $lang->def('_DESCRIPTION') . '</th><td>' . $course['description'] . '</td></tr>'
            . '<tr><th scope="row">' . $lang->def('_SUBSCRIBE_METHOD') . '</th><td>' . $subs_lang[$course['subscribe_method']] . '</td></tr>'
            . '<tr><th scope="row">' . $lang->def('_LANGUAGE') . '</th><td>' . $course['lang_code'] . '</td></tr>'
            , 'content');
        while (list($num_lv, $name_level) = each($levels)) {

            if ($course['level_show_user'] & (1 << $num_lv)) {

                $users =& $acl_man->getUsers(Man_Course::getIdUserOfLevel($_SESSION['idCourse'], $num_lv, $_SESSION['idEdition']));
                if (!empty($users)) {

                    $first = true;
                    $GLOBALS['page']->add('<tr><th scope="row">' . $name_level . '</th><td>', 'content');
                    while (list($id_user, $user_info) = each($users)) {

                        if ($first) $first = false;
                        else $GLOBALS['page']->add(', ', 'content');
                        $GLOBALS['page']->add(
                            '<a href="index.php?modname=course&amp;op=viewprofile&amp;id_user=' . $id_user . '">'
                            . $acl_man->getConvertedUserName($user_info)
                            . '</a>', 'content');
                    } // end while
                    $GLOBALS['page']->add('</td></tr>', 'content');
                } // end if
            } // end if
        } // end while

        if ($course['show_extra_info'] == '1') {

            $GLOBALS['page']->add(
                '<tr><th scope="row">' . $lang->def('_STATUS') . '</th><td>' . $status_lang[$course['status']] . '</td></tr>'
                . '<tr><th scope="row">' . $lang->def('_PERMCLOSE') . '</th><td>' . ($course['permCloseLO'] ? $lang->def('_MANUALACTION') : $lang->def('_ENDOBJECT')) . '</td></tr>'
                . '<tr><th scope="row">' . $lang->def('_MEDIUMTIME') . '</th><td>' . $course['mediumTime'] . ' ' . $lang->def('_DAYS') . '</td></tr>'
                . '<tr><th scope="row">' . $lang->def('_STATCANNOTENTER') . '</th><td>'
                , 'content');

            $first = true;
            if (statusNoEnter($course['userStatusOp'], _CUS_SUBSCRIBED)) {
                $GLOBALS['page']->add($lang->def('_USER_STATUS_SUBS'), 'content');
                $first = false;
            }
            if (statusNoEnter($course['userStatusOp'], _CUS_BEGIN)) {
                $GLOBALS['page']->add(($first ? '' : ', ') . $lang->def('_USER_STATUS_BEGIN'), 'content');
                $first = false;
            }
            if (statusNoEnter($course['userStatusOp'], _CUS_SUSPEND)) {
                $GLOBALS['page']->add(($first ? '' : ', ') . $lang->def('_USER_STATUS_SUSPEND'), 'content');
                $first = false;
            }
            if (statusNoEnter($course['userStatusOp'], _CUS_END)) {
                $GLOBALS['page']->add(($first ? '' : ', ') . $lang->def('_USER_STATUS_END'), 'content');
                $first = false;
            }
            $GLOBALS['page']->add('</td></tr>', 'content');
        }

        // course disk quota
        if ($_SESSION['levelCourse'] >= 4) {

            $max_quota = $GLOBALS['course_descriptor']->getQuotaLimit();
            $actual_space = $GLOBALS['course_descriptor']->getUsedSpace();

            $actual_space = number_format(($actual_space / (1024 * 1024)), '2');
            if ($max_quota == 0) {
                $percent = 0;
            } else $percent = ($actual_space != 0 ? number_format((($actual_space / $max_quota) * 100), '2') : '0');

            $GLOBALS['page']->add(
                '<tr>'
                . '<th scope="row">' . $lang->def('_USED_DISK') . '</th><td>'
                . ($max_quota == USER_QUOTA_UNLIMIT
                    ? ' ' . $actual_space . ' MB / ' . $lang->def('_UNLIMITED_QUOTA') . ' '
                    : '' . $actual_space . ' / ' . $max_quota . ' MB ' . Util::draw_progress_bar($percent, true, 'progress_bar cp_quota_bar', false, false)
                )
                . '</td></tr>', 'content');
        }

        $GLOBALS['page']->add('</table>', 'content');

        if ($mod_perm) {
            $GLOBALS['page']->add('<br /><div class="table-container-below">'
                . '<a class="infomod" href="index.php?modname=course&amp;op=modcourseinfo">'
                . '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" />&nbsp;' . $lang->def('_MOD') . '</a>'
                . '</div>', 'content');
        }

        $GLOBALS['page']->add('</div>', 'content');*/
    }
}

