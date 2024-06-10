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

/**
 * Display a progress bar with
 *	- $totComplete elementi completi (green see css)
 *  - $totFailed elementi falliti (yellow see css)
 *	- $total elementi in tutto (white see css).
 *
 * @param int  $totComplete number of completed elements
 * @param int  $totFailed   number of failed elements
 * @param int  $total       total number of elements
 * @param bool $show_title  show the title of the progress bar
 **/
function renderProgress($tot_complete, $tot_failed, $total, $show_title = false)
{
    //if($total == 0) return '';
    $perc_complete = round(($tot_complete / $total) * 100, 2);
    $perc_failed = round(($tot_failed / $total) * 100, 2);

    $title = str_replace('[total]', $total, Lang::t('_PROGRESS_TITLE', 'course'));
    $title = str_replace('[complete]', $tot_complete, $title);
    $title = str_replace('[failed]', $tot_failed, $title);

    //	$html = $perc_complete. " - ". $tot_complete. " - ".$total;
    $html = '';
    if ($show_title === true) {
        $html .= '<span class="progress_title">' . $title . '</span><br />';
    }
    if ($perc_complete >= 100) {
        /*
        $html .= "\n".'<div class="box_progress_complete" title="'.$title.'">'
            .'<div class="nofloat">'
            .'</div></div>'."\n";
          */
        $perc_complete = 100;
        $html .= '<div class="progress">
                              <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="' . $perc_complete . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $perc_complete . '%">
                                <span class="progress-completed">' . $perc_complete . '%</span>
                              </div>
                            </div>';
    } elseif ($perc_failed + $perc_complete >= 100) {
        /*
        $html .= "\n".'<div class="box_progress_failed" title="'.$title.'">';
        if($perc_complete != 0) $html .= '<div class="bar_complete" style="width: '.$perc_complete.'%;"></div>';
        $html .= '<div class="nofloat">'
            .'</div></div>'."\n";
            */
        $html .= '<div class="progress">
                              <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="' . $perc_complete . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $perc_complete . '%">
                                <span class="sr-only">' . $perc_failed . '% Complete (danger)</span>
                                <span class="progress-completed">' . $perc_failed . '%</span>
                              </div>
                            </div>';
    } else {
        /*
        $html .= "\n".'22<div class="box_progress_bar" title="'.$title.'">';
        if($perc_complete != 0) $html .= '<div class="bar_complete" style="width: '.$perc_complete.'%;"></div>';
        if($perc_failed != 0) $html .= '<div class="bar_failed" style="width: '.$perc_failed.'%;"></div>';
        $html .= '<div class="nofloat">'
          .'</div></div>'."\n";
         */
        if ($perc_complete >= 0) {
            $html .= '<div class="progress">
                              <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="' . $perc_complete . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $perc_complete . '%">
                                <span class="sr-only">' . $perc_complete . '% Complete (success)</span>
                                 <span class="progress-completed">' . $perc_complete . '%</span>
                                 
                              </div>
                            </div>';
        }

        /*
       if($perc_complete == 0 && $total==0){
                   $html .= '<div class="progress">
                              <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                <span class="sr-only">0% Complete (success)</span>
                              </div>
                            </div>';
        }
        */
    }

    return $html;
}


/**
 * Return total number of items in a course.
 *
 * @param int  $idCourse           id of course
 * @param bool $countHidden        count hidden elements
 * @param int  $idUser             id of user to filter accessibility
 * @param bool $countNotAccessible count not accessible elements to user
 *                                 this parameter require $idUser to be a valid user
 *
 * @return int number of items in course
 **/
function getNumCourseItems($idCourse, $countHidden = true, $idUser = false, $countNotAccessible = true)
{
    return count(getCountableCourseItems($idCourse, $countHidden, $idUser, $countNotAccessible));
}

/**
 * Return total number of items in a course.
 *
 * @param int  $idCourse           id of course
 * @param bool $countHidden        count hidden elements
 * @param int  $idUser             id of user to filter accessibility
 * @param bool $countNotAccessible count not accessible elements to user
 *                                 this parameter require $idUser to be a valid user
 *
 * @return int number of items in course
 **/
function getCountableCourseItems($idCourse, $countHidden = true, $idUser = false, $countNotAccessible = true) : array
{
    $query = 'SELECT GROUP_CONCAT(idOrg) as list FROM %lms_organization';
    if (!$countNotAccessible) {
        $query .= ' LEFT JOIN %lms_organization_access'
                 . ' ON ( %lms_organization.idOrg = %lms_organization_access.idOrgAccess )';
    }
    $query .= " WHERE (idCourse = '" . (int) $idCourse . "')"
            . '   AND (idResource <> 0)';
    if (!$countHidden) {
        $query .= " AND (visible = '1')";
    }

    if (!$countNotAccessible) {
        $query .= ' AND ( ( %lms_organization_access.kind = "user"'
                 . ' 	AND %lms_organization_access.value = "' . (int) $idUser . '")'
                 . ' OR ( %lms_organization_access.kind = "group"'
                 . ' 	AND %lms_organization_access.value IN (SELECT GROUP_CONCAT(idst) as idsts FROM %adm_group_members WHERE idstMember = "' . (int) $idUser .'" GROUP BY idstMember))'
                 . '	    OR %lms_organization_access.idOrgAccess IS NULL'
                 . ')';
    }

    $rs = sql_query($query);

    if ($rs === false) {
        return [];
    } else {
        [$list] = sql_fetch_row($rs);
        sql_free_result($rs);

        return explode("," , $list);
    }
}

