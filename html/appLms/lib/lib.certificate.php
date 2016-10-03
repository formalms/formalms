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

define("CERT_ID",					0);
define("CERT_NAME",					1);
define("CERT_DESCR",				2);
define("CERT_LANG",					3);
define("CERT_STRUCTURE",			4);
define("CERT_CODE",					5);

define("CERT_ID_COURSE",			4);
define("CERT_AV_STATUS",			5);
define("CERT_AV_POINT_REQUIRED", 	6);

define("CERTIFICATE_PATH", '/appLms/certificate/');

define("AVS_NOT_ASSIGNED", 					0);
define("AVS_ASSIGN_FOR_ALL_STATUS", 		1);
define("AVS_ASSIGN_FOR_STATUS_INCOURSE", 	2);
define("AVS_ASSIGN_FOR_STATUS_COMPLETED", 	3);

define("ASSIGN_CERT_ID", 		0);
define("ASSIGN_COURSE_ID", 		1);
define("ASSIGN_USER_ID", 		2);
define("ASSIGN_OD_DATE", 		3);
define("ASSIGN_CERT_FILE", 		4);

class Certificate {

        function countAssignment($filter){                
                return count($this->getAssignment($filter));
        }
    
        function getAssignment($filter){   
                $assigned = $this->getAssigned($filter);
                $assignable = $this->getAssignable($filter);
                
                $assignment = array();
                foreach ($assigned AS $as){
                    $assignment[] = $as;
                }
                foreach ($assignable AS $as){
                    $assignment[] = $as;
                }
                
                return $assignment;
        }

        function getAssigned($filter){
                $query = "      SELECT ca.id_certificate, ca.id_course," 
                            ."	ca.id_user, SUBSTRING(u.userid, 2) AS username,"
                            ."	u.lastname, u.firstname, co.code, cc.available_for_status,"
                            ."  co.name AS course_name, ce.name AS cert_name,"
                            ."	cu.status, co.date_begin, co.date_end, cu.date_inscr,"
                            ."	cu.date_complete, ca.on_date, ca.cert_file"
                            ."	FROM %lms_certificate_assign AS ca"
                            ."      LEFT JOIN %lms_certificate AS ce"
                            ."      ON ca.id_certificate = ce.id_certificate"
                            ."      LEFT JOIN %lms_course AS co"
                            ."      ON ca.id_course = co.idCourse"
                            ."      LEFT JOIN %lms_courseuser AS cu"
                            ."      ON ca.id_user = cu.idUser" 
                            ."          AND ca.id_course  = cu.idCourse"
                            ."      LEFT JOIN %lms_certificate_course AS cc" 
                            ."      ON ca.id_certificate = cc.id_certificate"
                            ."          AND ca.id_course  = cc.id_course"
                            ."      LEFT JOIN %adm_user AS u"
                            ."      ON ca.id_user = u.idst"
                            ."	WHERE 1 = 1";
                if (isset($filter['id_certificate'])) {
                        $query .= " AND ca.id_certificate = ".$filter['id_certificate'];	
                }
                if (isset($filter['id_course'])) {
                        $query .= " AND ca.id_course = ".$filter['id_course'];	
                }
                if (isset($filter['id_user'])) {
                        $query .= " AND ca.id_user = ".$filter['id_user'];	
                }
                if (!isset($filter['id_user'])) {
                    if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
                    {
                            require_once(_base_.'/lib/lib.preference.php');
                            $adminManager = new AdminPreference();
                            $query .= " AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'idUser');
                    }                        	
                }

        $assigned = array();
                $res = sql_query($query);
                while ($row = sql_fetch_assoc($res)){
                    $assigned[] = $row;
                }
                return $assigned;
        }

