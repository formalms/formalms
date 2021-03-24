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


		return $output;
	}


 //e-learning editions
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
				}	
			}
			else if (!empty($edition_id)) {

			}
		}




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

				}
			}
			else if (!empty($classroom_id)) {

			}
		}


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


        $status_arr = $model->getUserStatusList();
        // -- update status -----
        if (!empty($user_status)) {
            if (isset($status_arr[$user_status])) {

                if ($model->updateUserStatus($user_id, $user_status)) {
                    // SET EDIT STATUS SUBSCRIPTION EVENT
                    $event = new \appCore\Events\Core\Courses\CourseSubscriptionEditStatusEvent();
                    $userModel = new UsermanagementAdm();
                    $user = $userModel->getProfileData($user_id);

                    require_once(_lms_ . '/lib/lib.course.php');
                    $docebo_course = new DoceboCourse($course_id);

                    $event->setUser($user);
                    $event->setStatus(['id' => $user_status, 'name' => $status_arr[$user_status]]);
                    $event->setCourse($docebo_course->course_info);
                    \appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\Courses\CourseSubscriptionEditStatusEvent::EVENT_NAME, $event);
                } else {
                    $update_ok = false;
                }
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
                                                          'cert_file' => Get::site_url()._folder_files_."/appLms/certificate/".$row['cert_file']
                                                          
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
                                                          
                                                          'cert_file' => Get::site_url()._folder_files_."/appLms/certificate/".$row['cert_file'],
                                                        
                                                		   'custom_fields' => $fields                                                          
                                                          
                                                          
                                                          );

			}    
    
        return $output;
    
    }
        

	// ---------------------------------------------------------------------------
    // LRZ
    // Adding Course Category:
    // Input param:
    // category_id: category id of the parent category; category is created on root if no parent ID passed
    // node_name: category name 
    public function addCategory($params){

        $category_id =(isset($params['category_id']) ? (int)$params['category_id'] : 0);
        $category_name =(isset($params['name']) ? $params['name'] : false);
        
        
        if ( $category_name == false){
            $output = array('success'=>false, 'message'=>'Wrong parameters');
        }
        else{
            require_once(_lms_.'/lib/category/class.categorytree.php');
            $treecat = new Categorytree();
            
            $new_category_id = $treecat->addFolderById($category_id, $category_name);
            if ($new_category_id != false && $new_category_id>0){
                $output = array('success'=>true, 'category_id'=>$new_category_id, 'parent_category_id'=>$params['category_id']);
            }
            else{
                $output = array('success'=>false, 'message'=>'Cannot create category');
            }
        }
        
        return $output;
    }    
    
    
    
private function getInfoCourseAdd(){
    

        $db = DbConn::getInstance();
        $qtxt ="SELECT max(idCourse) as max_id  FROM learning_course ";
        $q =$db->query($qtxt);
        $course_info =$db->fetch_assoc($q);        
    
        return  $course_info['max_id'];    
    
}    
    
public function addCourse($params){
        require_once(_lms_.'/lib/lib.course.php');
        
        $output =array();
        $output['success']=true;

        $params['advance']=(isset($params['advance']) ? $params['advance'] : '');
        $params['allow_overbooking']=(isset($params['allow_overbooking']) ? 1 : 0);
        $params['selected_menu']=(isset($params['selected_menu']) ? $params['selected_menu'] : 11);
        if (empty($params['allow_overbooking'])) { unset($params['allow_overbooking']); }
        $params['auto_unsubscribe']=(isset($params['auto_unsubscribe']) ? 1 : 0);
        if (empty($params['auto_unsubscribe'])) { unset($params['auto_unsubscribe']); }
        $params['can_subscribe']=(isset($params['can_subscribe']) ? $params['can_subscribe'] : false);
        $params['course_advanced']=(isset($params['course_advanced']) ? 1 : 0);
        if (empty($params['course_advanced'])) { unset($params['course_advanced']); }
        $params['course_autoregistration_code']=(isset($params['course_autoregistration_code']) ? $params['course_autoregistration_code'] : false);
        $params['course_code']=(isset($params['course_code']) ? $params['course_code'] : false);
        $params['course_date_begin']=(isset($params['course_date_begin']) ? $params['course_date_begin'] : false);
        $params['course_date_end']=(isset($params['course_date_end']) ? $params['course_date_end'] : false);
        $params['course_day_of']=(isset($params['course_day_of']) ? $params['course_day_of'] : false);
        $params['course_descr']=(isset($params['course_descr']) ? $params['course_descr'] : false);
        $params['course_difficult']=(isset($params['course_difficult']) ? $params['course_difficult'] : false);
        $params['course_edition']=(isset($params['course_edition']) ? 1 : 0);
        if (empty($params['course_edition'])) { unset($params['course_edition']); }
        $params['course_em']=(isset($params['close_lo_perm']) ? 1 : 0);
        if (empty($params['course_em'])) { unset($params['course_em']); }
        $params['course_lang']=(isset($params['course_lang']) ? $params['course_lang'] : 'italian');
        $params['course_medium_time']=(isset($params['course_medium_time']) ? $params['course_medium_time'] : false);
        $params['course_name']=(isset($params['course_name']) ? $params['course_name'] : false);
        $params['course_prize']=(isset($params['course_prize']) ? $params['course_prize'] : false);
        $params['course_progress']=(isset($params['course_progress']) ? 1 : 0);
        if (empty($params['course_progress'])) { unset($params['course_progress']); }
        $params['course_quota']=(isset($params['course_quota']) ? $params['course_quota'] : false);
        $params['course_sell']=(isset($params['course_sell']) ? 1 : 0);
        if (empty($params['course_sell'])) { unset($params['course_sell']); }
        $params['course_show_rules']=(isset($params['course_show_rules']) ? $params['course_show_rules'] : false);
        $params['course_sponsor_link']=(isset($params['course_sponsor_link']) ? $params['course_sponsor_link'] : false);
        $params['course_status']=((isset($params['course_status']) && $params['course_status']) ? $params['course_status'] : 2);
        $params['course_subs']=(isset($params['course_subs']) ? $params['course_subs'] : false);
        $params['course_time']=(isset($params['course_time']) ? 1 : 0);
        if (empty($params['course_time'])) { unset($params['course_time']); }
        $params['course_type']=(isset($params['course_type']) ? $params['course_type'] : false);
        $params['credits']=(isset($params['credits']) ? $params['credits'] : false);
        $params['direct_play']=(isset($params['direct_play']) ? $params['direct_play'] : false);
        if (empty($params['direct_play'])) { unset($params['direct_play']); }
        $params['idCategory']=(isset($params['idCategory']) ? $params['idCategory'] : false);
        $params['inherit_quota']=(isset($params['inherit_quota']) ? 1 : 0);
        if (empty($params['inherit_quota'])) { unset($params['inherit_quota']); }
        $params['max_num_subscribe']=(isset($params['max_num_subscribe']) ? $params['max_num_subscribe'] : false);
        $params['min_num_subscribe']=(isset($params['min_num_subscribe']) ? $params['min_num_subscribe'] : false);
        $params['random_course_autoregistration_code']=(isset($params['random_course_autoregistration_code']) ? $params['random_course_autoregistration_code'] : false);
        $params['show_result']=(isset($params['show_result']) ? 1 : 0);
        if (empty($params['show_result'])) { unset($params['show_result']); }
        $params['show_who_online']=(isset($params['show_who_online']) ? 1 : 0);
        $params['sub_end_date']=(isset($params['sub_end_date']) ? $params['sub_end_date'] : false);
        $params['sub_start_date']=(isset($params['sub_start_date']) ? $params['sub_start_date'] : false);
        $params['unsubscribe_date_limit']=(isset($params['unsubscribe_date_limit']) ? $params['unsubscribe_date_limit'] : false);
        $params['use_logo_in_courselist']=(isset($params['use_logo_in_courselist']) ? 1 : 0);
        if (empty($params['use_logo_in_courselist'])) { unset($params['use_logo_in_courselist']); }
        $params['use_unsubscribe_date_limit']=(isset($params['use_unsubscribe_date_limit']) ? 1 : 0);
        if (empty($params['use_unsubscribe_date_limit'])) { unset($params['use_unsubscribe_date_limit']); }

                
        $course = new CourseAlms();
        $res =$course->insCourse($params);
        
        
        if ($res['res'] == '_ok_course') {
            $id_course = $this->getInfoCourseAdd();
            $output['message']=$res['res']." -  ".$params['course_type'] ;
            $output['course_id']=$id_course ;
            
        }
        else {
            $output['success']=false;
            $output['message']='Creation failed';
        }
        return $output;
    }    
    
    
    
    
