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

require_once(_base_.'/api/lib/lib.api.php');

class Course_API extends API {


	public function getCourses($params) {
		require_once(_lms_.'/lib/lib.course.php');
		$output =array();

		$output['success']=true;
        
        
        //die(count($params));
        

		$id_category =(isset($params['category']) ? (int)$params['category'] : false);

		$course_man =new Man_Course();
		$course_list =$course_man->getAllCoursesWithMoreInfo($id_category);


		foreach($course_list as $key=>$course_info) {
			if($category = $course_info['idCategory']) {
				$category = $course_man->getCategory($category)['path'];
			} else {
				$category = null;
            }
			$output['course_info'][]=array(
				'course_id'=>$course_info['idCourse'],
				'code'=>str_replace('&', '&amp;', $course_info['code']),
				'course_name'=>str_replace('&', '&amp;', $course_info['name']),
				'course_description'=>str_replace('&', '&amp;', $course_info['description']),
				'status'=>$course_info['status'],
				'selling'=>$course_info['selling'],
				'price'=>$course_info['prize'],
				'subscribe_method'=>$course_info['subscribe_method'],
				'course_edition'=>$course_info['course_edition'],
				'course_type'=>$course_info['course_type'],
				'can_subscribe'=>$course_info['can_subscribe'],
				'sub_start_date'=>$course_info['sub_start_date'],
				'sub_end_date'=>$course_info['sub_end_date'],
				'date_begin'=>$course_info['date_begin'],
				'date_end'=>$course_info['date_end'],
				'course_link'=>Get::site_url() . _folder_lms_ . "/index.php?modname=course&amp;op=aula&amp;idCourse={$course_info['idCourse']}",
				'img_course'=>$course_info['img_course'] ? Get::site_url() . _folder_files_ . '/' . _folder_lms_ . '/' . Get::sett('pathcourse') . $course_info['img_course'] : '',
				'category_id'=>$course_info['idCategory'],
				'category'=>$category
			);
		}

		//$output['debug']=print_r($course_list, true);

		return $output;
	}


	public function getEditions($params) {
		require_once(_lms_.'/lib/lib.course.php');
		require_once(_lms_.'/lib/lib.edition.php');
		$output =array();

		$output['success']=true;

		$course_id =(isset($params['course_id']) ? (int)$params['course_id'] : false);
		$course_code =(isset($params['course_code']) ? $params['course_code'] : false);

		if (empty($course_id) && empty($course_code)) {
			return false;
			// return array('success'=>true, 'debug'=>print_r($params, true));
		}
		else if (empty($course_id) && !empty($course_code)) { // grab course info by code:
			$db = DbConn::getInstance();
			$qtxt ="SELECT * FROM %lms_course
					WHERE code='".$course_code."'
					LIMIT 0,1";
			$q =$db->query($qtxt);
			$course_info =$db->fetch_assoc($q);
			if (!empty($course_info)) {
				$course_id =(int)$course_info['idCourse'];
			}
			else { // course not found
				return false;
				// return array('success'=>'true', 'debug'=>print_r($course_info));
			}
		}

		$edition_man = new EditionManager();
		$course_list =$edition_man->getEditionsInfoByCourses($course_id);

		$course_man =new Man_Course();
		$course =$course_man->getCourseInfo($course_id);

		foreach($course_list[$course_id] as $key=>$course_info) {
			$output[]['course_info']=array(
				'course_id'=>$course['idCourse'],
				'edition_id'=>$course_info['id_edition'],
				'code'=>str_replace('&', '&amp;', $course_info['code']),
				'course_name'=>str_replace('&', '&amp;', $course_info['name']),
				'course_description'=>str_replace('&', '&amp;', $course_info['description']),
				'status'=>$course_info['status'],
				'selling'=>$course['selling'],
				'price'=>$course_info['price'],
				'subscribe_method'=>$course['subscribe_method'],
				'sub_start_date'=>$course_info['sub_date_begin'],
				'sub_end_date'=>$course_info['sub_date_end'],
				'date_begin'=>$course_info['date_begin'],
				'date_end'=>$course_info['date_end'],
				'course_link'=>Get::site_url()._folder_lms_.'/index.php?modname=course&amp;op=aula&amp;idCourse='.$course['idCourse'],
			);
		}

		//$output['debug']=print_r($course_list, true).print_r($course, true);

		return $output;
	}


