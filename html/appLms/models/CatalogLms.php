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

class CatalogLms extends Model
{
	var $edition_man;
	var $course_man;
	var $classroom_man;

	var $cstatus;
	var $acl_man;

	/* category handling */
	var $children;
	var $show_all_category;
	var $current_catalogue;


	public function __construct()
	{
		require_once(_lms_ . '/lib/lib.course.php');
		require_once(_lms_ . '/lib/lib.edition.php');
		require_once(_lms_ . '/lib/lib.date.php');

		$this->course_man = new Man_Course();
		$this->edition_man = new EditionManager();
		$this->classroom_man = new DateManager();

		$this->cstatus = array(
			CST_PREPARATION => '_CST_PREPARATION',
			CST_AVAILABLE => '_CST_AVAILABLE',
			CST_EFFECTIVE => '_CST_CONFIRMED',
			CST_CONCLUDED => '_CST_CONCLUDED',
			CST_CANCELLED => '_CST_CANCELLED'
		);

		$this->acl_man = &Docebo::user()->getAclManager();
		$this->show_all_category = Get::sett('hide_empty_category') == 'off';

		$this->current_catalogue = 0;
	}


	public function enrolledStudent($idCourse)
	{
		$query = "SELECT COUNT(*)"
			. " FROM %lms_courseuser"
			. " WHERE idCourse = '" . $idCourse . "'";


		list($enrolled) = sql_fetch_row(sql_query($query));
		return $enrolled;
	}


	public function getInfoEnroll($idCourse, $idUser)
	{
		$query = "SELECT status, waiting, level"
			. " FROM %lms_courseuser"
			. " WHERE idCourse = " . $idCourse
			. " AND idUser = " . $idUser;
		$result_control = sql_query($query);
		return $result_control;
	}


	public function getInfoLO($idCourse)
	{

		$query_lo = "select org.idOrg, org.idCourse, org.objectType from (SELECT o.idOrg, o.idCourse, o.objectType 
              FROM %lms_organization AS o WHERE o.objectType != '' AND o.idCourse IN (" . $idCourse . ") ORDER BY o.path) as org 
              GROUP BY org.idCourse";