        function getAssignable($filter){
                $query = "	SELECT ce.id_certificate, co.idCourse AS id_course," 
                            ."	u.idst AS id_user, SUBSTRING(u.userid, 2) AS username,"
                            ."	u.lastname, u.firstname, co.code, cc.available_for_status,"
                            ."  co.name AS course_name, ce.name AS cert_name,"
                            ."	cu.status, co.date_begin, co.date_end, cu.date_inscr,"
                            ."	cu.date_complete, NULL AS on_date, NULL AS cert_file"         
                            ."	FROM %lms_certificate_course AS cc"
                            ."      JOIN %lms_certificate AS ce"
                            ."      ON cc.id_certificate = ce.id_certificate"
                            ."      JOIN %lms_course AS co"
                            ."      ON cc.id_course = co.idCourse"
                            ."      JOIN %lms_courseuser AS cu"
                            ."      ON co.idCourse = cu.idCourse"
                            ."          AND ("
                            ."              (cc.available_for_status = 1 AND cu.status >= 0 AND cu.status <= 2)"
                            ."              OR (cc.available_for_status = 2 AND cu.status >= 1 AND cu.status <= 2)" 
                            ."              OR (cc.available_for_status = 3 AND cu.status = 2)"
                            ."          )" 
                            ."      JOIN %adm_user AS u"
                            ."      ON cu.idUser = u.idst"
                            ."	WHERE (cc.id_certificate, co.idCourse, cu.idUser) NOT IN ("
                            ."      SELECT ca.id_certificate, ca.id_course, ca.id_user"
                            ."      FROM %lms_certificate_assign AS ca"
                            ."	)";
                if (isset($filter['id_certificate'])) {
                        $query .= " AND cc.id_certificate = ".$filter['id_certificate'];	
                }
                if (isset($filter['id_course'])) {
                        $query .= " AND co.idCourse = ".$filter['id_course'];	
                }
                if (isset($filter['id_user'])) {
                        $query .= " AND cu.idUser = ".$filter['id_user'];	
                }
                if (!isset($filter['id_user'])) {
                    if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
                    {
                            require_once(_base_.'/lib/lib.preference.php');
                            $adminManager = new AdminPreference();
                            $query .= " AND ".$adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'idUser');
                    }                        	
                }

