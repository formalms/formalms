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

class CatalogLms extends Model
{
    public $edition_man;
    public $course_man;
    public $classroom_man;

    public $cstatus;
    public $acl_man;

    /* category handling */
    public $children;
    public $show_all_category;
    public $currentCatalogue;

    public const SHOW_RULES_EVERYONE = 0;
    public const SHOW_RULES_LOGGED_USERS = 1;
    public const SHOW_RULES_SUBSCRIBED_USERS = 2;

    public function __construct()
    {
        require_once _lms_ . '/lib/lib.course.php';
        require_once _lms_ . '/lib/lib.edition.php';
        require_once _lms_ . '/lib/lib.date.php';

        $this->course_man = new Man_Course();
        $this->edition_man = new EditionManager();
        $this->classroom_man = new DateManager();

        $this->cstatus = [
            CST_PREPARATION => '_CST_PREPARATION',
            CST_AVAILABLE => '_CST_AVAILABLE',
            CST_EFFECTIVE => '_CST_CONFIRMED',
            CST_CONCLUDED => '_CST_CONCLUDED',
            CST_CANCELLED => '_CST_CANCELLED',
        ];

        $this->acl_man = &Docebo::user()->getAclManager();
        $this->show_all_category = FormaLms\lib\Get::sett('hide_empty_category') === 'off';

        $this->currentCatalogue = 0;
        parent::__construct();
    }

    public function enrolledStudent($idCourse)
    {
        $query = 'SELECT COUNT(*)'
            . ' FROM %lms_courseuser'
            . " WHERE idCourse = '" . $idCourse . "'";

        list($enrolled) = sql_fetch_row(sql_query($query));

        return $enrolled;
    }

    public function getInfoEnroll($idCourse, $idUser)
    {
        $query = 'SELECT status, waiting, level'
            . ' FROM %lms_courseuser'
            . ' WHERE idCourse = ' . $idCourse
            . ' AND idUser = ' . $idUser;

        return Docebo::db()->query($query);
    }

    public function getInfoLO($idCourse)
    {
        $query = "select org.idOrg, org.idCourse, org.objectType from (SELECT o.idOrg, o.idCourse, o.objectType 
              FROM %lms_organization AS o WHERE o.objectType != '' AND o.idCourse IN (" . $idCourse . ') ORDER BY o.path) as org 
              GROUP BY org.idCourse';

        return Docebo::db()->query($query);
    }

    public function getCourseList($type = '', $page = 1, $id_catalog, $id_category)
    {
        require_once _lms_ . '/lib/lib.catalogue.php';
        $cat_man = new Catalogue_Manager();

        $user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
        $category_filter = ($id_category == 0 || $id_category == null ? '' : ' and idCategory=' . $id_category);
        $cat_list_filter = '';
        if ($id_catalog > 0) {
            $q = 'select idEntry from learning_catalogue_entry where idCatalogue=' . $id_catalog . " and type_of_entry='course'";
            $r = sql_query($q);
            while (list($idcat) = sql_fetch_row($r)) {
                $cat_array[] = $idcat;
            }
            $cat_list_filter = ' and idCourse in (' . implode(',', $cat_array) . ')';
        }

        switch ($type) {
            case 'elearning':
                $filter = " AND course_type = '" . $type . "'";
                $base_link = 'index.php?r=catalog/elearningCourse&amp;page=' . $page;
                if (count($user_catalogue) > 0) {
                    $courses = [];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    $filter .= ' AND idCourse IN (' . implode(',', $courses) . ')';
                }
                break;
            case 'classroom':
                $filter = " AND course_type = '" . $type . "'";
                $base_link = 'index.php?r=catalog/classroomCourse&amp;page=' . $page;
                if (count($user_catalogue) > 0) {
                    $courses = [];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    $filter .= ' AND idCourse IN (' . implode(',', $courses) . ')';
                }
                break;
            case 'new':
                $filter = " AND create_date >= '" . date('Y-m-d', mktime(0, 0, 0, date('m'), ((int) date('d') - 7), date('Y'))) . "'";
                $base_link = 'index.php?r=catalog/newCourse&amp;page=' . $page;
                if (count($user_catalogue) > 0) {
                    $courses = [];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    $filter .= ' AND idCourse IN (' . implode(',', $courses) . ')';
                }
                break;
            case 'catalogue':
                $id_catalogue = FormaLms\lib\Get::req('id_cata', DOTY_INT, '0');
                $base_link = 'index.php?r=catalog/catalogueCourse&amp;id_cat=' . $id_catalogue . '&amp;page=' . $page;

                $catalogue_course = &$cat_man->getCatalogueCourse($id_catalogue);
                $filter = ' AND idCourse IN (' . implode(',', $catalogue_course) . ')';
                break;
            default:
                $filter = '';
                $base_link = 'index.php?r=catalog/allCourse&amp;page=' . $page;

                // var_dump($user_catalogue);

                if (count($user_catalogue) > 0) {
                    $courses = [];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    $filter .= ' AND idCourse IN (' . implode(',', $courses) . ')';
                }
                break;
        }

        $query = 'SELECT *'
            . ' FROM %lms_course'
            . ' WHERE status NOT IN (' . CST_PREPARATION . ', ' . CST_CONCLUDED . ', ' . CST_CANCELLED . ')'
            . " AND course_type <> 'assessment'"
            . " AND (                       
						(can_subscribe=2 AND (sub_end_date = '0000-00-00' OR sub_end_date >= '" . date('Y-m-d') . "') AND
                         (sub_start_date = '0000-00-00' OR '" . date('Y-m-d') . "' >= sub_start_date)) OR
                        (can_subscribe=1)
					) "
            . $filter
            . $category_filter
            . $cat_list_filter
            . ' ORDER BY name';

        $result = sql_query($query);

        return $result;
    }

