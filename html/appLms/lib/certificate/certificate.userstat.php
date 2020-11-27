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

require_once(dirname(__FILE__).'/certificate.base.php');

class CertificateSubs_UserStat extends CertificateSubstitution {

	function getSubstitutionTags() {

		$lang =& DoceboLanguage::createInstance('certificate', 'lms');

		$subs = array();
		if($this->id_meta != 0)
		{
			$subs['[table_blended]'] = $lang->def('_TABLE_BLENDED');
			$subs['[table_course]'] = $lang->def('_TABLE_COURSE');
			$subs['[meta_complete]'] = $lang->def('_META_COMPLETE');
			$subs['[meta_inscr]'] = $lang->def('_META_INSCR');
			$subs['[meta_access]'] = $lang->def('_META_ACCESS');
                        //$subs['[meta_level]'] = $lang->def('_LEVEL');
		}
		else
		{
        $subs['[user_level]'] 			= $lang->def('_LEVEL');
			$subs['[date_enroll]'] 			= $lang->def('_DATE_ENROLL');
			$subs['[date_first_access]'] 	= $lang->def('_DATE_FIRST_ACCESS');
			$subs['[date_complete]'] 		= $lang->def('_DATE_COMPLETE');
			$subs['[date_complete_year]'] 		= $lang->def('_DATE_COMPLETE');
			$subs['[total_time]'] 			= $lang->def('_TOTAL_TIME');
			$subs['[total_time_hour]'] 		= $lang->def('_TOTAL_TIME_HOUR');
			$subs['[total_time_minute]'] 	= $lang->def('_TOTAL_TIME_MINUTE');
			$subs['[total_time_second]'] 	= $lang->def('_TOTAL_SECONDS');
			$subs['[test_score_start]'] 	= $lang->def('_TEST_SCORE_START');
			$subs['[test_score_start_max]'] = $lang->def('_TEST_SCORE_START_MAX');
			$subs['[test_score_final]'] 	= $lang->def('_TEST_SCORE_FINAL');
			$subs['[test_score_final_max]'] = $lang->def('_TEST_SCORE_FINAL_MAX');
			$subs['[course_score_final]'] 	= $lang->def('_FINAL_SCORE');
			$subs['[course_score_final_max]'] = $lang->def('_COURSE_SCORE_FINAL_MAX');
			$subs['[meta_assoc]'] = $lang->def('_META_ASSOC');
            
		}
		return $subs;
	}

