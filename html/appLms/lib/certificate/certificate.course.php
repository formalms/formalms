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

require_once dirname(__FILE__) . '/certificate.base.php';

class CertificateSubs_Course extends CertificateSubstitution
{
    public function getSubstitutionTags()
    {
        $subs = [];
        if ($this->id_meta !== 0) {
        } else {
            $subs['[course_code]'] = Lang::t('_COURSE_CODE', 'certificate', 'lms');
            $subs['[course_name]'] = Lang::t('_COURSE_NAME', 'certificate', 'lms');
            $subs['[course_description]'] = Lang::t('_COURSE_DESCRIPTION', 'certificate', 'lms');
            $subs['[cert_number]'] = Lang::t('_COURSE_FILE_NUMBER', 'certificate', 'lms');
            $subs['[date_begin]'] = Lang::t('_COURSE_BEGIN', 'certificate', 'lms');
            $subs['[date_end]'] = Lang::t('_COURSE_END', 'certificate', 'lms');
            $subs['[medium_time]'] = Lang::t('_COURSE_MEDIUM_TIME', 'certificate', 'lms');

            // ticket: #19791
            $subs['[ed_date_begin]'] = Lang::t('_ED_DATE_BEGIN', 'certificate', 'lms');
            $subs['[ed_classroom]'] = Lang::t('_ED_CLASSROOM', 'certificate', 'lms');

            $subs['[cl_date_begin]'] = Lang::t('_CL_DATE_BEGIN', 'certificate', 'lms');
            $subs['[cl_date_end]'] = Lang::t('_CL_DATE_END', 'certificate', 'lms');
            $subs['[cl_classroom]'] = Lang::t('_CL_CLASSROOM', 'certificate', 'lms');

            $subs['[ed_dates_subscribed]'] = Lang::t('_ED_DATES_SUBSCRIBED', 'certificate', 'lms');

            $subs['[teacher_list]'] = Lang::t('_TEACHER_LIST', 'certificate', 'lms');
            $subs['[teacher_list_inverse]'] = Lang::t('_TEACHER_LIST_INVERSE', 'certificate', 'lms');
            $subs['[course_credits]'] = Lang::t('_CREDITS', 'certificate', 'lms');
        }

        return $subs;
    }

    public function getUserNameInv($idst_user = false, $user_id = false)
    {
        $acl_manager = \FormaLms\lib\Forma::getAclManager();
        $user_info = $acl_manager->getUser($idst_user, $user_id);

        return $user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
            ? $user_info[ACL_INFO_FIRSTNAME] . ' ' . $user_info[ACL_INFO_LASTNAME]
            : $acl_manager->relativeId($user_info[ACL_INFO_USERID]);
    }