	public function getClassrooms($params) {
		require_once(_lms_.'/lib/lib.course.php');
		require_once(_lms_.'/lib/lib.date.php');
		$output =array();

		$output['success']=true;

		$course_id =(isset($params['course_id']) ? (int)$params['course_id'] : false);
		$course_code =(isset($params['course_code']) ? $params['course_code'] : false);

		if (empty($course_id) && empty($course_code)) {
			return false;
			// return array('success'=>true, 'debug'=>print_r($params, true));
		}
		else if (empty($course_id) && !empty($course_code)) { // grab course info by code:
			$db = DbConn::getInstance();
			$qtxt ="SELECT * FROM %lms_course
					WHERE code='".$course_code."'
					LIMIT 0,1";
			$q =$db->query($qtxt);
			$course_info =$db->fetch_assoc($q);
			if (!empty($course_info)) {
				$course_id =(int)$course_info['idCourse'];
			}
			else { // course not found
				return false;
				// return array('success'=>'true', 'debug'=>print_r($course_info));
			}
		}

		$classroom_man = new DateManager();
		$course_list =$classroom_man->getCourseDate($course_id);

		$course_man =new Man_Course();
		$course =$course_man->getCourseInfo($course_id);

		foreach($course_list as $key=>$course_info) {
			$output[]['course_info']=array(
				'course_id'=>$course['idCourse'],
				'date_id'=>$course_info['id_date'],
				'code'=>str_replace('&', '&amp;', $course_info['code']),
				'course_name'=>str_replace('&', '&amp;', $course_info['name']),
				'course_description'=>str_replace('&', '&amp;', $course_info['description']),
				'status'=>$course_info['status'],
				'selling'=>$course['selling'],
				'price'=>$course_info['price'],
				'subscribe_method'=>$course['subscribe_method'],
				'sub_start_date'=>$course_info['sub_start_date'],
				'sub_end_date'=>$course_info['sub_end_date'],
				'date_begin'=>$course_info['date_begin'],
				'date_end'=>$course_info['date_end'],
				'num_day'=>$course_info['num_day'],
				'classroom'=>$course_info['classroom'],
				'course_link'=>Get::site_url()._folder_lms_.'/index.php?modname=course&amp;op=aula&amp;idCourse='.$course['idCourse'],
			);
		}

		//$output['debug']=print_r($course_list, true).print_r($course, true);

		return $output;
	}


	protected function getUserLevelId($my_level) {

		if ($my_level === false) { return false; }

		$lev_arr =array(
			'administrator' => 7,
			'instructor' => 6,
			'mentor' => 5,
			'tutor' => 4,
			'student' => 3,
			'ghost' => 2,
			'guest' => 1,
		);

		return (int)$lev_arr[$my_level];
	}


	protected function getUserStatusId($my_status) {
		require_once(_lms_.'/lib/lib.subscribe.php');

		if ($my_status === false) { return false; }

		$lev_arr =array(
			'waiting_list' => _CUS_WAITING_LIST,
			'to_confirm' => _CUS_CONFIRMED,
			'subscribed' => _CUS_SUBSCRIBED,
			'started' => _CUS_BEGIN,
			'completed' => _CUS_END,
			'suspended' => _CUS_SUSPEND,
			'overbooking' => _CUS_OVERBOOKING,
		);

		return (int)$lev_arr[$my_status];
	}