public function addClassroom($params) {
    
    
        require_once(_lms_.'/lib/lib.date.php');
        require_once(_lms_.'/lib/lib.course.php');
        $output =array();
        $course_id =(isset($params['course_id']) ? $params['course_id'] : '');
        
        if (empty($course_id)) {
            $output['success']=false;
            $output['message']='Missing Course ID'.$params['course_id'];
            return $output;
        }

        $course = new CourseAlms();
        $classroom_man = new DateManager();

        $res =false;
        
        $info =$course->getInfo($course_id);
        if (empty($info)) {
            $output['success']=false;
            $output['message']='Course not found:'.$params['course_id'];
            return $output;
        }
        
        
        $params['classroom_sub_start_date']=substr(Format::dateDb($params['classroom_sub_start_date'], 'date'),0,10);
        $params['classroom_sub_end_date']=substr(Format::dateDb($params['classroom_sub_end_date'], 'date'),0,10);
        $params['classroom_unsubscribe_date_limit']=substr(Format::dateDb($params['classroom_unsubscribe_date_limit'], 'date'),0,10);

        $res =$classroom_man->insDate(
            $course_id,
            (($params['classroom_code']) ? $params['classroom_code'] : false),
            (($params['classroom_name']) ? $params['classroom_name'] : false),
            (($params['classroom_descr']) ? $params['classroom_descr'] : false),
            (($params['classroom_medium_time']) ? $params['classroom_medium_time'] : false),
            (($params['classroom_max_users']) ? $params['classroom_max_users'] : false),
            (($params['classroom_price']) ? $params['classroom_price'] : false),
            (($params['classroom_allow_overbooking']) ? $params['classroom_allow_overbooking'] : false),
            (($params['classroom_status']) ? $params['classroom_status'] : 0),
            (($params['classroom_test_type']) ? $params['classroom_test_type'] : 0),
            (($params['classroom_sub_start_date']) ? $params['classroom_sub_start_date'] : '0000-00-00 00:00:00'),
            (($params['classroom_sub_end_date']) ? $params['classroom_sub_end_date'] : '0000-00-00 00:00:00'),
            (($params['classroom_unsubscribe_date_limit']) ? $params['classroom_unsubscribe_date_limit'] : false)
        );
        
        if ($res) {
            $output['success']=true;
            $output['id_date']=$res;
        }
        else {
            $output['success']=false;
            $output['message']='Error creating classroom';
        }

        return $output;
    }    
    
    
    
    private function getMaxDateDay($id_date)
    {
      
       
        $db = DbConn::getInstance();
        $query =    "select max(id_day) as max_id FROM learning_course_date_day " 
                    ." WHERE    ID_DATE = ".$id_date;
        $q =$db->query($query);
        $course_info =$db->fetch_assoc($q);        
    
        if($course_info['max_id']==null) return -1;
        return  $course_info['max_id'];  
    
    }
    
    
    private function insDateDayfromParams($id_date, $day_info)
    {
        $res = false;

        $index=$this->getMaxDateDay($id_date);
        $id_day=$index+1;
        
        $query =    "INSERT INTO learning_course_date_day"
                    ." (id_day, id_date, classroom, date_begin, date_end, pause_begin, pause_end)";

        $query .= " VALUES (".$id_day.", ".$id_date.", ".$day_info[0]['classroom'].", '".$day_info[0]['date_begin']."', '".$day_info[0]['date_end']."', '".$day_info[0]['pause_begin']."', '".$day_info[0]['pause_end']."')";
        
        

        $res = sql_query($query);
        
        if($res)
            return $id_day;
        else 
            return false;
    }    
    
    
    
    private function saveNewDateFromParams($date_info, $array_day)
    {
        
       
        
        $course_man = new Man_Course();        
        
        $sub_start_date = trim( $date_info['sub_start_date'] );
        $sub_end_date = trim( $date_info['sub_end_date'] );
        $unsubscribe_date_limit = trim( $date_info['unsubscribe_date_limit'] );

        $sub_start_date = (!empty($sub_start_date) ? Format::dateDb($sub_start_date, 'date') : '0000-00-00').' 00:00:00';
        $sub_end_date = (!empty($sub_end_date) ? Format::dateDb($sub_end_date, 'date') : '0000-00-00').' 00:00:00';
        $unsubscribe_date_limit = (!empty($unsubscribe_date_limit) ? Format::dateDb($unsubscribe_date_limit, 'date') : '0000-00-00').' 00:00:00';

        $id_date=$date_info['id_date'];
        
        
        
        
        if($id_date)
            return $this->insDateDayfromParams($id_date, $array_day);
        else
            return false;
    }    
    
    
    
    public function addDay($params){
        require_once(_lms_.'/admin/models/ClassroomAlms.php');
        $output =array();
        $id_date =(isset($params['id_date']) ? $params['id_date'] : '');
        
        if (empty($id_date)) {
            $output['success']=false;
            $output['message']='Missing ID Date'.$params['id_date'];
            return $output;
        }
        
        $course_id =(isset($params['course_id']) ? $params['course_id'] : '');
        
        if (empty($course_id)) {
            $output['success']=false;
            $output['message']='Missing Course ID'.$params['course_id'];
            return $output;
        }

        $model = new ClassroomAlms($course_id);

        $res =false;
            
        $array_day = array();
        $array_day[0]['date_begin'] = substr(Format::dateDb($params['edition_date_selected'], 'date'),0,10).' '.$params['edition_b_hours'].':'.$params['edition_b_minutes'].':00';
        $array_day[0]['pause_begin'] = substr(Format::dateDb($params['edition_date_selected'], 'date'),0,10).' '.$params['edition_pb_hours'].':'.$params['edition_pb_minutes'].':00';
        $array_day[0]['pause_end'] = substr(Format::dateDb($params['edition_date_selected'], 'date'),0,10).' '.$params['edition_pe_hours'].':'.$params['edition_pe_minutes'].':00';
        $array_day[0]['date_end'] = substr(Format::dateDb($params['edition_date_selected'], 'date'),0,10).' '.$params['edition_e_hours'].':'.$params['edition_e_minutes'].':00';
        $array_day[0]['classroom'] = $params['edition_classroom'];
        
        $availability_info = $model->checkDateAvailability($array_day);
        if (!empty($availability_info)) {
            
        }

        //save class info
        $res=$this->saveNewDateFromParams($params, $array_day);
        
        if($res!==false){
            $output['success']=true;
            $output['id_day']=$res;
        }
        else {
            $output['success']=false;
            $output['message']='Error creating edition'.count($array_day);
        }

        return $output;
    }    
    
    

	public function delDay($params){
	        require_once(_lms_.'/admin/models/ClassroomAlms.php');
	        require_once(_lms_.'/lib/lib.date.php');
	        
	        $output =array();
	        
	        
	        $id_day =(isset($params['id_day']) ? $params['id_day'] : '');
	        
	     
	        if (empty($id_day)) {
	            $output['success']=false;
	            $output['message']='Missing ID Day: '.$params['id_day'];
	            return $output;
	        }
	 
	      
	        $id_date =(isset($params['id_date']) ? $params['id_date'] : '');
	        
	        if (empty($id_date)) {
	            $output['success']=false;
	            $output['message']='Missing ID Date'.$params['id_date'];
	            return $output;
	        }
	        
	        $course_id =(isset($params['course_id']) ? $params['course_id'] : '');
	        
	        if (empty($course_id)) {
	            $output['success']=false;
	            $output['message']='Missing Course ID'.$params['course_id'];
	            return $output;
	        }

	        $query = "delete from learning_course_date_day where id_date=".$params['id_date']." and id_day=".$params['id_day'];
	        $res = sql_query($query);
	        
	        
	        if($res){
	            $output['success']=true;
	            $output['day_delete']=$params['id_day'];
	            $output['id_date']=$params['id_date'];
	            
	        }
	        else {
	            $output['success']=false;
	            $output['message']='Error deleting day';
	        }
	
	        return $output;
	 }    
    
    
    public function updateCourse($params){
        require_once(_lms_.'/lib/lib.course.php');
        
        $output = array();
        $output['success']=true;
        
        $course_id=(isset($params['course_id']) ? $params['course_id'] : '');
        
        if (empty($course_id)) {
            $output['success']=false;
            $output['message']='Missing Course ID'.$params['course_id'];
            return $output;
        }
        
        
        $course = new CourseAlms();
        $course_info =$course->getInfo($course_id);
        $params['advance']=(($params['advance']) ? $params['advance'] : $course_info['advance']);
        $params['allow_overbooking']=(($params['allow_overbooking']) ? 1 : 0);
        $params['allow_overbooking']=(($params['allow_overbooking']) ? $params['allow_overbooking'] : $course_info['allow_overbooking']);
        if (empty($params['allow_overbooking'])) { unset($params['allow_overbooking']); }
        $params['auto_unsubscribe']=(($params['auto_unsubscribe']) ? 1 : 0);
        $params['auto_unsubscribe']=(($params['auto_unsubscribe']) ? $params['auto_unsubscribe'] : $course_info['auto_unsubscribe']);
        if (empty($params['auto_unsubscribe'])) { unset($params['auto_unsubscribe']); }
        $params['can_subscribe']=(($params['can_subscribe']) ? $params['can_subscribe'] : $course_info['can_subscribe']);
        $params['course_advanced']=(($params['course_advanced']) ? 1 : 0);
        $params['course_advanced']=(($params['course_advanced']) ? $params['course_advanced'] : $course_info['show_extra_info']);
        if (empty($params['course_advanced'])) { unset($params['course_advanced']); }
        $params['course_autoregistration_code']=(($params['course_autoregistration_code']) ? $params['course_autoregistration_code'] : $course_info['autoregistration_code']);
        $params['course_code']=(($params['course_code']) ? $params['course_code'] : $course_info['code']);
        $params['course_date_begin']=(($params['course_date_begin']) ? $params['course_date_begin'] : false);
        $params['course_date_end']=(($params['course_date_end']) ? $params['course_date_end'] : false);
        $params['course_day_of']=(($params['course_day_of']) ? $params['course_day_of'] : $course_info['valid_time']);
        $params['course_descr']=(($params['course_descr']) ? $params['course_descr'] : $course_info['description']);
        $params['course_difficult']=(($params['course_difficult']) ? $params['course_difficult'] : $course_info['difficult']);
        $params['course_edition']=(($params['course_edition']) ? 1 : 0);
        $params['course_edition']=(($params['course_edition']) ? $params['course_edition'] : $course_info['course_edition']);
        if (empty($params['course_edition'])) { unset($params['course_edition']); }
        $params['course_em']=(($params['close_lo_perm']) ? 1 : 0);
        $params['course_em']=(($params['course_em']) ? $params['course_em'] : $course_info['permCloseLO']);
        if (empty($params['course_em'])) { unset($params['course_em']); }
        $params['course_lang']=(($params['course_lang']) ? $params['course_lang'] : $course_info['lang_code']);
        $params['course_medium_time']=(($params['course_medium_time']) ? $params['course_medium_time'] : $course_info['mediumTime']);
        $params['course_name']=(($params['course_name']) ? $params['course_name'] : $course_info['name']);
        $params['course_prize']=(($params['course_price']) ? $params['course_price'] : $course_info['prize']);
        $params['course_progress']=(($params['course_progress']) ? 1 : 0);
        $params['course_progress']=(($params['course_progress']) ? $params['course_progress'] : $course_info['show_progress']);
        if (empty($params['course_progress'])) { unset($params['course_progress']); }
        $params['course_quota']=(($params['course_quota']) ? $params['course_quota'] : $course_info['course_quota']);
        $params['course_sell']=(($params['course_sell']) ? 1 : 0);
        $params['course_sell']=(($params['course_sell']) ? $params['course_sell'] : $course_info['selling']);
        if (empty($params['course_sell'])) { unset($params['course_sell']); }
        $params['course_show_rules']=(($params['course_show_rules']) ? $params['course_show_rules'] : $course_info['show_rules']);
        $params['course_sponsor_link']=(($params['course_sponsor_link']) ? $params['course_sponsor_link'] : $course_info['linkSponsor']);
        $params['course_status']=(($params['course_status']) ? $params['course_status'] : $course_info['status']);
        $params['course_subs']=(($params['course_subs']) ? $params['course_subs'] : $course_info['subscribe_method']);
        $params['course_time']=(($params['course_time']) ? 1 : 0);
        $params['course_time']=(($params['course_time']) ? $params['course_time'] : $course_info['show_time']);
        if (empty($params['course_time'])) { unset($params['course_time']); }
        $params['course_type']=(($params['course_type']) ? $params['course_type'] : $course_info['course_type']);
        $params['credits']=(($params['credits']) ? $params['credits'] : $course_info['credits']);
        $params['direct_play']=(($params['direct_play']) ? 1 : 0);
        $params['direct_play']=(($params['direct_play']) ? $params['direct_play'] : $course_info['direct_play']);
        if (empty($params['direct_play'])) { unset($params['direct_play']); }
        $params['idCategory']=(($params['idCategory']) ? $params['idCategory'] : $course_info['idCategory']);
        $params['inherit_quota']=(($params['inherit_quota']) ? 1 : 0);
        $params['inherit_quota']=(($params['inherit_quota']) ? $params['inherit_quota'] : $course_info['inherit_quota']);
        if (empty($params['inherit_quota'])) { unset($params['inherit_quota']); }
        $params['max_num_subscribe']=(($params['max_num_subscribe']) ? $params['max_num_subscribe'] : $course_info['max_num_subscribe']);
        $params['min_num_subscribe']=(($params['min_num_subscribe']) ? $params['min_num_subscribe'] : $course_info['min_num_subscribe']);
        $params['random_course_autoregistration_code']=(($params['random_course_autoregistration_code']) ? $params['random_course_autoregistration_code'] : false);
        $params['show_result']=(($params['show_result']) ? 1 : 0);
        $params['show_result']=(($params['show_result']) ? $params['show_result'] : $course_info['show_result']);
        if (empty($params['show_result'])) { unset($params['show_result']); }
        $params['show_who_online']=(($params['show_who_online']) ? 1 : 0);
        $params['show_who_online']=(($params['show_who_online']) ? $params['show_who_online'] : $course_info['show_who_online']);
        $params['sub_end_date']=(($params['sub_end_date']) ? $params['sub_end_date'] : $course_info['sub_end_date']);
        $params['sub_start_date']=(($params['sub_start_date']) ? $params['sub_start_date'] : $course_info['sub_start_date']);
        $params['unsubscribe_date_limit']=(($params['unsubscribe_date_limit']) ? $params['unsubscribe_date_limit'] : $course_info['unsubscribe_date_limit']);
        $params['use_logo_in_courselist']=(($params['use_logo_in_courselist']) ? 1 : 0);
        $params['use_logo_in_courselist']=(($params['use_logo_in_courselist']) ? $params['use_logo_in_courselist'] : $course_info['use_logo_in_courselist']);
        if (empty($params['use_logo_in_courselist'])) { unset($params['use_logo_in_courselist']); }
        $params['use_unsubscribe_date_limit']=(($params['use_unsubscribe_date_limit']) ? 1 : 0);
        if (empty($params['use_unsubscribe_date_limit'])) { unset($params['use_unsubscribe_date_limit']); }
        
        
        
        $res =$course->upCourse($course_id, $params);
        
        
        
         $output['course_id']=$course_id;

        if ($res['res'] == '_ok_course') {
 
        }
        else {
            $output['success']=false;
            $output['message']='Update failed';
        }
        return $output;
    }    
    
    
     // update an appointment related to an edition
    public function upDay($params){
        require_once(_lms_.'/admin/models/ClassroomAlms.php');
        require_once(_lms_.'/lib/lib.date.php');
        
        $output =array();
        
        $id_day = (isset($params['id_day']) ? $params['id_day'] : 0);
        
   
        if (empty($id_day)) {
            $output['success']=false;
            $output['message']='Missing ID Day: '.$params['id_day']." - ".$params['id_day'];
            return $output;
        }
      
        
        $id_date =(isset($params['id_date']) ? $params['id_date'] : '');
        
        if (empty($id_date)) {
            $output['success']=false;
            $output['message']='Missing ID Date'.$params['id_date'];
            return $output;
        }
        
        $course_id =(isset($params['course_id']) ? $params['course_id'] : '');
        
        if (empty($course_id)) {
            $output['success']=false;
            $output['message']='Missing Course ID'.$params['course_id'];
            return $output;
        }

        $model = new ClassroomAlms($course_id, $id_date);
        
        $array_day = $model->classroom_man->getDateDay($id_date);
        
        $array_day[$id_day]['date_begin'] = substr(Format::dateDb($params['edition_date_selected'], 'date'),0,10).' '.$params['edition_b_hours'].':'.$params['edition_b_minutes'].':00';
        $array_day[$id_day]['pause_begin'] = substr(Format::dateDb($params['edition_date_selected'], 'date'),0,10).' '.$params['edition_pb_hours'].':'.$params['edition_pb_minutes'].':00';
        $array_day[$id_day]['pause_end'] = substr(Format::dateDb($params['edition_date_selected'], 'date'),0,10).' '.$params['edition_pe_hours'].':'.$params['edition_pe_minutes'].':00';
        $array_day[$id_day]['date_end'] = substr(Format::dateDb($params['edition_date_selected'], 'date'),0,10).' '.$params['edition_e_hours'].':'.$params['edition_e_minutes'].':00';
        $array_day[$id_day]['classroom'] = $params['edition_classroom'];
                      

        
        $classroom_man = new DateManager();
        $res=$classroom_man->insDateDay($id_date, $array_day);
        
        if($res){
            $output['success']=true;
            $output['id_date']=$id_date;
            $output['id_day']=$id_day;
        }
        else {
            $output['success']=false;
            $output['message']='Error updating day ';
        }

        return $output;
    }    
    
    
	 // update date    
	public function updateClassroom($params) {
	        require_once(_lms_.'/lib/lib.date.php');
	        require_once(_lms_.'/lib/lib.course.php');
	        $output =array();
	        
	        $id_date =(isset($params['id_date']) ? $params['id_date'] : '');
	        
	        if (empty($id_date)) {
	            $output['success']=false;
	            $output['message']='Missing ID date '.$params['id_date'];
	            return $output;
	        }
	        
	        $course_id =(isset($params['course_id']) ? $params['course_id'] : '');
	        
	        if (empty($course_id)) {
	            $output['success']=false;
	            $output['message']='Missing Course ID'.$params['course_id'];
	            return $output;
	        }
	
	        $course = new CourseAlms();
	        $classroom_man = new DateManager();
	
	        $res =false;
	        
	        
	        $info =$course->getInfo($course_id);
	        if (empty($info)) {
	            $output['success']=false;
	            $output['message']='Course not found:'.$params['course_id'];
	            return $output;
	        }
	      
	        
	        $params['classroom_sub_start_date']=substr(Format::dateDb($params['classroom_sub_start_date'], 'date'),0,10);
	        $params['classroom_sub_end_date']=substr(Format::dateDb($params['classroom_sub_end_date'], 'date'),0,10);
	        $params['classroom_unsubscribe_date_limit']=substr(Format::dateDb($params['classroom_unsubscribe_date_limit'], 'date'),0,10);
	
	        
	        
	        
	        
	        $res =$this->upDate(
	            $id_date,
	            (!empty($params['classroom_code']) ? $params['classroom_code'] : false),
	            (!empty($params['classroom_name']) ? $params['classroom_name'] : false),
	            (!empty($params['classroom_descr']) ? $params['classroom_descr'] : false),
	            (!empty($params['classroom_medium_time']) ? $params['classroom_medium_time'] : false),
	            (!empty($params['classroom_max_users']) ? $params['classroom_max_users'] : 0),
	            (!empty($params['classroom_price']) ? $params['classroom_price'] : null),
	            (!empty($params['classroom_allow_overbooking']) ? $params['classroom_allow_overbooking'] : 0),
	            (!empty($params['classroom_status']) ? $params['classroom_status'] : 0),
	            (!empty($params['classroom_test_type']) ? $params['classroom_test_type'] : 0),
	            (!empty($params['classroom_sub_start_date']) ? $params['classroom_sub_start_date'] : false),
	            (!empty($params['classroom_sub_end_date']) ? $params['classroom_sub_end_date'] : false),
	            (!empty($params['classroom_unsubscribe_date_limit']) ? $params['classroom_unsubscribe_date_limit'] : false)
	        );
	        
	        if ($res) {
	            $output['success']=true;
	            $output['id_date']=$id_date;
	        }
	        else {
	            $output['success']=false;
	            $output['message']='Error updating classroom<br>'.$id_date. "<br>- ".$params['classroom_code']."<br>- ".$params['classroom_name'];
	        }
	
	
	        
	        return $output;
	    }    
    
    
    

    private function upDate($id_date, $code, $name, $description, $medium_time, $max_par, $price, $overbooking, $status, $test_type, $sub_start_date, $sub_end_date, $unsubscribe_date_limit)
    {
        $query =    "UPDATE ".$GLOBALS['prefix_lms']."_course_date"
                    ." SET code = '".$code."',"
                    ." name = '".$name."',"
                    ." description = '".$description."',"
                    ." medium_time = '".$medium_time."',"
                    ." max_par = '".$max_par."',"
                    ." price = '".$price."',"
                    ." overbooking = ".$overbooking.","
                    ." test_type = ".$test_type.","
                    ." status = ".$status.","
                    ." sub_start_date = '".$sub_start_date."',"
                    ." sub_end_date = '".$sub_end_date."',"
                    ." unsubscribe_date_limit = '".$unsubscribe_date_limit."'"
                    ." WHERE id_date = ".$id_date;
         
         
        return sql_query($query);
    }    
    
  
    // 
    public function deleteCourse($params){
        require_once(_lms_.'/lib/lib.course.php');
        $output =array();
        
        $course_id=(isset($params['course_id']) ? $params['course_id'] : '');
        
        if (empty($course_id)) {
            $output['success']=false;
            $output['message']='Missing Course ID'.$params['course_id'];
            return $output;
        }

        $course = new CourseAlms();

        $res =false;
        $info =$course->getInfo($course_id);

        if (!empty($info)) {
            $res =$this->delCourse($course_id);
        }

        if ($res) {
            $output['success']=true;
            $output['course_id']=$course_id;
        }
        else {
            $output['success']=false;
            $output['message']='Delete Failed';
        }

        return $output;
    }    
    


    /* Delete a course
    *  Input param:
    *  $id_course: id of the course to delete
    */
 	public function delCourse($id_course)
    {
        if ((int)$id_course <= 0) return false;

        require_once(_lms_ . '/lib/lib.course.php');
        require_once(_base_ . '/lib/lib.upload.php');

        $course_man = new Man_Course();

        $course = new DoceboCourse($id_course);
        if(!$course->getAllInfo()) {
            return false;
        }

        //remove course subscribed------------------------------------------

        $levels =& $course_man->getCourseIdstGroupLevel($id_course);
        foreach ($levels as $lv => $idst) {
            Docebo::aclm()->deleteGroup($idst);
        }

        $alluser = getIDGroupAlluser($id_course);
        Docebo::aclm()->deleteGroup($alluser);
        $course_man->removeCourseRole($id_course);
        $course_man->removeCourseMenu($id_course);

        $query = "DELETE FROM %lms_courseuser WHERE idCourse = '" . (int)$id_course . "'";
        $qres = sql_query($query);
        if (!$qres) return false;

        //--- remove course data ---------------------------------------------------

        $query_course = "SELECT imgSponsor, img_course, img_material, img_othermaterial, course_demo, course_type, has_editions
            FROM %lms_course
            WHERE idCourse = '" . (int)$id_course . "'";
        $qres = sql_query($query_course);
        list($file_sponsor, $file_logo, $file_material, $file_othermaterial, $file_demo, $course_type, $course_edition) = sql_fetch_row($qres);

        require_once(_base_ . '/lib/lib.upload.php');

        $path = '/appLms/' . Get::sett('pathcourse');
        if (substr($path, -1) != '/' && substr($path, -1) != '\\') $path .= '/';
        sl_open_fileoperations();
        if ($file_sponsor != '') sl_unlink($path . $file_sponsor);
        if ($file_logo != '') sl_unlink($path . $file_logo);
        if ($file_material != '') sl_unlink($path . $file_material);
        if ($file_othermaterial != '') sl_unlink($path . $file_othermaterial);
        if ($file_demo != '') sl_unlink($path . $file_demo);
        sl_close_fileoperations();

        //if the scs exist delete course rooms
        if ($GLOBALS['where_scs'] !== false) {
            require_once(_scs_ . '/lib/lib.room.php');
            $re = deleteRoom(false, 'course', $id_course);
        }


        //--- delete classroom or editions -----------------------------------------
        if ($course_type == 'classroom') {
            require_once(_lms_ . '/admin/model/ClassroomAlms.php');
            $classroom_model = new ClassroomAlms($id_course);

            $classroom = $classroom_model->classroom_man->getDateIdForCourse($id_course);

            foreach ($classroom as $id_date)
                if (!$classroom_model->classroom_man->delDate($id_date))
                    return false;
        } elseif ($course_edition == 1) {
            require_once(_lms_ . '/admin/model/EditionAlms.php');
            $edition_model = new EditionAlms($id_course);

            $editions = $edition_model->classroom_man->getEditionIdFromCourse($id_course);

            foreach ($editions as $id_edition)
                if (!$edition_model->edition_man->delEdition($id_edition))
                    return false;
        }
        //--- end classrooms or editions -------------------------------------------


        //--- clear LOs ------------------------------------------------------------

        require_once(_lms_ . '/lib/lib.module.php');
        require_once(_lms_ . '/lib/lib.param.php');
        require_once(_lms_ . '/class.module/track.object.php');

        $arr_lo_param = array();
        $arr_lo_track = array();
        $arr_org_access = array();

        $query = "SELECT * FROM %lms_organization WHERE idCourse = " . (int)$id_course;
        $ores = sql_query($query);
   
        while ($obj = sql_fetch_object($ores)) {
            $deleted = true;
        
            if ($obj->idResource != 0 && $obj->objectType != "") {
                $lo = createLO($obj->objectType);
               // $deleted = $lo->del($obj->idResource); //delete learning object
            }
         
            if ($deleted) {
                $arr_lo_track[] = $obj->idOrg;
                $arr_org_access[] = $obj->idOrg; //collect org access ids
                $arr_lo_param[] = $obj->idParam; //collect idParams ids
            }
        }

        //delete all organizations references for the course
        $query = "DELETE FROM %lms_organization WHERE idCourse = " . (int)$id_course;
        $res = sql_query($query);

        
        $query = "DELETE FROM %lms_course WHERE idCourse = " . (int)$id_course;
        $res = sql_query($query);
        
        
        //delete LOs trackings
     
        if (!empty($arr_lo_track)) {
            $track_object = new Track_Object(false, 'course_lo');
            $track_object->delIdTrackFromCommon($arr_lo_track);
        }

        //delete org accesses
        if (!empty($arr_org_access)) {
            $query = "DELETE FROM %lms_organization_access
                WHERE idOrgAccess IN (" . implode(",", $arr_org_access) . ")";
            $res = sql_query($query);
        }

        //delete lo params
        if (!empty($arr_lo_param)) {
            $query = "DELETE FROM %lms_lo_param
                WHERE idParam IN (" . implode(",", $arr_lo_param) . ")";
        }

        //--- end LOs --------------------------------------------------------------


        //--- clear coursepath references ------------------------------------------
        require_once(_lms_ . '/lib/lib.coursepath.php');
        $cman = new CoursePath_Manager();
        $cman->deleteCourseFromCoursePaths($id_course);
        //--- end coursepath references --------------------------------------------


        //--- clear certificates assignments ---------------------------------------
        require_once(Forma::inc(_lms_ . '/lib/lib.certificate.php'));
        $cman = new Certificate();
        $cman->deleteCourseCertificateAssignments($id_course);
        //--- end certificates assignments -----------------------------------------


        //--- clear labels ---------------------------------------------------------
        $lmodel = new LabelAlms();
        $lmodel->clearCourseLabel($id_course);
        //--- end labels -----------------------------------------------------------


        //--- clear advices --------------------------------------------------------
        require_once(_lms_ . '/lib/lib.advice.php');
        $aman = new Man_Advice();
        $aman->deleteAllCourseAdvices($id_course);
        //--- end advices ----------------------------------------------------------


        //--- clear coursereports --------------------------------------------------
        require_once(_lms_ . '/lib/lib.coursereport.php');
        $cman = new CourseReportManager();
        $cman->deleteAllReports($id_course);
        //--- end coursereports ----------------------------------------------------


        //--- clear competences ----------------------------------------------------
        $cmodel = new CompetencesAdm();
        $cmodel->deleteAllCourseCompetences($id_course);
        //--- end competences ------------------------------------------------------

        //remove customfield
        if (!sql_query("DELETE FROM " . $GLOBALS['prefix_fw'] . "_customfield_entry WHERE id_field IN (SELECT id_field FROM core_customfield WHERE area_code = 'COURSE') AND id_obj = '" . $id_course . "'"))
            return false;

        //--- finally delete course from courses table -----------------------------
        if (!sql_query("DELETE FROM %lms_course WHERE idCourse = '" . $id_course . "'"))
            return false;

        //TODO: EVT_OBJECT (§)
        //$event = new \appLms\Events\Lms\CourseDeletedEvent($course);
        //TODO: EVT_LAUNCH (&)
        //\appCore\Events\DispatcherManager::dispatch($event::EVENT_NAME, $event);

        return true;
    }    
    
    
    
    /* Update the name of a course category
    *  Input param:
    *  idCategory: id of the category to update
    *  name: new name
    */
    function updateCategory($params){
        $idCategory = (isset($params['idCategory']) ? $params['idCategory'] : '');
        $name = (isset($params['name']) ? $params['name'] : '');
        
        $output =array();
        
        
        if (empty($idCategory)) {
            $output['success']=false;
            $output['message']='Missing category ID: '.$params['idCategory'];
            return $output;
        }        
        
        if (empty($name)) {
            $output['success']=false;
            $output['message']='Missing category name: '.$params['idCategory'];
            return $output;
        }         
        
        require_once(_lms_ . '/lib/category/class.categorytree.php'); 
        require_once(_base_.'/lib/lib.treedb.php');       
        $catClass = new CategoryTree();
        $classFolder = new Folder();
    
        $res =  $catClass->renameFolder( $catClass->getFolderById($idCategory), $name );
            
        if($res){
            $output['success']=true;
            $output['idCategory']=$params['idCategory'];
            $output['message']='success updating name category name '.$params['idCategory'];
            
        }else{
            $output['success']=false;
            $output['message']='Error while update: '.$params['idCategory'];
            
        }
                    
        
        
        return $output;
        
        
    }
    
    
    // delete ILT Classroom edition
    function deteleClassroom($params){
        $course_id = (isset($params['course_id']) ? $params['course_id'] : '');
        $id_date = (isset($params['id_date']) ? $params['id_date'] : '');        
        $output =array();
        

        if (empty($course_id)) {
            $output['success']=false;
            $output['message']='Missing course_id '.$course_id;
            return $output;
        }         
        
        if (empty($id_date)) {
            $output['success']=false;
            $output['message']='Missing id_date '.$id_date;
            return $output;
        }             
        
        require_once(_lms_ . '/admin/models/CourseAlms.php'); 
        $model = new ClassroomAlms($id_course, $id_date);

        $res = array('success' => $model->delClassroom());        
        
        if($res){
            $output['success']=true;
            $output['id_date']=$id_date;
            $output['message']='success delete edition classroom';            
            
        }else{
            $output['success']=false;
            $output['id_date']=$id_date;
            $output['message']='Error delete edition classroom';              
            
        }
        
        return $output;
        
    }
    
    
    /**
     * list of lo for a course
     * @param <type> $id_course 
     * GRIFO:LRZ
     */
    private function getLo($params) {

        $output = array();
        $query = "SELECT lo.title,lo.idOrg,lo.idCourse,lo.visible,lo.objectType , lc.name
                     FROM learning_organization lo, learning_course lc
                     WHERE lo.idCourse = ".$params['course_id']." and lo.idCourse=lc.idCourse";
                     
       
        $res = $this->db->query($query);
        
            $output['success'] = true;
            //$output['query'] = $query;
            while($row = $this->db->fetch_assoc($res)) {
                
                
                $output[]['lo_course']=array(
                    'nome_lo' => $row['title'],
                    'nome_corso' => $row['name'],
                    'id_item' => $row['idOrg'],
                    'id_corso' => $row['idCourse'],
                    'visibile' => $row['visible'],
                    'tipo' => $row['objectType'] ,
                    'src' => 'appLms/index.php?modname=organization&amp;op=custom_playitem&amp;id_item='.$row['idOrg'] ,
                    'id_item' => $row['idOrg']
                );
            }

        return $output;
               
        
    }     
    
    
    /**
     * answer of lerning object type test
     * @param <type> $id_course 
     * @param <type> $id_org 
     * GRIFO:LRZ
     */    
    function getAnswerTest($params){
        $output = array();
        
        // recupera TRACK della risposta del test
        $db = DbConn::getInstance();
        $qtxt ="SELECT idTrack, idTest, date_end_attempt FROM learning_testtrack where idReference=".$params['id_org']." and idUser=".$params['id_user'];
        $q =$db->query($qtxt);
        $course_info =$db->fetch_assoc($q);        
    
        $id_track = $course_info['idTrack'];           
        $id_test = $course_info['idTest'];        
        $date_end_attempt = $course_info['date_end_attempt'];   
        
        
        
        $q_test = 'select lta.idQuest, lta.idAnswer , title_quest, score_assigned  , lta.idTrack as idTrack
                    from learning_testtrack_answer lta, learning_testquest ltq
                    where lta.idTrack='.$id_track." 
                    and lta.idQuest=ltq.idQuest and lta.user_answer=1";
        
        
           
        $output['success'] = true;
        $output['id_user'] = $params['id_user'];
        $output['id_org'] = $params['id_org'];
        $output['date_end_attempt'] = $date_end_attempt;
       
        
            $qc =$db->query($q_test);
            while($row = $db->fetch_assoc($qc)) {
            
            
                    $vett_quest_answer = array();
                    $vett_quest_answer = $this->getAnswerQuest($row['idQuest']);
                
                
                
                    $res_esito = 'wrong';
                    if($row['score_assigned'] > 0) $res_esito = 'correct';
                
                    $output['quest_list'][$row['idQuest']] = array(
                                                          'id_quest' => $row['idQuest'],
                                                          'title_quest' => $row['title_quest'],
                                                          'score_assigned' => $row['score_assigned'],
                                                          'answer'  => $this->getAnswerQuest($row['idQuest'],$row['idAnswer']) ,                                    
                                                          'response' => $this->getTrackAnswer($row['idTrack'], $row['idQuest']),
                                                          'esito' => $res_esito
                                                          
                                                          );

            }         
        
        
        
        
        return $output;
        
        
    }
    

    private function getTrackAnswer($idTrack, $idQuest){
        $db = DbConn::getInstance();
        $sql = "select idAnswer, more_info from learning_testtrack_answer where idTrack=".$idTrack." and idQuest=".$idQuest;
        
        $qca =$db->query($sql);
        $output_a =  array();
        while($row_t = $db->fetch_assoc($qca)) {        
            if($row_t['idAnswer']>0) {
                $output_a[] = $row_t['idAnswer'];
            }else{
                $output_a[] = $row_t['more_info'];    
            }
            
        }    
            
        return $output_a    ;
        
        
    }
    
    
    
    
    
    function getAnswerQuest($idQuest, $idAnsw){
       $db = DbConn::getInstance();
        $out = array(); 
       $q_ans = "select idAnswer, sequence, is_correct, answer, score_correct, score_incorrect from learning_testquestanswer where idQuest=".$idQuest." order by sequence"; 

       $vett_answer = array();
    
            $qa =$db->query($q_ans);
                
            while($row_ans = $db->fetch_assoc($qa)) {
                $vett_answer = array();
                $vett_answer['id_answer'] = $row_ans['idAnswer'];
                $vett_answer['sequence'] = $row_ans['sequence'];
                $vett_answer['answer'] = $row_ans['answer'];
                $vett_answer['is_correct'] = $row_ans['is_correct'];
                
                $out[] = $vett_answer;
            
            }       
   
        
       return $out ;
    }
    
    
    
    // copia corso partendo dal course_id
    function copyCourse($params){
        
            $id_dupcourse = $params['course_id'];    
        
            // read the old course info dalla sorgente del corso selezionato
            $query_sel = "SELECT * FROM %lms_course WHERE idCourse = '".$id_dupcourse."' ";
            $result_sel = sql_query($query_sel);
            $list_sel = sql_fetch_array($result_sel);

            foreach($list_sel as $k=>$v)
                $list_sel[$k] = sql_escape_string($v);

            $new_course_dup = 0;        
        
            $new_file_array = array();        
        
        
        
             
        
            if($params['image'] == true)
            {
            
                $new_name_array = explode('_', str_replace('course_logo_', '', $list_sel['img_course']));
                //$filename = 'course_logo_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_logo_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['img_course']);
                $filename = $list_sel['img_course'];

                $new_file_array[1]['old'] = $list_sel['img_course'];
                $new_file_array[1]['new'] = $filename;
                $list_sel['img_course'] = $filename;
            }        
        
        
            if($params['advice'] == true )
            {
                $new_name_array = explode('_', str_replace('course_sponsor_logo_', '', $list_sel['imgSponsor']));
               // $filename = 'course_sponsor_logo_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_sponsor_logo_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['imgSponsor']);

                $new_file_array[0]['old'] = $list_sel['imgSponsor'];
                $new_file_array[0]['new'] = $list_sel['imgSponsor'];
                $list_sel['imgSponsor'] = $filename;
            }        
        
        
        
            // duplicate the entry of learning_course
            $query_ins = "INSERT INTO %lms_course
                ( idCategory, code, name, description, lang_code, status, level_show_user,
                subscribe_method, linkSponsor, imgSponsor, img_course, img_material, img_othermaterial,
                course_demo, mediumTime, permCloseLO, userStatusOp, difficult, show_progress, show_time, show_extra_info,
                show_rules, valid_time, max_num_subscribe, min_num_subscribe,
                max_sms_budget, selling, prize, course_type, policy_point, point_to_all, course_edition, classrooms, certificates,
                create_date, security_code, imported_from_connection, course_quota, used_space, course_vote, allow_overbooking, can_subscribe,
                sub_start_date, sub_end_date, advance, show_who_online, direct_play, autoregistration_code, use_logo_in_courselist )
                VALUES
                ( '".$list_sel['idCategory']."', '".$list_sel['code']."', '"."Copia di ".$list_sel['name']."', '".$list_sel['description']."', '".$list_sel['lang_code']."', '".$list_sel['status']."', '".$list_sel['level_show_user']."',
                '".$list_sel['subscribe_method']."', '".$list_sel['linkSponsor']."', '".$list_sel['imgSponsor']."', '".$list_sel['img_course']."', '".$list_sel['img_material']."', '".$list_sel['img_othermaterial']."',
                '".$list_sel['course_demo']."', '".$list_sel['mediumTime']."', '".$list_sel['permCloseLO']."', '".$list_sel['userStatusOp']."', '".$list_sel['difficult']."', '".$list_sel['show_progress']."', '".$list_sel['show_time']."', '".$list_sel['show_extra_info']."',
                '".$list_sel['show_rules']."', '".$list_sel['valid_time']."', '".$list_sel['max_num_subscribe']."', '".$list_sel['min_num_subscribe']."',
                '".$list_sel['max_sms_budget']."', '".$list_sel['selling']."', '".$list_sel['prize']."', '".$list_sel['course_type']."', '".$list_sel['policy_point']."', '".$list_sel['point_to_all']."', '".$list_sel['course_edition']."', '".$list_sel['classrooms']."', '".$list_sel['certificates']."',
                '".date('Y-m-d H:i:s')."', '".$list_sel['security_code']."', '".$list_sel['imported_from_connection']."', '".$list_sel['course_quota']."', '".$list_sel['used_space']."', '".$list_sel['course_vote']."', '".$list_sel['allow_overbooking']."', '".$list_sel['can_subscribe']."',
                '".$list_sel['sub_start_date']."', '".$list_sel['sub_end_date']."', '".$list_sel['advance']."', '".$list_sel['show_who_online']."', '".$list_sel['direct_play']."', '".$list_sel['autoregistration_code']."', '".$list_sel['use_logo_in_courselist']."' )";
            $result_ins = sql_query($query_ins);        
            
            // the id of the new course created
            $new_id_course = $new_course_dup = sql_insert_id();            
        
            if(!$result_ins)
            {
                ob_clean();
                ob_start();
                $output['success'] = false;
                return $output;
            }        
        
        
        //--- copy menu data -----------------------------------------------------

            // copy the old course menu into the new one
            $query_selmen = "SELECT * FROM %lms_menucourse_main WHERE idCourse = '".$id_dupcourse."' ";
            $result_selmen = sql_query($query_selmen);
            while($list_selmen = sql_fetch_array($result_selmen))
            {
                $query_dupmen = "INSERT INTO %lms_menucourse_main ".
                    " (idCourse, sequence, name, image) ".
                    " VALUES ".
                    " ( '".$new_course_dup."', '".$list_selmen['sequence']."', '".$list_selmen['name']."', '".$list_selmen['image']."' )";
                $result_dupmen = sql_query($query_dupmen);
                $array_seq[$list_selmen['idMain']] = sql_insert_id();
            }

            $query_insert_list = array();
            $query_selmenun = "SELECT * FROM %lms_menucourse_under WHERE idCourse = '".$id_dupcourse."' ";
            $result_selmenun = sql_query($query_selmenun);
            while($new_org = sql_fetch_array($result_selmenun)) {
                $valore_idn = $new_org['idMain'];
                $_idMain = $array_seq[$valore_idn];
                
                $query_insert_list[] = "('".$_idMain."', '".$new_course_dup."', '".$new_org['sequence']."', '".$new_org['idModule']."', '".$new_org['my_name']."')";
            }
            $result_dupmen = true;
            if (!empty($query_insert_list)) {
                $query_dupmen = "INSERT INTO %lms_menucourse_under
                    (idMain, idCourse, sequence, idModule, my_name)
                    VALUES ".implode(",", $query_insert_list);
                $result_dupmen = sql_query($query_dupmen);
            }        
        
        
        
        
        function &getCourseLevelSt($id_course) {
                $map         = array();
                $levels     = CourseLevel::getLevels();

                // find all the group created for this menu custom for permission management
                foreach($levels as $lv => $name_level) {
                    $group_info = Docebo::aclm()->getGroup(FALSE, '/lms/course/'.$id_course.'/subscribed/'.$lv);
                    $map[$lv]     = $group_info[ACL_INFO_IDST];
                }
                return $map;
            }

            function funAccess($functionname, $mode, $returnValue = false, $custom_mod_name = false) { return true; }

            require_once(_lms_.'/lib/lib.course.php');
            require_once(_lms_.'/lib/lib.manmenu.php');
            require_once(_lms_.'/lib/lib.subscribe.php');

            $docebo_course = new DoceboCourse($id_dupcourse);
            $subscribe_man = new CourseSubscribe_Manager();

            $group_idst =& $docebo_course->createCourseLevel($new_course_dup);
            $group_of_from  =& $docebo_course->getCourseLevel($id_dupcourse);
            $perm_form   =& createPermForCoursebis($group_of_from, $new_course_dup, $id_dupcourse);
            $levels    =  $subscribe_man->getUserLevel();

            foreach($levels as $lv => $name_level) {
                foreach($perm_form[$lv] as $idrole => $v) {
                    if($group_idst[$lv] != 0 && $idrole != 0) {
                        Docebo::aclm()->addToRole( $idrole, $group_idst[$lv] );
                    }
                }
            }        
        
        
        
            if($params['certificate']==true)
            {
                // duplicate the certificate assigned
                $query_insert_list = array();
                $query_selmenun = "SELECT * FROM %lms_certificate_course WHERE id_course = '".$id_dupcourse."' ";
                $result_selmenun = sql_query($query_selmenun);
                while($new_org = sql_fetch_assoc($result_selmenun)) {
                    $query_insert_list[] = "('".$new_org['id_certificate']."', '".$new_course_dup."', 
                        '".$new_org['available_for_status']."', '".$new_org['point_required']."' )";
                }
                $result_dupmen = true;
                if (!empty($query_insert_list)) {
                    $query_dupmen = "INSERT INTO %lms_certificate_course
                        (id_certificate, id_course, available_for_status, point_required)
                        VALUES ".implode(",", $query_insert_list);
                    $result_dupmen = sql_query($query_dupmen);
                }
            }
        
        
        
            require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
            require_once(_lms_.'/lib/lib.param.php');
            require_once(_lms_.'/class.module/track.object.php');
            require_once(_lms_.'/class.module/learning.object.php' );

            

            $nullVal = NULL;
            $id_orgs = array();
            $map_org = array();
            
            
            $output['lo'] = $params['lo'];
            
            if($params['lo']=='true' || $params['lo']==true)
            {
                
                
                $output['lo'] = true;
                
                
                $org_map = array();
                $id_orgs = array();
                $prereq_map  = array();
                
                // retrive all the folders and object, the order by grant that folder are created before the elements contained in them
                $query = "SELECT * FROM %lms_organization WHERE idCourse = ".(int)$id_dupcourse." ORDER BY path ASC";
                $source_res = sql_query($query);
                
                // Go trough all the entry of learning_organization
                while($source = sql_fetch_object($source_res)) {
                
                        // if it's an object we must make a copy, if it's a folder we can create a row
                        // inside learning_orgation and save the id for later use

                        if($source->objectType == '') {

                            // is a folder
                            // create a new row in learning_organization
                            $query_new_org = "INSERT INTO %lms_organization (
                                idParent,
                                path, lev, title,
                                objectType, idResource, idCategory, idUser,
                                idAuthor, version, difficult, description,
                                language, resource, objective, dateInsert,
                                idCourse, prerequisites, isTerminator, idParam,
                                visible, milestone)
                                VALUES
                                ('".( isset($id_orgs[ $source->idParent ]) ? $id_orgs[ $source->idParent ] : 0 )."',
                                '".$source->path."', '".$source->lev."', '".sql_escape_string($source->title)."',
                                '".$source->objectType."', '".$source->idResource."', '".$source->idCategory."', '".$source->idUser."',
                                '".$source->idAuthor."', '".$source->version."', '".$source->difficult."', '".sql_escape_string($source->description)."',
                                '".$source->language."', '".$source->resource."', '".$source->objective."', '".$source->dateInsert."',
                                '".$new_id_course."', '".$source->prerequisites."', '".$source->isTerminator."', '".$source->idParam."',
                                '".$source->visible."', '".$source->milestone."')";
                            $re_new_org = sql_query($query_new_org);
                            $new_id_reference = sql_insert_id();

                            // map for later use
                            $org_map['folder'][$source->idOrg] = $new_id_reference;
                        } else {
          
                            // is an object
                            // make a copy
                            $lo                 = $this->_createLO($source->objectType);
                            $new_id_resource    = $lo->copy($source->idResource);

                            // create a new row in learning_organization
                            $query_new_org = "INSERT INTO %lms_organization (
                                idParent, path, lev, title,
                                objectType, idResource, idCategory, idUser,
                                idAuthor, version, difficult, description,
                                language, resource, objective, dateInsert,
                                idCourse, prerequisites, isTerminator, idParam,
                                visible, milestone)
                                VALUES
                                ('".( isset($id_orgs[ $source->idParent ]) ? $id_orgs[ $source->idParent ] : 0 )."',
                                '".$source->path."', '".$source->lev."', '".sql_escape_string($source->title)."',
                                '".$source->objectType."', '".$new_id_resource."', '".$source->idCategory."', '".$source->idUser."',
                                '".$source->idAuthor."', '".$source->version."', '".$source->difficult."', '".sql_escape_string($source->description)."',
                                '".$source->language."', '".$source->resource."', '".$source->objective."', '".$source->dateInsert."',
                                '".$new_id_course."', '".$source->prerequisites."', '".$source->isTerminator."', '0',
                                '".$source->visible."', '".$source->milestone."')";
                            $re_new_org = sql_query($query_new_org);
                            $new_id_reference = sql_insert_id();

                            // for a learning_object we have to create a row in lo_param as well
                            // with 4.1 or 4.2 we plan to remove this table, but until then we need this
                            $query_lo_par  = "INSERT INTO %lms_lo_param (param_name, param_value) VALUES ('idReference', '".$new_id_reference."') ";
                            $result_lo_par = sql_query($query_lo_par);
                            $id_lo_par = sql_insert_id();

                            $query_up_lo = "UPDATE %lms_lo_param SET idParam = '".$id_lo_par."' WHERE id = '".$id_lo_par."' ";
                            $result_up_lo = sql_query($query_up_lo);

                            $query_up_or = "UPDATE %lms_organization SET idParam = '".$id_lo_par."' WHERE idOrg = '".$new_id_reference."' ";
                            $result_up_or = sql_query($query_up_or);

                            // map for later use
                            $org_map[$source->objectType][$source->idResource] = $new_id_resource;
                        }
                        // create a map for the olds and new idReferences
                        $id_orgs[$source->idOrg] = $new_id_reference;
                        if($source->prerequisites != '') $prereq_map[$new_id_reference] = $source->prerequisites;                
                
                
                }
                
                
                
                // updates prerequisites
                foreach($prereq_map as $new_id_reference => $old_prerequisites) {
                    
                    $new_prerequisites = array();
                    $old_prerequisites = explode(",", $old_prerequisites);
                    foreach($old_prerequisites as $old_p) {
                        
                        //a prerequisite can be a pure number or something like 7=NULL, or 7=incomplete
                        $old_id = intval($old_p);
                        if(isset($id_orgs[$old_id])) $new_prerequisites[] = str_replace($old_id, $id_orgs[$old_id], $old_p );
                    }
                    if(!empty($new_prerequisites)) {
                        
                        $query_updcor = "UPDATE %lms_organization "
                            ."SET prerequisites = '".implode(",", $new_prerequisites)."' "
                            ."WHERE idOrg = ".$new_id_reference." ";
                        $result_upcor = sql_query($query_updcor);
                    }
                }                
                
                
                //--- copy htmlfront data ----------------------------------------------
                $query_insert_list = array();
                $query_selmenun = "SELECT * FROM %lms_htmlfront WHERE id_course = '".$id_dupcourse."' ";
                $result_selmenun = sql_query($query_selmenun);
                while($new_org = sql_fetch_array($result_selmenun)){
                    $query_insert_list[] = "('".$new_course_dup."', '".sql_escape_string($new_org['textof'])."')";
                }
                
                $result_dupmen = true;
                if (!empty($query_insert_list)) {
                    $query_dupmen = "INSERT INTO %lms_htmlfront
                        (id_course, textof)
                        VALUES ".implode(",", $query_insert_list);
                    $result_dupmen = sql_query($query_dupmen);
                }

                //--- end htmlfront ----------------------------------------------------
                
                
                
            }    
        
        
            $output['success'] = true;
            $output['from_course_id'] = $id_dupcourse;
            $output['new_course_id'] = $new_id_course;
            $output['new_course_name'] = "Copia di ".$list_sel['name'];
            
            return $output;
        
    }
    
    
     function _createLO( $objectType, $idResource = NULL ) {
        
            $lo_types_cache = array();
            $query = "SELECT objectType, className, fileName FROM %lms_lo_types";
            $rs = sql_query( $query );
            while (list( $type, $className, $fileName ) = sql_fetch_row( $rs )) {
                $lo_types_cache[$type] = array( $className, $fileName );
            }
        
	        if (!isset($lo_types_cache[$objectType])) return NULL;
	        list( $className, $fileName ) = $lo_types_cache[$objectType];
	        require_once(_lms_.'/class.module/'.$fileName );
	        $lo =  new $className ( $idResource );
	        return $lo;
    }    
    
           
           