    /**
     * return the list of substitution.
     */
    public function getSubstitution()
    {
        $subs = [];

        if ($this->id_meta == 0) {
            require_once _lms_ . '/lib/lib.course.php';

            $acl_manager = \FormaLms\lib\Forma::getAclManager();
            $man_course = new FormaCourse($this->id_course);

            $query = 'SELECT idUser'
                . ' FROM %lms_courseuser'
                . " WHERE idCourse = '" . $this->id_course . "'"
                . " AND level = '6'";
            $result = sql_query($query);

            $first = true;

            while (list($id_user) = sql_fetch_row($result)) {
                if ($first) {
                    $subs['[teacher_list]'] = '' . $acl_manager->getUserName($id_user, false);
                    $subs['[teacher_list_inverse]'] = '' . $this->getUserNameInv($id_user, false);
                    $first = false;
                } else {
                    $subs['[teacher_list]'] .= ', ' . $acl_manager->getUserName($id_user, false);
                    $subs['[teacher_list_inverse]'] .= ', ' . $this->getUserNameInv($id_user, false);
                }
            }

            $subs['[course_code]'] = $man_course->getValue('code');
            $subs['[course_name]'] = $man_course->getValue('name');
            $subs['[cert_number]'] = $this->id_user . '-' . time();
            $subs['[date_begin]'] = Format::date($man_course->getValue('date_begin'), 'date');
            $subs['[date_end]'] = Format::date($man_course->getValue('date_end'), 'date');

            $subs['[course_description]'] = html_entity_decode($man_course->getValue('description'), ENT_QUOTES, 'UTF-8');

            $subs['[medium_time]'] = $man_course->getValue('mediumTime');
            $subs['[course_credits]'] = $man_course->getValue('credits');

            $subs['[ed_date_begin]'] = '';
            $subs['[ed_classroom]'] = '';

            $subs['[cl_date_begin]'] = '';
            $subs['[cl_date_end]'] = '';
            $subs['[cl_classroom]'] = '';

            if ($man_course->getValue('course_edition') == 1) {
                $query = 'SELECT date_begin '
                    . 'FROM %lms_course_editions INNER JOIN %lms_course_editions_user ON '
                    . $GLOBALS['prefix_lms'] . '_course_editions_user.id_edition = %lms_course_editions.id_edition '
                    . 'where %lms_course_editions .id_course = ' . $this->id_course . ' and %lms_course_editions_user.id_user = ' . $this->id_user;
                $result = sql_query($query);

                if (sql_num_rows($result) > 0) {
                    list($date_begin) = sql_fetch_row($result);
                    $subs['[ed_date_begin]'] = Format::date($date_begin, 'date');
                }
            } // end session

            if ($man_course->getValue('course_type') == 'classroom') {
                $date_arr = [];

                $qtxt = 'SELECT d.id_date, MIN( dd.date_begin ) AS date_begin, MAX( dd.date_end ) AS date_end, d.name
												 FROM %lms_course_date_day AS dd
												 JOIN %lms_course_date AS d
                                                 ON (d.id_date = dd.id_date)
				              	 LEFT JOIN %lms_course_date_user ON %lms_course_date_user.id_date = d.id_date
                				 WHERE d.id_course = ' . (int) $this->id_course . '  and %lms_course_date_user.id_user=' . $this->id_user . ' AND dd.deleted = 0' . '
                				 GROUP BY dd.id_date';

                list($id_date, $subs['[cl_date_begin]'], $subs['[cl_date_end]'], $subs['[ed_classroom]']) = sql_fetch_row(sql_query($qtxt));

                $qtxt = 'SELECT distinct c.name AS class_name
                             FROM %lms_course_date_day AS dd
                             JOIN %lms_course_date AS d
                             JOIN %lms_classroom AS c
                             ON ( dd.classroom = c.idClassroom AND d.id_date = dd.id_date )
                             LEFT JOIN %lms_course_date_user ON %lms_course_date_user.id_date = d.id_date
                             WHERE d.id_course = ' . (int) $this->id_course . '  and %lms_course_date_user.id_user=' . $this->id_user;

                $result = sql_query($qtxt);
                $num_pv = 0;
                while (list($classroom) = sql_fetch_row($result)) {
                    if ($num_pv > 0) {
                        $subs['[cl_classroom]'] .= '; ';
                    }
                    $subs['[cl_classroom]'] .= $classroom;
                    ++$num_pv;
                }

                $qdates = 'SELECT dd.date_begin, dd.pause_begin, dd.pause_end, dd.date_end
                             FROM learning_course_date_day AS dd
														 INNER JOIN learning_course_date AS d ON dd.id_date = d.id_date
                             INNER JOIN learning_course_date_user ON learning_course_date_user.id_date = d.id_date
														 WHERE d.id_course = ' . (int) $this->id_course . ' AND learning_course_date_user.id_user=' . $this->id_user . ' AND dd.deleted = 0';

                $query = 'SELECT cd.id_date
                 FROM %lms_course_date AS cd
                 INNER JOIN %lms_course_date_user cdu ON cd.id_date = cdu.id_date
                 WHERE id_course = ' . $this->id_course . '
                 AND cdu.id_user = ' . $this->id_user . '
                 ORDER BY cd.id_date DESC LIMIT 1;';
                // AND cdu.date_complete IS NOT NULL
                list($id_date) = sql_fetch_row(sql_query($query));

                if ($id_date) {
                    $qdates .= " AND d.id_date = $id_date";
                }
                $qdates = sql_query($qdates);

                $subs['[ed_dates_subscribed]'] = '';
                $num_ds = 0;
                while (list($date_begin, $pause_begin, $pause_end, $date_end) = sql_fetch_row($qdates)) {
                    if (!$num_ds) {
                        $subs['[ed_dates_subscribed]'] = '<ul>';
                    }
                    $subs['[ed_dates_subscribed]'] .= '<li>' . Format::date($date_begin, 'date') . '</li>';
                    if ($num_ds == $qdates->num_rows - 1) {
                        $subs['[ed_dates_subscribed]'] .= '</ul>';
                    }
                    ++$num_ds;
                }

                $subs['[course_description]'] = html_entity_decode($man_course->getValue('description'), ENT_QUOTES, 'UTF-8');
                $subs['[cl_date_begin]'] = Format::date($subs['[cl_date_begin]'], 'date');
                $subs['[cl_date_end]'] = Format::date($subs['[cl_date_end]'], 'date');

                if ($id_date) {
                    $subs['[teacher_list]'] = $subs['[teacher_list_inverse]'] = null;

                    $query = 'SELECT idUser'
                        . ' FROM %lms_courseuser'
                        . " WHERE idCourse = '" . $this->id_course . "'"
                        . " AND level = '6'"
                        . ' AND idUser IN '
                        . ' ('
                        . ' SELECT id_user'
                        . ' FROM %lms_course_date_user'
                        . ' WHERE id_date = ' . $id_date
                        . ' )';

                    $result = sql_query($query);

                    $first = true;

                    while (list($id_user) = sql_fetch_row($result)) {
                        if ($first) {
                            $subs['[teacher_list]'] = '' . $acl_manager->getUserName($id_user, false);
                            $subs['[teacher_list_inverse]'] = '' . $this->getUserNameInv($id_user, false);
                            $first = false;
                        } else {
                            $subs['[teacher_list]'] .= ', ' . $acl_manager->getUserName($id_user, false);
                            $subs['[teacher_list_inverse]'] .= ', ' . $this->getUserNameInv($id_user, false);
                        }
                    }
                }
            } // end classroom
        }

        return $subs;
    }
}
