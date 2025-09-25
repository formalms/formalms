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

const CERT_ID = 0;
const CERT_NAME = 1;
const CERT_DESCR = 2;
const CERT_LANG = 3;
const CERT_STRUCTURE = 4;
const CERT_CODE = 5;

const CERT_ID_COURSE = 4;
const CERT_AV_STATUS = 5;

const CERTIFICATE_PATH = '/appLms/certificate/';

const AVS_NOT_ASSIGNED = 0;
const AVS_ASSIGN_FOR_ALL_STATUS = 1;
const AVS_ASSIGN_FOR_STATUS_INCOURSE = 2;
const AVS_ASSIGN_FOR_STATUS_COMPLETED = 3;

const ASSIGN_CERT_ID = 0;
const ASSIGN_COURSE_ID = 1;
const ASSIGN_USER_ID = 2;
const ASSIGN_OD_DATE = 3;
const ASSIGN_CERT_FILE = 4;
const ASSIGN_CERT_SENDNAME = 5;

class Certificate
{
    public function findAll($filter, $pagination)
    {
        [$aval_status, $minutes_required] = sql_fetch_row(
            sql_query(
                'SELECT available_for_status, minutes_required FROM %lms_certificate_course as cc'
                . ' WHERE id_course=' . $filter['id_course']
                . isset($filter['id_certificate']) ? ' AND id_certificate = ' . $filter['id_certificate'] : ''
            )
        );

        $whereConditions = '';
        if (isset($filter['search_filter'])) {
            $whereConditions .= " AND (u.userid LIKE '%" . $filter['search_filter'] . "%' OR"
                . " u.lastname LIKE '%" . $filter['search_filter'] . "%' OR"
                . " u.firstname LIKE '%" . $filter['search_filter'] . "%' ) ";
        }

        if ($minutes_required > 0) {
            $whereConditions .= ' AND ('
                . ' ca.on_date IS NOT NULL OR (('
                . ' SELECT SUM((UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)))'
                . ' FROM %lms_tracksession'
                . ' WHERE idCourse = cu.idCourse AND idUser = cu.idUser )'
                . ' / 60 ) >= ' . $minutes_required
                . ') ';
        }

        if ($filter['only_released']) {
            $whereConditions .= ' AND ca.on_date ' . ($filter['only_released'] == 1 ? 'IS NOT NULL ' : 'IS NULL ');
        }

        //apply sub admin filters, if needed
        $userLevelId = \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId();
        if ($userLevelId != ADMIN_GROUP_GODADMIN && !\FormaLms\lib\FormaUser::getCurrentUser()->isAnonymous()) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $admin_users = $adminManager->getAdminUsers(\FormaLms\lib\FormaUser::getCurrentUser()->getIdST());
            $whereConditions .= ' AND cu.idUser IN (' . implode(',', $admin_users) . ')';
        }

        switch ($aval_status) {
            case AVS_ASSIGN_FOR_ALL_STATUS:
                $aval_status = ' 1 ';

                break;
            case AVS_ASSIGN_FOR_STATUS_INCOURSE:
                $aval_status = ' cu.status = ' . _CUS_BEGIN . ' ';

                break;
            case AVS_ASSIGN_FOR_STATUS_COMPLETED:
                $aval_status = ' cu.status = ' . _CUS_END . ' ';

                break;
        }

        $dynUserFiltersCondition = is_array($filter['idsts']) ? ' AND u.idst IN (' . implode(',', $filter['idsts']) . ')' : '';

        $select = 'SELECT u.idst, u.userid, u.firstname, u.lastname, cu.date_complete, ca.on_date, cu.idUser as id_user,'
            . ' cu.status , cu.idCourse, cc.id_certificate, c.name as name_certificate';
        $from = ' FROM ( %adm_user as u JOIN %lms_courseuser as cu ON (u.idst = cu.idUser) )'
            . ' JOIN %lms_certificate_course as cc ON cc.id_course = cu.idCourse'
            . ' JOIN %lms_certificate as c ON c.id_certificate = cc.id_certificate'
            . ' LEFT JOIN %lms_certificate_assign as ca ON'
            . ' ( ca.id_course = cu.idCourse AND ca.id_user=cu.idUser AND ca.id_certificate = cc.id_certificate )'
            . ' LEFT JOIN ('
            . ' SELECT iduser, idcourse, SUM( (UNIX_TIMESTAMP( lastTime ) - UNIX_TIMESTAMP( enterTime ) ) ) elapsed'
            . ' FROM %lms_tracksession group by iduser, idcourse) t_elapsed on t_elapsed.idcourse=cu.idCourse and cu.idUser = t_elapsed.idUser';
        $where = ' WHERE 1=1'
            . (isset($aval_status) ? ' AND ' . $aval_status : '')
            . ($filter['id_certificate'] ? ' AND cc.id_certificate = ' . $filter['id_certificate'] : '')
            . ' AND coalesce(elapsed,0) >= coalesce(cc.minutes_required,0) * 60 '
            . " AND cu.idCourse='" . $filter['id_course'] . "' " . $whereConditions
            . $dynUserFiltersCondition;

        $orderBy = ' ORDER BY u.userid, c.name LIMIT ' . $pagination['offset'] . ', ' . $pagination['num_rows'];

        $res = sql_query($select . $from . $where . $orderBy);
        $assignment = [];
        while ($row = sql_fetch_assoc($res)) {
            $assignment[] = $row;
        }