	protected function fillCourseDataFromParams(
		&$params, &$db, &$course_id, &$edition_id, &$classroom_id,
		&$course_code, &$edition_code, &$classroom_code,
		&$course_info, &$edition_info, &$classroom_info, &$output
	) {

		// -- read course info / id ----------

		if (empty($course_id) && empty($course_code)) {
			return false;
			// return array('success'=>true, 'debug'=>print_r($params, true));
		}
		else if (empty($course_id) && !empty($course_code)) { // grab course info by code:
			$qtxt ="SELECT * FROM %lms_course
					WHERE code='".$course_code."'
					LIMIT 0,1";
			$q =$db->query($qtxt);
			$course_info =$db->fetch_assoc($q);
			if (!empty($course_info)) {
				$course_id =(int)$course_info['idCourse'];
			}
			else { // course not found
				return false;
				// return array('success'=>'true', 'debug'=>print_r($course_info));
			}
		}
		else if (!empty($course_id)) {
			$qtxt ="SELECT * FROM %lms_course
					WHERE idCourse='".$course_id."'
					LIMIT 0,1";
			$q =$db->query($qtxt);
			$course_info =$db->fetch_assoc($q);
			if (empty($course_info)) { // course not found
				return false;
				// return array('success'=>'true', 'debug'=>print_r($course_info));
			}
		}

		// $output['debug']=print_r($course_info, true);


		// -- read edition info / id ----------

		if (!empty($edition_id) || !empty($edition_code)) {
			if (empty($edition_id) && !empty($edition_code)) { // grab edition info by code:
				$qtxt ="SELECT * FROM %lms_course_editions
					WHERE id_course='".$course_id."' AND code='".$edition_code."'
					LIMIT 0,1";
				$q =$db->query($qtxt);
				$edition_info =$db->fetch_assoc($q);
				if (!empty($edition_info)) {
					$edition_id =(int)$edition_info['id_edition'];
				}
				else { // edition not found
					return false;
					// return array('success'=>'true', 'debug'=>print_r($edition_info));
				}
			}
			else if (!empty($edition_id)) {

			}
		}

		// $output['debug'].=$edition_id." || ".print_r($edition_info, true);


		// -- read classroom info / id ----------

		if (!empty($classroom_id) || !empty($classroom_code)) {
			if (empty($classroom_id) && !empty($classroom_code)) { // grab edition info by code:
				$qtxt ="SELECT * FROM %lms_course_date
					WHERE id_course='".$course_id."' AND code='".$classroom_code."'
					LIMIT 0,1";
				$q =$db->query($qtxt);
				$classroom_info =$db->fetch_assoc($q);
				if (!empty($classroom_info)) {
					$classroom_id =(int)$classroom_info['id_date'];
				}
				else { // classroom not found
					return false;
					// return array('success'=>'true', 'debug'=>print_r($edition_info));
				}
			}
			else if (!empty($classroom_id)) {

			}
		}

		// $output['debug'].=$edition_id." || ".print_r($edition_info, true);
	}


