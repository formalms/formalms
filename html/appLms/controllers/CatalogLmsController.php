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

class CatalogLmsController extends LmsController {

	public $name = 'catalog';

	private $path_course = '';

	protected $_default_action = 'show';

	var $model;
	var $json;
	var $acl_man;

	public function isTabActive($tab_name)
	{
		return true;
	}

	public function init()
	{
		YuiLib::load('base,tabview');
		Lang::init('course');
		$this->path_course = $GLOBALS['where_files_relative'].'/appLms/'.Get::sett('pathcourse').'/';
		$this->model = new CatalogLms();

		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();

		$this->acl_man =& Docebo::user()->getAclManager();
	}

	// displays header and catalogue tree
    public function show()
	{
        $id_catalogue = Get::req('id_catalogue', DOTY_INT, 0);
        $typeCourse = Get::req('type_course', DOTY_STRING, '');
        $user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
        echo '<div class="middlearea_container middlearea_container--margintop-small">';

        $this->render('catalog_header', array("user_catalogue" => $user_catalogue, 'catalogue_exist'=>$catalogue_exist));
        $this->render('catalog_tree', array('model' => $this->model, 'user_catalogue' => $user_catalogue, 'id_catalogue' => $id_catalogue, 'catalogue_exist'=>$catalogue_exist));
                      
        echo '</div>';
	}


    
    // AJAX: display courses from selected catalogue, category, courses
    public function allCourseForma()
    {
  
       $id_category = Get::req('id_category', DOTY_INT, 0);     
       $typeCourse = Get::req('type_course', DOTY_STRING, '');
       $val_enroll = Get::req('val_enroll', DOTY_STRING, '');
       $val_enroll_not = Get::req('val_enroll_not', DOTY_STRING, '');
       
       $id_catalogue = Get::req('id_catalogue', DOTY_INT, 0);          
       $model = new CatalogLms();       
              
       $result = $this->model->getCourseList($typeCourse,1,$id_catalogue, $id_category);
       $this->render('courselist', array( "result" => $result, "smodel"=>$model));

    }    
    
    
    