// assign meta certificate & course to user
    function assignMetaUser($params){
        
        require_once(Forma::inc(_lms_.'/'._folder_lib_.'/lib.aggregated_certificate.php'));

        $aggCertLib = new AggregatedCertificate();
        
        $meta_cert_id = (isset($params['meta_cert_id']) ? $params['meta_cert_id'] : '');
        $meta_user_id = (isset($params['meta_user_id']) ? $params['meta_user_id'] : '');  
        $meta_course_id = (isset($params['meta_course_id']) ? $params['meta_course_id'] : '');       
        $meta_type_course = (isset($params['meta_type_course']) ? $params['meta_type_course'] : 'COURSE'); 
        
        $output =array();

        if (empty(meta_cert_id)) {
            $output['success']=false;
            $output['message']='Missing meta_cert_id '.$meta_cert_id;
            return $output;
        }         
        
        if (empty($meta_user_id)) {
            $output['success']=false;
            $output['message']='Missing meta_user_id '.$meta_user_id;
            return $output;
        }             
        
        if (empty($meta_course_id)) {
            $output['success']=false;
            $output['message']='Missing meta_course_id '.$meta_course_id;
            return $output;
        } 
        
        // get value course & user
        $vett_user = explode(",",$meta_user_id);
        $vett_course = explode(",",$meta_course_id);
        
        $output['success']=true;
        
         
        //-----------------------------------

        foreach($vett_user as $keyUser => $valueUser)
            $userArr[$valueUser] = $vett_course;
        
        $assocArr[$meta_cert_id] = $userArr;
        $assocArr['type_assoc']=0;
        $assocArr['id_assoc']=$meta_cert_id; 
        
        
        // TO DO RIVIEW  - not working
        //$aggCertLib->insertAssociationLink(COURSE, $assocArr);
        
        foreach($vett_course as $keyCourse => $valueCourse){  
            
            // assign course to user by meta cert id
            try {
                
                if($meta_type_course=='COURSE'){
                    $query_meta = "INSERT INTO %lms_aggregated_cert_course (idAssociation, idUser, idCourse,idCourseEdition) 
                                     VALUES (".$meta_cert_id.",0,".$valueCourse.",0)";                                     
                } else{
                    $query_meta = "INSERT INTO %lms_aggregated_cert_coursepath (idAssociation, idUser, idCoursePath) 
                                     VALUES (".$meta_cert_id.",0,".$valueCourse.")"; 
                    
                    
                }                
                                 
                                 
                $result_meta = sql_query($query_meta);
            } catch(Exception $e) {
              $output['success']=false;
            }                
            
        }         
                 
        foreach($vett_user as $keyUser => $valueUser){
            foreach($vett_course as $keyCourse => $valueCourse){   
                
                // assign course to user by association cert id
                try {
                    
                    if($meta_type_course=='COURSE'){
                        $query_meta = "INSERT INTO %lms_aggregated_cert_course(idAssociation, idUser, idCourse,idCourseEdition) 
                                         VALUES (".$meta_cert_id.",".$valueUser.",".$valueCourse.",0)";  
                    }else{
                        $query_meta = "INSERT INTO %lms_aggregated_cert_coursepath(idAssociation, idUser, idCoursePath) 
                                         VALUES (".$meta_cert_id.",".$valueUser.",".$valueCourse.")"; 
   
                    }                 
                                     
                                     
                                                                        
                    $result_meta = sql_query($query_meta);
                } catch(Exception $e) {
                  $output['success']=false;
                }                
                
            }    
            
        }

        
        return $output;
        
    }
     
    // add association to meta certificate
    function addAssociationCertificate($params){
        
        require_once(Forma::inc(_lms_.'/'._folder_lib_.'/lib.aggregated_certificate.php'));
        $aggCertLib = new AggregatedCertificate();
        
        
          $output =array();
          $output['success']=true;
        

        $meta_cert_id = (isset($params['meta_cert_id']) ? $params['meta_cert_id'] : '');
        $name_ass = (isset($params['name_ass']) ? $params['name_ass'] : '');
        $descr_ass = (isset($params['descr_ass']) ? $params['descr_ass'] : '');
     
        if (empty($meta_cert_id)) {
            $output['success']=false;
            $output['message']='Missing meta_cert_id '.$meta_cert_id;
            return $output;
        } 

        if (empty($name_ass)) {
            $output['success']=false;
            $output['message']='Missing name_ass '.$name_ass;
            return $output;
        }        
        
           // add association to meta cert id
            try {
                $query_meta = "INSERT INTO %lms_aggregated_cert_metadata ( idCertificate, title, description) 
                                 VALUES (".$meta_cert_id.",'".$name_ass."','".$descr_ass."')";                                     
                $result_meta = sql_query($query_meta);
                
                
                // get id new association
                $query_association = "select max(idAssociation) as id_association from %lms_aggregated_cert_metadata";
                $qres = sql_query($query_association);
                list($id_association) = sql_fetch_row($qres);                
                
                $output['id_new_association'] = $id_association;
                
                
            } catch(Exception $e) {       
              $output['success']=false;
            }           
                                
        return $output;
        
    }             
           
           
           

    /**
     * put introduction of course    
     * @param <type> course_id 
     * @param <type> text_intro 
     * GRIFO:LRZ
     */      
    public function putIntroductionCourse($params){
        $output =array();
        $output['success']=true;
        
        $course_id = (isset($params['course_id']) ? $params['course_id'] : '');
        $text_intro = (isset($params['text_intro']) ? $params['text_intro'] : '');
        
        if (empty($course_id)) {
            $output['success']=false;
            $output['message']='Missing course_id '.$course_id;
            return $output;
        }    
       
        // get exist record intro-course
        $sql_exist = "select count(id_course) as exist from learning_htmlfront where id_course=".$params['course_id'];
        $qres = sql_query($sql_exist);
        list($exist) = sql_fetch_row($qres); 

        if($exist==0){
            // insert
            $sql_intro = "insert into learning_htmlfront (id_course, textof) values (".$params['course_id'].",'".(($params['text_intro']))."')";
            
        }else{
            // update
            $sql_intro = "update learning_htmlfront set textof='".($params['text_intro'])."'   where id_course=".$params['course_id'];
        }
        
        try {
            $q_intro = sql_query($sql_intro);
        } catch(Exception $e) {
              $output['success']=false;
        }          
        
        
        $output['course_id']=$course_id;
        
        return $output;
        
    }
    
    /**
     * copy image cover from another course    
     * @param <type> course_id_from 
     * @param <type> course_id_to
     * GRIFO:LRZ
     */     
    public function copyImgFromCourse($params){
        $output =array();
        $output['success']=true;
        
        $course_id_from = (isset($params['course_id_from']) ? $params['course_id_from'] : '');
        $course_id_to = (isset($params['course_id_to']) ? $params['course_id_to'] : '');
        
        if (empty($course_id_to)) {
            $output['success']=false;
            $output['message']='Missing course_id_to '.$course_id_to;
            return $output;
        }

        // if $course_id_from equal zero, l'img of course destination is image default
        $img_course='';
        
        // get exist image course source
        if($course_id_from>0){
            $sql_img = "select img_course from learning_course where idCourse=".$params['course_id_from'];
            $qres = sql_query($sql_img);
            list($img_course) = sql_fetch_row($qres);         
        }
            
        // associate img_course to course destination
        $sql_img = "update learning_course set img_course = '".$img_course."' where idCourse=".$params['course_id_to'];
        
       try {
            $q_img = sql_query($sql_img);
        } catch(Exception $e) {
              $output['success']=false;
        }        
        
        
        $output['course_id_from'] = $course_id_from;
        $output['course_id_to'] = $course_id_to;
        
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


			//e-learning editions
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


            // LRZ 
            // CATEGORY API 
            case 'addCategory':
            case 'addcategory': {
                $output = $this->addCategory($params);
            } break;

            case 'updateCategory':
            case 'updatecategory': {
                $output = $this->updateCategory($params);
            } break;            
            

            // COURSE API
            // add elearning or ILT course
            case 'addCourse':
            case 'addcourse': {
                $output = $this->addCourse($params);
            } break;
            
            case 'updateCourse':
            case 'updatecourse': {
                $output = $this->updateCourse($params);
            } break; 
            
            case 'deleteCourse':             
            case 'deletecourse': {
                $output = $this->deleteCourse($params);
            } break; 
            
            case 'copyCourse':
            case 'copycourse':{
                $output = $this->copyCourse($_POST);
                
            }break;               
                                                               
            
            
            // CLASSROOM (ILT) API
            // add (ILT) classroom edition
            case 'addClassroom':
            case 'addclassroom': {
                $output = $this->addClassroom($params);

            } break;

             // update (ILT) classroom edition
            case 'updateClassroom':
            case 'updateclassroom': {
                $output = $this->updateClassroom($params);
            } break;
            
            case 'deleteClassroom':
            case 'deleteclassroom': {
                $output = $this->deteleClassroom($params);
            } break;             
            
            
            // add appointment for classroom edition
            case 'addDay':
            case 'addday': {
                $output = $this->addDay($params);
            } break;            
            
            // delete appointment for classroom edition
            case 'delDay':
            case 'delday': {
                $output = $this->delDay($params);
            } break;            
        
            
            // update appointment for classroom edition
            case 'upDay':
            case 'upday': {
                $output = $this->upDay($params);
            } break;   
            
            
            // LO API
            case 'getLO':
            case 'getlo': {         
                $output = $this->getLo($_POST);
            } break;               
            
            case 'getAnswerTest':
            case 'getanswertest':{
                $output = $this->getAnswerTest($_POST);
                
            }break;               

            //META CERTIFICATE 
            case 'assignMetaUser':
            case 'assignmetauser':{
                $output = $this->assignMetaUser($_POST);
            }break;        
            
            case 'addCertificate':
            case 'addcertificate':{
                $output = $this->addAssociationCertificate($_POST);                
        
            }break;             
            
           // manage introduction module of course
           case 'putintroductioncourse':
           case 'putIntroductionCourse':{
                $output = $this->putIntroductionCourse($_POST);
               
           } break;
           
           // copy image from course source
           case 'copyimgfromcourse':
           case 'copyImgFromCourse':{
                $output = $this->copyImgFromCourse($_POST);
           }
           
           
           
           
            
			default: $output = parent::call($name, $params);
		}
		return $output;
	}

}