        $res = sql_query('SELECT u.idst ' . $from . $where);
        $totalrows = [];
        while ($row = sql_fetch_row($res)) {
            $totalrows[] = current($row);
        }

        return [$assignment, $totalrows];
    }

    public function countAssignment($filter)
    {
        return count($this->getAssignment($filter));
    }

    /**
     * put your comment there...
     *
     * @param mixed $filter
     * @param mixed $pagination
     * @param mixed $count
     */
    public function getAssignment($filter, $pagination = false, $count = null)
    {
        if ($pagination && isset($pagination['search'])) {
            $filter['search'] = $pagination['search'];
        }
        $assigned = $this->getAssigned($filter);
        $assignable = $this->getAssignable($filter);

        $assignment = [];
        foreach ($assigned as $as) {
            $assignment[] = $as;
        }
        foreach ($assignable as $as) {
            $assignment[] = $as;
        }

        $paginated_assignment = [];
        if ($pagination) {
            $offset = $pagination['startIndex'];
            $limit = $offset + $pagination['rowsPerPage'];
            $limit = ($limit <= count($assignment) ? $limit : count($assignment));
            for ($i = $offset; $i < $limit; ++$i) {
                $paginated_assignment[] = $assignment[$i];
            }
        }

        if ($count) {
            return count($assignable) + count($assigned);
        }

        return $pagination ? $paginated_assignment : $assignment;
    }

    public function getAssigned($filter)
    {
        $query = 'SELECT ca.id_certificate, ca.id_course,'
            . '	ca.id_user, SUBSTRING(u.userid, 2) AS username,'
            . '	u.lastname, u.firstname, co.code, cc.available_for_status,'
            . '  co.name AS course_name, ce.name AS cert_name,'
            . '	cu.status, co.date_begin, co.date_end, cu.date_inscr,'
            . '	cu.date_complete, ca.on_date, ca.cert_file'
            . '	FROM %lms_certificate_assign AS ca'
            . '      LEFT JOIN %lms_certificate AS ce'
            . '      ON ca.id_certificate = ce.id_certificate'
            . '      LEFT JOIN %lms_course AS co'
            . '      ON ca.id_course = co.idCourse'
            . '      LEFT JOIN %lms_courseuser AS cu'
            . '      ON ca.id_user = cu.idUser'
            . '          AND ca.id_course  = cu.idCourse'
            . '      LEFT JOIN %lms_certificate_course AS cc'
            . '      ON ca.id_certificate = cc.id_certificate'
            . '          AND ca.id_course  = cc.id_course'
            . '      LEFT JOIN %adm_user AS u'
            . '      ON ca.id_user = u.idst'
            . '	WHERE 1 = 1';
        if (isset($filter['id_certificate'])) {
            $query .= ' AND ca.id_certificate = ' . $filter['id_certificate'];
        }
        if (isset($filter['id_course'])) {
            $query .= ' AND ca.id_course = ' . $filter['id_course'];
        }
        if (isset($filter['id_user'])) {
            $query .= ' AND ca.id_user = ' . $filter['id_user'];
        }
        if (isset($filter['search'])) {
            $query .= ' AND (1 = 0';
            $query .= "     OR u.userid LIKE '%" . $filter['search'] . "%'";
            $query .= "     OR u.lastname LIKE '%" . $filter['search'] . "%'";
            $query .= "     OR u.firstname LIKE '%" . $filter['search'] . "%'";
            $query .= "     OR co.code LIKE '%" . $filter['search'] . "%'";
            $query .= "     OR co.name LIKE '%" . $filter['search'] . "%'";
            $query .= "     OR ce.name LIKE '%" . $filter['search'] . "%'";
            $query .= ' )';
        }
        if (isset($filter['search_user'])) {
            $query .= ' AND (1 = 0';
            $query .= "     OR u.userid LIKE '%" . $filter['search_user'] . "%'";
            $query .= "     OR u.lastname LIKE '%" . $filter['search_user'] . "%'";
            $query .= "     OR u.firstname LIKE '%" . $filter['search_user'] . "%'";
            $query .= ' )';
        }
        if (isset($filter['search_course'])) {
            $query .= ' AND (1 = 0';
            $query .= "     OR co.code LIKE '%" . $filter['search_course'] . "%'";
            $query .= "     OR co.name LIKE '%" . $filter['search_course'] . "%'";
            $query .= ' )';
        }
        if (isset($filter['search_cert'])) {
            $query .= ' AND (1 = 0';
            $query .= "     OR ce.name LIKE '%" . $filter['search_cert'] . "%'";
            $query .= ' )';
        }
        if (!isset($filter['id_user'])) {
            if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                require_once _base_ . '/lib/lib.preference.php';
                $adminManager = new AdminPreference();
                $query .= ' AND ' . $adminManager->getAdminUsersQuery(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(), 'idUser');
            }
        }

        $assigned = [];
        $res = sql_query($query);
        while ($row = sql_fetch_assoc($res)) {
            $assigned[] = $row;
        }

        return $assigned;
    }

    public function getAssignable($filter)
    {
        $query = '	SELECT ce.id_certificate, co.idCourse AS id_course, co.permCloseLO as perm_close_lo,'
            . '	u.idst AS id_user, SUBSTRING(u.userid, 2) AS username,'
            . '	u.lastname, u.firstname, co.code, cc.available_for_status,'
            . '  co.name AS course_name, cc.point_required, ce.name AS cert_name,'
            . '	cu.status, co.date_begin, co.date_end, cu.date_inscr,'
            . '	cu.date_complete, NULL AS on_date, NULL AS cert_file'
            . '	FROM %lms_certificate_course AS cc'
            . '      JOIN %lms_certificate AS ce'
            . '      ON cc.id_certificate = ce.id_certificate'
            . '      JOIN %lms_course AS co'
            . '      ON cc.id_course = co.idCourse'
            . '      JOIN %lms_courseuser AS cu'
            . '      ON co.idCourse = cu.idCourse'
            . '          AND ('
            . '              (cc.available_for_status = 1 AND cu.status >= 0 AND cu.status <= 2)'
            . '              OR (cc.available_for_status = 2 AND cu.status >= 1 AND cu.status <= 2)'
            . '              OR (cc.available_for_status = 3 AND cu.status = 2)'
            . '          )'
            . '      JOIN %adm_user AS u'
            . '      ON cu.idUser = u.idst'
            . '	WHERE (cc.id_certificate, co.idCourse, cu.idUser) NOT IN ('
            . '      SELECT ca.id_certificate, ca.id_course, ca.id_user'
            . '      FROM %lms_certificate_assign AS ca'
            . '	)';
        if (isset($filter['id_certificate'])) {
            $query .= ' AND cc.id_certificate = ' . $filter['id_certificate'];
        }
        if (isset($filter['id_course'])) {
            $query .= ' AND co.idCourse = ' . $filter['id_course'];
        }
        if (isset($filter['id_user'])) {
            $query .= ' AND cu.idUser = ' . $filter['id_user'];
            $query .= ' AND ce.user_release = 1';
        }
        if (isset($filter['search'])) {
            $query .= ' AND (1 = 0';
            $query .= "     OR u.userid LIKE '%" . $filter['search'] . "%'";
            $query .= "     OR u.lastname LIKE '%" . $filter['search'] . "%'";
            $query .= "     OR u.firstname LIKE '%" . $filter['search'] . "%'";
            $query .= "     OR co.code LIKE '%" . $filter['search'] . "%'";
            $query .= "     OR co.name LIKE '%" . $filter['search'] . "%'";
            $query .= "     OR ce.name LIKE '%" . $filter['search'] . "%'";
            $query .= ' )';
        }
        if (isset($filter['search_user'])) {
            $query .= ' AND (1 = 0';
            $query .= "     OR u.userid LIKE '%" . $filter['search_user'] . "%'";
            $query .= "     OR u.lastname LIKE '%" . $filter['search_user'] . "%'";
            $query .= "     OR u.firstname LIKE '%" . $filter['search_user'] . "%'";
            $query .= ' )';
        }
        if (isset($filter['search_course'])) {
            $query .= ' AND (1 = 0';
            $query .= "     OR co.code LIKE '%" . $filter['search_course'] . "%'";
            $query .= "     OR co.name LIKE '%" . $filter['search_course'] . "%'";
            $query .= ' )';
        }
        if (isset($filter['search_cert'])) {
            $query .= ' AND (1 = 0';
            $query .= "     OR ce.name LIKE '%" . $filter['search_cert'] . "%'";
            $query .= ' )';
        }
        if (!isset($filter['id_user'])) {
            if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                require_once _base_ . '/lib/lib.preference.php';
                $adminManager = new AdminPreference();
                $query .= ' AND ' . $adminManager->getAdminUsersQuery(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(), 'idUser');
            }
        }

        $assignable = [];
        $res = sql_query($query);
        if($res) {
            foreach ($res as $row) {
                if ($this->certificateAvailableForUser($row['id_certificate'], $row['id_course'], $row['id_user'])) {
                    $assignable[] = $row;
                }
            }
        }

        return $assignable;
    }



    public function getCertificateList($name_filter = false, $code_filter = false)
    {
        $cert = [];
        $query_certificate = '
		SELECT id_certificate, name, description, base_language, cert_structure, code
		FROM %lms_certificate'
            . ' WHERE meta = 0';

        if ($name_filter && $code_filter) {
            $query_certificate .= " AND name LIKE '%" . $name_filter . "%'" .
                " AND code LIKE '%" . $code_filter . "%'";
        } elseif ($name_filter) {
            $query_certificate .= " AND name LIKE '%" . $name_filter . "%'";
        } elseif ($code_filter) {
            $query_certificate .= " AND code LIKE '%" . $code_filter . "%'";
        }

        $query_certificate .= ' ORDER BY name';

        $re_certificate = sql_query($query_certificate);

        while ($row = sql_fetch_row($re_certificate)) {
            $cert[$row[CERT_ID]] = $row;
        }

        return $cert;
    }

    public function getCourseCertificateObj($id_course)
    {
        $cert = [];
        $query_certificate = '
                SELECT id_certificate, certificate_available_for_obj
                FROM ' . $GLOBALS['prefix_lms'] . "_certificate_course
                WHERE id_course = '" . $id_course . "' ";
        $re_certificate = sql_query($query_certificate);
        while (list($id, $available_for_status) = sql_fetch_row($re_certificate)) {
            $cert[$id] = $available_for_status;
        }

        return $cert;
    }

    public function getCourseCertificateAchi($id_course)
    {
        $cert = [];
        $query_certificate = '
                SELECT id_certificate, certificate_available_for_who
                FROM ' . $GLOBALS['prefix_lms'] . "_certificate_course
                WHERE id_course = '" . $id_course . "' ";
        $re_certificate = sql_query($query_certificate);
        while (list($id, $available_for_who) = sql_fetch_row($re_certificate)) {
            $cert[$id] = $available_for_who;
        }

        return $cert;
    }

    public function certificateAvailableForUser($id_cert, $id_course, $id_user)
    {
        $sql = 'SELECT minutes_required FROM learning_certificate_course WHERE id_course = ' . $id_course . ' AND id_certificate = ' . $id_cert;
        $re = sql_query($sql);
        [$minutes_required] = sql_fetch_row($re);
        if ($minutes_required > 0) {
            require_once _lms_ . '/lib/lib.track_user.php';

            $time_in = TrackUser::getUserTotalCourseTime($id_user, $id_course);
            $minutes_in = (float)($time_in / 60);
            if ($minutes_in < $minutes_required) {
                return false;
            }
        }

        return true;
    }

    public function getCourseCertificate($id_course)
    {
        $cert = [];
        $query_certificate = "
		SELECT id_certificate, available_for_status, minutes_required
		FROM %lms_certificate_course
		WHERE id_course = '" . $id_course . "' "
            . ' AND point_required = 0';
        $re_certificate = sql_query($query_certificate);
        while (list($id, $available_for_status, $minutes_required) = sql_fetch_row($re_certificate)) {
            $cert[$id] = ['available_for_status' => $available_for_status, 'minutes_required' => $minutes_required];
        }

        return $cert;
    }

    public function getCourseExCertificate($id_course)
    {
        $cert = [];
        $query_certificate = "
		SELECT id_certificate, available_for_status
		FROM %lms_certificate_course
		WHERE id_course = '" . $id_course . "' "
            . ' AND point_required > 0';
        $re_certificate = sql_query($query_certificate);
        while (list($id, $available_for_status) = sql_fetch_row($re_certificate)) {
            $cert[$id] = $available_for_status;
        }

        return $cert;
    }

    public function getPointRequiredForCourse($id_course)
    {
        $query = 'SELECT MAX(point_required)'
            . ' FROM %lms_certificate_course'
            . ' WHERE id_course = ' . $id_course;

        [$res] = sql_fetch_row(sql_query($query));

        if ($res == null) {
            $res = '0';
        }

        return $res;
    }

    /**
     * @return array idcourse => array( idcert => array( CERT_ID, CERT_NAME, CERT_DESCR, CERT_LANG, CERT_STRUCTURE, CERT_ID_COURSE, CERT_AV_STATUS ) )
     */
    public function certificateForCourses($arr_course = false, $base_language = false)
    {
        $query_certificate = ''
            . ' SELECT c.id_certificate, c.name, c.description, c.base_language, course.id_course, course.available_for_status, course.point_required '
            . ' FROM %lms_certificate AS c '
            . ' 		JOIN %lms_certificate_course AS course'
            . ' WHERE c.id_certificate = course.id_certificate '
            . " 		AND course.available_for_status <> '" . AVS_NOT_ASSIGNED . "' "
            . ' AND c.user_release = 1';

        if ($arr_course !== false && !empty($arr_course)) {
            $query_certificate .= ' AND course.id_course IN ( ' . implode(',', $arr_course) . ' )';
        }

        if ($base_language !== false) {
            $query_certificate .= " AND c.base_language = '" . $base_language . "' ";
        }

        $query_certificate .= ' ORDER BY course.available_for_status, c.name';

        $re = sql_query($query_certificate);
        if (!$re) {
            return [];
        }

        $list_of = [];
        while ($row = sql_fetch_row($re)) {
            $list_of[$row[CERT_ID_COURSE]][$row[CERT_ID]] = $row;
        }

        return $list_of;
    }

    public function numberOfCertificateReleased($id_certificate = false)
    {
        $query_certificate = '
		SELECT id_certificate, COUNT(*)
		FROM %lms_certificate_assign
		WHERE 1 ';
        if ($id_certificate !== false) {
            $query_certificate .= " AND id_certificate = '" . $id_certificate . "' ";
        }

        $re = sql_query($query_certificate);
        if (!$re) {
            return [];
        }

        $list_of = [];
        while (list($id_c, $number) = sql_fetch_row($re)) {
            $list_of[$id_c] = $number;
        }
        reset($list_of);
        if ($id_certificate !== false) {
            return current($list_of);
        }

        return $list_of;
    }

    public function certificateReleased($id_user, $arr_course = false)
    {
        $query_certificate = '
		SELECT id_course, id_certificate, on_date
		FROM ' . $GLOBALS['prefix_lms'] . "_certificate_assign
		WHERE id_user = '" . $id_user . "' ";
        if ($arr_course) {
            $query_certificate .= ' AND id_course IN ( ' . implode(',', $arr_course) . '';
        }
        $re = sql_query($query_certificate);
        if (!$re) {
            return [];
        }

        $list_of = [];
        while (list($id_course, $id_cert, $on_date) = sql_fetch_row($re)) {
            $list_of[$id_course][$id_cert] = $on_date;
        }

        return $list_of;
    }

    public function certificateStatus($id_user, $id_course)
    {
        $query_certificate = '
		SELECT ca.id_course, c.id_certificate, c.name
		FROM %lms_certificate AS c
			JOIN %lms_certificate_assign AS ca
			ON (c.id_certificate = ca.id_certificate)
		WHERE ca.id_user = ' . (int)$id_user . ' AND ca.id_course = ' . (int)$id_course . ' ';

        $re = sql_query($query_certificate);
        if (!$re) {
            return [];
        }

        $list_of = [];
        while (list($id_course, $id_cert, $name) = sql_fetch_row($re)) {
            $list_of[$id_cert] = $name;
        }

        return $list_of;
    }

    public function certificateReleasedMultiUser($arr_user = false, $arr_course = false)
    {
        $query_certificate = '
		SELECT id_user, id_certificate, id_course, on_date
		FROM %lms_certificate_assign
		WHERE 1 ';
        if (is_array($arr_user) && !empty($arr_user)) {
            $query_certificate .= ' AND id_user IN ( ' . implode(',', $arr_user) . '';
        }
        if (is_array($arr_course) && !empty($arr_course)) {
            $query_certificate .= ' AND id_course IN ( ' . implode(',', $arr_course) . '';
        }
        $re = sql_query($query_certificate);
        if (!$re) {
            return [];
        }

        $list_of = [];
        while (list($id_user, $id_course, $id_cert, $on_date) = sql_fetch_row($re)) {
            $list_of[$id_user][$id_cert]['on_date'] = $on_date;
            $list_of[$id_user][$id_cert]['id_course'] = $id_course;
        }

        return $list_of;
    }

    public function numOfCertificateReleasedForCourse($id_course)
    {
        $query_certificate = '
		SELECT id_certificate, COUNT(*)
		FROM ' . $GLOBALS['prefix_lms'] . "_certificate_assign
		WHERE id_course = '" . $id_course . "'
		GROUP BY id_certificate ";
        $re = sql_query($query_certificate);
        if (!$re) {
            return [];
        }

        $list_of = [];
        while (list($id_cert, $num_of) = sql_fetch_row($re)) {
            $list_of[$id_cert] = $num_of;
        }

        return $list_of;
    }

    public function certificateReleasedForCourse($id_course)
    {
        $query_certificate = '
		SELECT id_user, id_certificate, on_date
		FROM ' . $GLOBALS['prefix_lms'] . "_certificate_assign
		WHERE id_course = '" . $id_course . "' ";
        $re = sql_query($query_certificate);
        if (!$re) {
            return [];
        }

        $list_of = [];
        while (list($id_user, $id_cert, $on_date) = sql_fetch_row($re)) {
            $list_of[$id_user][$id_cert] = $on_date;
        }

        return $list_of;
    }

    public function isReleased($id_certificate, $id_course, $id_user)
    {
        $query_certificate = '
		SELECT cert_file
		FROM ' . $GLOBALS['prefix_lms'] . "_certificate_assign
		WHERE id_certificate = '" . $id_certificate . "'
			 AND id_course = '" . $id_course . "'
			 AND id_user = '" . $id_user . "' ";

        $re = sql_query($query_certificate);
        if (!$re) {
            return false;
        }

        return sql_num_rows($re) > 0;
    }

    public function canRelease($av_for_status, $user_status)
    {
        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.course.php');

        switch ($av_for_status) {
            case AVS_NOT_ASSIGNED:
                return false;

                break;
            case AVS_ASSIGN_FOR_ALL_STATUS:
                return true;

                break;
            case AVS_ASSIGN_FOR_STATUS_INCOURSE:
                return $user_status == _CUS_BEGIN;

                break;
            case AVS_ASSIGN_FOR_STATUS_COMPLETED:
                return $user_status == _CUS_END;

                break;
        }

        return false;
    }

    public function updateCertificateCourseAssign($id_course, $list_of_assign, $list_of_assign_ex, $point_required, $minutes_required)
    {
        $course = new FormaCourse($id_course);

        $query = 'DELETE FROM %lms_certificate_course WHERE id_course = ' . $id_course;

        if (!sql_query($query)) {
            return false;
        }

        if (is_array($list_of_assign) && !empty($list_of_assign)) {
            foreach ($list_of_assign as $id_cert => $status) {
                if ((int)$status !== 0) {
                    $minutes = $minutes_required[$id_cert];
                    $query = 'INSERT INTO %lms_certificate_course'
                        . ' (id_certificate, id_course, available_for_status, minutes_required)'
                        . ' VALUES (' . (int)$id_cert . ', ' . (int)$id_course . ', ' . (int)$status . ', ' . (int)$minutes . ')';

                    $certificate_info = $this->getCertificateInfo($id_cert);
                    Events::trigger('lms.course_certificate.assigned', ['id_course' => $id_course, 'course' => $course, 'id_cert' => $id_cert, 'certificate_info' => $certificate_info, 'status' => $status, 'minutes' => $minutes, 'point_required' => $point_required]);

                    if (!sql_query($query)) {
                        return false;
                    }
                }
            }
        }
        /*
        if (is_array($list_of_assign_ex) && !empty($list_of_assign_ex) && $point_required > 0)
            foreach ($list_of_assign_ex as $id_cert => $status)
                if ($status != 0) {
                    $query =	"INSERT INTO %lms_certificate_course"
                        . " (id_certificate, id_course, available_for_status, point_required)"
                        . " VALUES (" . (int)$id_cert . ", " . (int)$id_course . ", " . (int)$status . ", " . (int)$point_required . ")";

                    $certificate_info = $this->getCertificateInfo($id_cert);
                    Events::trigger('lms.course_certificate.assigned', ['id_course' => $id_course, 'course' => $course, 'id_cert' => $id_cert, 'certificate_info' => $certificate_info, 'status' => $status, 'minutes' => $minutes, 'point_required' => $point_required]);

                    if (!sql_query($query))
                        return false;
                }
        */
        return true;
    }

    public function getSubstitutionArray($id_user, $id_course, $id_meta = 0)
    {
        $query_certificate = 'SELECT file_name, class_name FROM %lms_certificate_tags';
        $re = sql_query($query_certificate);

        $subst = [];
        foreach ($re as $row) {
            $file_name = $row['file_name'];
            $class_name = $row['class_name'];

            if (file_exists(_lms_ . '/lib/certificate/' . $file_name)) {
                require_once _lms_ . '/lib/certificate/' . $file_name;
                $instance = new $class_name($id_user, $id_course, $id_meta);
                $this_subs = $this->cleanSpecialChars($instance->getSubstitution());
                $subst = array_merge($subst, $this_subs);
            }
        }

        return $subst;
    }

    private function cleanSpecialChars($stringArray)
    {
        $stringArray = str_replace('`', '\'', $stringArray);

        return $stringArray;
    }

    public function send_preview_certificate($id_certificate, $array_substituton = false)
    {
        $query_certificate = '
		SELECT name, cert_structure, base_language, orientation, bgimage
		FROM ' . $GLOBALS['prefix_lms'] . "_certificate
		WHERE id_certificate = '" . $id_certificate . "'";
        [$name, $cert_structure, $base_language, $orientation, $bgimage] = sql_fetch_row(sql_query($query_certificate));

        //require_once($GLOBALS['where_framework'].'/addons/html2pdf/html2fpdf.php');

        if ($array_substituton !== false) {
            $cert_structure = str_replace(array_keys($array_substituton), $array_substituton, $cert_structure);
        }
        $cert_structure = fillSiteBaseUrlTag($cert_structure);

        $name = str_replace(
            ['\\', '/', ':', '\'', '\*', '?', '"', '<', '>', '|'],
            ['', '', '', '', '', '', '', '', '', ''],
            $name
        );

        $this->getPdf($cert_structure, $name, $bgimage, $orientation, true, false);
    }

    public function getPdf($html, $name, $img = false, $orientation = 'P', $download = true, $facs_simile = false, $for_saving = false)
    {
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/pdf/lib.pdf.php');

        $pdf = new PDF($orientation);
        $pdf->setEncrypted(FormaLms\lib\Get::cfg('certificate_encryption', true));
        $pdf->setPassword(FormaLms\lib\Get::cfg('certificate_password', null));

        if ($for_saving) {
            return $pdf->getPdf($html, $name, $img, $download, $facs_simile, $for_saving);
        } else {
            $pdf->getPdf($html, $name, $img, $download, $facs_simile, $for_saving);
        }
    }

    public function send_facsimile_certificate($id_certificate, $id_user, $id_course, $array_substituton = false)
    {
        $query_certificate = '
		SELECT name, cert_structure, base_language, orientation, bgimage
		FROM ' . $GLOBALS['prefix_lms'] . "_certificate
		WHERE id_certificate = '" . $id_certificate . "'";
        [$name, $cert_structure, $base_language, $orientation, $bgimage] = sql_fetch_row(sql_query($query_certificate));

        if ($array_substituton !== false) {
            $cert_structure = str_replace(array_keys($array_substituton), $array_substituton, $cert_structure);
        }
        $cert_structure = fillSiteBaseUrlTag($cert_structure);

        $this->getPdf($cert_structure, $name, $bgimage, $orientation, true, true);
    }

    /**
     *  $from_multi true and download false used for generating multiple certificates zipped in a file.
     */
    public function send_certificate($id_certificate, $id_user, $id_course, $array_substituton = false, $download = true, $from_multi = false, $id_association = 0)
    {
        $isAggregatedCert = FormaLms\lib\Get::req('aggCert', DOTY_INT, 0);
        if ($isAggregatedCert) {
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/' . _folder_lib_ . '/lib.aggregated_certificate.php');
            $aggCertLib = new AggregatedCertificate();
        }

        // funct. not called not by aggregated cert.
        if (!$isAggregatedCert) {
            $query_certificate = '
			SELECT cert_file
			FROM ' . $GLOBALS['prefix_lms'] . "_certificate_assign
			WHERE id_certificate = '" . $id_certificate . "'
				 AND id_course = '" . $id_course . "'
				 AND id_user = '" . $id_user . "' ";
        } else {
            $query_certificate = 'SELECT cert_file'
                . ' FROM ' . $aggCertLib->table_assign_agg_cert
                . ' WHERE idUser = ' . $id_user
                . ' AND idCertificate = ' . $id_certificate
                . ' AND idAssociation = ' . $id_association
                . " AND cert_file != ''";
        }

        $re = sql_query($query_certificate);

        if ((sql_num_rows($re) > 0)) {
            if (!$download) {
                return;
            }
            require_once _base_ . '/lib/lib.download.php';
            [$cert_file] = sql_fetch_row($re);
            sendFile(CERTIFICATE_PATH, $cert_file);

            return;
        }

        Events::trigger('lms.certificate_user.assign', [
            'id_user' => $id_user,
            'id_certificate' => $id_certificate,
            'id_course' => $id_course
        ], 0);
        
        $query_certificate = '
		SELECT name, cert_structure, base_language, orientation, bgimage
		FROM %lms_certificate WHERE id_certificate = "' . $id_certificate . '"';
        [$name, $cert_structure, $base_language, $orientation, $bgimage] = sql_fetch_row(sql_query($query_certificate));

        require_once _base_ . '/lib/lib.upload.php';

        if ($array_substituton !== false) {
            $cert_structure = str_replace(array_keys($array_substituton), $array_substituton, $cert_structure);
        }
        $cert_structure = fillSiteBaseUrlTag($cert_structure);

        $cert_file = $id_course . '_' . $id_certificate . '_' . $id_user . '_' . time() . '_' . $name . '.pdf';

        sl_open_fileoperations();
        if (!$fp = sl_fopen(CERTIFICATE_PATH . $cert_file, 'w')) {
            sl_close_fileoperations();

            return false;
        }
        if (!fwrite($fp, $this->getPdf($cert_structure, $name, $bgimage, $orientation, false, false, true))) {
            sl_close_fileoperations();

            return false;
        }
        fclose($fp);
        sl_close_fileoperations();

        //save the generated file in database
        $the_date = date('Y-m-d H:i:s');
        if (!$isAggregatedCert) {
            $query = 'INSERT INTO %lms_certificate_assign '
                . ' ( id_certificate, id_course, id_user, on_date, cert_file ) '
                . ' VALUES '
                . " ( '" . $id_certificate . "', '" . $id_course . "', '" . $id_user . "', '" . $the_date . "', '" . addslashes($cert_file) . "' ) ";
        } else {
            $query = 'UPDATE ' . $aggCertLib->table_assign_agg_cert
                . " SET on_date = '" . $the_date
                . "', cert_file = '" . addslashes($cert_file)
                . "' WHERE idUser = " . intval($id_user)
                . ' AND idCertificate = ' . intval($id_certificate)
                . ' AND idAssociation = ' . intval($id_association);
        }
        if (!sql_query($query)) {
            return false;
        }

        $new_data['cert_file'] = $cert_file;
        $new_data['on_date'] = $the_date;
        Events::trigger('lms.certificate_user.assigned', [
            'id_user' => $id_user,
            'id_certificate' => $id_certificate,
            'new_data' => $new_data,
        ], 0);

        if ($from_multi) {
            return;
        }

        $this->getPdf($cert_structure, $name, $bgimage, $orientation, $download, false);
    }

    public function getCourseForCertificate($id_certificate)
    {
        $id_course = [];

        $query_id_course = 'SELECT cc.id_course' .
            ' FROM %lms_certificate_course AS cc JOIN %lms_course AS c ' .
            ' ON (cc.id_course = c.idCourse) ' .
            " WHERE cc.id_certificate = '" . $id_certificate . "' " .
            " AND cc.available_for_status <> '" . AVS_NOT_ASSIGNED . "'";

        $result_id_course = sql_query($query_id_course);

        while (list($id_course_find) = sql_fetch_row($result_id_course)) {
            $id_course[] = $id_course_find;
        }

        return $id_course;
    }

    public function getInfoForCourseCertificate($id_course, $id_certificate, $id_user = false)
    {
        // Get Course info
        $query = 'SELECT name' .
            ' FROM %lms_course' .
            ' WHERE  idCourse = ' . $id_course;
        $result = sql_query($query);
        while ($row = sql_fetch_row($result)) {
            $arrCourseInfo[] = $row;
        }

        $courseInfo = $arrCourseInfo[0][0];
        // Parsing campo  ( togliere: blank ' " & / \ )
        $pattern = "/['\"\s&\/]/i";
        $replacement = '';
        $courseInfo = preg_replace($pattern, $replacement, $courseInfo);

        if ($id_user) {
            // Get User info
            $query = 'SELECT lastname, firstname' .
                ' FROM ' . $GLOBALS['prefix_fw'] . '_user' .
                ' WHERE idst = ' . $id_user;
            $result = sql_query($query);
            while ($row = sql_fetch_row($result)) {
                $arrUserInfo[] = $row;
            }

            $userInfo = $arrUserInfo[0][0] . $arrUserInfo[0][1];
            // Parsing campo  ( togliere: blank ' " & / \ )
            $pattern = "/['\"\s&\/]/i";
            $replacement = '';
            $userInfo = preg_replace($pattern, $replacement, $userInfo);
        }

        // Get Certificate info
        $info = [];
        $query = 'SELECT *' .
            ' FROM %lms_certificate_assign' .
            " WHERE id_certificate = '" . $id_certificate . "'" .
            " AND id_course = '" . $id_course . "'";
        if ($id_user) {
            $query .= " AND id_user = $id_user";
        }

        $result = sql_query($query);

        while ($row = sql_fetch_row($result)) {
            $dateTimeCert = strtotime($row[ASSIGN_OD_DATE]);
            $dateInfo = date('YmdHis', $dateTimeCert);
            if ($id_user) {
                $row[ASSIGN_CERT_SENDNAME] = $userInfo . '_' . $courseInfo . '_' . $dateInfo . '.pdf';
            } else {
                $row[ASSIGN_CERT_SENDNAME] = $courseInfo . '_' . $dateInfo . '.pdf';
            }
            $info[] = $row;
        }

        return $info;
    }

    public function getCertificateInfo($id_certificate)
    {
        $info = [];
        $query = 'SELECT id_certificate, name, description, base_language, cert_structure FROM %lms_certificate ';
        if (is_array($id_certificate) && count($id_certificate) > 0) {
            $query .= " WHERE id_certificate IN ('" . implode("','", $id_certificate) . "')";
        } else {
            $query .= " WHERE id_certificate = '" . (int)$id_certificate . "'";
        }
        $result = sql_query($query);

        while ($row = sql_fetch_row($result)) {
            $info[$row[CERT_ID]] = $row;
        }

        return $info;
    }

    public function delCertificateForUserInCourse($id_certificate, $id_user, $id_course)
    {
        $query = 'DELETE ' .
            ' FROM %lms_certificate_assign ' .
            " WHERE id_certificate = '" . $id_certificate . "'" .
            " AND id_course = '" . $id_course . "'" .
            " AND id_user = '" . $id_user . "'";

        return sql_query($query);
    }

    public function getNumberOfCertificateForCourse($id_certificate, $id_course)
    {
        $query = 'SELECT COUNT(*)' .
            ' FROM %lms_certificate_assign' .
            " WHERE id_certificate = '" . $id_certificate . "'" .
            " AND id_course = '" . $id_course . "'";

        [$res] = sql_fetch_row(sql_query($query));

        return $res;
    }

    public function deleteCourseCertificateAssignments($id_course)
    {
        if ((int)$id_course <= 0) {
            return false;
        }

        $query = 'DELETE FROM %lms_certificate_course WHERE id_course = ' . (int)$id_course;
        $res = sql_query($query);

        return $res ? true : false;
    }

    public function getCertificateQuery($users = false, $id_cert = false, $year = false)
    {
        $conditions = [];

        if ($users) {
            if (is_numeric($users)) {
                $users = [$users];
            }
            if (is_array($users)) {
                $conditions[] = ' t3.idst IN (' . implode(',', $users) . ') ';
            }
        }

        if ($id_cert) {
            if (is_numeric($id_cert)) {
                $conditions[] = " t1.id_certificate = '" . (int)$id_cert . "' ";
            }
        }

        if ($year) {
            if (is_numeric($year)) {
                $conditions[] = " YEAR(t2.ondate) = '" . (int)$year . "' ";
            }
        }

        $query = 'SELECT t1.code, t1.name, YEAR(t2.on_date) as year, t3.firstname, t3.lastname '
            . ' FROM %lms_certificate as t1 JOIN %lms_certificate_assign as t2 JOIN ' . $GLOBALS['prefix_fw'] . '_user as t3 '
            . ' ON (t1.id_certificate = t2.id_certificate AND t2.id_user = t3.idst)  '
            . (count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '')
            . ' ORDER BY t3.lastname, t3.firstname, t1.name';

        return $query;
    }

    public function getCertificateQueryTotal($users = false, $id_cert = false, $year = false)
    {
        $conditions = [];

        if ($users) {
            if (is_numeric($users)) {
                $users = [$users];
            }
            if (is_array($users)) {
                $conditions[] = ' t3.idst IN (' . implode(',', $users) . ') ';
            }
        }

        if ($id_cert) {
            if (is_numeric($id_cert)) {
                $conditions[] = " t1.id_certificate = '" . (int)$id_cert . "' ";
            }
        }

        if ($year) {
            if (is_numeric($year)) {
                $conditions[] = " YEAR(t2.ondate) = '" . (int)$year . "' ";
            }
        }

        $query = 'SELECT COUNT(*) '
            . ' FROM %lms_certificate as t1 JOIN %lms_certificate_assign as t2 JOIN ' . $GLOBALS['prefix_fw'] . '_user as t3 '
            . ' ON (t1.id_certificate = t2.id_certificate AND t2.id_user = t3.idst)  '
            . (count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '');

        [$total] = sql_fetch_row(sql_query($query));
        echo sql_error();

        return $total;
    }
}