	public function addUserSubscription($params) {
		require_once(_lms_.'/lib/lib.subscribe.php');
		require_once(_lms_.'/lib/lib.course.php');
		$output =array();

		$output['success']=true;

		if (empty($params['idst']) || (int)$params['idst'] <= 0) {
			$output['success']=false;
			$output['message']='INVALID REQUEST';
			return $output;
		}
		else {
			$user_id =$params['idst'];
		}

		$course_id =(isset($params['course_id']) ? (int)$params['course_id'] : false);
		$course_code =(isset($params['course_code']) ? $params['course_code'] : false);
		$edition_id =(isset($params['edition_id']) ? (int)$params['edition_id'] : false);
		$edition_code =(isset($params['edition_code']) ? $params['edition_code'] : false);
		$classroom_id =(isset($params['classroom_id']) ? (int)$params['classroom_id'] : false);
		$classroom_code =(isset($params['classroom_code']) ? $params['classroom_code'] : false);

		$user_level =$this->getUserLevelId((isset($params['user_level']) ? $params['user_level'] : 'student'));
		// $user_status =(isset($params['user_status']) ? $params['user_status'] : false);

                if (!isset($params['sendmail']) || $params['sendmail'] == "") {
                        $sendMailToUser = false;
                } else {
                        $sendMailToUser = true;
                }

		$acl_man =Docebo::user()->getAclManager();
		$course_man =new Man_Course();
		$db = DbConn::getInstance();

		$user_data = $this->aclManager->getUser($user_id, false);

		if (!$user_data) {
				$output['success']=false;
				$output['message']='NO_DATA_FOUND';
				return $output;
		}

		$course_info =false;
		$edition_info =false;
		$classroom_info =false;

		$course_exists = $this->fillCourseDataFromParams(
			$params, $db, $course_id, $edition_id, $classroom_id, $course_code,
			$edition_code, $classroom_code, $course_info, $edition_info,
			$classroom_info, $output
		);
		if ($course_exists === false){
			$output['success']=false;
			$output['message']='NO_DATA_FOUND';
			return $output;
		}


		// --------------- add user: -----------------------------------

		$model = new SubscriptionAlms($course_id, $edition_id, $classroom_id);
		$docebo_course = new DoceboCourse($course_id);
		$level_idst = $docebo_course->getCourseLevel($course_id);
		if (count($level_idst) == 0 || $level_idst[1] == ''){
			$level_idst = $docebo_course->createCourseLevel($course_id);
		}
		$waiting = 0;

		$acl_man->addToGroup($level_idst[$user_level], $user_id);

		$subscribe_ok =$model->subscribeUser($user_id, $user_level, $waiting, false, false);

		if (!$subscribe_ok) {
			$acl_man->removeFromGroup($level_idst[$user_level], $user_id);
			$output['success']=false;
		}
		else {
			$output['message']='User has been subscribed to the course';
		}

                if ($sendMailToUser) {
                    // Send Message
                    require_once(_base_.'/lib/lib.eventmanager.php');

                    $array_subst = array(	'[url]' => Get::site_url(),
                                            '[course]' => $course_info['name'] );

                    $msg_composer = new EventMessageComposer();
                    $msg_composer->setSubjectLangText('email', '_APPROVED_SUBSCRIBED_SUBJECT', false);
                    $msg_composer->setBodyLangText('email', '_APPROVED_SUBSCRIBED_TEXT', $array_subst);

                    $recipients = array($user_id);

                    if(!empty($recipients)) {
                                    createNewAlert(	'UserCourseInsertedApi', 'subscribe', 'insert', '1', 'User subscribed API', $recipients, $msg_composer);
                    }
                }
		return $output;
	}