/**
 * Return total items for a user in a given course whit a specified state.
 *
 * @param int   $stat_idUser   id of user
 * @param int   $stat_idCourse id of the course
 * @param mixed $arrStatus     array of status to search
 * @param mixed $arrayFilter     array of ids filtered
 *
 * @return int number of items in requested status
 **/
function getStatStatusCount($stat_idUser, $stat_idCourse, $arrStauts, $arrayFilter = [])
{
    $query = 'SELECT count(ct.idreference)'
        . ' FROM %lms_commontrack ct, %lms_organization org'
        . ' WHERE (ct.idReference = org.idOrg)'
        . "   AND (ct.idUser = '" . (int) $stat_idUser . "')"
        . "   AND (idCourse = '" . (int) $stat_idCourse . "')"
        . "   AND (status IN ('" . implode("','", $arrStauts) . "'))";

    if(count($arrayFilter)) {
        $query .= "   AND (ct.idReference IN ('" . implode("','", $arrayFilter) . "'))";
    }
    if (($rsItems = sql_query($query)) === false) {
        echo $query;
        errorCommunication('Error on query to get user count based on status');

        return;
    }
    [$tot] = sql_fetch_row($rsItems);
    sql_free_result($rsItems);

    return $tot;
}

/**
 * Save notification of user status in a course.
 *
 * @param int $idUser   id of the user
 * @param int $idCourse id of the course
 * @param int $status   new status
 **/
function saveTrackStatusChange($idUser, $idCourse, $status)
{
    [$prev_status] = sql_fetch_row(sql_query("
        SELECT status
        FROM %lms_courseuser
        WHERE idUser = '" . (int)$idUser . "' AND idCourse = '" . (int)$idCourse . "'"));

    $new_data = ['status' => $status, 'prev_status' => $prev_status];
    $data = Events::trigger('lms.course_user.updating', [
        'id_user' => $idUser,
        'id_course' => $idCourse,
        'new_data' => $new_data,
    ]);

    $status = $data['new_data']['status'];

    require_once _lms_ . '/lib/lib.course.php';

    /*
    list($prev_status) = sql_fetch_row(sql_query("
        SELECT status
        FROM %lms_courseuser
        WHERE idUser = '".(int)$idUser."' AND idCourse = '".(int)$idCourse."'")
    );
         */
    $extra = '';
    if ($prev_status != $status) {
        switch ($status) {
            case _CUS_SUBSCRIBED:
                //approved subscriptin for example
                $extra = ', date_inscr = NOW()';
             break;
            case _CUS_BEGIN:
                //first access
                UpdatesLms::resetCache();
                $extra = ', date_first_access = NOW()';
             break;
            case _CUS_END:
                //end course
                $extra = ', date_complete = NOW()';
             break;
        }
    }

    if (!sql_query("
	UPDATE %lms_courseuser
	SET status = '" . (int) $status . "' " . $extra . "
	WHERE idUser = '" . (int) $idUser . "' AND idCourse = '" . (int) $idCourse . "'")) {
        return false;
    }

    $re = sql_query("
	SELECT when_do 
	FROM %lms_statuschangelog
	WHERE status_user = '" . (int) $status . "' AND 
		idUser = '" . (int) $idUser . "' AND 
		idCourse = '" . (int) $idCourse . "'");

    if (sql_num_rows($re)) {
        sql_query("
			UPDATE %lms_statuschangelog
			SET when_do = NOW()
			WHERE status_user = '" . (int) $status . "' AND
				idUser = '" . (int) $idUser . "' AND
				idCourse = '" . (int) $idCourse . "'"
        );
    } else {
        sql_query("
			INSERT INTO %lms_statuschangelog
			SET status_user = '" . (int) $status . "',
				idUser = '" . (int) $idUser . "',
				idCourse = '" . (int) $idCourse . "',
				when_do = NOW()"
        );
    }

    if ($prev_status != $status && $status == _CUS_END) {
        // send alert
        require_once _lms_ . '/lib/lib.course.php';
        require_once _base_ . '/lib/lib.eventmanager.php';

        $cd = new FormaCourse($idCourse);
        $acl_man = \FormaLms\lib\Forma::getAclManager();
        $teachers = Man_Course::getIdUserOfLevel($idCourse, '6');

        $array_subst = [
            '[user]' => $acl_man->getUserName($idUser),
            '[course]' => $cd->getValue('name'),
        ];

        $msg_composer = new EventMessageComposer();

        $msg_composer->setSubjectLangText('email', '_USER_END_COURSE_SBJ', false);
        $msg_composer->setBodyLangText('email', '_USER_END_COURSE_TEXT', $array_subst);

        $msg_composer->setBodyLangText('sms', '_USER_END_COURSE_TEXT_SMS', $array_subst);

        // send message to the user subscribed
        createNewAlert('UserCourseEnded',
                        'status',
                        'modify',
                        '1',
                        'User end course',
                        $teachers,
                        $msg_composer);

        //add course's competences scores to user
        $cmodel = new CompetencesAdm();
        $cmodel->assignCourseCompetencesToUser($idCourse, $idUser);

        //increment coursecompleted if this course is in a coursepath
        require_once _lms_ . '/lib/lib.coursepath.php';
        $cpmodel = new CoursePath_Manager();
        $cpmodel->assignComplete($idCourse, $idUser);
    }

    Events::trigger('lms.course_user.updated', [
        'id_user' => $idUser,
        'id_course' => $idCourse,
        'new_data' => $new_data,
    ]);

    return true;
}