function getCertificateQuery($users = false, $id_cert = false, $year = false)
{
    $conditions = [];

    if ($users) {
        if (is_numeric($users)) {
            $users = [$users];
        }
        if (is_array($users)) {
            $conditions[] = ' t3.idst IN (' . implode(',', $users) . ') ';
        }
    }

    if ($id_cert) {
        if (is_numeric($id_cert)) {
            $conditions[] = " t1.id_certificate = '" . (int)$id_cert . "' ";
        }
    }

    if ($year) {
        if (is_numeric($year)) {
            $conditions[] = " YEAR(t2.ondate) = '" . (int)$year . "' ";
        }
    }

    $query = 'SELECT t1.code, t1.name, YEAR(t2.on_date) as year, t3.firstname, t3.lastname '
        . ' FROM %lms_certificate as t1 JOIN %lms_certificate_assign as t2 JOIN ' . $GLOBALS['prefix_fw'] . '_user as t3 '
        . ' ON (t1.id_certificate = t2.id_certificate AND t2.id_user = t3.idst)  '
        . (count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '')
        . ' ORDER BY t3.lastname, t3.firstname, t1.name';

    return $query;
}

function getCertificateQueryTotal($users = false, $id_cert = false, $year = false)
{
    $conditions = [];

    if ($users) {
        if (is_numeric($users)) {
            $users = [$users];
        }
        if (is_array($users)) {
            $conditions[] = ' t3.idst IN (' . implode(',', $users) . ') ';
        }
    }

    if ($id_cert) {
        if (is_numeric($id_cert)) {
            $conditions[] = " t1.id_certificate = '" . (int)$id_cert . "' ";
        }
    }

    if ($year) {
        if (is_numeric($year)) {
            $conditions[] = " YEAR(t2.ondate) = '" . (int)$year . "' ";
        }
    }

    $query = 'SELECT COUNT(*) '
        . ' FROM %lms_certificate as t1 JOIN %lms_certificate_assign as t2 JOIN ' . $GLOBALS['prefix_fw'] . '_user as t3 '
        . ' ON (t1.id_certificate = t2.id_certificate AND t2.id_user = t3.idst)  '
        . (count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '');

    [$total] = sql_fetch_row(sql_query($query));

    return $total;
}