    public function getNewCourseList($type = '', $page = 1, $idCatalog = 0, $idCategory = 0)
    {
        require_once _lms_ . '/lib/lib.catalogue.php';
        $cat_man = new Catalogue_Manager();

        $user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
        $categoryFilter = (empty($idCategory) ? '' : ' and idCategory=' . $idCategory);
        $categoryListFilter = '';
        if ($idCatalog > 0) {
            $query = 'select idEntry from learning_catalogue_entry where idCatalogue=' . $idCatalog . " and type_of_entry='course'";
            $result = Docebo::db()->query($query);
            foreach ($result as $item) {
                $cat_array[] = $item['idEntry'];
            }
            $categoryListFilter = ' and idCourse in (' . implode(',', $cat_array) . ')';
        }
        $courses = [];
        $filter = '';
        switch ($type) {
            case 'elearning':
            case 'classroom':
                $filter = " AND course_type = '" . $type . "'";
                break;
            case 'new':
                $filter = " AND create_date >= '" . date('Y-m-d', mktime(0, 0, 0, date('m'), ((int) date('d') - 7), date('Y'))) . "'";
                break;
            case 'catalogue':
                $idCatalogue = FormaLms\lib\Get::req('id_cata', DOTY_INT, '0');

                $user_catalogue[] = $idCatalogue;
                break;
            default:
                break;
        }
        if (count($user_catalogue) > 0) {
            foreach ($user_catalogue as $id_cat) {
                $catalogue_courses = $cat_man->getCatalogueCourse($id_cat);
                $courses = array_merge($courses, $catalogue_courses);
            }
        }

        if (count($courses) > 0) {
            $filter .= ' AND idCourse IN (' . implode(',', $courses) . ')';
        }

        $query = 'SELECT *'
            . ' FROM %lms_course'
            . ' WHERE status NOT IN (' . CST_PREPARATION . ', ' . CST_CONCLUDED . ', ' . CST_CANCELLED . ')'
            . " AND course_type <> 'assessment'"
            . " AND (                       
						(can_subscribe=2 AND (sub_end_date = '0000-00-00' OR sub_end_date >= '" . date('Y-m-d') . "') AND
                         (sub_start_date = '0000-00-00' OR '" . date('Y-m-d') . "' >= sub_start_date)) OR
                        (can_subscribe=1)
					) "
            . $filter
            . $categoryFilter
            . $categoryListFilter
            . ' ORDER BY name';

