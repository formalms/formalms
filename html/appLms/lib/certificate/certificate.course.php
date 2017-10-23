<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

require_once(dirname(__FILE__).'/certificate.base.php');

class CertificateSubs_Course extends CertificateSubstitution {

	function getSubstitutionTags() {

		$lang =& DoceboLanguage::createInstance('certificate', 'lms');

		$subs = array();
		if($this->id_meta != 0)
		{

		}
		else
		{
			$subs['[course_code]'] 			= $lang->def('_COURSE_CODE');
			$subs['[course_name]'] 			= $lang->def('_COURSE_NAME');
			$subs['[course_description]'] 	= $lang->def('_COURSE_DESCRIPTION');
            $subs['[cert_number]'] 			= $lang->def('_COURSE_FILE_NUMBER');
			$subs['[date_begin]'] 			= $lang->def('_COURSE_BEGIN');
			$subs['[date_end]'] 			= $lang->def('_COURSE_END');
			$subs['[medium_time]'] 			= $lang->def('_COURSE_MEDIUM_TIME');
            
			$subs['[ed_date_begin]']		= $lang->def('_ED_DATE_BEGIN');
			$subs['[ed_classroom]'] 		= $lang->def('_ED_CLASSROOM');

			$subs['[cl_date_begin]']		= $lang->def('_DATE_BEGIN');
			$subs['[cl_date_end]']			= $lang->def('_DATE_END');
			$subs['[cl_classroom]'] 		= $lang->def('_CLASSROOM');

			$subs['[teacher_list]'] 		= $lang->def('_TEACHER_LIST');
			$subs['[teacher_list_inverse]'] 	= $lang->def('_TEACHER_LIST_INVERSE');
			$subs['[course_credits]']		= $lang->def('_CREDITS');
		}

		return $subs;
	}

