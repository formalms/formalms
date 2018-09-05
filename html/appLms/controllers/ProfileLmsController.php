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

if (Docebo::user()->isAnonymous()) die("You can't access!");

class ProfileLmsController extends LmsController {

	protected $db;
	protected $model;
	protected $json;
	protected $aclManager;

	protected $max_dim_avatar;

	public function init() {
		require_once(_base_.'/lib/lib.json.php');
		$this->db = DbConn::getInstance();
		$this->model = new ProfileLms();
		$this->json = new Services_JSON();
		$this->aclManager = Docebo::user()->getAClManager();
		$this->max_dim_avatar = 150;
	}


	protected function _profileBackUrl()	{
		$id_user = Get::req('id_user', DOTY_INT, 0);
		$type = Get::req('type', DOTY_STRING, 'false');
		$from = Get::req('from', DOTY_INT, 0);
		$back_my_friend = Get::req('back', DOTY_INT, 0);
		if ($type !== 'false')
			if ($from == 0)
				return getBackUi('index.php?modname=profile&op=profile&id_user='.$id_user.'&ap=goprofile', Lang::t('_BACK', 'standard'));
			else
				return getBackUi('index.php?modname=myfiles&op=myfiles&working_area='.$type, Lang::t('_BACK', 'standard'));
		if ($back_my_friend)
			return getBackUi('index.php?modname=myfriends&op=myfriends', Lang::t('_BACK', 'standard'));
		return false;
	}

	public function show() {

		if (!defined("LMS")) {
			checkRole('/lms/course/public/profile/view', false);
		} else {
			checkPerm('view', false, 'profile', 'lms');
		}

		/** Force show lms_user template */
        $_SESSION['layoutToRender'] = Layout::LAYOUT_LMS_USER;

		require_once(_lms_.'/lib/lib.lms_user_profile.php');

		$id_user = Docebo::user()->getIdST();
		$profile = new LmsUserProfile($id_user);
		$profile->init('profile', 'framework', 'r=lms/profile/show'/*&id_user'.(int)$id_user*/, 'ap');//'modname=profile&op=profile&id_user='.$id_user

		$_check = false;
		if (!defined("LMS")) {
			$_check = checkRole('/lms/course/public/profile/mod', true);
		} else {
			$_check = checkPerm('mod', true, 'profile', 'lms');
		}
		if ($_check) $profile->enableEditMode();

		//evento mostra profilo
		$event = new \appLms\Events\Lms\UserProfileShowEvent();
		$event->setProfile($profile);
		\appCore\Events\DispatcherManager::dispatch(\appLms\Events\Lms\UserProfileShowEvent::EVENT_NAME, $event);

		//view part
		if(Get::sett('profile_only_pwd') == 'on') {

			echo $profile->getTitleArea();
			echo $profile->getHead();
			echo $profile->performAction(false, 'mod_password');
			echo $this->_profileBackUrl();
			echo $profile->getFooter();

		} else {

			echo $profile->getTitleArea();
			echo $profile->getHead();
			echo $profile->performAction();
			echo $this->_profileBackUrl();
			echo $profile->getFooter();

		}
	}

	function renewalpwd() {
		require_once(Forma::inc(_base_ . '/lib/lib.usermanager.php'));
		$user_manager = new UserManager();

		$_title = "";
		$_error_message = "";
		$_content = "";

		$url = 'index.php?r=lms/profile/renewalpwd';//'index.php?modname=profile&amp;op=renewalpwd'

		if ($user_manager->clickSaveElapsed()) {
			$error = $user_manager->saveElapsedPassword();
			if ($error['error'] == true) {
				$res = Docebo::user()->isPasswordElapsed();
				
				if ($res == 2)
					$_title = getTitleArea(Lang::t('_CHANGEPASSWORD', 'profile'));
				else
					$_title = getTitleArea(Lang::t('_TITLE_CHANGE', 'profile'));

				$_error_message = $error['msg'];
				$_content = $user_manager->getElapsedPassword($url);

			} else {
				unset($_SESSION['must_renew_pwd']);
				//Util::jump_to('index.php?r=lms/profile/show');
				$user = new DoceboUser(Docebo::user()->getUserId(), 'public_area');
				$homepageAdm = new HomepageAdm();
				switch ($homepageAdm->saveUser($user)) {
					case MANDATORY_FIELDS:
						$_SESSION['request_mandatory_fields_compilation'] = 1;
						break;
					case USER_SAVED:
						break;
				}
				Util::jump_to('index.php');
			}

		} else {
				$_SESSION['must_renew_pwd'] = 1;
				$res = Docebo::user()->isPasswordElapsed();
				if ($res == 2) 
					$_title = getTitleArea(Lang::t('_CHANGEPASSWORD', 'profile'));
				else
					$_title = getTitleArea(Lang::t('_TITLE_CHANGE', 'profile'));
				$_content = $user_manager->getElapsedPassword($url);
				

		}
		
		//view part
		echo $_title.'<div class="std_block">'.$_error_message.$_content.'</div>';
	}