		$result_lo = sql_query($query_lo);
		return $result_lo;
	}


	public function getCourseList($type = '', $page = 1, $id_catalog, $id_category)
	{
		require_once(_lms_ . '/lib/lib.catalogue.php');
		$cat_man = new Catalogue_Manager();

		$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
		$category_filter = ($id_category == 0 || $id_category == null ? '' : ' and idCategory=' . $id_category);
		$cat_list_filter = "";
		if ($id_catalog > 0) {
			$q = "select idEntry from learning_catalogue_entry where idCatalogue=" . $id_catalog . " and type_of_entry='course'";
			$r = sql_query($q);
			while (list($idcat) = sql_fetch_row($r)) {
				$cat_array[] = $idcat;
			}
			$cat_list_filter = " and idCourse in (" . implode(",", $cat_array) . ")";
		}

		switch ($type) {
			case 'elearning':
				$filter = " AND course_type = '" . $type . "'";
				$base_link = 'index.php?r=catalog/elearningCourse&amp;page=' . $page;
				if (count($user_catalogue) > 0) {
					$courses = array();

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (" . implode(',', $courses) . ")";
				}
				break;
			case 'classroom':
				$filter = " AND course_type = '" . $type . "'";
				$base_link = 'index.php?r=catalog/classroomCourse&amp;page=' . $page;
				if (count($user_catalogue) > 0) {
					$courses = array();

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (" . implode(',', $courses) . ")";
				}
				break;
			case 'new':
				$filter = " AND create_date >= '" . date('Y-m-d', mktime(0, 0, 0, date('m'), ((int) date('d') - 7), date('Y'))) . "'";
				$base_link = 'index.php?r=catalog/newCourse&amp;page=' . $page;
				if (count($user_catalogue) > 0) {
					$courses = array();

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (" . implode(',', $courses) . ")";
				}
				break;
			case 'catalogue':
				$id_catalogue = Get::req('id_cata', DOTY_INT, '0');
				$base_link = 'index.php?r=catalog/catalogueCourse&amp;id_cat=' . $id_catalogue . '&amp;page=' . $page;

				$catalogue_course = &$cat_man->getCatalogueCourse($id_catalogue);
				$filter = " AND idCourse IN (" . implode(',', $catalogue_course) . ")";
				break;
			default:
				$filter = '';
				$base_link = 'index.php?r=catalog/allCourse&amp;page=' . $page;

				// var_dump($user_catalogue);

				if (count($user_catalogue) > 0) {
					$courses = array();

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (" . implode(',', $courses) . ")";
				}
				break;
		}


		$query = "SELECT *"
			. " FROM %lms_course"
			. " WHERE status NOT IN (" . CST_PREPARATION . ", " . CST_CONCLUDED . ", " . CST_CANCELLED . ")"
			. " AND course_type <> 'assessment'"
			. " AND (                       
						(can_subscribe=2 AND (sub_end_date = '0000-00-00' OR sub_end_date >= '" . date('Y-m-d') . "') AND
                         (sub_start_date = '0000-00-00' OR '" . date('Y-m-d') . "' >= sub_start_date)) OR
                        (can_subscribe=1)
					) "
			. $filter
			. $category_filter
			. $cat_list_filter
			. " ORDER BY name";


		$result = sql_query($query);
		return $result;
	}

	public function getTotalCourseNumber($type = '')
	{
		require_once(_lms_ . '/lib/lib.catalogue.php');
		$cat_man = new Catalogue_Manager();

		$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());

		switch ($type) {
			case 'elearning':
				$filter = " AND course_type = '" . $type . "'";
				if (count($user_catalogue) > 0) {
					$courses = array();

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (" . implode(',', $courses) . ")";
				}
				break;
			case 'classroom':
				$filter = " AND course_type = '" . $type . "'";
				if (count($user_catalogue) > 0) {
					$courses = array();

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (" . implode(',', $courses) . ")";
				}
				break;
			case 'edition':
				$filter = " AND course_edition = 1";
				if (count($user_catalogue) > 0) {
					$courses = array();

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (" . implode(',', $courses) . ")";
				}
				break;
			case 'new':
				$filter = " AND create_date >= '" . date('Y-m-d', mktime(0, 0, 0, date('m'), ((int) date('d') - 7), date('Y'))) . "'";
				if (count($user_catalogue) > 0) {
					$courses = array();

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (" . implode(',', $courses) . ")";
				}
				break;
			case 'catalogue':
				$id_catalogue = Get::req('id_cata', DOTY_INT, '0');

				$catalogue_course = &$cat_man->getCatalogueCourse($id_catalogue);
				$filter = " AND idCourse IN (" . implode(',', $catalogue_course) . ")";
				break;
			default:
				$filter = '';

				if (count($user_catalogue) > 0) {
					$courses = array();

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (" . implode(',', $courses) . ")";
				}
				break;
		}

		if (count($user_catalogue) == 0 && Get::sett('on_catalogue_empty', 'off') == 'off') {
			$filter = " AND 0 "; //query won't return any results with this setting
		}

		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$query = "SELECT COUNT(*)"
			. " FROM %lms_course"
			. " WHERE status NOT IN (" . CST_PREPARATION . ", " . CST_CONCLUDED . ", " . CST_CANCELLED . ")"
			. " AND course_type <> 'assessment'"
			. " AND ("
			. " date_begin = '0000-00-00'"
			. " OR date_begin > '" . date('Y-m-d') . "'"
			. " )"
			. $filter
			. ($id_cat > 0 ? " AND idCategory = " . (int) $id_cat : '')
			. " ORDER BY name";

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getUserCatalogue($id_user)
	{
		require_once(_lms_ . '/lib/lib.catalogue.php');
		$cat_man = new Catalogue_Manager();

		$res = &$cat_man->getUserAllCatalogueInfo($id_user);

		return $res;
	}

	public function getUserCoursepath($id_user)
	{
		$user_catalogue = array_keys($this->getUserCatalogue($id_user));

		$query = "SELECT idEntry"
			. " FROM %lms_catalogue_entry"
			. " WHERE idCatalogue IN (" . implode(',', $user_catalogue) . ")"
			. " AND type_of_entry = 'coursepath'";

		$result = sql_query($query);
		$res = array();

		while (list($id_path) = sql_fetch_row($result))
			$res[$id_path] = $id_path;

		return $res;
	}

	public function getUserCoursepathSubscription($id_user)
	{
		$query = "SELECT id_path"
			. " FROM %lms_coursepath_user"
			. " WHERE idUser = '" . $id_user . "'";

		$result = sql_query($query);
		$res = array();

		while (list($id_path) = sql_fetch_row($result))
			$res[$id_path] = $id_path;

		return $res;
	}

	public function getCoursepathList($id_user, $page)
	{
		$html = '';
		$coursepath = $this->getUserCoursepath($id_user);
		$user_coursepath = $this->getUserCoursepathSubscription($id_user);
		$limit = ($page - 1) * Get::sett('visuItem');

		$query = "SELECT id_path, path_name, path_code, path_descr, subscribe_method"
			. " FROM %lms_coursepath"
			. " WHERE id_path IN (" . implode(',', $coursepath) . ")"
			. " LIMIT " . $limit . ", " . Get::sett('visuItem');

		$result = sql_query($query);

		while (list($id_path, $name, $code, $descr, $subscribe_method) = sql_fetch_row($result)) {
			$action = '';
			if (isset($user_coursepath[$id_path]))
				$action = '<div class="catalog_action"><p class="subscribed">' . Lang::t('_USER_STATUS_SUBS', 'catalogue') . '</p></div>';
			elseif ($subscribe_method != 0)
				$action = "<div class=\"catalog_action\" id=\"action_" . $id_path . "\"><a href=\"javascript:;\" onclick=\"subscriptionCoursePathPopUp('" . $id_path . "')\" title=\"Subscribe\"><p class=\"can_subscribe\">" . Lang::t('_SUBSCRIBE', 'catalogue') . "</p></a></div>";
			elseif ($subscribe_method == 0)
				$action .= '<div class="catalog_action"><p class="cannot_subscribe">' . Lang::t('_COURSE_S_GODADMIN', 'catalogue') . '</p></div>';

			$html .= '<div style="position:relative;clear: none;margin: .4em 1em 1em;padding-bottom:1em;border-bottom:1px solid #BAC2CF;">'
				. '<h2>'
				. $name
				. '</h2>'
				. '<p class="course_support_info">'
				. $descr
				. '</p>'
				. '<p style="padding:.4em">'
				. ($code ? '<i style="font-size:.88em">[' . $code . ']</i>' : '')
				. '</p>'
				. '' //lista corsi
				. $action
				. '</div>';
		}

		return $html;
	}

	public function subscribeCoursePathInfo($id_path)
	{

		$res = array();

		$res['success'] = true;
		$res['title'] = Lang::t('_COURSEPATH_SUBSCRIBE_WIN_TIT', 'catalogue');
		$res['body'] = Lang::t('_COURSEPATH_SUBSCRIBE_WIN_TXT', 'catalogue');
		$res['footer'] = '<a href="javascript:;" onclick="subscribeToCoursePath(\'' . $id_path . '\');"><span class="close_dialog">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</span></a>'
			. '&nbsp;&nbsp;<a href="javascript:;" onclick="hideDialog();"><span class="close_dialog">' . Lang::t('_UNDO', 'catalogue') . '</span></a>';
		return $res;
	}


	public function subscribeInfo($id_course, $id_date, $id_edition, $selling)
	{
		$res = array();

		require_once(_lms_ . '/lib/lib.course.php');

		$teachers = $this->course_man->getIdUserOfLevel($id_course, CourseLevel::COURSE_LEVEL_TEACHER);

		$query = "SELECT *"
			. " FROM %lms_course"
			. " WHERE idCourse = " . (int) $id_course;

		$course = sql_fetch_assoc(sql_query($query));

		if ($id_date != 0) {
			$classroom_info = $this->classroom_man->getDateInfo($id_date);

			$res['success'] = true;

			if ($selling == 1)
				$res['title'] = Lang::t('_CONFIRM_ADD_TO_CART', 'catalogue');
			else
				$res['title'] = Lang::t('_CONFIRM_SUBSCRIPTION', 'catalogue');

			$res['body'] .= '<div class="edition_container">'
				. '<div class="edition__body">'
				. '<b>' . Lang::t('_NAME', 'catalogue') . '</b>: ' . $classroom_info['name'] . '<br/><br/>'
				. ($classroom_info['code'] !== '' ? '<b>' . Lang::t('_CODE', 'catalogue') . '</b>: ' . $classroom_info['code'] . '<br/>' : '')
				. '<b>' . Lang::t('_TITLE', 'catalogue') . '</b>: ' . $course['name'] . '<br/><br/>'
				. '<div class="edition__twocol">'
				. (($classroom_info['date_begin'] !== '0000-00-00 00:00:00' || $classroom_info['date_end'] !== '0000-00-00 00:00:00') ? '<div class="edition__col"><b>' . Lang::t('_DAYS', 'course') . '</b><br />' . Format::date($classroom_info['date_begin'], 'datetime') . ' <span class="edition_arrow"></span> ' . Format::date($classroom_info['date_end'], 'datetime') . '</div>' : '')
				. '<div class="edition__col"><b>' . Lang::t('_DURATION', 'course') . '</b><br />' . $classroom_info['num_day'] . ' ' . Lang::t('_DAYS', 'course') . '</div>'
				. '</div>';
			if (count($teachers) > 0) {
				$res['body'] .= '<b>' . Lang::t('_THEACER_LIST', 'course') . '</b><br />';

				$index = 0;
				foreach ($teachers as $teacher) {
					$acl_man = Docebo::user()->getAclManager();
					$teacher_info = $acl_man->getUser($teacher, false);
					if (!empty($teacher_info[ACL_INFO_FIRSTNAME] && $teacher_info[ACL_INFO_LASTNAME])) {
						if ($index > 0 && $index <= count($teachers)) {
							$res['body'] .= ',';
						}
						$res['body'] .= $teacher_info[ACL_INFO_FIRSTNAME] . ' ' . $teacher_info[ACL_INFO_LASTNAME];
						$index++;
					}
				}
				$res['body'] .= '<br /><br />';
			}

			switch ($course['subscribe_method']) {
				case 1:
					// moderate
					$res['body'] .= '<div class="moderation-alert">' . Lang::t('_COURSE_S_MODERATE', 'catalogue') . '</div>';
					break;
				case 0:
					// only admin
					$res['body'] .= '<div class="moderation-alert">' . Lang::t('_COURSE_S_GODADMIN', 'catalogue') . '</div>';
					break;
			}

			$is_in_overbooking = $classroom_info['max_par'] <= $classroom_info['user_subscribed'] && $classroom_info['overbooking'] > 0;
			if ($is_in_overbooking) {
				$res['body'] .= '<div class="moderation-alert"><b>' . Lang::t('_OVERBOOKING_WARNING', 'catalogue') . '</b></div><br /><br />';
			}

			$res['body'] .= '</div>'
				. '</div>';

			$res['footer'] = ($selling == 1 ? '<div class="edition__buttonContainer"><a href="javascript:;" class="subscribe-button" onclick="subscribeToCourse(\'' . $id_course . '\', \'' . $id_date . '\', \'' . $id_edition . '\', \'' . $selling . '\');"><span class="close_dialog">' . Lang::t('_CONFIRM', 'catalogue') . ' (' . $classroom_info['price'] . ' ' . Get::sett('currency_symbol', '&euro;') . ')' . '</span></a>'
				: '<div class="edition__buttonContainer"><a href="javascript:;" class="subscribe-button" onclick="subscribeToCourse(\'' . $id_course . '\', \'' . $id_date . '\', \'' . $id_edition . '\', \'' . $selling . '\');"><span class="close_dialog">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</span></a>')
				. '&nbsp;&nbsp;<a href="javascript:;" class="undo-button" onclick="hideDialog();"><span class="close_dialog">' . Lang::t('_UNDO', 'catalogue') . '</span></a></div>';
		} elseif ($id_edition != 0) {
			$edition_info = $this->edition_man->getEditionInfo($id_edition);
			$res['success'] = true;

			if ($selling == 1)
				$res['title'] = Lang::t('_CONFIRM_ADD_TO_CART', 'catalogue');
			else
				$res['title'] = Lang::t('_CONFIRM_SUBSCRIPTION', 'catalogue');

			$res['body'] .= '<div class="edition_container">'
				. '<div class="edition__body">'
				. '<b>' . Lang::t('_NAME', 'catalogue') . '</b>: ' . $edition_info['name'] . '<br/><br/>';

			if ($edition_info['code'] !== '') {
				$res['body'] .= '<b>' . Lang::t('_CODE', 'catalogue') . '</b>: ' . $edition_info['code'] . '<br/><br/>';
			}
			$res['body'] .= '<b>' . Lang::t('_TITLE', 'catalogue') . '</b>: ' . $course['name'] . '<br/><br/>';
			$res['body'] .= '<div class="edition__twocol">';

			if ($edition_info['date_begin'] !== '0000-00-00 00:00:00' || $edition_info['date_end'] !== '0000-00-00 00:00:00') {

				$res['body'] .= '<div class="edition__col"><b>' . Lang::t('_DAYS', 'course') . '</b><br />' . Format::date($edition_info['date_begin'], 'datetime') . ' <span class="edition_arrow"></span> ' . Format::date($edition_info['date_end'], 'datetime') . '</div>';

				if (($edition_info['date_begin'] !== '0000-00-00' && $edition_info['date_end'] !== '0000-00-00') && ($edition_info['date_begin'] !== '0000-00-00 00:00:00' && $edition_info['date_end'] !== '0000-00-00 00:00:00')) {
					$earlier = new DateTime($edition_info['date_begin']);
					$later = new DateTime($edition_info['date_end']);

					$days = $later->diff($earlier)->format("%a") + 1;
					if ($days > 1) {
						$dayString = ' ' . Lang::t('_DAYS', 'course');
					} else {
						$dayString = ' ' . Lang::t('_DAY', 'course');
					}
				} else {
					$days = '--';
					$dayString = '';
				}

				$res['body'] .= '<div class="edition__col"><b>' . Lang::t('_DURATION', 'course') . '</b><br />' . $days . $dayString . '</div>';
			}

			$res['body'] .= '</div>';
			if (count($teachers) > 0) {
				$res['body'] .= '<b>' . Lang::t('_THEACER_LIST', 'course') . '</b><br />';

				$index = 0;
				foreach ($teachers as $teacher) {
					$acl_man = Docebo::user()->getAclManager();
					$teacher_info = $acl_man->getUser($teacher, false);
					if (!empty($teacher_info[ACL_INFO_FIRSTNAME] && $teacher_info[ACL_INFO_LASTNAME])) {
						if ($index > 0 && $index <= count($teachers)) {
							$res['body'] .= ',';
						}
						$res['body'] .= $teacher_info[ACL_INFO_FIRSTNAME] . ' ' . $teacher_info[ACL_INFO_LASTNAME];
						$index++;
					}
				}
				$res['body'] .= '<br /><br />';
			}

			switch ($course['subscribe_method']) {
				case 1:
					// moderate
					$res['body'] .= '<div class="moderation-alert">' . Lang::t('_COURSE_S_MODERATE', 'catalogue') . '</div>';
					break;
				case 0:
					// only admin
					$res['body'] .= '<div class="moderation-alert">' . Lang::t('_COURSE_S_GODADMIN', 'catalogue') . '</div>';
					break;
			}

			$res['body'] .= '</div>'
				. '</div>';
			$res['footer'] = ($selling == 1 ? '<div class="edition__buttonContainer"><a href="javascript:;" class="subscribe-button" onclick="subscribeToCourse(\'' . $id_course . '\', \'' . $id_date . '\', \'' . $id_edition . '\', \'' . $selling . '\');"><span class="close_dialog">' . Lang::t('_CONFIRM', 'catalogue') . ' (' . $edition_info['price'] . ' ' . Get::sett('currency_symbol', '&euro;') . ')' . '</span></a>'
				: '<div class="edition__buttonContainer"><a href="javascript:;" class="subscribe-button" onclick="subscribeToCourse(\'' . $id_course . '\', \'' . $id_date . '\', \'' . $id_edition . '\', \'' . $selling . '\');"><span class="close_dialog">' . Lang::t('_CONFIRM', 'catalogue') . '</span></a>')
				. '&nbsp;&nbsp;<a href="javascript:;" class="undo-button" onclick="hideDialog();"><span class="close_dialog">' . Lang::t('_UNDO', 'catalogue') . '</span></a></div>';
		} else {


			$res['success'] = true;

			if ($selling == 1)
				$res['title'] = Lang::t('_CONFIRM_ADD_TO_CART', 'catalogue');
			else
				$res['title'] = Lang::t('_CONFIRM_SUBSCRIPTION', 'catalogue');

			$res['body'] .= '<div class="edition_container">'
				. '<div class="edition__body">';
			if ($course['code'] !== '') {
				$res['body'] .= '<b>' . Lang::t('_CODE', 'catalogue') . '</b>: ' . $course['code'] . '<br/>';
			}
			$res['body'] .= '<b>' . Lang::t('_TITLE', 'catalogue') . '</b>: ' . $course['name'] . '<br/><br/>';

			$res['body'] .= '<div class="edition__twocol">';

			if (($course['date_begin'] !== '0000-00-00' || $course['date_end'] !== '0000-00-00') && ($course['date_begin'] !== '0000-00-00 00:00:00' || $course['date_end'] !== '0000-00-00 00:00:00')) {

				$res['body'] .= '<div class="edition__col"><b>' . Lang::t('_DAYS', 'course') . '</b><br />' . Format::date($course['date_begin'], 'date') . ' <span class="edition_arrow"></span> ' . Format::date($course['date_end'], 'date') . '</div>';

				if (($course['date_begin'] !== '0000-00-00' && $course['date_end'] !== '0000-00-00') && ($course['date_begin'] !== '0000-00-00 00:00:00' && $course['date_end'] !== '0000-00-00 00:00:00')) {
					$earlier = new DateTime($course['date_begin']);
					$later = new DateTime($course['date_end']);

					$days = $later->diff($earlier)->format("%a") + 1;
					if ($days > 1) {
						$dayString = ' ' . Lang::t('_DAYS', 'course');
					} else {
						$dayString = ' ' . Lang::t('_DAY', 'course');
					}
				} else {
					$days = '--';
					$dayString = '';
				}

				$res['body'] .= '<div class="edition__col"><b>' . Lang::t('_DURATION', 'course') . '</b><br />' . $days . $dayString . '</div>';
			}

			$res['body'] .= '</div>';
			if (count($teachers) > 0) {
				$res['body'] .= '<b>' . Lang::t('_THEACER_LIST', 'course') . '</b><br />';

				$index = 0;
				foreach ($teachers as $teacher) {
					$acl_man = Docebo::user()->getAclManager();
					$teacher_info = $acl_man->getUser($teacher, false);
					if (!empty($teacher_info[ACL_INFO_FIRSTNAME] && $teacher_info[ACL_INFO_LASTNAME])) {
						if ($index > 0 && $index <= count($teachers)) {
							$res['body'] .= ',';
						}
						$res['body'] .= $teacher_info[ACL_INFO_FIRSTNAME] . ' ' . $teacher_info[ACL_INFO_LASTNAME];
						$index++;
					}
				}
				$res['body'] .= '<br /><br />';
			}

			switch ($course['subscribe_method']) {
				case 1:
					// moderate
					$res['body'] .= '<div class="moderation-alert">' . Lang::t('_COURSE_S_MODERATE', 'catalogue') . '</div>';
					break;
				case 0:
					// only admin
					$res['body'] .= '<div class="moderation-alert">' . Lang::t('_COURSE_S_GODADMIN', 'catalogue') . '</div>';
					break;
			}

			$res['body'] .= '</div>'
				. '</div>';
			$res['footer'] = ($selling == 1 ? '<div class="edition__buttonContainer"><a href="javascript:;" class="subscribe-button" onclick="subscribeToCourse(\'' . $id_course . '\', \'' . $id_date . '\', \'' . $id_edition . '\', \'' . $selling . '\');"><span class="confirm_dialog">' . Lang::t('_CONFIRM', 'catalogue') . ' (' . $course['prize'] . ' ' . Get::sett('currency_symbol', '&euro;') . ')' . '</span></a>'
				: '<div class="edition__buttonContainer"><a href="javascript:;" class="subscribe-button" onclick="subscribeToCourse(\'' . $id_course . '\', \'' . $id_date . '\', \'' . $id_edition . '\', \'' . $selling . '\');"><span class="confirm_dialog">' . Lang::t('_CONFIRM', 'catalogue') . '</span></a>')
				. '&nbsp;&nbsp;<a href="javascript:;" class="undo-button" onclick="hideDialog();"><span class="close_dialog">' . Lang::t('_UNDO', 'catalogue') . '</span></a></div>';
		}

		return $res;
	}

	public function courseSelectionInfo($id_course, $selling)
	{
		
        $query = "SELECT name"
			. " FROM %lms_course"
			. " WHERE idCourse = " . (int) $id_course;
            
        list($course_name) = sql_fetch_row(sql_query($query));    
        $classrooms = $this->classroom_man->getCourseDate($id_course, false);        
        $classroom_not_confirmed = $this->classroom_man->getNotConfirmetDateForCourse($id_course);        
            // cutting not confirmed classrooms
        $available_classrooms = array_diff_key($classrooms, $classroom_not_confirmed);
        $full_classrooms = $this->classroom_man->getFullDateForCourse($id_course);
        $overbooking_classrooms = $this->classroom_man->getOverbookingDateForCourse($id_course);
        foreach ($available_classrooms as  $id_date => $classroom_info){
            $available_classrooms[$id_date]['in_cart'] = isset($_SESSION[$id_course]['classroom'][$id_date]);
            $available_classrooms[$id_date]['days'] = $this->classroom_man->getDateDayDateDetails($id_date);            
            $available_classrooms[$id_date]['full'] = isset($full_classrooms[$id_date]);
            $available_classrooms[$id_date]['overbooking'] = isset($overbooking_classrooms[$id_date]);
            

        }
        $teachers = array_intersect_key($this->course_man->getClassroomTeachers($id_course), $available_classrooms);               
        return compact('available_classrooms', 'teachers', 'course_name');

	}

	public function controlSubscriptionRemaining($id_course)
	{
		$query = "SELECT *"
			. " FROM %lms_course"
			. " WHERE idCourse = " . (int) $id_course;

		$result = sql_query($query);

		$row = sql_fetch_assoc($result);
		if ($row['course_type'] === 'classroom') {
			$additional_info = '';

			$classrooms = $this->classroom_man->getCourseDate($row['idCourse'], false);

			if (count($classrooms) == 0)
				return false;
			else {
				//Controllo che l'utente non sia iscritto a tutte le edizioni future
				$date_id = array();

				$user_classroom = $this->classroom_man->getUserDates(Docebo::user()->getIdSt());
				$classroom_full = $this->classroom_man->getFullDateForCourse($row['idCourse']);
				$classroom_not_confirmed = $this->classroom_man->getNotConfirmetDateForCourse($row['idCourse']);

				foreach ($classrooms as $classroom_info)
					$date_id[] = $classroom_info['id_date'];

				reset($classrooms);

				$control = array_diff($date_id, $user_classroom, $classroom_full, $classroom_not_confirmed);

				if (count($control) == 0)
					return false;
				else {
					if ($row['selling'] == 0)
						return true;
					else {
						$classroom_in_chart = array();

						if (isset($_SESSION['lms_cart'][$row['idCourse']]['classroom']))
							$classroom_in_chart = $_SESSION['lms_cart'][$row['idCourse']]['classroom'];

						$control = array_diff($control, $classroom_in_chart);

						if (count($control) == 0)
							return false;
						else
							return true;
					}
				}
			}
		} elseif ($row['course_edition'] == 1) {
			$additional_info = '';

			$editions = $this->edition_man->getEditionAvailableForCourse(Docebo::user()->getIdSt(), $row['idCourse']);

			if (count($editions) == 0)
				return false;
			else {
				if ($row['selling'] == 0)
					return true;
				else {
					$edition_in_chart = array();

					if (isset($_SESSION['lms_cart'][$row['idCourse']]['editions']))
						$edition_in_chart = $_SESSION['lms_cart'][$row['idCourse']]['editions'];

					$editions = array_diff($editions, $edition_in_chart);

					if (count($editions) == 0)
						return false;
					else
						return true;
				}
			}
		}
	}


	public function GetGlobalJsonTree($id_catalogue)
	{
		$this->current_catalogue = $id_catalogue;
		$global_tree = [];
		$top_category = $this->getMajorCategory();
		foreach ($top_category as $id_key => $val) {
			if ($this->CategoryHasChildrenCourses($id_key, $val['iLeft'], $val['iRight'])) {
				$this->children = $this->getMinorCategoryTree($id_key, $val['iLeft'], $val['iRight'], 2);
				$global_tree[] = array('text' => $val['text'], "id_cat" => $id_key, 'nodes' => $this->children);
			}
		}
		return $global_tree;
	}

	private function getMajorCategory()
	{
		$q = "SELECT idCategory, path, iLeft, iRight"
			. " FROM %lms_category"
			. " WHERE lev = 1"
			. " ORDER BY path";

		$res = [];
		$records = sql_query($q);
		while ($row = sql_fetch_assoc($records)) {
			$res[$row['idCategory']] = array('text' => end(explode('/', $row['path'])), 'iLeft' => $row['iLeft'], 'iRight' => $row['iRight']);
		}

		return $res;
	}

	public function getMinorCategoryTree($idCat, $ileft, $iright, $lev)
	{

		if (($iright - $ileft > 1) && $this->CategoryHasChildrenCourses($idCat, $ileft, $iright)) {
			$q = "SELECT idCategory, path, idParent, lev, iLeft, iRight  FROM %lms_category  
                        WHERE iLeft > " . (int) $ileft . " AND iRight < " . $iright . " AND lev=" . $lev;
			$res = [];
			$records = sql_query($q);
			while ($row = sql_fetch_assoc($records)) {
				// including only if there are courses starting from here
				if ($this->CategoryHasChildrenCourses($row['idCategory'], $row['iLeft'], $row['iRight'])) {
					$res[$row['idCategory']] = array(
						'text' => end(explode('/', $row['path'])),
						'id_cat' => $row['idCategory']
					);
					// getting all children of next level, if any
					$children = $this->getMinorCategoryTree($row['idCategory'], $row['iLeft'], $row['iRight'], $row['lev'] + 1);
					if ($children) {
						$res[$row['idCategory']]['nodes'] = $children;
					}
				}
			}
			return $res;
		} else {
			return '';
		}
	}

	/**
	 * checking if there are courses starting from id_cat and searching through all children nodes 
	 */
	private function CategoryHasChildrenCourses($id_cat, $ileft, $iright)
	{

		if ($this->show_all_category) {
			return true;
		} else {
			if ($this->current_catalogue == 0) {

				$query = "select count(*) as t from
                       %lms_course, %lms_category  where
                       %lms_course.idCategory = %lms_category.idCategory and
                       %lms_category.iLeft >=" . $ileft . " and %lms_category.iRight <= " . $iright . " and
                       %lms_course.course_type <> 'assessment' and
                       %lms_course.status NOT IN (" . CST_PREPARATION . ", " . CST_CONCLUDED . ", " . CST_CANCELLED . ")
                       AND (                       
                              (can_subscribe=2 AND (sub_end_date = '0000-00-00' OR sub_end_date >= '" . date('Y-m-d') . "') AND (sub_start_date = '0000-00-00' OR '" . date('Y-m-d') . "' >= sub_start_date)) OR
                              (can_subscribe=1)
                          )";
			} else {

				$query = "select count(*) as t from
                       %lms_course, %lms_category,  %lms_catalogue_entry where
                       %lms_course.idCategory = %lms_category.idCategory and
                       %lms_category.iLeft >=" . $ileft . " and %lms_category.iRight <= " . $iright . " and
                       %lms_course.course_type <> 'assessment' and
                       %lms_course.status NOT IN (" . CST_PREPARATION . ", " . CST_CONCLUDED . ", " . CST_CANCELLED . ")
                       AND (                       
                              (can_subscribe=2 AND (sub_end_date = '0000-00-00' OR sub_end_date >= '" . date('Y-m-d') . "') AND (sub_start_date = '0000-00-00' OR '" . date('Y-m-d') . "' >= sub_start_date)) OR
                              (can_subscribe=1)
                          )
                       AND idCatalogue = " . (int) $this->current_catalogue .
					" AND %lms_catalogue_entry.idEntry=%lms_course.idCourse";
			}
			list($c) = sql_fetch_row(sql_query($query));
			return ($c > 0);
		}
	}
}