	public function updateUserSubscription($params) {
		require_once(_lms_.'/lib/lib.subscribe.php');
		require_once(_lms_.'/lib/lib.course.php');
		$output =array();

		$output['success']=true;

		if (empty($params['idst']) || (int)$params['idst'] <= 0) {
			return false;
			// return array('success'=>true, 'debug'=>print_r($params, true));
		}
		else {
			$user_id =$params['idst'];
		}

		$course_id =(isset($params['course_id']) ? (int)$params['course_id'] : false);
		$course_code =(isset($params['course_code']) ? $params['course_code'] : false);
		$edition_id =(isset($params['edition_id']) ? (int)$params['edition_id'] : false);
		$edition_code =(isset($params['edition_code']) ? $params['edition_code'] : false);
		$classroom_id =(isset($params['classroom_id']) ? (int)$params['classroom_id'] : false);
		$classroom_code =(isset($params['classroom_code']) ? $params['classroom_code'] : false);

		$user_level =$this->getUserLevelId((isset($params['user_level']) ? $params['user_level'] : false));
		$user_status =$this->getUserStatusId((isset($params['user_status']) ? $params['user_status'] : false));


		$acl_man =Docebo::user()->getAclManager();
		$course_man =new Man_Course();
		$db = DbConn::getInstance();

		$course_info =false;
		$edition_info =false;
		$classroom_info =false;

		$this->fillCourseDataFromParams(
			$params, $db, $course_id, $edition_id, $classroom_id, $course_code,
			$edition_code, $classroom_code, $course_info, $edition_info,
			$classroom_info, $output
		);


		// --------------- update user subscription: ------------------------

		$model = new SubscriptionAlms($course_id, $edition_id, $classroom_id);
		$docebo_course = new DoceboCourse($course_id);
		$level_idst = $docebo_course->getCourseLevel($course_id);
		if (count($level_idst) == 0 || $level_idst[1] == '')
			$level_idst = $docebo_course->createCourseLevel($course_id);


		$update_ok =true;

		// -- update level -----
		if (!empty($user_level)) {
			$old_level =$model->getUserLevel($user_id);

			if (isset($level_idst[$user_level]) && isset($level_idst[$old_level])) {
				$acl_man->removeFromGroup($level_idst[$old_level], $user_id);
				$acl_man->addToGroup($level_idst[$user_level], $user_id);
				$ok =$model->updateUserLevel($user_id, $user_level);
				if (!$ok) { $update_ok =false; }
			}
		}


		// -- update status -----
		if (!empty($user_status)) {
			$status_arr =$model->getUserStatusList();

			if (isset($status_arr[$user_status])) {
				$ok =$model->updateUserStatus($user_id, $user_status);
				if (!$ok) { $update_ok =false; }
			}
		}


		if (!$update_ok) {
			$output['success']=false;
		}
		else {
			$output['message']='User subscription has been updated';
		}

		return $output;
	}


	public function deleteUserSubscription($params) {
		require_once(_lms_.'/lib/lib.subscribe.php');
		require_once(_lms_.'/lib/lib.course.php');
		$output =array();

		$output['success']=true;

		if (empty($params['idst']) || (int)$params['idst'] <= 0) {
			return false;
			// return array('success'=>true, 'debug'=>print_r($params, true));
		}
		else {
			$user_id =$params['idst'];
		}

		$course_id =(isset($params['course_id']) ? (int)$params['course_id'] : false);
		$course_code =(isset($params['course_code']) ? $params['course_code'] : false);
		$edition_id =(isset($params['edition_id']) ? (int)$params['edition_id'] : false);
		$edition_code =(isset($params['edition_code']) ? $params['edition_code'] : false);
		$classroom_id =(isset($params['classroom_id']) ? (int)$params['classroom_id'] : false);
		$classroom_code =(isset($params['classroom_code']) ? $params['classroom_code'] : false);

		$user_level =$this->getUserLevelId((isset($params['user_level']) ? $params['user_level'] : false));
		$user_status =$this->getUserStatusId((isset($params['user_status']) ? $params['user_status'] : false));


		$acl_man =Docebo::user()->getAclManager();
		$course_man =new Man_Course();
		$db = DbConn::getInstance();

		$course_info =false;
		$edition_info =false;
		$classroom_info =false;

		$this->fillCourseDataFromParams(
			$params, $db, $course_id, $edition_id, $classroom_id, $course_code,
			$edition_code, $classroom_code, $course_info, $edition_info,
			$classroom_info, $output
		);


		// --------------- delete user subscription: ------------------------

		$model = new SubscriptionAlms($course_id, $edition_id, $classroom_id);
		$docebo_course = new DoceboCourse($course_id);
		$level_idst = $docebo_course->getCourseLevel($course_id);

		$old_level =$model->getUserLevel($user_id);

		$delete_ok =$model->delUser($user_id);

		if ($delete_ok) {
			if (empty($edition_id) && empty($classroom_id)) {
				$acl_man->removeFromGroup($level_idst[$old_level], $user_id);
			}
		}


		if (!$delete_ok) {
			$output['success']=false;
		}
		else {
			$output['message']='User has been removed from the course';
		}

		return $output;
	}