	function getSubstitution() {

		$subs = array();

		$lang =& DoceboLanguage::createInstance('course', 'lms');
		$lang =& DoceboLanguage::createInstance('certificate', 'lms');
                $lang =& DoceboLanguage::createInstance('levels', 'lms');
		if($this->id_meta != 0)
		{
			require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
			require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
			require_once($GLOBALS['where_lms'].'/lib/lib.aggregated_certificate.php');

			$acl_man =& $GLOBALS['current_user']->getAclManager();

			$aggCertLib = new AggregatedCertificate();

			$courses = array();

			$array_coursetype = array(	'elearning' => $lang->def('_COURSE_TYPE_ELEARNING', 'course', 'lms'),
										'classroom' => $lang->def('_CLASSROOM', 'course', 'lms'),
										'web_seminar' => $lang->def('Web seminar'));

			$course_time = 0;
			$blended_time = 0;
			$array_meta_complete = array();
			$array_meta_inscr = array();
			$array_meta_access = array();
            
            $assocType = $aggCertLib->getTypeAssoc($this->id_meta, $this->id_user);
            
            if ($assocType == COURSE_PATH) {
                $table_blended =    '<table width="100%" cellspacing="1" cellpadding="1" border="1" align="">'
                                .'<thead>'
                                .'<tr>'
                                .'<td>'.$lang->def('_COURSEPATH').'</td>'
                                .'<td>'.$lang->def('_TOTAL_SESSION', 'report').'</td>'
                                .'</tr>'                                
                                .'</thead>'
                                .'<tbody>';
                
                $path = $aggCertLib->getIdsCoursePath($this->id_meta, $this->id_user);
                require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
                $coursePath_man = new CoursePath_Manager();
                foreach ($path as $id_path){
                    $courses = $coursePath_man->getAllCourses($id_path);
                    $courses_path_time = 0;
                    foreach ($courses as $id_course){
                        $query =    "SELECT date_complete, date_inscr, date_first_access "
                                ." FROM ".$GLOBALS['prefix_lms']."_courseuser"
                                ." WHERE idCourse = '".$id_course."'"
                                ." AND idUser = '".$this->id_user."'";

                        list($date_complete_meta, $date_inscr_meta, $date_access_meta) = sql_fetch_row(sql_query($query));

                        $array_meta_complete[] = $date_complete_meta;
                        $array_meta_inscr[] = $date_inscr_meta;
                        $array_meta_access[] = $date_access_meta;

                        $man_course = new Man_Course();

                        $course_info = $man_course->getCourseInfo($id_course);

                        $rep_man = new CourseReportManager();

                        $score_course = $rep_man->getUserFinalScore(array($this->id_user), array($this->id_course));
                        $courses_path_time += $course_info['mediumTime'];
                        $course_time += $course_info['mediumTime'];
                    }
                    $info_path = $coursePath_man->getCoursepathInfo($id_path);
                    $table_blended .= '<tr>'
                                      .'<td>'.$info_path['path_name'].'</td>'
                                      .'<td>'.$courses_path_time.'</td>'
                                      .'</tr>';
                                      
                }
                $table_blended .=    '<tr>'
                                .'<td>'.$lang->def('_TOTAL_TIME').'</td>'
                                .'<td>'.$course_time.'</td>'
                                .'</tr>'
                                .'</tbody>'
                                .'</table>';
                $subs['[table_blended]'] = $table_blended;
                $subs['[table_course]'] = $subs['[table_blended]'];
                
            } 
            if ($assocType == COURSE){
                $table_course =     '<table width="100%" cellspacing="1" cellpadding="1" border="1" align="">'
                                .'<thead>'
                                .'<tr>'
                                .'<td>'.$lang->def('_COURSE_NAME').'</td>'
                                .'<td>'.$lang->def('_COURSE_TYPE', 'course').'</td>'
                                .'<td>'.$lang->def('_TOTAL_SESSION', 'report').'</td>'
                                .'</tr>'
                                .'</thead>'
                                .'<tbody>';
                $courses = $aggCertLib->getIdsCourse($this->id_meta, $this->id_user);
                foreach ($courses as $id_course) {
                    $query =    "SELECT date_complete, date_inscr, date_first_access, level"
                            ." FROM ".$GLOBALS['prefix_lms']."_courseuser"
                            ." WHERE idCourse = '".$id_course."'"
                            ." AND idUser = '".$this->id_user."'";

                    list($date_complete_meta, $date_inscr_meta, $date_access_meta, $level) = sql_fetch_row(sql_query($query));

                    $array_meta_complete[] = $date_complete_meta;
                    $array_meta_inscr[] = $date_inscr_meta;
                    $array_meta_access[] = $date_access_meta;
                                    //$array_meta_level[] = $level;

                    $man_course = new Man_Course();

                    $course_info = $man_course->getCourseInfo($id_course);

                    $rep_man = new CourseReportManager();

                    $score_course = $rep_man->getUserFinalScore(array($this->id_user), array($this->id_course));
                    $table_course .=    '<tr>'
                                        .'<td>'.$course_info['name'].'</td>'
                                        .'<td>'.$array_coursetype[$course_info['course_type']].'</td>'
                                        .'<td align="right">'.$course_info['mediumTime'].'</td>'
                                        .'</tr>';
                    $course_time += $course_info['mediumTime'];
                }
                $table_course .= '<tr>'
                                .'<td align="right" colspan="2">'.$lang->def('_TOTAL_TIME').'</td>'
                                .'<td align="right">'.$course_time.'</td>'
                                .'</tr>'
                                .'</tbody>'
                                .'</table>'; 
                $subs['[table_course]'] = $table_course; 
                $subs['[table_blended]'] = $subs['[table_course]']; 
            }
			rsort($array_meta_complete);
			sort($array_meta_inscr);
			sort($array_meta_access);

			$subs['[meta_complete]'] = $array_meta_complete[0];
			$subs['[meta_inscr]'] = $array_meta_inscr[0];
			$subs['[meta_access]'] = $array_meta_access[0];

			$sql = "
				SELECT title FROM ".$aggCertLib->table_cert_meta_association ." AS cm 
				WHERE cm.idAssociation = {$this->id_meta}";
			$q = sql_query($sql);
			$meta = sql_fetch_object($q);
			$subs['[meta_assoc]'] = $meta->title;
		}
		else
		{
			require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

			$courseuser = new Man_CourseUser();
			$course_stat =& $courseuser->getUserCourses($this->id_user, false, false, false, array($this->id_course));

			if(isset($course_stat[$this->id_course])) {

				$subs['[date_enroll]'] = Format::date($course_stat[$this->id_course]['date_inscr'], 'date');
				$subs['[date_first_access]'] = Format::date($course_stat[$this->id_course]['date_first_access'], 'date');
				$subs['[date_complete]'] = Format::date($course_stat[$this->id_course]['date_complete'], 'date');
				$subs['[date_complete_year]'] = substr($course_stat[$this->id_course]['date_complete'], 0, 4);
                                
                                $subs['[user_level]'] = $lang->def('_LEVEL_'.$course_stat[$this->id_course]['level'],'levels');
			} else {

				$subs['[date_enroll]'] = '';
				$subs['[date_first_access]'] = '';
				$subs['[date_complete]'] = '';
				$subs['[date_complete_year]'] = '';
                                $subs['[user_level]'] = '';
			}

			require_once($GLOBALS['where_lms'].'/lib/lib.orgchart.php');
			$org_man = new OrganizationManagement($this->id_course);

			$score_start = $org_man->getStartObjectScore(array($this->id_user), array($this->id_course));
			$score_final = $org_man->getFinalObjectScore(array($this->id_user), array($this->id_course));


			require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
			$rep_man = new CourseReportManager();

			$score_course = $rep_man->getUserFinalScore(array($this->id_user), array($this->id_course));


			$subs['[test_score_start]'] = ( isset($score_start[$this->id_course][$this->id_user]) ? $score_start[$this->id_course][$this->id_user]['score'] : '' );
			$subs['[test_score_start_max]'] = ( isset($score_start[$this->id_course][$this->id_user]) ? $score_start[$this->id_course][$this->id_user]['max_score'] : '' );
			$subs['[test_score_final]'] = ( isset($score_final[$this->id_course][$this->id_user]) ? $score_final[$this->id_course][$this->id_user]['score'] : '' );
			$subs['[test_score_final_max]'] = ( !empty($score_final[$this->id_course][$this->id_user]['max_score'] )
				? $score_final[$this->id_course][$this->id_user]['max_score']
				: '100' );

			$subs['[course_score_final]'] 	= ( isset($score_course[$this->id_user][$this->id_course]) ? $score_course[$this->id_user][$this->id_course]['score'] : '' );
			$subs['[course_score_final_max]'] = ( isset($score_course[$this->id_user][$this->id_course]) ? $score_course[$this->id_user][$this->id_course]['max_score'] : '' );

			require_once($GLOBALS['where_lms'].'/lib/lib.track_user.php');
			$time_in = TrackUser::getUserTotalCourseTime($this->id_user, $this->id_course);

			$hours = (int)($time_in/3600);
			$minutes = (int)(($time_in%3600)/60);
			$seconds = (int)($time_in%60);
			if($minutes < 10) $minutes = '0'.$minutes;
			if($seconds < 10) $seconds = '0'.$seconds;

			$subs['[total_time]'] 		= $hours.'h '.$minutes.'m '.$seconds.'s';
			$subs['[total_time_hour]'] 	= $hours;
			$subs['[total_time_minute]'] 	= $minutes;
			$subs['[total_time_second]'] 	= $seconds;
		}

		return $subs;
	}

}