        $assignable = array();
                $res = sql_query($query);
                while ($row = sql_fetch_assoc($res)){
                    if($this->certificateAvailableForUser($row['id_certificate'],$row['id_course'],$row['id_user'])
                            && $this->canRelExceptional($row['id_user'], $row['id_course'])){
                        $assignable[] = $row;
                    }
                }
                return $assignable;
        }
        
    function countMetaAssignment($filter) {
        return count($this->getMetaAssignment($filter));
    }
    
    function getMetaAssignment($filter){
        $metaAssigned = $this->getMetaAssigned($filter);
        $metaAssignable = $this->getMetaAssignable($filter);

        $metaAssignment = array();
        foreach ($metaAssigned AS $mas) {
            $metaAssignment[] = $mas;
        }
        foreach ($metaAssignable AS $mas) {
            $metaAssignment[] = $mas;
        }

        return $metaAssignment;        
    }
    
    function getMetaAssigned($filter){
        $query = "  SELECT cma.idCertificate AS id_certificate, cma.idMetaCertificate AS id_meta, cmc.idUser AS id_user,"
               . "      ce.code AS cert_code, ce.name AS cert_name, cma.on_date,"
               . "      GROUP_CONCAT(DISTINCT CONCAT('(', co.code, ') - ', co.name) SEPARATOR '<br>') AS courses"
               . "  FROM %lms_certificate_meta_course as cmc"
               . "      JOIN %lms_certificate_meta_assign as cma"
               . "      ON cmc.idMetaCertificate = cma.idMetaCertificate"
               . "      JOIN %lms_certificate AS ce"
               . "      ON cma.idCertificate = ce.id_certificate"
               . "      JOIN %lms_course AS co"
               . "      ON cmc.idCourse = co.idCourse"
               . "      WHERE 1 = 1";
        
        if (isset($filter['id_certificate'])) {
            $query .= " AND cma.idCertificate = " . $filter['id_certificate'];
        }
        if (isset($filter['id_course'])) {
            $query .= " AND cmc.idCourse = " . $filter['id_course'];
        }
        if (isset($filter['id_user'])) {
            $query .= " AND cma.idUser = " . $filter['id_user'];
        }
        if (!isset($filter['id_user'])) {
            if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                require_once(_base_ . '/lib/lib.preference.php');
                $adminManager = new AdminPreference();
                $query .= " AND " . $adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'idUser');
            }
        }
        $query .= " GROUP BY cmc.idMetaCertificate";

        $res = sql_query($query);
        while ($row = sql_fetch_assoc($res)) {
            $metaAssigned[] = $row;
        }
        return $metaAssigned;
    }
    
    function getMetaAssignable($filter){
        $query = "  SELECT cm.idCertificate AS id_certificate, cm.idMetaCertificate AS id_meta, cmc.idUser AS id_user,"
               . "      ce.code AS cert_code, ce.name AS cert_name, NULL AS on_date,"
               . "      GROUP_CONCAT(DISTINCT CONCAT('(', co.code, ') - ', co.name) SEPARATOR '<br>') AS courses"
               . "  FROM %lms_certificate_meta_course as cmc"
               . "      JOIN %lms_certificate_meta as cm"
               . "      ON cmc.idMetaCertificate = cm.idMetaCertificate"
               . "      JOIN %lms_certificate AS ce"
               . "      ON cm.idCertificate = ce.id_certificate"
               . "      JOIN %lms_course AS co"
               . "      ON cmc.idCourse = co.idCourse"
               . "  WHERE (cm.idCertificate, cm.idMetaCertificate, cmc.idUser) NOT IN ("
               . "      SELECT cma.idCertificate, cma.idMetaCertificate, cma.idUser"
               . "      FROM %lms_certificate_meta_assign AS cma"
               . "  ) AND 2 = ALL ("
               . "      SELECT cu.status"
               . "      FROM %lms_courseuser AS cu"
               . "          JOIN %lms_certificate_meta_course AS mc"
               . "          ON cu.idCourse = mc.idCourse"
               . "              AND cu.idUser = mc.idUser"
               . "      WHERE mc.idUser = cmc.idUser"
               . "          AND mc.idMetaCertificate = cmc.idMetaCertificate"
               . "  )" ;
        
        if (isset($filter['id_certificate'])) {
            $query .= " AND cm.idCertificate = " . $filter['id_certificate'];
        }
        if (isset($filter['id_course'])) {
            $query .= " AND cmc.idCourse = " . $filter['id_course'];
        }
        if (isset($filter['id_user'])) {
            $query .= " AND cmc.idUser = " . $filter['id_user'];
        }
        if (!isset($filter['id_user'])) {
            if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                require_once(_base_ . '/lib/lib.preference.php');
                $adminManager = new AdminPreference();
                $query .= " AND " . $adminManager->getAdminUsersQuery(Docebo::user()->getIdSt(), 'idUser');
            }
        }
        $query .= " GROUP BY cmc.idMetaCertificate";

        $res = sql_query($query);
        while ($row = sql_fetch_assoc($res)) {
            $metaAssignable[] = $row;
        }
        return $metaAssignable;
    }

        function canRelExceptional($perm_close_lo, $id_user, $id_course){
            require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
            require_once($GLOBALS['where_lms'] . '/lib/lib.orgchart.php');

            $course_score_final = false;
            $org_man = new OrganizationManagement(false);
            $rep_man = new CourseReportManager();

            if ($perm_close_lo == 0) {
                $score_final = $org_man->getFinalObjectScore(array($id_user), array($id_course));

                if (isset($score_final[$id_course][$id_user]) && $score_final[$id_course][$id_user]['max_score']) {
                    $course_score_final = $score_final[$id_course][$id_user()]['score'];
                    $course_score_final_max = $score_final[$id_course][$id_user()]['max_score'];
                }
            } else {
                $score_course = $rep_man->getUserFinalScore(array($id_user), array($id_course));

                if (!empty($score_course)) {
                    $course_score_final = (isset($score_course[$id_user][$id_course]) ? $score_course[$id_user][$id_course]['score'] : false);
                    $course_score_final_max = (isset($score_course[$id_user][$id_course]) ? $score_course[$id_user][$id_course]['max_score'] : false);
                }
            }

            if ($course_score_final >= $certificate[CERT_AV_POINT_REQUIRED]){
                return true;
            } else {
                return false;
            }
		}
	function getCertificateList($name_filter = false, $code_filter = false) {

		$cert = array();
		$query_certificate = "
		SELECT id_certificate, name, description, base_language, cert_structure, code
		FROM ".$GLOBALS['prefix_lms']."_certificate"
		." WHERE meta = 0";

		if ($name_filter && $code_filter)
			$query_certificate .= " AND name LIKE '%".$name_filter."%'" .
									" AND code LIKE '%".$code_filter."%'";
		elseif ($name_filter)
			$query_certificate .= " AND name LIKE '%".$name_filter."%'";
		elseif ($code_filter)
			$query_certificate .= " AND code LIKE '%".$code_filter."%'";

		$query_certificate .= " ORDER BY name";

		$re_certificate = sql_query($query_certificate);

		while($row = sql_fetch_row($re_certificate))
		{
			$cert[$row[CERT_ID]] = $row;
		}

		return $cert;
	}
		
        function getCourseCertificateObj($id_course) {

                $cert = array();
                $query_certificate = "
                SELECT id_certificate, certificate_available_for_obj
                FROM ".$GLOBALS['prefix_lms']."_certificate_course
                WHERE id_course = '".$id_course."' ";
                $re_certificate = sql_query($query_certificate);
                while(list($id, $available_for_status) = sql_fetch_row($re_certificate)) {

                        $cert[$id] = $available_for_status;
                }
                return $cert;
        }

        function getCourseCertificateAchi($id_course) {

                $cert = array();
                $query_certificate = "
                SELECT id_certificate, certificate_available_for_who
                FROM ".$GLOBALS['prefix_lms']."_certificate_course
                WHERE id_course = '".$id_course."' ";
                $re_certificate = sql_query($query_certificate);
                while(list($id, $available_for_who) = sql_fetch_row($re_certificate)) {

                        $cert[$id] = $available_for_who;
                }
                return $cert;
        }

        function certificateAvailableForUser($id_cert, $id_course, $id_user) {
                $sql = "SELECT minutes_required FROM learning_certificate_course WHERE id_course = ".$id_course." AND id_certificate = ".$id_cert;
                $re = sql_query($sql);
                list($minutes_required) = sql_fetch_row($re);
                if ($minutes_required > 0){
                    require_once(_lms_.'/lib/lib.track_user.php');

                    $time_in = TrackUser::getUserTotalCourseTime($id_user, $id_course);
                    $minutes_in = (double) ($time_in / 60);
                    if ($minutes_in<$minutes_required){
	                                return false;
	                }
                }

                return true;
        }


	function getCourseCertificate($id_course) {

		$cert = array();
		$query_certificate = "
		SELECT id_certificate, available_for_status, minutes_required
		FROM %lms_certificate_course
		WHERE id_course = '".$id_course."' "
		." AND point_required = 0";
		$re_certificate = sql_query($query_certificate);
		while(list($id, $available_for_status, $minutes_required) = sql_fetch_row($re_certificate)) {

			$cert[$id] = array("available_for_status"=>$available_for_status, "minutes_required"=>$minutes_required);
		}
		return $cert;
	}

	function getCourseExCertificate($id_course) {

		$cert = array();
		$query_certificate = "
		SELECT id_certificate, available_for_status
		FROM %lms_certificate_course
		WHERE id_course = '".$id_course."' "
		." AND point_required > 0";
		$re_certificate = sql_query($query_certificate);
		while(list($id, $available_for_status) = sql_fetch_row($re_certificate)) {

			$cert[$id] = $available_for_status;
		}
		return $cert;
	}

	function getPointRequiredForCourse($id_course)
	{
		$query =	"SELECT MAX(point_required)"
					." FROM %lms_certificate_course"
					." WHERE id_course = ".$id_course;

		list($res) = sql_fetch_row(sql_query($query));

		if($res == NULL)
			$res = '0';

		return $res;
	}

	/**
	 * @return array 	idcourse => array( idcert => array( CERT_ID, CERT_NAME, CERT_DESCR, CERT_LANG, CERT_STRUCTURE, CERT_ID_COURSE, CERT_AV_STATUS ) )
	 */
	function certificateForCourses($arr_course = false, $base_language = false) {

		$query_certificate = ""
		." SELECT c.id_certificate, c.name, c.description, c.base_language, course.id_course, course.available_for_status, course.point_required "
		." FROM ".$GLOBALS['prefix_lms']."_certificate AS c "
		." 		JOIN ".$GLOBALS['prefix_lms']."_certificate_course AS course"
		." WHERE c.id_certificate = course.id_certificate "
		." 		AND course.available_for_status <> '".AVS_NOT_ASSIGNED."' "
		." AND c.user_release = 1";

		if($arr_course !== false && !empty($arr_course))
			$query_certificate .= " AND course.id_course IN ( ".implode(',', $arr_course)." )";

		if($base_language !== false)
			$query_certificate .= " AND c.base_language = '".$base_language."' ";

		$query_certificate .= " ORDER BY course.available_for_status, c.name";

		$re = sql_query($query_certificate);
		if(!$re) return array();

		$list_of = array();
		while($row = sql_fetch_row($re)) {

			$list_of[$row[CERT_ID_COURSE]][$row[CERT_ID]] = $row;
		}
		return $list_of;
	}

	function numberOfCertificateReleased($id_certificate = false) {

		$query_certificate = "
		SELECT id_certificate, COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_certificate_assign
		WHERE 1 ";
		if($id_certificate !== false) $query_certificate .= " AND id_certificate = '".$id_certificate."' ";

		$re = sql_query($query_certificate);
		if(!$re) return array();

		$list_of = array();
		while(list($id_c, $number) = sql_fetch_row($re)) {
			$list_of[$id_c] = $number;
		}
		reset($list_of);
		if($id_certificate !== false) return current($list_of);
		return $list_of;
	}

	function certificateReleased($id_user, $arr_course = false) {

		$query_certificate = "
		SELECT id_course, id_certificate, on_date
		FROM ".$GLOBALS['prefix_lms']."_certificate_assign
		WHERE id_user = '".$id_user."' ";
		if($arr_course) {
			$query_certificate .= " AND id_course IN ( ".implode(',', $arr_course)."";
		}
		$re = sql_query($query_certificate);
		if(!$re) return array();

		$list_of = array();
		while(list($id_course, $id_cert, $on_date) = sql_fetch_row($re)) {

			$list_of[$id_course][$id_cert] = $on_date;
		}
		return $list_of;
	}

	function certificateStatus($id_user, $id_course) {

		$query_certificate = "
		SELECT ca.id_course, c.id_certificate, c.name
		FROM ".$GLOBALS['prefix_lms']."_certificate AS c
			JOIN ".$GLOBALS['prefix_lms']."_certificate_assign AS ca
			ON (c.id_certificate = ca.id_certificate)
		WHERE ca.id_user = ".(int)$id_user." AND ca.id_course = ".(int)$id_course." ";

		$re = sql_query($query_certificate);
		if(!$re) return array();

		$list_of = array();
		while(list($id_course, $id_cert, $name) = sql_fetch_row($re)) {

			$list_of[$id_cert] = $name;
		}
		return $list_of;
	}

	function certificateReleasedMultiUser($arr_user = false, $arr_course = false) {

		$query_certificate = "
		SELECT id_user, id_certificate, id_course, on_date
		FROM ".$GLOBALS['prefix_lms']."_certificate_assign
		WHERE 1 ";
		if(is_array($arr_user) && !empty($arr_user)) {
			$query_certificate .= " AND id_user IN ( ".implode(',', $arr_user)."";
		}
		if(is_array($arr_course) && !empty($arr_course)) {
			$query_certificate .= " AND id_course IN ( ".implode(',', $arr_course)."";
		}
		$re = sql_query($query_certificate);
		if(!$re) return array();

		$list_of = array();
		while(list($id_user, $id_course, $id_cert, $on_date) = sql_fetch_row($re)) {

			$list_of[$id_user][$id_cert]['on_date'] = $on_date;
			$list_of[$id_user][$id_cert]['id_course'] = $id_course;
		}
		return $list_of;
	}

	function numOfCertificateReleasedForCourse($id_course) {

		$query_certificate = "
		SELECT id_certificate, COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_certificate_assign
		WHERE id_course = '".$id_course."'
		GROUP BY id_certificate ";
		$re = sql_query($query_certificate);
		if(!$re) return array();

		$list_of = array();
		while(list($id_cert, $num_of) = sql_fetch_row($re)) {
			$list_of[$id_cert] = $num_of;
		}
		return $list_of;
	}

	function certificateReleasedForCourse($id_course) {

		$query_certificate = "
		SELECT id_user, id_certificate, on_date
		FROM ".$GLOBALS['prefix_lms']."_certificate_assign
		WHERE id_course = '".$id_course."' ";
		$re = sql_query($query_certificate);
		if(!$re) return array();

		$list_of = array();
		while(list($id_user, $id_cert, $on_date) = sql_fetch_row($re)) {
			$list_of[$id_user][$id_cert] = $on_date;
		}
		return $list_of;
	}

	function isReleased($id_certificate, $id_course, $id_user) {

		$query_certificate = "
		SELECT cert_file
		FROM ".$GLOBALS['prefix_lms']."_certificate_assign
		WHERE id_certificate = '".$id_certificate."'
			 AND id_course = '".$id_course."'
			 AND id_user = '".$id_user."' ";

		$re = sql_query($query_certificate);
		if(!$re) return false;
		return (sql_num_rows($re) > 0);
	}

	function canRelease($av_for_status, $user_status) {

		require_once(_lms_.'/lib/lib.course.php');

		switch($av_for_status) {
			case AVS_NOT_ASSIGNED 				: { return false; };break;
			case AVS_ASSIGN_FOR_ALL_STATUS 		: { return true; };break;
			case AVS_ASSIGN_FOR_STATUS_INCOURSE : { return ($user_status == _CUS_BEGIN); };break;
			case AVS_ASSIGN_FOR_STATUS_COMPLETED : { return ($user_status == _CUS_END); };break;
		}
		return false;
	}

	function updateCertificateCourseAssign($id_course, $list_of_assign, $list_of_assign_ex, $point_required, $minutes_required)
	{
		$query =	"DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_course"
					." WHERE id_course = ".$id_course;

		if(!sql_query($query))
			return false;

		if(is_array($list_of_assign) && !empty($list_of_assign))
			foreach($list_of_assign as $id_cert => $status)
				if($status != 0)
				{
                    $minutes = $minutes_required[$id_cert];
					$query =	"INSERT INTO ". $GLOBALS['prefix_lms'] . "_certificate_course"
								." (id_certificate, id_course, available_for_status, minutes_required)"
								." VALUES (".(int)$id_cert.", ".(int)$id_course.", ".(int)$status.", ".(int)$minutes.")";

					if(!sql_query($query))
						return false;
                                        
				}

		if(is_array($list_of_assign_ex) && !empty($list_of_assign_ex) && $point_required > 0)
			foreach($list_of_assign_ex as $id_cert => $status)
				if($status != 0)
				{
					$query =	"INSERT INTO ". $GLOBALS['prefix_lms'] . "_certificate_course"
                        ." (id_certificate, id_course, available_for_status, point_required)"
                        ." VALUES (".(int)$id_cert.", ".(int)$id_course.", ".(int)$status.", ".(int)$point_required.")";

					if(!sql_query($query))
						return false;
				}

		return true;
	}

	function getSubstitutionArray($id_user, $id_course, $id_meta = 0) {

		$query_certificate = "
		SELECT file_name, class_name
		FROM ".$GLOBALS['prefix_lms']."_certificate_tags ";
		$re = sql_query($query_certificate);

		$subst = array();
		while(list($file_name, $class_name) = sql_fetch_row($re)) {

			if(file_exists(_lms_.'/lib/certificate/'.$file_name)) {

				require_once(_lms_.'/lib/certificate/'.$file_name);
				$instance = new $class_name($id_user, $id_course, $id_meta);
				$this_subs = $instance->getSubstitution();
				$subst = $subst + $this_subs;
			}
		}
		return $subst;
	}

	function send_preview_certificate($id_certificate, $array_substituton = false) {

		$query_certificate = "
		SELECT name, cert_structure, base_language, orientation, bgimage
		FROM ".$GLOBALS['prefix_lms']."_certificate
		WHERE id_certificate = '".$id_certificate."'";
		list($name, $cert_structure, $base_language, $orientation, $bgimage) = sql_fetch_row(sql_query($query_certificate));

		//require_once($GLOBALS['where_framework'].'/addons/html2pdf/html2fpdf.php');

		if($array_substituton !== false) {
			$cert_structure = str_replace(array_keys($array_substituton), $array_substituton, $cert_structure);
		}
		$cert_structure = fillSiteBaseUrlTag($cert_structure);

		$name = str_replace(
			array('\\', '/', 	':', 	'\'', 	'\*', 	'?', 	'"', 	'<', 	'>', 	'|'),
			array('', 	'', 	'', 	'', 	'', 	'', 	'', 	'', 	'', 	'' ),
			$name
		);
		
		$this->getPdf($cert_structure, $name, $bgimage, $orientation, true, false);
	}

	function getPdf($html, $name, $img = false, $orientation = 'P', $download = true, $facs_simile = false, $for_saving = false)
	{
		require_once(Docebo::inc(_base_.'/lib/pdf/lib.pdf.php'));

		$pdf = new PDF($orientation);
		$pdf->setEncrypted(Get::cfg('certificate_encryption', true));
		$pdf->setPassword(Get::cfg('certificate_password', null));

		if($for_saving)
			return $pdf->getPdf($html, $name, $img, $download, $facs_simile, $for_saving);
		else
			$pdf->getPdf($html, $name, $img, $download, $facs_simile, $for_saving);
	}

	function send_facsimile_certificate($id_certificate, $id_user, $id_course, $array_substituton = false)
	{
		$query_certificate = "
		SELECT name, cert_structure, base_language, orientation, bgimage
		FROM ".$GLOBALS['prefix_lms']."_certificate
		WHERE id_certificate = '".$id_certificate."'";
		list($name, $cert_structure, $base_language, $orientation, $bgimage) = sql_fetch_row(sql_query($query_certificate));

		if($array_substituton !== false) {
			$cert_structure = str_replace(array_keys($array_substituton), $array_substituton, $cert_structure);
		}
		$cert_structure = fillSiteBaseUrlTag($cert_structure);

		$this->getPdf($cert_structure, $name, $bgimage, $orientation, true, true);
	}

	function send_certificate($id_certificate, $id_user, $id_course, $array_substituton = false, $download = true, $from_multi = false)
	{
		$id_meta = Get::req('id_meta', DOTY_INT, 0);

		if(!isset($_GET['id_meta']))
			$query_certificate = "
			SELECT cert_file
			FROM ".$GLOBALS['prefix_lms']."_certificate_assign
			WHERE id_certificate = '".$id_certificate."'
				 AND id_course = '".$id_course."'
				 AND id_user = '".$id_user."' ";
		else
			$query_certificate = "
			SELECT cert_file
			FROM ".$GLOBALS['prefix_lms']."_certificate_meta_assign
			WHERE idUser = '".$id_user."'
			AND idMetaCertificate = '".$id_meta."'";

		$re = sql_query($query_certificate);
		echo sql_error();
		if((sql_num_rows($re) > 0)) {
			if(!$download)
				return;
			require_once(_base_.'/lib/lib.download.php' );
			list($cert_file) = sql_fetch_row($re);
			sendFile(CERTIFICATE_PATH, $cert_file);
			return;
		}

		$query_certificate = "
		SELECT name, cert_structure, base_language, orientation, bgimage
		FROM ".$GLOBALS['prefix_lms']."_certificate
		WHERE id_certificate = '".$id_certificate."'";
		list($name, $cert_structure, $base_language, $orientation, $bgimage) = sql_fetch_row(sql_query($query_certificate));

		require_once(_base_.'/lib/lib.upload.php');

		if($array_substituton !== false) {
			$cert_structure = str_replace(array_keys($array_substituton), $array_substituton, $cert_structure);
		}
		$cert_structure = fillSiteBaseUrlTag($cert_structure);

		$cert_file = $id_course.'_'.$id_certificate.'_'.$id_user.'_'.time().'_'.$name.'.pdf';

		sl_open_fileoperations();
		if(!$fp = sl_fopen(CERTIFICATE_PATH.$cert_file, 'w')) { sl_close_fileoperations(); return false; }
		if(!fwrite($fp, $this->getPdf($cert_structure, $name, $bgimage, $orientation, false, false, true))) { sl_close_fileoperations(); return false; }
		fclose($fp);
		sl_close_fileoperations();

		//save the generated file in database
		if(!isset($_GET['id_meta']))
			$query = "INSERT INTO ".$GLOBALS['prefix_lms']."_certificate_assign "
			." ( id_certificate, id_course, id_user, on_date, cert_file ) "
			." VALUES "
			." ( '".$id_certificate."', '".$id_course."', '".$id_user."', '".date("Y-m-d H:i:s")."', '".addslashes($cert_file)."' ) ";
		else
			$query = "INSERT INTO ".$GLOBALS['prefix_lms']."_certificate_meta_assign "
			." ( idUser, idMetaCertificate, idCertificate, on_date, cert_file ) "
			." VALUES "
			." ('".$id_user."', '".$id_meta."', '".$id_certificate."', '".date("Y-m-d H:i:s")."', '".addslashes($cert_file)."' ) ";

		if(!sql_query($query)) return false;

		if($from_multi)
			return;

		$this->getPdf($cert_structure, $name, $bgimage, $orientation, $download, false);
	}

	function getCourseForCertificate($id_certificate)
	{
		$id_course = array();

		$query_id_course = "SELECT cc.id_course" .
						" FROM %lms_certificate_course AS cc JOIN %lms_course AS c " .
						" ON (cc.id_course = c.idCourse) ".
						" WHERE cc.id_certificate = '".$id_certificate."' " .
						" AND cc.available_for_status <> '".AVS_NOT_ASSIGNED."'";

		$result_id_course = sql_query($query_id_course);

		while (list($id_course_find) = sql_fetch_row($result_id_course))
			$id_course[] = $id_course_find;

		return $id_course;
	}

	function getInfoForCourseCertificate($id_course, $id_certificate, $id_user = false)
	{
		$info = array();

		$query = "SELECT *" .
				" FROM ".$GLOBALS['prefix_lms']."_certificate_assign" .
				" WHERE id_certificate = '".$id_certificate."'" .
				" AND id_course = '".$id_course."'";
		if ($id_user)
			$query .= " AND id_user = $id_user";

		$result = sql_query($query);

		while ($row = sql_fetch_row($result))
			$info[] = $row;

		return $info;
	}

	function getCertificateInfo($id_certificate)
	{
		$info = array();
		$query = "SELECT id_certificate, name, description, base_language, cert_structure FROM ".$GLOBALS['prefix_lms']."_certificate ";
		if (is_array($id_certificate) && count($id_certificate)>0) {
			$query .= " WHERE id_certificate IN ('".implode("','", $id_certificate)."')";
		} else {
			$query .= " WHERE id_certificate = '".(int)$id_certificate."'";
		}
		$result = sql_query($query);

		while ($row = sql_fetch_row($result))
			$info[$row[CERT_ID]] = $row;

		return $info;
	}

	function delCertificateForUserInCourse($id_certificate, $id_user, $id_course)
	{
		$query = "DELETE " .
				" FROM ".$GLOBALS['prefix_lms']."_certificate_assign " .
				" WHERE id_certificate = '".$id_certificate."'" .
				" AND id_course = '".$id_course."'" .
				" AND id_user = '".$id_user."'";

		return sql_query($query);
	}

	function getNumberOfCertificateForCourse($id_certificate, $id_course)
	{
		$query = "SELECT COUNT(*)" .
				" FROM ".$GLOBALS['prefix_lms']."_certificate_assign" .
				" WHERE id_certificate = '".$id_certificate."'" .
				" AND id_course = '".$id_course."'";

		list ($res) = sql_fetch_row(sql_query($query));

		return $res;
	}


	function deleteCourseCertificateAssignments($id_course) {
		if ((int)$id_course <= 0) return false;

		$query = "DELETE FROM %lms_certificate_course WHERE id_course = ".(int)$id_course;
		$res = sql_query($query);

		return $res ? true : false;
	}





	function getCertificateQuery($users = false, $id_cert = false, $year = false) {
		$conditions = array();

		if ($users) {
			if (is_numeric($users)) $users = array($users);
			if (is_array($users)) $conditions[] = " t3.idst IN (".implode(',', $users).") ";
		}

		if ($id_cert) {
			if (is_numeric($id_cert)) $conditions[] = " t1.id_certificate = '".(int)$id_cert."' ";
		}

		if ($year) {
			if (is_numeric($year)) $conditions[] = " YEAR(t2.ondate) = '".(int)$year."' ";
		}

		$query = "SELECT t1.code, t1.name, YEAR(t2.on_date) as year, t3.firstname, t3.lastname "
			." FROM ".$GLOBALS['prefix_lms']."_certificate as t1 JOIN ".$GLOBALS['prefix_lms']."_certificate_assign as t2 JOIN ".$GLOBALS['prefix_fw']."_user as t3 "
			." ON (t1.id_certificate = t2.id_certificate AND t2.id_user = t3.idst)  "
			.(count($conditions)>0 ? "WHERE ".implode(" AND ", $conditions) : "")
			." ORDER BY t3.lastname, t3.firstname, t1.name";

		return $query;
	}


	function getCertificateQueryTotal($users = false, $id_cert = false, $year = false) {
		$conditions = array();

		if ($users) {
			if (is_numeric($users)) $users = array($users);
			if (is_array($users)) $conditions[] = " t3.idst IN (".implode(',', $users).") ";
		}

		if ($id_cert) {
			if (is_numeric($id_cert)) $conditions[] = " t1.id_certificate = '".(int)$id_cert."' ";
		}

		if ($year) {
			if (is_numeric($year)) $conditions[] = " YEAR(t2.ondate) = '".(int)$year."' ";
		}

		$query = "SELECT COUNT(*) "
			." FROM ".$GLOBALS['prefix_lms']."_certificate as t1 JOIN ".$GLOBALS['prefix_lms']."_certificate_assign as t2 JOIN ".$GLOBALS['prefix_fw']."_user as t3 "
			." ON (t1.id_certificate = t2.id_certificate AND t2.id_user = t3.idst)  "
			.(count($conditions)>0 ? "WHERE ".implode(" AND ", $conditions) : "");

		list($total) = sql_fetch_row(sql_query($query)); echo sql_error();
		return $total;
	}

}