	public function subscribeUserWithCode($params) {
		require_once(_lms_.'/lib/lib.subscribe.php');
		require_once(_lms_.'/lib/lib.course.php');
		$output =array();

		$output['success']=true;

		if (empty($params['idst']) || (int)$params['idst'] <= 0) {
			return false;
		}
		else {
			$user_id =$params['idst'];
		}


		$registration_code_type =$params['reg_code_type'];
		$code =$params['reg_code'];
		$code = strtoupper($code);
		$code = str_replace('-', '', $code);

		if (empty($registration_code_type) || empty($code)) {
			$output['success']=false;
		}
		else {

			if($registration_code_type == 'tree_course') $code = substr($code, 10, 10);

			$course_registration_result = false;
			$man_course_user = new Man_CourseUser();
			$course_registration_result = $man_course_user->subscribeUserWithCode($code, $user_id);


			if ($course_registration_result <= 0) {
				if ($course_registration_result == 0) {
					$output['message']='Invalid code';
				}
				else if ($course_registration_result < 0) {
					$output['message']='Code already used';
				}
				$output['success']=false;
			}
			else {
				$output['message']='User has been subscribed to the course';
			}
		}


		return $output;
	}



    
    /**
       dev: LRZ 
       Get certificate by username
       @param $params
              - username
              - course_id (optional)
       @return array
    */    
    public function getCertificateByUser($params){
		require_once(_lms_.'/lib/lib.subscribe.php');
		require_once(_lms_.'/lib/lib.course.php');
		$output =array();

		$output['success']=true;
        
		if (empty($params['username'])) {
			return false;
		}
		else {
			$username =$params['username'];
		}    
    
    
		if (!empty($params['course_id']))  $id_course = (int)$params['course_id'];
	   
    
    
        // recupera info utente 
        $db = DbConn::getInstance();
		$qtxt ="SELECT idst, firstname, lastname  FROM core_user 
				WHERE userid='/".$username."' ";
		$q =$db->query($qtxt);
		$user_info =$db->fetch_assoc($q);        

        $output['idst'] = (int)$user_info['idst'];
        $output['firstname'] = $user_info['firstname'];
        $output['lastname'] = $user_info['lastname'];
        $output['userid'] = $username;
        if($output['idst']==0)  $output['message']="User not found";
    
        // recupera info certificati
        // titolo corso, link attestato, data , id_corso
        $qcert = "select id_course, name, code, on_date, cert_file from  learning_certificate_assign, learning_course  where id_user=".$output['idst']." and idCourse=id_course";
        if($id_course>0) $qcert = $qcert." and id_course=".$id_course; 
        $qcert = $qcert." order by on_date desc";
    
        
			$output['certificate_list'] = array();
		
            $qc =$db->query($qcert);
        	while($row = $db->fetch_assoc($qc)) {
            
                	$output['certificate_list'][] = array('course_id' => $row['id_course'],
                                                          'course_code' => $row['code'],
                                                          'course_name' => $row['name']   ,
                                                          'date_generate' =>  $row['on_date'],
                                                          'cert_file' => Get::site_url()."files/appLms/certificate/".$row['cert_file']
                                                          
                                                          );

			}
		
    
        return $output;
    
    }