	public function newCourse()
	{
		require_once(_base_.'/lib/lib.navbar.php');
		$active_tab = 'new';

		$page = Get::req('page', DOTY_INT, 1);
		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$nav_bar = new NavBar('page', Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

		$nav_bar->setLink('index.php?r=catalog/newCourse'.($id_cat > 1 ? '&amp;id_cat='.$id_cat : ''));

		$html = $this->model->getCourseList($active_tab, $page);
		$user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
		$user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

		echo '<div class="middlearea_container">';

		$lmstab = $this->widget('lms_tab', array(
								'active' => 'catalog',
								'close' => false));

		$this->render('tab_start', array(	'user_catalogue' => $user_catalogue,
											'active_tab' => $active_tab,
											'user_coursepath' => $user_coursepath,
											'std_link' => 'index.php?r=catalog/newCourse'.($page > 1 ? '&amp;page='.$page : ''),
											'model' => $this->model)); 
		$this->render('courselist', array(	'html' => $html,
											'nav_bar' => $nav_bar));
		$this->render('tab_end', array(	'std_link' => 'index.php?r=catalog/newCourse'.($page > 1 ? '&amp;page='.$page : ''),
										'model' => $this->model));
		$lmstab->endWidget();

		echo '</div>';
	}

	public function elearningCourse()
	{
		require_once(_base_.'/lib/lib.navbar.php');
		$active_tab = 'elearning';

		$page = Get::req('page', DOTY_INT, 1);
		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$nav_bar = new NavBar('page', Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

		$nav_bar->setLink('index.php?r=catalog/elearningCourse'.($id_cat > 1 ? '&amp;id_cat='.$id_cat : ''));

		$html = $this->model->getCourseList($active_tab, $page);
		$user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
		$user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

		echo '<div class="middlearea_container">';

		$lmstab = $this->widget('lms_tab', array(
								'active' => 'catalog',
								'close' => false));

		$this->render('tab_start', array(	'user_catalogue' => $user_catalogue,
											'active_tab' => $active_tab,
											'user_coursepath' => $user_coursepath,
											'std_link' => 'index.php?r=catalog/elearningCourse'.($page > 1 ? '&amp;page='.$page : ''),
											'model' => $this->model));
		$this->render('courselist', array(	'html' => $html,
											'nav_bar' => $nav_bar));
		$this->render('tab_end', array(	'std_link' => 'index.php?r=catalog/elearningCourse'.($page > 1 ? '&amp;page='.$page : ''),
										'model' => $this->model));
		$lmstab->endWidget();

		echo '</div>';
	}

	public function classroomCourse()
	{
		require_once(_base_.'/lib/lib.navbar.php');
		$active_tab = 'classroom';

		$page = Get::req('page', DOTY_INT, 1);
		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$nav_bar = new NavBar('page', Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

		$nav_bar->setLink('index.php?r=catalog/classroomCourse'.($id_cat > 1 ? '&amp;id_cat='.$id_cat : ''));

		$html = $this->model->getCourseList($active_tab, $page);
		$user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
		$user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

		echo '<div class="middlearea_container">';

		$lmstab = $this->widget('lms_tab', array(
								'active' => 'catalog',
								'close' => false));

		$this->render('tab_start', array(	'user_catalogue' => $user_catalogue,
											'active_tab' => $active_tab,
											'user_coursepath' => $user_coursepath,
											'std_link' => 'index.php?r=catalog/classroomCourse'.($page > 1 ? '&amp;page='.$page : ''),
											'model' => $this->model));
		$this->render('courselist', array(	'html' => $html,
											'nav_bar' => $nav_bar));
		$this->render('tab_end', array(	'std_link' => 'index.php?r=catalog/classroomCourse'.($page > 1 ? '&amp;page='.$page : ''),
										'model' => $this->model));
		$lmstab->endWidget();

		echo '</div>';
	}

	public function catalogueCourse()
	{
		require_once(_base_.'/lib/lib.navbar.php');
		$id_cata = Get::req('id_cata', DOTY_INT, 0);
		$active_tab = 'catalogue';

		$page = Get::req('page', DOTY_INT, 1);
		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$nav_bar = new NavBar('page', Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

		$nav_bar->setLink('index.php?r=catalog/catalogueCourse&amp;id_cata='.$id_cata.($id_cat > 1 ? '&amp;id_cat='.$id_cat : ''));

		$html = $this->model->getCourseList($active_tab, $page);
		$user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
		$user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

		echo '<div class="middlearea_container">';

		$lmstab = $this->widget('lms_tab', array(
								'active' => 'catalog',
								'close' => false));

		$this->render('tab_start', array(	'user_catalogue' => $user_catalogue,
											'active_tab' => $active_tab.'_'.$id_cat,
											'user_coursepath' => $user_coursepath,
											'std_link' => 'index.php?r=catalog/catalogueCourse&amp;id_cata='.$id_cata.($page > 1 ? '&amp;page='.$page : ''),
											'model' => $this->model));
		$this->render('courselist', array(	'html' => $html,
											'nav_bar' => $nav_bar));
		$this->render('tab_end', array(	'std_link' => 'index.php?r=catalog/catalogueCourse&amp;id_cata='.$id_cata.($page > 1 ? '&amp;page='.$page : ''),
										'model' => $this->model));
		$lmstab->endWidget();

		echo '</div>';
	}

	public function coursepathCourse()
	{
		require_once(_base_.'/lib/lib.navbar.php');
		$active_tab = 'coursepath';

		$nav_bar = new NavBar('page', Get::sett('visuItem'), count($this->model->getUserCoursepath(Docebo::user()->getIdSt())), 'link');

		$nav_bar->setLink('index.php?r=catalog/coursepathCourse');

		$page = Get::req('page', DOTY_INT, 1);

		$html = $this->model->getCoursepathList(Docebo::user()->getIdSt(), $page);
		$user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
		$user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

		echo '<div class="layout_colum_container">';

		$lmstab = $this->widget('lms_tab', array(
								'active' => 'catalog',
								'close' => false));

		$this->render('tab_start', array(	'user_catalogue' => $user_catalogue,
											'active_tab' => $active_tab,
											'user_coursepath' => $user_coursepath));
		$this->render('courselist', array(	'html' => $html,
											'nav_bar' => $nav_bar));
		$this->render('tab_end', array());
		$lmstab->endWidget();

		echo '</div>';
	}

	public function calendarCourse()
	{
		$active_tab = 'calendar';
		$user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
		$user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

		echo '<div class="layout_colum_container">';

		$lmstab = $this->widget('lms_tab', array(
								'active' => 'catalog',
								'close' => false));

		$this->render('tab_start', array(	'user_catalogue' => $user_catalogue,
											'active_tab' => $active_tab,
											'user_coursepath' => $user_coursepath));
		$this->render('calendar', array());
		$this->render('tab_end', array());
		$lmstab->endWidget();

		echo '</div>';
	}

	public function subscribeInfo()
	{
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);
		$selling = Get::req('selling', DOTY_INT, 0);

		$res = $this->model->subscribeInfo($id_course, $id_date, $id_edition, $selling);

		echo $this->json->encode($res);
	}

	public function subscribeCoursePathInfo() {
		$id_path = Get::req('id_path', DOTY_INT, 0);

		$res = $this->model->subscribeCoursePathInfo($id_path);

		echo $this->json->encode($res);
	}

	public function courseSelection()
	{
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$selling = Get::req('selling', DOTY_INT, 0);

		$res = $this->model->courseSelectionInfo($id_course, $selling);

		echo $this->json->encode($res);
	}

	//UG  select a user subscription level
	function get_userlevel_subscription($idu ) {

		$level = 3;		// default subscription level = Student
		$reg_code = '';
		$reg_code = Get::cfg('registration_code_gu','');
		if ( Get::cfg('register_type_guestuser') && $reg_code != '' ) {
			$uma = new UsermanagementAdm();
			$array_folder = $uma->getFoldersFromCode($reg_code);
		  	$userfolders = $uma->getUserFoldersCode($idu);
			if ( in_array($reg_code,$userfolders) ) {
				// it's a guest user , register to guest level
				$level = 1;			// Guest user level subscription = Guest
			}
		}
	  	return($level);
	}

	public function subscribeToCourse()
	{
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);

		$id_user = Docebo::user()->getIdSt();

		$docebo_course = new DoceboCourse($id_course);

		require_once(_lms_.'/admin/models/SubscriptionAlms.php');
		$model = new SubscriptionAlms($id_course, $id_edition, $id_date);

		$course_info = $model->getCourseInfoForSubscription();
		$userinfo = $this->acl_man->getUser($id_user);

		$level_idst =& $docebo_course->getCourseLevel($id_course);

		if(count($level_idst) == 0 || $level_idst[1] == '')
			$level_idst =& $docebo_course->createCourseLevel($id_course);

		$waiting = 0;

		if($course_info['subscribe_method'] != 2)
			$waiting = 1;

		$userlevel_subscrip = $this->get_userlevel_subscription($id_user);	//UG
		
//UG		$this->acl_man->addToGroup($level_idst[3], $id_user);
		$this->acl_man->addToGroup($level_idst[$userlevel_subscrip], $id_user);	//UG

//UG		if($model->subscribeUser($id_user, 3, $waiting))
		if($model->subscribeUser($id_user, $userlevel_subscrip, $waiting))		//UG
		{
			$res['success'] = true;
			$res['new_status_code'] = '';

			if($id_edition != 0 || $id_date != 0)
			{
				$must_change_status = $this->model->controlSubscriptionRemaining($id_course);
				$res['new_status'] = '';

				if(!$must_change_status)
					$res['new_status'] = '<p class="cannot_subscribe">'.Lang::t('_NO_EDITIONS', 'catalogue').'</p>';
			}
			else
			{
				if($waiting == 1) {
					$res['new_status'] = '<p class="cannot_subscribe">'.Lang::t('_WAITING', 'catalogue').'</p>';
					$res['new_status_code'] = 'waiting';
				} else {
					$res['new_status'] = '<p class="subscribed">'.Lang::t('_USER_STATUS_ENTER', 'catalogue').'</p>';
					$res['new_status_code'] = 'subscribed';
                }
			}
			
			
			$array_subst = array(	'[url]' => Get::site_url(),
				'[course]' => $course_info['name'],
				'[firstname]' => $userinfo[ACL_INFO_FIRSTNAME] ,
				'[lastname]' => $userinfo[ACL_INFO_LASTNAME]  );			

			// message to user that is waiting
			require_once(_base_.'/lib/lib.eventmanager.php');
			$msg_composer = new EventMessageComposer('subscribe', 'lms');

			$msg_composer->setSubjectLangText('email', '_NEW_USER_SUBS_WAITING_SUBJECT', false);
			$msg_composer->setBodyLangText('email', '_NEW_USER_SUBS_WAITING_TEXT', $array_subst);

			$msg_composer->setSubjectLangText('sms', '_NEW_USER_SUBS_WAITING_SUBJECT_SMS', false);
			$msg_composer->setBodyLangText('sms', '_NEW_USER_SUBS_WAITING_TEXT_SMS', $array_subst);

			$acl =& Docebo::user()->getAcl();
			$acl_man =& $this->acl_man;

			$recipients = array();

			$idst_group_god_admin = $acl->getGroupST(ADMIN_GROUP_GODADMIN);
			$recipients = $acl_man->getGroupMembers($idst_group_god_admin);
			$idst_group_admin = $acl->getGroupST(ADMIN_GROUP_ADMIN);
			$idst_admin = $acl_man->getGroupMembers($idst_group_admin);

			require_once(_adm_.'/lib/lib.adminmanager.php');

			foreach($idst_admin as $id_user) {
				$adminManager = new AdminManager();
				$acl_manager = & $acl_man;

				$idst_associated = $adminManager->getAdminTree($id_user);

				$array_user =& $acl_manager->getAllUsersFromIdst($idst_associated);

				$array_user = array_unique($array_user);

				$array_user[] = $array_user[0];
				unset($array_user[0]);
 
				$control_user = array_search(getLogUserId(), $array_user);

				$query =	"SELECT COUNT(*)"
							." FROM ".Get::cfg('prefix_fw')."_admin_course"
							." WHERE idst_user = '".$id_user."'"
							." AND type_of_entry = 'course'"
							." AND id_entry = '".$id_course."'";

				list($control_course) = sql_fetch_row(sql_query($query));

				/*if($control)
					$recipients[] = $id_user;*/

				$query =	"SELECT COUNT(*)"
							." FROM ".Get::cfg('prefix_fw')."_admin_course"
							." WHERE idst_user = '".$id_user."'"
							." AND type_of_entry = 'coursepath'"
							." AND id_entry IN"
							." ("
							." SELECT id_path"
							." FROM ".Get::cfg('prefix_lms')."_coursepath_courses"
							." WHERE id_item = '".$id_course."'"
							." )";

				list($control_coursepath) = sql_fetch_row(sql_query($query));

				/*if($control)
					$recipients[] = $id_user;*/

				$query =	"SELECT COUNT(*)"
							." FROM ".Get::cfg('prefix_fw')."_admin_course"
							." WHERE idst_user = '".$id_user."'"
							." AND type_of_entry = 'catalogue'"
							." AND id_entry IN"
							." ("
							." SELECT idCatalogue"
							." FROM ".Get::cfg('prefix_lms')."_catalogue_entry"
							." WHERE idEntry = '".$id_course."'"
							." )";

				list($control_catalogue) = sql_fetch_row(sql_query($query));

				if($control_user && ($control_course || $control_coursepath || $control_catalogue))
					$recipients[] = $id_user;
			}

			$recipients = array_unique($recipients);

			createNewAlert(	'UserCourseInsertModerate', 'subscribe', 'insert', '1', 'User subscribed with moderation', $recipients, $msg_composer );

			$res['message'] = UIFeedback::info(Lang::t('_SUBSCRIPTION_CORRECT', 'catalogue'), true);
		}
		else
		{
			$this->acl_man->removeFromGroup($level_idst[3], $id_user);
			$res['success'] = false;

			$res['message'] = UIFeedback::error(Lang::t('_SUBSCRIPTION_ERROR', 'catalogue'), true);
		}

		echo $this->json->encode($res);
	}

	public function subscribeToCoursePath()
	{
		$id_path = Get::req('id_path', DOTY_INT, 0);

		$id_user = Docebo::user()->getIdSt();

		$query_pathlist = "
		SELECT path_name, subscribe_method
		FROM ".$GLOBALS['prefix_lms']."_coursepath
		WHERE id_path = '".$id_path."'
		ORDER BY path_name ";
		list($path_name, $subscribe_method) = sql_fetch_row(sql_query($query_pathlist));


		if($subscribe_method == 1) $waiting = 1;
		else $waiting = 0;
		$text_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_coursepath_user
			( id_path, idUser, waiting, subscribed_by ) VALUES
			( '".$id_path."', '".$id_user."', '".$waiting."', '".getLogUserId()."' )";
		$re_s = sql_query($text_query);

		/////////////////////////

		if($waiting == 0) {
			require_once(_lms_.'/lib/lib.subscribe.php');
			require_once(_lms_.'/lib/lib.coursepath.php');

			$cpath_man = new CoursePath_Manager();
			$subs_man = new CourseSubscribe_Management();

			$id_path = Get::req('id_path', DOTY_INT, 0);
			$user_selected = Util::unserialize(urldecode(Get::req('users', DOTY_MIXED, array())));

			$courses = $cpath_man->getAllCourses(array($id_path));

			$users_subsc = array($id_user);

			$re &= $subs_man->multipleSubscribe($users_subsc, $courses, 3);
		}

		$res['success'] = true;
		if($waiting == 1)
			$res['new_status'] = '<p class="cannot_subscribe">'.Lang::t('_WAITING', 'catalogue').'</p>';
		else
			$res['new_status'] = '<p class="cannot_subscribe">'.Lang::t('_USER_STATUS_SUBS', 'catalogue').'</p>';

		$res['message'] = $res['message'] = UIFeedback::info(Lang::t('_SUBSCRIPTION_CORRECT', 'catalogue'), true);

		echo $this->json->encode($res);
	}

	public function addToCart()
	{
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);

		if($id_edition != 0)
			$_SESSION['lms_cart'][$id_course]['edition'][$id_edition] = $id_edition;
		elseif($id_date != 0)
			$_SESSION['lms_cart'][$id_course]['classroom'][$id_date] = $id_date;
		else
			$_SESSION['lms_cart'][$id_course] = $id_course;

		$res['success'] = true;
		$res['message'] = UIFeedback::info(Lang::t('_COURSE_ADDED_IN_CART', 'catalogue'), true);

		if($id_edition != 0 || $id_date != 0)
		{
			$must_change_status = $this->model->controlSubscriptionRemaining($id_course);
			$res['new_status'] = '';

			if(!$must_change_status)
				$res['new_status'] = '<p class="cannot_subscribe">'.Lang::t('_ALL_EDITION_BUYED', 'catalogue').'</p>';
		}
		else
			$res['new_status'] = '<p class="cannot_subscribe">'.Lang::t('_COURSE_IN_CART', 'catalogue').'</p>';

		require_once(_lms_.'/lib/lib.cart.php');

		$res['cart_element'] = ''.Learning_Cart::cartItemCount().'';
		$res['num_element'] = Learning_Cart::cartItemCount();
		$res['cart_message'] = Lang::t('_COURSE_ADDED_IN_CART', 'catalogue');

		echo $this->json->encode($res);
	}


	function downloadDemoMaterialTask() {
		require_once(_base_.'/lib/lib.download.php');

		$id =Get::gReq('course_id', DOTY_INT);
		$db =DbConn::getInstance();

		$qtxt ="SELECT course_demo FROM %lms_course WHERE idCourse=".$id;

		$q =$db->query($qtxt);
		list($fname) =$db->fetch_row($q);

		if (!empty($fname)) {
			sendFile('/appLms/course/', $fname);
		}
		else {
			echo "nothing found";
		}
		die();
	}
    
        public function self_unsubscribe()
    {
        $id_user = Docebo::user()->idst;
        $id_course = Get::req('id_course', DOTY_INT, 0);
        $id_edition = Get::req('id_edition', DOTY_INT, 0);
        $id_date = Get::req('id_date', DOTY_INT, 0);

        $cmodel = new CourseAlms();
        $cinfo = $cmodel->getCourseModDetails($id_course);


        if ($cinfo['auto_unsubscribe'] == 0) {
            //no self unsubscribe possible for this course
            return '_NO_SELF_UNSUBSCRIBE_POSSIBLE';
            
        }

        $date_ok = TRUE;
        if ($cinfo['unsubscribe_date_limit'] !== '' && $cinfo['unsubscribe_date_limit'] !== '0000-00-00 00:00:00' && $cinfo['unsubscribe_date_limit'] !== NULL) {
            if ($cinfo['unsubscribe_date_limit'] < date('Y-m-d H:i:s')) {
                //self unsubscribing is no more allowed, go back to courselist page
                return '_SELF_UNSUBSCRIBE_NO_MORE_ALLOWED';
                
            }
        }

        $smodel = new SubscriptionAlms();
        $param = '';

        if ($cinfo['auto_unsubscribe'] == 1) {
            //moderated self unsubscribe
            $res = $smodel->setUnsubscribeRequest($id_user, $id_course, $id_edition, $id_date);

        }

        if ($cinfo['auto_unsubscribe'] == 2) {
            //directly unsubscribe user
            $res = $smodel->unsubscribeUser($id_user, $id_course, $id_edition, $id_date);

        }
        
        if ($res) {
            $this->allCourseForma();
        } else {
            return 'error';
        }


    }
    

}