    public function credits() {
        require_once(_lms_.'/lib/lib.course.php');
//		$str = '<h2 class="heading">' . Lang::t ( '_CREDITS', 'catalogue' ) . '</h2>' . '<div class="content">';
        $str = '';
        $period_start = '';
        $period_end = '';

        // extract checking period
        $year = date ( "Y" );
        $p_list = array ();
        $p_selected = Get::pReq(  'credits_period', DOTY_INT, 0 );
        $p_res = sql_query ( "SELECT * FROM " . $GLOBALS ['prefix_lms'] . "_time_period ORDER BY end_date DESC, start_date DESC" );
        if (sql_num_rows ( $p_res ) > 0) {
            while ( $obj = sql_fetch_object ( $p_res ) ) {
                if ($p_selected == 0)
                    $p_selected = $obj->id_period;
                $p_list [$obj->id_period] = Format::date ( $obj->start_date, 'date' ) . ' - ' . Format::date ( $obj->end_date, 'date' );
                if ($p_selected == $obj->id_period) {
                    $period_start = $obj->start_date;
                    $period_end = $obj->end_date;
                }
            }
        }

        if (count ( $p_list ) <= 0)
            $p_list ['0'] = Lang::t ( '_NO_PERIODS', 'catalogue' );
        if (! array_key_exists ( $p_selected, $p_list ))
            $p_selected = 0;
        if ($p_selected == 0)
            $p_selected = false;

        // extract courses which have been completed in the considered period and the credits associated
        $course_type_trans = getCourseTypes ();
        $query = "SELECT c.idCourse, c.name, c.course_type, c.credits, cu.status " . " FROM " . $GLOBALS ['prefix_lms'] . "_course as c " . " JOIN " . $GLOBALS ['prefix_lms'] . "_courseuser as cu " . " ON (cu.idCourse = c.idCourse) WHERE cu.idUser=" . ( int ) getLogUserId () . " AND c.course_type IN ('" . implode ( "', '", array_keys ( $course_type_trans ) ) . "') " . " AND cu.status = '" . _CUS_END . "' " . ($period_start != '' ? " AND cu.date_complete > '" . $period_start . "' " : "") . ($period_end != '' ? " AND cu.date_complete < '" . $period_end . "' " : "") . " ORDER BY c.name";
        $res = sql_query ( $query );

        $course_data = array ();
        while ( $obj = sql_fetch_object ( $res ) ) {
            switch ($obj->course_type) {
                case 'elearning' :
                    $course_data ['elearning'] [$obj->idCourse] = $obj;
                    break;
                case 'classroom' :
                case 'blended' :
                    $course_data ['classroom'] [$obj->idCourse] = $obj;
                    break;
            }
        }


        // draw tables
        $no_cdata = true;

        $table = '<div class="table-credit-wrapper">';
        if (count($course_data) > 0) {
            $table .= '
                    <table class="table-credit">
                        <thead>
                            <tr class="table-credit__row table-credit__row--head">
                                <td>' . Lang::t('_COURSE', 'catalogue') . '</td>
                                <td>' . Lang::t('_CREDITS', 'catalogue') . '</td>
                            </tr>
                        </thead>
                        <tbody>';
            foreach ($course_data as $ctype => $cdata) {

                if (count($cdata) > 0) {

                    $no_cdata = false;

                    $total = 0;
                    foreach ($cdata as $id_course => $data) {

                        $table .= '<tr class="table-credit__row">
                            <td>
                                ' . $data->name . '
                            </td>
                            <td>
                                ' . $data->credits . '
                            </td>
                        </tr>';

                        $total += $data->credits;
                    }
                }
            }

            $table .= '
                        </tbody>
                        <tfoot>
                            <tr class="table-credit__row table-credit__row--footer">
                                <td>' . Lang::t('_TOTAL', 'catalogue') . '</td>
                                <td>' . $total . '</td>
                            </tr>
                        </tfoot>    
                    </table>';
        }

        if ($no_cdata) {
            $table .= '<p>' . Lang::t ( '_NO_CONTENT', 'catalogue' ) . '</p>';
        }

        $table .= '</div>';

        echo $table;
    }

}


?>