    /**
       dev: LRZ 
       Get certificate by id_course
       @param $params
              - username  (optional)
              - course_id 
       @return array
    */   
    public function getCertificateByCourse($params){
    	require_once(_lms_.'/lib/lib.subscribe.php');
		require_once(_lms_.'/lib/lib.course.php');
		require_once(_adm_.'/lib/lib.field.php');
        
        $output =array();

		$output['success']=true;
        
		if (empty($params['course_id'])) {
			return false;
		}
		else {
			$id_course =$params['course_id'];
		}    
    
    
		if (!empty($params['username']))  $username = $params['username'];
    
        // recupera info corso 
        $db = DbConn::getInstance();
		$qtxt ="SELECT idCourse, code, name, box_description  FROM learning_course 
				WHERE idCourse=".(int)$id_course;
		$q =$db->query($qtxt);
		$course_info =$db->fetch_assoc($q);        

        $output['course_id'] = (int)$id_course;
        $output['course_code'] = $course_info['code'];
        $output['course_name'] = $course_info['name'];
        $output['box_description'] = $course_info['box_description'];
        if((int)$course_info['idCourse']==0)  $output['message']="Course not found";    
    
    
    
    
        // recupera info certificati sugli utenti
        $qcert = "select id_course, firstname, lastname, userid, idst, on_date, cert_file from  learning_certificate_assign, %adm_user   where id_course=".$output['course_id']." and id_user=idst";
        if($username !='' ) $qcert = $qcert." and userid = '/".$username."'"; 
        $qcert = $qcert." order by on_date desc";
    
        
			$output['certificate_list'] = array();
        
            $qc =$db->query($qcert);
        	while($row = $db->fetch_assoc($qc)) {
            
            
 			        $field_man = new FieldList();
          			$field_data = $field_man->getFieldsAndValueFromUser($row['idst'], false, true);
          
          			$fields = array();
          			foreach($field_data as $field_id => $value) {
          				$fields[] = array('id'=>$field_id, 'name'=>$value[0], 'value'=>$value[1]);
          			}            
            
            
            
                	$output['certificate_list'][] = array(
                                                          'idst' => $row['idst'],
                                                          'firstname' => $row['firstname'],
                                                          'lastname' => $row['lastname']   ,
                                                          'userid' =>  $row['userid'],
                                                          'date_generate' =>  $row['on_date'],
                                                          
                                                          'cert_file' => Get::site_url()."files/appLms/certificate/".$row['cert_file'],
                                                        
                                                		   'custom_fields' => $fields                                                          
                                                          
                                                          
                                                          );

			}    
    
        return $output;
    
    }



	// ---------------------------------------------------------------------------

	public function call($name, $params) {
		$output = false;

		// Loads user information according to the external user data provided:
		$params =$this->fillParamsFrom($params, $_POST);
		$params =$this->checkExternalUser($params, $_POST);


		if (!empty($params[0]) && !isset($params['idst'])) {
			$params['idst']=$params[0]; //params[0] should contain user idst
		}


		switch ($name) {

			case 'listCourses':
			case 'courses': {
				$output = $this->getCourses($params);
			} break;


			case 'editions': {
				$output = $this->getEditions($params);
			} break;


			case 'classrooms': {
				$output = $this->getClassrooms($params);
			} break;


			case 'addUserSubscription':
			case 'addusersubscription': {
				if (!isset($params['ext_not_found'])) {
					$output = $this->addUserSubscription($params);
				}
			} break;

			case 'updateUserSubscription':
			case 'updateusersubscription': {
				if (!isset($params['ext_not_found'])) {
					$output = $this->updateUserSubscription($params);
				}
			} break;

			case 'deleteUserSubscription':
			case 'deleteusersubscription': {
				if (!isset($params['ext_not_found'])) {
					$output = $this->deleteUserSubscription($params);
				}
			} break;

			case 'subscribeUserWithCode':
			case 'subscribeuserwithcode': {
				if (!isset($params['ext_not_found'])) {                
					$output = $this->subscribeUserWithCode($params);
				}
			} break;


            case 'getCertificateByUser':
            case 'getcertificatebyuser': {
				if (!isset($params['ext_not_found'])) {     
               
					$output = $this->getCertificateByUser($params);
				}
			} break;

            case 'getCertificateByCourse':
            case 'getcertificatebycourse': {
				if (!isset($params['ext_not_found'])) {     
               
					$output = $this->getCertificateByCourse($params);
				}
			} break;




			default: $output = parent::call($name, $params);
		}
		return $output;
	}

}