	function getUserNameInv($idst_user = false, $user_id = false) {

		$acl_manager =& Docebo::user()->getAclManager();
		$user_info = $acl_manager->getUser($idst_user, $user_id);

		return ( $user_info[ACL_INFO_LASTNAME].$user_info[ACL_INFO_FIRSTNAME]
			? $user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME]
			: $acl_manager->relativeId($user_info[ACL_INFO_USERID]) );
	}

	/**
	 * return the list of substitution
	 */
	function getSubstitution() {

		$subs = array();

		if($this->id_meta != 0)
		{

		}
		else
		{
			require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

			$acl_manager =& Docebo::user()->getAclManager();
			$man_course = new DoceboCourse($this->id_course);

			$query =	"SELECT idUser"
						." FROM ".$GLOBALS['prefix_lms']."_courseuser"
						." WHERE idCourse = '".$this->id_course."'"
						." AND level = '6'";
			$result = sql_query($query);

			$first = true;

			while(list($id_user) = sql_fetch_row($result)) {
				if($first) {
					$subs['[teacher_list]'] = ''.$acl_manager->getUserName($id_user, false);
					$subs['[teacher_list_inverse]'] = ''.$this->getUserNameInv($id_user, false);
					$first = false;
				} else {
					$subs['[teacher_list]'] .= ', '.$acl_manager->getUserName($id_user, false);
					$subs['[teacher_list_inverse]'] .= ', '.$this->getUserNameInv($id_user, false);
				}
			}

			$subs['[course_code]'] 			= $man_course->getValue('code');
			$subs['[course_name]'] 			= $man_course->getValue('name');
            $subs['[cert_number]'] 			= $this->id_user.'-'.time();
			$subs['[date_begin]'] 			= Format::date($man_course->getValue('date_begin'), 'date');
			$subs['[date_end]'] 			= Format::date($man_course->getValue('date_end'), 'date');

			$subs['[course_description]'] 	= html_entity_decode(strip_tags($man_course->getValue('description')), ENT_QUOTES, "UTF-8");

			$subs['[medium_time]'] 			= $man_course->getValue('mediumTime');
			$subs['[course_credits]']		= $man_course->getValue('credits');

			$subs['[ed_date_begin]'] = '';
			$subs['[ed_classroom]'] =  ''; 

			$subs['[cl_date_begin]'] = '';
			$subs['[cl_date_end]'] = '';
			$subs['[cl_classroom]'] = '';

			if( $man_course->getValue('course_edition') == 1) {

                
                $query = "SELECT date_begin " 
                         ."FROM ".$GLOBALS['prefix_lms']."_course_editions INNER JOIN ".$GLOBALS['prefix_lms']."_course_editions_user ON "
                          .$GLOBALS['prefix_lms']."_course_editions_user.id_edition = ".$GLOBALS['prefix_lms']."_course_editions.id_edition "
                         ."where ".$GLOBALS['prefix_lms']."_course_editions .id_course = ".$this->id_course." and ".$GLOBALS['prefix_lms']."_course_editions_user.id_user = ".$this->id_user;
                $result = sql_query($query);
                         
                
				if(sql_num_rows($result) > 0) {  
                    list($date_begin) = sql_fetch_row($result);
					$subs['[ed_date_begin]'] = Format::date($date_begin, 'date');
				}
			} // end session
            
            
            
			if( $man_course->getValue('course_type') == 'classroom') {

				$date_arr =array();
                
                $qtxt = "SELECT d.id_date, MIN( dd.date_begin ) AS date_begin, MAX( dd.date_end ) AS date_end, d.name
												 FROM ".$GLOBALS['prefix_lms']."_course_date_day AS dd
												 JOIN ".$GLOBALS['prefix_lms']."_course_date AS d
												 JOIN ".$GLOBALS['prefix_lms']."_classroom AS c
												 ON ( dd.classroom = c.idClassroom AND d.id_date = dd.id_date )
				              	 LEFT JOIN ".$GLOBALS['prefix_lms']."_course_date_user ON ".$GLOBALS['prefix_lms']."_course_date_user.id_date = d.id_date
                				 WHERE d.id_course = ".(int)$this->id_course."  and ".$GLOBALS['prefix_lms']."_course_date_user.id_user=".$this->id_user."
                				 GROUP BY dd.id_date";

                
                list($id_date, $subs['[cl_date_begin]'], $subs['[cl_date_end]'], $subs['[ed_classroom]']) = sql_fetch_row(sql_query($qtxt)) ;
                
                
                $qtxt = "SELECT distinct c.name AS class_name
                             FROM ".$GLOBALS['prefix_lms']."_course_date_day AS dd
                             JOIN ".$GLOBALS['prefix_lms']."_course_date AS d
                             JOIN ".$GLOBALS['prefix_lms']."_classroom AS c
                             ON ( dd.classroom = c.idClassroom AND d.id_date = dd.id_date )
                             LEFT JOIN ".$GLOBALS['prefix_lms']."_course_date_user ON ".$GLOBALS['prefix_lms']."_course_date_user.id_date = d.id_date
                             WHERE d.id_course = ".(int)$this->id_course."  and ".$GLOBALS['prefix_lms']."_course_date_user.id_user=".$this->id_user; 

                $result = sql_query($qtxt);
                $num_pv = 0;
                while(list($classroom) = sql_fetch_row($result)){
                     if ($num_pv > 0)  $subs['[cl_classroom]'] .= "; ";
                     $subs['[cl_classroom]'] .= $classroom;
                     $num_pv++;           
                }
                
                
                
         
                $subs['[course_description]']  = html_entity_decode(strip_tags($man_course->getValue('description')), ENT_QUOTES, "UTF-8");
                $subs['[cl_date_begin]'] = Format::date($subs['[cl_date_begin]'], 'date');
                $subs['[cl_date_end]'] =  Format::date($subs['[cl_date_end]'], 'date');

                
                
                
                
				$query =	"SELECT idUser"
							." FROM ".$GLOBALS['prefix_lms']."_courseuser"
							." WHERE idCourse = '".$this->id_course."'"
							." AND level = '6'"
							." AND idUser IN "
							." ("
							." SELECT id_user"
							." FROM ".$GLOBALS['prefix_lms']."_course_date_user"
							." WHERE id_date = ".$id_date
							." )";

				$result = sql_query($query);

                
				$first = true;

				while(list($id_user) = sql_fetch_row($result))
				{
					if($first)
					{
						$subs['[teacher_list]'] = ''.$acl_manager->getUserName($id_user, false);
						$subs['[teacher_list_inverse]'] = ''.$this->getUserNameInv($id_user, false);
						$first = false;
					}
					else
					{
						$subs['[teacher_list]'] .= ', '.$acl_manager->getUserName($id_user, false);
						$subs['[teacher_list_inverse]'] .= ', '.$this->getUserNameInv($id_user, false);
					}
				}
			} // end classroom

		}

		return $subs;
	}
}

?>