function getCertificateQuery($users = false, $id_cert = false, $year = false) {
		$conditions = array();

		if ($users) {
			if (is_numeric($users)) $users = array($users);
			if (is_array($users)) $conditions[] = " t3.idst IN (".implode(',', $users).") ";
		}

		if ($id_cert) {
			if (is_numeric($id_cert)) $conditions[] = " t1.id_certificate = '".(int)$id_cert."' ";
		}

		if ($year) {
			if (is_numeric($year)) $conditions[] = " YEAR(t2.ondate) = '".(int)$year."' ";
		}

		$query = "SELECT t1.code, t1.name, YEAR(t2.on_date) as year, t3.firstname, t3.lastname "
			." FROM ".$GLOBALS['prefix_lms']."_certificate as t1 JOIN ".$GLOBALS['prefix_lms']."_certificate_assign as t2 JOIN ".$GLOBALS['prefix_fw']."_user as t3 "
			." ON (t1.id_certificate = t2.id_certificate AND t2.id_user = t3.idst)  "
			.(count($conditions)>0 ? "WHERE ".implode(" AND ", $conditions) : "")
			." ORDER BY t3.lastname, t3.firstname, t1.name";

		return $query;
	}


	function getCertificateQueryTotal($users = false, $id_cert = false, $year = false) {
		$conditions = array();

		if ($users) {
			if (is_numeric($users)) $users = array($users);
			if (is_array($users)) $conditions[] = " t3.idst IN (".implode(',', $users).") ";
		}

		if ($id_cert) {
			if (is_numeric($id_cert)) $conditions[] = " t1.id_certificate = '".(int)$id_cert."' ";
		}

		if ($year) {
			if (is_numeric($year)) $conditions[] = " YEAR(t2.ondate) = '".(int)$year."' ";
		}

		$query = "SELECT COUNT(*) "
			." FROM ".$GLOBALS['prefix_lms']."_certificate as t1 JOIN ".$GLOBALS['prefix_lms']."_certificate_assign as t2 JOIN ".$GLOBALS['prefix_fw']."_user as t3 "
			." ON (t1.id_certificate = t2.id_certificate AND t2.id_user = t3.idst)  "
			.(count($conditions)>0 ? "WHERE ".implode(" AND ", $conditions) : "");

		list($total) = sql_fetch_row(sql_query($query));
		return $total;
	}



?>