        return Docebo::db()->query($query);
    }

    public function getCatalogCourseList($type, $page, $idCatalog, $idCategory)
    {
        $courses = [];
        $response = $this->getNewCourseList($type, $page, $idCatalog, $idCategory);

        foreach ($response as $course) {
            $course = CourseLms::getCourseParsedData($course);

            if ($course['course_type'] === 'elearning') {
                $course['courseBoxEnabled'] = CourseLms::isBoxEnabledForElearningInCatalogue($course);
            } elseif ($course['course_type'] === 'classroom') {
                $course['courseBoxEnabled'] = CourseLms::isBoxEnabledForClassroomInCatalogue($course);
            }

            $courses[] = $course;
        }

        return $courses;
    }

    public function getTotalCourseNumber($type = '')
    {
        require_once _lms_ . '/lib/lib.catalogue.php';
        $cat_man = new Catalogue_Manager();

        $user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());

        switch ($type) {
            case 'elearning':
                $filter = " AND course_type = '" . $type . "'";
                if (count($user_catalogue) > 0) {
                    $courses = [];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    $filter .= ' AND idCourse IN (' . implode(',', $courses) . ')';
                }
                break;
            case 'classroom':
                $filter = " AND course_type = '" . $type . "'";
                if (count($user_catalogue) > 0) {
                    $courses = [];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    $filter .= ' AND idCourse IN (' . implode(',', $courses) . ')';
                }
                break;
            case 'edition':
                $filter = ' AND course_edition = 1';
                if (count($user_catalogue) > 0) {
                    $courses = [];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    $filter .= ' AND idCourse IN (' . implode(',', $courses) . ')';
                }
                break;
            case 'new':
                $filter = " AND create_date >= '" . date('Y-m-d', mktime(0, 0, 0, date('m'), ((int) date('d') - 7), date('Y'))) . "'";
                if (count($user_catalogue) > 0) {
                    $courses = [];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    $filter .= ' AND idCourse IN (' . implode(',', $courses) . ')';
                }
                break;
            case 'catalogue':
                $idCatalogue = FormaLms\lib\Get::req('id_cata', DOTY_INT, '0');

                $catalogue_course = &$cat_man->getCatalogueCourse($idCatalogue);
                $filter = ' AND idCourse IN (' . implode(',', $catalogue_course) . ')';
                break;
            default:
                $filter = '';

                if (count($user_catalogue) > 0) {
                    $courses = [];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    $filter .= ' AND idCourse IN (' . implode(',', $courses) . ')';
                }
                break;
        }

        if (count($user_catalogue) == 0 && FormaLms\lib\Get::sett('on_catalogue_empty', 'off') == 'off') {
            $filter = ' AND 0 '; //query won't return any results with this setting
        }

        $id_cat = FormaLms\lib\Get::req('id_cat', DOTY_INT, 0);

        $query = 'SELECT COUNT(*)'
            . ' FROM %lms_course'
            . ' WHERE status NOT IN (' . CST_PREPARATION . ', ' . CST_CONCLUDED . ', ' . CST_CANCELLED . ')'
            . " AND course_type <> 'assessment'"
            . ' AND ('
            . " date_begin = '0000-00-00'"
            . " OR date_begin > '" . date('Y-m-d') . "'"
            . ' )'
            . $filter
            . ($id_cat > 0 ? ' AND idCategory = ' . (int) $id_cat : '')
            . ' ORDER BY name';

        list($res) = sql_fetch_row(sql_query($query));

        return $res;
    }

    public function _getClassDisplayInfo($id_course, &$course_array)
    {
        require_once _lms_ . '/lib/lib.date.php';
        $dm = new DateManager();
        $cl = new ClassroomLms();
        $course_editions = $cl->getUserEditionsInfo(Docebo::user()->idst, $id_course);
        $out = [];
        $course_array['next_lesson'] = '-';
        $next_lesson_array = [];
        $currentDate = new DateTime();

        // user can be enrolled in more than one edition (as a teacher or crazy student....)
        foreach ($course_editions[$id_course] as $id_date => $obj_data) {
            // skip if course if over or not available
            $end_course = new DateTime(Format::date($obj_data->date_max, 'datetime'));
            if ($end_course > $currentDate && $obj_data->status == 0) {
                $out[$id_date]['code'] = $obj_data->code;
                $out[$id_date]['name'] = $obj_data->name;
                $out[$id_date]['date_begin'] = $obj_data->date_min;
                $out[$id_date]['date_end'] = $obj_data->date_max;
                $array_day = $dm->getDateDayDateDetails($obj_data->id_date);

                foreach ($array_day as $id => $day) {
                    $out[$id_date]['days'][$id]['classroom'] = $day['classroom'];
                    $out[$id_date]['days'][$id]['day'] = Format::date($day['date_begin'], 'date');
                    $out[$id_date]['days'][$id]['begin'] = Format::date($day['date_begin'], 'time');
                    $out[$id_date]['days'][$id]['end'] = Format::date($day['date_end'], 'time');
                    $next_lesson_array[$id_date . ',' . $id] = new DateTime(Format::date($day['date_begin'], 'datetime'));
                }
            }
        }

        // calculating what's next lession will be; safe mode in case of more editions with different days
        if (count($next_lesson_array > 0)) {
            asort($next_lesson_array);
            foreach ($next_lesson_array as $k => $v) {
                if ($v > $currentDate) {
                    $j = explode(',', $k);
                    $course_array['next_lesson'] = $out[$j[0]]['days'][$j[1]]['day'] . ' ' . $out[$j[0]]['days'][$j[1]]['begin'];
                    break;
                }
            }
        }

        return $out;
    }

    public function getUserCatalogue($id_user)
    {
        require_once _lms_ . '/lib/lib.catalogue.php';
        $cat_man = new Catalogue_Manager();

        $res = $cat_man->getUserAllCatalogueInfo($id_user);

        return $res;
    }

    public function getUserCoursepath($id_user)
    {
        $user_catalogue = array_keys($this->getUserCatalogue($id_user));

        $query = 'SELECT idEntry'
            . ' FROM %lms_catalogue_entry'
            . ' WHERE idCatalogue IN (' . implode(',', $user_catalogue) . ')'
            . " AND type_of_entry = 'coursepath'";

        $result = sql_query($query);
        $res = [];

        while (list($id_path) = sql_fetch_row($result)) {
            $res[$id_path] = $id_path;
        }

        return $res;
    }

    public function getUserCoursepathSubscription($id_user)
    {
        $query = 'SELECT id_path'
            . ' FROM %lms_coursepath_user'
            . " WHERE idUser = '" . $id_user . "'";

        $result = sql_query($query);
        $res = [];

        while (list($id_path) = sql_fetch_row($result)) {
            $res[$id_path] = $id_path;
        }

        return $res;
    }

    public function getCoursepathList($id_user, $page)
    {
        $html = '';
        $coursepath = $this->getUserCoursepath($id_user);
        $user_coursepath = $this->getUserCoursepathSubscription($id_user);
        $limit = ($page - 1) * FormaLms\lib\Get::sett('visuItem');

        $query = 'SELECT id_path, path_name, path_code, path_descr, subscribe_method'
            . ' FROM %lms_coursepath'
            . ' WHERE id_path IN (' . implode(',', $coursepath) . ')'
            . ' LIMIT ' . $limit . ', ' . FormaLms\lib\Get::sett('visuItem');

        $result = sql_query($query);

        while (list($id_path, $name, $code, $descr, $subscribe_method) = sql_fetch_row($result)) {
            $action = '';
            if (isset($user_coursepath[$id_path])) {
                $action = '<div class="catalog_action"><p class="subscribed">' . Lang::t('_USER_STATUS_SUBS', 'catalogue') . '</p></div>';
            } elseif ($subscribe_method != 0) {
                $action = '<div class="catalog_action" id="action_' . $id_path . "\"><a href=\"javascript:;\" onclick=\"subscriptionCoursePathPopUp('" . $id_path . "')\" title=\"Subscribe\"><p class=\"can_subscribe\">" . Lang::t('_SUBSCRIBE', 'catalogue') . '</p></a></div>';
            } elseif ($subscribe_method == 0) {
                $action .= '<div class="catalog_action"><p class="cannot_subscribe">' . Lang::t('_COURSE_S_GODADMIN', 'catalogue') . '</p></div>';
            }

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
        $res = [];

        $res['success'] = true;
        $res['title'] = Lang::t('_COURSEPATH_SUBSCRIBE_WIN_TIT', 'catalogue');
        $res['body'] = Lang::t('_COURSEPATH_SUBSCRIBE_WIN_TXT', 'catalogue');
        $res['footer'] = '<a href="javascript:;" onclick="subscribeToCoursePath(\'' . $id_path . '\');"><span class="close_dialog">' . Lang::t('_SUBSCRIBE', 'catalogue') . '</span></a>'
            . '&nbsp;&nbsp;<a href="javascript:;" onclick="hideDialog();"><span class="close_dialog">' . Lang::t('_UNDO', 'catalogue') . '</span></a>';

        return $res;
    }

    public function courseSelectionInfo($id_course)
    {
        $query = 'SELECT name, selling, prize'
            . ' FROM %lms_course'
            . ' WHERE idCourse = ' . (int) $id_course;

        list($course_name, $selling, $price) = sql_fetch_row(sql_query($query));
        $classrooms = $this->classroom_man->getCourseDate($id_course, false);
        $classroom_not_confirmed = $this->classroom_man->getNotConfirmetDateForCourse($id_course);
        // cutting not confirmed classrooms
        $available_classrooms = array_diff_key($classrooms, $classroom_not_confirmed);
        $full_classrooms = $this->classroom_man->getFullDateForCourse($id_course);
        $overbooking_classrooms = $this->classroom_man->getOverbookingDateForCourse($id_course);
        foreach ($available_classrooms as $id_date => $classroom_info) {
            $available_classrooms[$id_date]['in_cart'] = isset($this->session->get($id_course)['classroom'][$id_date]);
            $available_classrooms[$id_date]['selling'] = $selling;
            $available_classrooms[$id_date]['price'] = $price;
            $available_classrooms[$id_date]['days'] = $this->classroom_man->getDateDayDateDetails($id_date);
            $available_classrooms[$id_date]['full'] = isset($full_classrooms[$id_date]);
            $available_classrooms[$id_date]['overbooking'] = isset($overbooking_classrooms[$id_date]);
        }
        $teachers = array_intersect_key($this->course_man->getClassroomTeachers($id_course), $available_classrooms);

        return compact('available_classrooms', 'teachers', 'course_name');
    }

    public function controlSubscriptionRemaining($id_course)
    {
        $query = 'SELECT *'
            . ' FROM %lms_course'
            . ' WHERE idCourse = ' . (int) $id_course;

        $result = sql_query($query);

        $row = sql_fetch_assoc($result);
        if ($row['course_type'] === 'classroom') {
            $additional_info = '';

            $classrooms = $this->classroom_man->getCourseDate($row['idCourse'], false);

            if (count($classrooms) == 0) {
                return false;
            } else {
                //Controllo che l'utente non sia iscritto a tutte le edizioni future
                $date_id = [];

                $user_classroom = $this->classroom_man->getUserDates(Docebo::user()->getIdSt());
                $classroom_full = $this->classroom_man->getFullDateForCourse($row['idCourse']);
                $classroom_not_confirmed = $this->classroom_man->getNotConfirmetDateForCourse($row['idCourse']);

                foreach ($classrooms as $classroom_info) {
                    $date_id[] = $classroom_info['id_date'];
                }

                reset($classrooms);

                $control = array_diff($date_id, $user_classroom, $classroom_full, $classroom_not_confirmed);

                if (count($control) == 0) {
                    return false;
                } else {
                    if ($row['selling'] == 0) {
                        return true;
                    } else {
                        $classroom_in_chart = [];

                        if ($this->session->has('lms_cart') && isset($this->session->get('lms_cart')[$row['idCourse']]['classroom'])) {
                            $classroom_in_chart = $this->session->get('lms_cart')[$row['idCourse']]['classroom'];
                        }

                        $control = array_diff($control, $classroom_in_chart);

                        if (count($control) == 0) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                }
            }
        } elseif ($row['course_edition'] == 1) {
            $additional_info = '';

            $editions = $this->edition_man->getEditionAvailableForCourse(Docebo::user()->getIdSt(), $row['idCourse']);

            if (count($editions) == 0) {
                return false;
            } else {
                if ($row['selling'] == 0) {
                    return true;
                } else {
                    $edition_in_chart = [];

                    if ($this->session->has('lms_cart') && isset($this->session->get('lms_cart')[$row['idCourse']]['editions'])) {
                        $edition_in_chart = $this->session->get('lms_cart')[$row['idCourse']]['editions'];
                    }

                    $editions = array_diff($editions, $edition_in_chart);

                    if (count($editions) == 0) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }
    }

    public function GetGlobalJsonTree($idCatalogue, $showRulesValues = [self::SHOW_RULES_EVERYONE, self::SHOW_RULES_LOGGED_USERS, self::SHOW_RULES_SUBSCRIBED_USERS])
    {
        $this->currentCatalogue = $idCatalogue;
        $global_tree = [];
        $top_category = $this->getMajorCategory();
        foreach ($top_category as $id_key => $val) {
            if ($this->CategoryHasChildrenCourses($id_key, $val['iLeft'], $val['iRight'], $showRulesValues)) {
                $this->children = $this->getMinorCategoryTree($id_key, $val['iLeft'], $val['iRight'], 2, $showRulesValues);
                $global_tree[] = ['text' => $val['text'], 'id_cat' => $id_key, 'nodes' => $this->children];
            }
        }

        return $global_tree;
    }

    private function getMajorCategory()
    {
        $query = 'SELECT idCategory, path, iLeft, iRight'
            . ' FROM %lms_category'
            . ' WHERE lev = 1'
            . ' ORDER BY path';

        $res = [];
        $records = sql_query($query);
        foreach ($records as $row) {
            $array = explode('/', $row['path']);
            $res[$row['idCategory']] = ['text' => end($array), 'iLeft' => $row['iLeft'], 'iRight' => $row['iRight']];
        }

        return $res;
    }

    public function getMinorCategoryTree($idCat, $ileft, $iright, $lev, $showRulesValues = [self::SHOW_RULES_EVERYONE, self::SHOW_RULES_LOGGED_USERS, self::SHOW_RULES_SUBSCRIBED_USERS])
    {
        if (($iright - $ileft > 1) && $this->CategoryHasChildrenCourses($idCat, $ileft, $iright, $showRulesValues)) {
            $q = 'SELECT idCategory, path, idParent, lev, iLeft, iRight  FROM %lms_category  
                        WHERE iLeft > ' . (int) $ileft . ' AND iRight < ' . $iright . ' AND lev=' . $lev;
            $res = [];
            $records = sql_query($q);
            foreach ($records as $row) {
                // including only if there are courses starting from here
                if ($this->CategoryHasChildrenCourses($row['idCategory'], $row['iLeft'], $row['iRight'], $showRulesValues)) {
                    $res[$row['idCategory']] = [
                        'text' => end(explode('/', $row['path'])),
                        'id_cat' => $row['idCategory'],
                    ];
                    // getting all children of next level, if any
                    $children = $this->getMinorCategoryTree($row['idCategory'], $row['iLeft'], $row['iRight'], $row['lev'] + 1, $showRulesValues);
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
     * checking if there are courses starting from id_cat and searching through all children nodes.
     */
    private function CategoryHasChildrenCourses($id_cat, $ileft, $iright, $showRulesValues = [self::SHOW_RULES_EVERYONE, self::SHOW_RULES_LOGGED_USERS, self::SHOW_RULES_SUBSCRIBED_USERS])
    {
        if ($this->show_all_category) {
            return true;
        } else {
            if ($this->currentCatalogue == 0) {
                $query = 'select count(*) as t from
                       %lms_course, %lms_category  where
                       %lms_course.idCategory = %lms_category.idCategory and
                       %lms_category.iLeft >=' . $ileft . ' and %lms_category.iRight <= ' . $iright . " and
                       %lms_course.course_type <> 'assessment' and
                       %lms_course.status NOT IN (" . CST_PREPARATION . ', ' . CST_CONCLUDED . ', ' . CST_CANCELLED . ")
                       AND (                       
                              (can_subscribe=2 AND (sub_end_date = '0000-00-00' OR sub_end_date >= '" . date('Y-m-d') . "') AND (sub_start_date = '0000-00-00' OR '" . date('Y-m-d') . "' >= sub_start_date)) OR
                              (can_subscribe=1)
                          )";
            } else {
                $query = 'select count(*) as t from
                       %lms_course, %lms_category,  %lms_catalogue_entry where
                       %lms_course.idCategory = %lms_category.idCategory and
                       %lms_category.iLeft >=' . $ileft . ' and %lms_category.iRight <= ' . $iright . " and
                       %lms_course.course_type <> 'assessment' and
                       %lms_course.status NOT IN (" . CST_PREPARATION . ', ' . CST_CONCLUDED . ', ' . CST_CANCELLED . ")
                       AND (                       
                              (can_subscribe=2 AND (sub_end_date = '0000-00-00' OR sub_end_date >= '" . date('Y-m-d') . "') AND (sub_start_date = '0000-00-00' OR '" . date('Y-m-d') . "' >= sub_start_date)) OR
                              (can_subscribe=1)
                          )
                       AND idCatalogue = " . (int) $this->currentCatalogue .
                    ' AND %lms_catalogue_entry.idEntry=%lms_course.idCourse';
            }

            if (!empty($showRulesValues)) {
                $query .= ' AND %lms_course.show_rules IN (' . implode(',', $showRulesValues) . ')';
            }

            list($c) = sql_fetch_row(sql_query($query));

            return $c > 0;
        }
    }
}
