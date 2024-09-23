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

use FormaLms\lib\Encryption\SSLEncryption;

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once _base_ . '/api/lib/lib.api.php';
require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.course.php');

require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.certificate.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.manmenu.php');
require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.upload.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.subscribe.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.date.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.edition.php');
require_once \FormaLms\lib\Forma::inc(_adm_ . '/lib/lib.field.php');
require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.eventmanager.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/category/class.categorytree.php');

class Course_API extends API
{
    /**
     * @param $date
     * @param string $format
     *
     * @return bool
     */
    private function _validateDate($date, $format = 'd-m-Y')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    /**
     * @return array
     */
    private function getAndValidateIdDayFromParams($params)
    {
        $idDay = is_numeric($params['id_day']) ? $params['id_day'] : '';

        $response['success'] = true;
        $response['data'] = $idDay;
        if (!is_numeric($idDay)) {
            $response['success'] = false;
            $response['message'] = 'Missing or Wrong ID Day: ' . $params['id_day'];
        }

        return $response;
    }

    /**
     * @param $params
     *
     * @return array
     */
    private function getAndValidateIdDateFromParams($params)
    {
        $idDate = $params['id_date'] ?? '';

        $response = [
            'success' => true,
            'data' => $idDate,
        ];
        if (empty($idDate)) {
            $response['success'] = false;
            $response['message'] = 'Missing or Wrong ID Date' . $params['id_date'];
        }

        return $response;
    }

    /**
     * @param $params
     *
     * @return array
     */
    private function getAndValidateSendCalendarFromParams($params)
    {
        $sendCalendar = (bool) ($params['sendCalendar'] ?? false);

        $response = [
            'success' => true,
            'data' => $sendCalendar,
        ];

        if (!is_bool($sendCalendar)) {
            $response['success'] = false;
            $response['message'] = 'Missing or Wrong SendCalendar' . $params['sendCalendar'];
        }

        return $response;
    }

    /**
     * @param $params
     *
     * @return array
     */
    private function getAndValidateCourseIdCourseFromParams($params)
    {
        $courseId = $params['course_id'] ?? '';

        $response = [
            'success' => true,
            'data' => $courseId,
        ];

        if (empty($courseId)) {
            $response['success'] = false;
            $response['message'] = 'Missing or Wrong Course ID' . $params['course_id'];
        }

        $course = new CourseAlms();
        $info = $course->getInfo($courseId);
        if (empty($info)) {
            $response['success'] = false;
            $response['message'] = 'Course not found:' . $params['course_id'];
        }

        return $response;
    }

    /**
     * @param $params
     *
     * @return array
     */
    private function validateIdDateExistsInCourseFromParams($params)
    {
        $courseId = $params['course_id'] ?? '';

        $idDate = $params['id_date'] ?? '';

        $response = ['success' => true, 'data' => $idDate];

        $model = new ClassroomAlms($courseId, $idDate);

        $dates = $model->classroom_man->getCourseDate($courseId);

        if (!array_key_exists($idDate, $dates)) {
            $response['success'] = false;
            $response['message'] = 'Date does not exists in course';
        }

        return $response;
    }

    public function getCourses($params)
    {
        $response = ['success' => true, 'courses' => []];

        $id_category = isset($params['category']) ? (int) $params['category'] : false;

        $course_man = new Man_Course();
        $course_list = $course_man->getAllCoursesWithMoreInfo($id_category);

        foreach ($course_list as $course_info) {
            $course_info['dates'] = [];

            if ($category = $course_info['idCategory']) {
                $category = $course_man->getCategory($category)['path'];
            } else {
                $category = null;
            }

            if ($course_info['course_type'] === 'classroom') {
                $classroom_man = new DateManager();
                $course_dates = $classroom_man->getCourseDate($course_info['idCourse']);

                require_once \FormaLms\lib\Forma::include(_adm_ . '/lib/', 'lib.customfield.php');
                $courseCustomFields = [];
                $fman = new CustomFieldList();
                $fman->setFieldArea('COURSE_CLASSROOM');

                if ($fman->getNumberFieldbyArea() > 0) {
                    $courseCustomFields = $fman->playFieldsFlat($course_info['idCourse']);
                }

                foreach ($course_dates as $key => $course_date) {
                    $classroomModel = new ClassroomAlms($course_info['idCourse'], $course_date['id_date']);
                    unset($course_dates[$key]['id_course']);
                    $course_dates[$key]['days'] = array_values($classroom_man->getAllDateDay($course_date['id_date'], ['id_day', 'id_date']));

                    $customFields = $courseCustomFields;
                    foreach ($customFields as $customFieldKey => $customField) {
                        if ($customField['type_field'] === 'dropdown') {
                            $customFields[$customFieldKey]['elems'] = $fman->getDropdownElems($customField['id']);
                        }
                        $customFields[$customFieldKey]['entry'] = $classroomModel->getCustomFieldsValue($course_date['id_date'], $customField['id']);
                    }

                    $course_dates[$key]['custom_fields'] = array_values($customFields);

                    $course_info['dates'] = array_values($course_dates);
                }
            }
            $response['courses'][] = [
                'course_id' => $course_info['idCourse'],
                'code' => str_replace('&', '&amp;', $course_info['code']),
                'course_name' => str_replace('&', '&amp;', $course_info['name']),
                'course_description' => str_replace('&', '&amp;', $course_info['description']),
                'course_box_description' => str_replace('&', '&amp;', $course_info['box_description']),
                'status' => $course_info['status'],
                'selling' => $course_info['selling'],
                'price' => $course_info['prize'],
                'subscribe_method' => $course_info['subscribe_method'],
                'course_edition' => $course_info['course_edition'],
                'course_type' => $course_info['course_type'],
                'can_subscribe' => $course_info['can_subscribe'],
                'sub_start_date' => $course_info['sub_start_date'],
                'sub_end_date' => $course_info['sub_end_date'],
                'date_begin' => $course_info['date_begin'],
                'date_end' => $course_info['date_end'],
                'course_link' => FormaLms\lib\Get::site_url() . _folder_lms_ . "/index.php?modname=course&amp;op=aula&amp;idCourse={$course_info['idCourse']}",
                'img_course' => $course_info['img_course'] ? FormaLms\lib\Get::site_url() . _folder_files_ . '/' . _folder_lms_ . '/' . FormaLms\lib\Get::sett('pathcourse') . $course_info['img_course'] : '',
                'category_id' => $course_info['idCategory'],
                'category' => $category,
                'dates' => $course_info['dates'],
            ];
        }

        return $response;
    }

    //e-learning editions
    public function getEditions($params)
    {
        $response = [];

        $response['success'] = true;

        $courseId = isset($params['course_id']) ? (int) $params['course_id'] : false;
        $course_code = isset($params['course_code']) ? $params['course_code'] : false;

        if (empty($courseId) && empty($course_code)) {
            return false;
        // return array('success'=>true, 'debug'=>print_r($params, true));
        } elseif (empty($courseId) && !empty($course_code)) { // grab course info by code:
            $db = \FormaLms\db\DbConn::getInstance();
            $qtxt = "SELECT * FROM %lms_course
					WHERE code='" . $course_code . "'
					LIMIT 0,1";
            $q = $db->query($qtxt);
            $course_info = $db->fetch_assoc($q);
            if (!empty($course_info)) {
                $courseId = (int) $course_info['idCourse'];
            } else { // course not found
                return false;
                // return array('success'=>'true', 'debug'=>print_r($course_info));
            }
        }

        $edition_man = new EditionManager();
        $course_list = $edition_man->getEditionsInfoByCourses($courseId);

        $course_man = new Man_Course();
        $course = $course_man->getCourseInfo($courseId);

        foreach ($course_list[$courseId] as $key => $course_info) {
            $response['course_info'][] = [
                'course_id' => $course['idCourse'],
                'edition_id' => $course_info['id_edition'],
                'code' => str_replace('&', '&amp;', $course_info['code']),
                'course_name' => str_replace('&', '&amp;', $course_info['name']),
                'course_description' => str_replace('&', '&amp;', $course_info['description']),
                'status' => $course_info['status'],
                'selling' => $course['selling'],
                'price' => $course_info['price'],
                'subscribe_method' => $course['subscribe_method'],
                'sub_start_date' => $course_info['sub_date_begin'],
                'sub_end_date' => $course_info['sub_date_end'],
                'date_begin' => $course_info['date_begin'],
                'date_end' => $course_info['date_end'],
                'course_link' => FormaLms\lib\Get::site_url() . _folder_lms_ . '/index.php?modname=course&amp;op=aula&amp;idCourse=' . $course['idCourse'],
            ];
        }

        //$output['debug']=print_r($course_list, true).print_r($course, true);

        return $response;
    }

    public function getClassrooms($params)
    {
        $response = [];

        $response['success'] = true;

        $courseId = isset($params['course_id']) ? (int) $params['course_id'] : false;
        $course_code = isset($params['course_code']) ? $params['course_code'] : false;

        if (empty($courseId) && empty($course_code)) {
            return false;
        // return array('success'=>true, 'debug'=>print_r($params, true));
        } elseif (empty($courseId) && !empty($course_code)) { // grab course info by code:
            $db = \FormaLms\db\DbConn::getInstance();
            $qtxt = "SELECT * FROM %lms_course
					WHERE code='" . $course_code . "'
					LIMIT 0,1";
            $q = $db->query($qtxt);
            $course_info = $db->fetch_assoc($q);
            if (!empty($course_info)) {
                $courseId = (int) $course_info['idCourse'];
            } else { // course not found
                return false;
                // return array('success'=>'true', 'debug'=>print_r($course_info));
            }
        }

        $classroom_man = new DateManager();
        $course_list = $classroom_man->getCourseDate($courseId);

        $course_man = new Man_Course();
        $course = $course_man->getCourseInfo($courseId);

        foreach ($course_list as $key => $course_info) {
            $response['course_info'][] = [
                'course_id' => $course['idCourse'],
                'date_id' => $course_info['id_date'],
                'code' => str_replace('&', '&amp;', $course_info['code']),
                'course_name' => str_replace('&', '&amp;', $course_info['name']),
                'course_description' => str_replace('&', '&amp;', $course_info['description']),
                'status' => $course_info['status'],
                'selling' => $course['selling'],
                'price' => $course_info['price'],
                'subscribe_method' => $course['subscribe_method'],
                'sub_start_date' => $course_info['sub_start_date'],
                'sub_end_date' => $course_info['sub_end_date'],
                'date_begin' => $course_info['date_begin'],
                'date_end' => $course_info['date_end'],
                'num_day' => $course_info['num_day'],
                'classroom' => $course_info['classroom'],
                'course_link' => FormaLms\lib\Get::site_url() . _folder_lms_ . '/index.php?modname=course&amp;op=aula&amp;idCourse=' . $course['idCourse'],
            ];
        }

        //$output['debug']=print_r($course_list, true).print_r($course, true);

        return $response;
    }

    protected function getUserLevelId($my_level)
    {
        if ($my_level === false) {
            return false;
        }

        $lev_arr = [
            'administrator' => 7,
            'instructor' => 6,
            'mentor' => 5,
            'tutor' => 4,
            'student' => 3,
            'ghost' => 2,
            'guest' => 1,
        ];

        return (int) $lev_arr[$my_level];
    }

    protected function getUserStatusId($my_status)
    {
        if ($my_status === false) {
            return false;
        }

        $lev_arr = [
            'waiting_list' => _CUS_WAITING_LIST,
            'to_confirm' => _CUS_CONFIRMED,
            'subscribed' => _CUS_SUBSCRIBED,
            'started' => _CUS_BEGIN,
            'completed' => _CUS_END,
            'suspended' => _CUS_SUSPEND,
            'overbooking' => _CUS_OVERBOOKING,
        ];

        return (int) $lev_arr[$my_status];
    }

    protected function fillCourseDataFromParams(
        &$params, &$db, &$courseId, &$edition_id, &$classroom_id,
        &$course_code, &$edition_code, &$classroom_code,
        &$course_info, &$edition_info, &$classroom_info, &$response
    ) {
        // -- read course info / id ----------

        if (empty($courseId) && empty($course_code)) {
            return false;
        // return array('success'=>true, 'debug'=>print_r($params, true));
        } elseif (empty($courseId) && !empty($course_code)) { // grab course info by code:
            $qtxt = "SELECT * FROM %lms_course
					WHERE code='" . $course_code . "'
					LIMIT 0,1";
            $q = $db->query($qtxt);
            $course_info = $db->fetch_assoc($q);
            if (!empty($course_info)) {
                $courseId = (int) $course_info['idCourse'];
            } else { // course not found
                return false;
            }
        } elseif (!empty($courseId)) {
            $qtxt = "SELECT * FROM %lms_course
					WHERE idCourse='" . $courseId . "'
					LIMIT 0,1";
            $q = $db->query($qtxt);
            $course_info = $db->fetch_assoc($q);
            if (empty($course_info)) { // course not found
                return false;
                // return array('success'=>'true', 'debug'=>print_r($course_info));
            }
        }

        // -- read edition info / id ----------

        if (!empty($edition_id) || !empty($edition_code)) {
            if (empty($edition_id) && !empty($edition_code)) { // grab edition info by code:
                $qtxt = "SELECT * FROM %lms_course_editions
					WHERE id_course='" . $courseId . "' AND code='" . $edition_code . "'
					LIMIT 0,1";
                $q = $db->query($qtxt);
                $edition_info = $db->fetch_assoc($q);
                if (!empty($edition_info)) {
                    $edition_id = (int) $edition_info['id_edition'];
                } else { // edition not found
                    return false;
                }
            } elseif (!empty($edition_id)) {
            }
        }

        // -- read classroom info / id ----------

        if (!empty($classroom_id) || !empty($classroom_code)) {
            if (empty($classroom_id) && !empty($classroom_code)) { // grab edition info by code:
                $qtxt = "SELECT * FROM %lms_course_date
					WHERE id_course='" . $courseId . "' AND code='" . $classroom_code . "'
					LIMIT 0,1";
                $q = $db->query($qtxt);
                $classroom_info = $db->fetch_assoc($q);
                if (!empty($classroom_info)) {
                    $classroom_id = (int) $classroom_info['id_date'];
                } else { // classroom not found
                    return false;
                }
            } elseif (!empty($classroom_id)) {
            }
        }
    }

    public function addUserSubscription($params)
    {
        $response = [];

        $response['success'] = true;

        if (empty($params['idst']) || (int) $params['idst'] <= 0) {
            $response['success'] = false;
            $response['message'] = 'INVALID REQUEST';

            return $response;
        } else {
            $user_id = $params['idst'];
        }

        $courseId = isset($params['course_id']) ? (int) $params['course_id'] : false;
        $course_code = isset($params['course_code']) ? $params['course_code'] : false;
        $edition_id = isset($params['edition_id']) ? (int) $params['edition_id'] : false;
        $edition_code = isset($params['edition_code']) ? $params['edition_code'] : false;
        $classroom_id = isset($params['classroom_id']) ? (int) $params['classroom_id'] : false;
        $classroom_code = isset($params['classroom_code']) ? $params['classroom_code'] : false;

        $user_level = $this->getUserLevelId(isset($params['user_level']) ? $params['user_level'] : 'student');

        if (!isset($params['sendmail']) || $params['sendmail'] == '') {
            $sendMailToUser = false;
        } else {
            $sendMailToUser = true;
        }

        $acl_man = \FormaLms\lib\Forma::getAclManager();
        $course_man = new Man_Course();
        $db = \FormaLms\db\DbConn::getInstance();

        $user_data = $this->aclManager->getUser($user_id, false);

        if (!$user_data) {
            $response['success'] = false;
            $response['message'] = 'NO_DATA_FOUND';

            return $response;
        }

        $course_info = false;
        $edition_info = false;
        $classroom_info = false;

        $course_exists = $this->fillCourseDataFromParams(
            $params, $db, $courseId, $edition_id, $classroom_id, $course_code,
            $edition_code, $classroom_code, $course_info, $edition_info,
            $classroom_info, $response
        );
        if ($course_exists === false) {
            $response['success'] = false;
            $response['message'] = 'NO_DATA_FOUND';

            return $response;
        }

        // --------------- add user: -----------------------------------

        $model = new SubscriptionAlms($courseId, $edition_id, $classroom_id);
        $formaCourse = new FormaCourse($courseId);
        $level_idst = $formaCourse->getCourseLevel($courseId);
        if (count($level_idst) == 0 || $level_idst[1] == '') {
            $level_idst = FormaCourse::createCourseLevel($courseId);
        }
        $waiting = 0;

        $acl_man->addToGroup($level_idst[$user_level], $user_id);

        $subscribe_ok = $model->subscribeUser($user_id, $user_level, $waiting, false, false);

        if (!$subscribe_ok) {
            $acl_man->removeFromGroup($level_idst[$user_level], $user_id);
            $response['success'] = false;
        } else {
            $response['message'] = 'User has been subscribed to the course';
        }

        if ($sendMailToUser) {
            // Send Message
            $reg_code = null;
            $uma = new UsermanagementAdm();
            $nodes = $uma->getUserFolders($user_id);
            if ($nodes) {
                $idst_oc = array_keys($nodes)[0];

                $query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idst_oc LIMIT 1");
                if ($query) {
                    $reg_code = sql_fetch_object($query)->idOrg;
                }
            }

            $array_subst = [
                '[url]' => FormaLms\lib\Get::site_url(),
                '[dynamic_link]' => getCurrentDomain($reg_code) ?: FormaLms\lib\Get::site_url(),
                '[course]' => $course_info['name'], ];

            $msg_composer = new EventMessageComposer();
            $msg_composer->setSubjectLangText('email', '_APPROVED_SUBSCRIBED_SUBJECT', false);
            $msg_composer->setBodyLangText('email', '_APPROVED_SUBSCRIBED_TEXT', $array_subst);

            $recipients = [$user_id];

            if ($course_info['sendCalendar']) {
                $uinfo = \FormaLms\lib\Forma::getAclManager()->getUser($user_id, false);
                $calendar = CalendarManager::getCalendarDataContainerForDateDays((int) $courseId, (int) $classroom_id, (int) $uinfo[ACL_INFO_IDST]);
                $msg_composer->setAttachments([$calendar->getFile()]);
            }

            if (!empty($recipients)) {
                createNewAlert('UserCourseInsertedApi', 'subscribe', 'insert', '1', 'User subscribed API', $recipients, $msg_composer);
            }
        }

        return $response;
    }

    public function updateUserSubscription($params)
    {
        $response = [];

        $response['success'] = true;

        if (empty($params['idst']) || (int) $params['idst'] <= 0) {
            return false;
        // return array('success'=>true, 'debug'=>print_r($params, true));
        } else {
            $user_id = $params['idst'];
        }

        $courseId = isset($params['course_id']) ? (int) $params['course_id'] : false;
        $course_code = isset($params['course_code']) ? $params['course_code'] : false;
        $edition_id = isset($params['edition_id']) ? (int) $params['edition_id'] : false;
        $edition_code = isset($params['edition_code']) ? $params['edition_code'] : false;
        $classroom_id = isset($params['classroom_id']) ? (int) $params['classroom_id'] : false;
        $classroom_code = isset($params['classroom_code']) ? $params['classroom_code'] : false;

        $user_level = $this->getUserLevelId(isset($params['user_level']) ? $params['user_level'] : false);
        $user_status = $this->getUserStatusId(isset($params['user_status']) ? $params['user_status'] : false);

        $acl_man = \FormaLms\lib\Forma::getAclManager();
        $course_man = new Man_Course();
        $db = \FormaLms\db\DbConn::getInstance();

        $course_info = false;
        $edition_info = false;
        $classroom_info = false;

        $this->fillCourseDataFromParams(
            $params, $db, $courseId, $edition_id, $classroom_id, $course_code,
            $edition_code, $classroom_code, $course_info, $edition_info,
            $classroom_info, $response
        );

        // --------------- update user subscription: ------------------------

        $model = new SubscriptionAlms($courseId, $edition_id, $classroom_id);
        $formaCourse = new FormaCourse($courseId);
        $level_idst = $formaCourse->getCourseLevel($courseId);
        if (count($level_idst) == 0 || $level_idst[1] == '') {
            $level_idst = FormaCourse::createCourseLevel($courseId);
        }

        $update_ok = true;

        // -- update level -----
        if (!empty($user_level)) {
            $old_level = $model->getUserLevel($user_id);

            if (isset($level_idst[$user_level]) && isset($level_idst[$old_level])) {
                $acl_man->removeFromGroup($level_idst[$old_level], $user_id);
                $acl_man->addToGroup($level_idst[$user_level], $user_id);
                $ok = $model->updateUserLevel($user_id, $user_level);
                if (!$ok) {
                    $update_ok = false;
                }
            }
        }

        $status_arr = $model->getUserStatusList();
        // -- update status -----
        if (!empty($user_status)) {
            if (isset($status_arr[$user_status])) {
                if ($model->updateUserStatus($user_id, $user_status)) {
                    // SET EDIT STATUS SUBSCRIPTION EVENT
                    // $event = new \appCore\Events\Core\Courses\CourseSubscriptionEditStatusEvent();
                    // $userModel = new UsermanagementAdm();
                    // $user = $userModel->getProfileData($user_id);

                    // require_once(_lms_ . '/lib/lib.course.php');
                    // $formaCourse = new FormaCourse($course_id);

                    // $event->setUser($user);
                    // $event->setStatus(['id' => $user_status, 'name' => $status_arr[$user_status]]);
                    // $event->setCourse($formaCourse->course_info);
                    // \appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\Courses\CourseSubscriptionEditStatusEvent::EVENT_NAME, $event);
                } else {
                    $update_ok = false;
                }
            }
        }

        if (!$update_ok) {
            $response['success'] = false;
        } else {
            $response['message'] = 'User subscription has been updated';
        }

        return $response;
    }

    public function deleteUserSubscription($params)
    {
        $response = [];

        $response['success'] = true;

        if (empty($params['idst']) || (int) $params['idst'] <= 0) {
            return false;
        // return array('success'=>true, 'debug'=>print_r($params, true));
        } else {
            $user_id = $params['idst'];
        }

        $courseId = isset($params['course_id']) ? (int) $params['course_id'] : false;
        $course_code = isset($params['course_code']) ? $params['course_code'] : false;
        $edition_id = isset($params['edition_id']) ? (int) $params['edition_id'] : false;
        $edition_code = isset($params['edition_code']) ? $params['edition_code'] : false;
        $classroom_id = isset($params['classroom_id']) ? (int) $params['classroom_id'] : false;
        $classroom_code = isset($params['classroom_code']) ? $params['classroom_code'] : false;

        $user_level = $this->getUserLevelId(isset($params['user_level']) ? $params['user_level'] : false);
        $user_status = $this->getUserStatusId(isset($params['user_status']) ? $params['user_status'] : false);

        $acl_man = \FormaLms\lib\Forma::getAclManager();
        $course_man = new Man_Course();
        $db = \FormaLms\db\DbConn::getInstance();

        $course_info = false;
        $edition_info = false;
        $classroom_info = false;

        $this->fillCourseDataFromParams(
            $params, $db, $courseId, $edition_id, $classroom_id, $course_code,
            $edition_code, $classroom_code, $course_info, $edition_info,
            $classroom_info, $response
        );

        // --------------- delete user subscription: ------------------------

        $model = new SubscriptionAlms($courseId, $edition_id, $classroom_id);
        $formaCourse = new FormaCourse($courseId);
        $level_idst = $formaCourse->getCourseLevel($courseId);

        $old_level = $model->getUserLevel($user_id);

        $delete_ok = $model->delUser($user_id);

        if ($delete_ok) {
            if (empty($edition_id) && empty($classroom_id)) {
                $acl_man->removeFromGroup($level_idst[$old_level], $user_id);
            }
        }

        if (!$delete_ok) {
            $response['success'] = false;
        } else {
            $response['message'] = 'User has been removed from the course';
        }

        return $response;
    }

    public function getUsersSubscription($params)
    {
        $response = [];

        $response['success'] = true;

        $idCourse = $params['course_id'];
        $idEdition = $params['edition_id'];
        $idDate = $params['date_id'];
        $idUsers = !empty($params['user_ids']) ? explode(',', $params['user_ids']) : [];
        $userFilterEnabled = count($idUsers) > 0;

        try {
            $subscriptionModel = new SubscriptionAlms($idCourse, $idEdition, $idDate);

            $arrayUsers = $subscriptionModel->loadUser(false, false, false, false, false, false);

            $list = [];
            foreach ($arrayUsers as $value) {
                if (!$userFilterEnabled || in_array($value['id_user'], $idUsers)) {
                    $is_valid_begin = $value['date_begin_validity'];
                    $is_valid_expire = $value['date_expire_validity'];

                    $del_url = 'ajax.adm_server.php?r=' . $this->link . '/del&id_user=' . $value['id_user']
                        . '&id_course=' . $this->id_course . '&id_edition=' . $this->id_edition . '&id_date=' . $this->id_date;

                    $record = [
                        'id' => $value['id_user'],
                        'userid' => highlightText($value['userid'], $filter['text']),
                        'fullname' => highlightText($value['fullname'], $filter['text']),
                        'level' => $value['level_id'],
                        'status' => $value['status_id'],
                        'date_begin' => $is_valid_begin ? Format::date($value['date_begin_validity'], 'date') : false,
                        'date_expire' => $is_valid_expire ? Format::date($value['date_expire_validity'], 'date') : false,
                        'date_begin_timestamp' => $is_valid_begin ? Format::toTimestamp($value['date_begin_validity']) : 0,
                        'date_expire_timestamp' => $is_valid_expire ? Format::toTimestamp($value['date_expire_validity']) : 0,
                        'del' => $del_url,
                        'date_complete' => $value['date_complete'],
                    ];
                    if (isset($value['overbooking'])) {
                        $record['overbooking'] = $value['overbooking'];
                        if ($value['overbooking']) {
                            $record['status'] = '' . _CUS_OVERBOOKING;
                        }
                    }
                    $list[] = $record;
                }
            }

            $response['courseSubscriptions'] = $list;
        } catch (\Exception $exception) {
            $response['success'] = false;
            $response['message'] = $exception->getMessage();
        }

        return $response;
    }

    public function subscribeUserWithCode($params)
    {
        $response = [];

        $response['success'] = true;

        if (empty($params['idst']) || (int) $params['idst'] <= 0) {
            return false;
        } else {
            $user_id = $params['idst'];
        }

        $registration_code_type = $params['reg_code_type'];
        $code = $params['reg_code'];
        $code = strtoupper($code);
        $code = str_replace('-', '', $code);

        if (empty($registration_code_type) || empty($code)) {
            $response['success'] = false;
        } else {
            if ($registration_code_type == 'tree_course') {
                $code = substr($code, 10, 10);
            }

            $course_registration_result = false;
            $man_course_user = new Man_CourseUser();
            $course_registration_result = $man_course_user->subscribeUserWithCode($code, $user_id);

            if ($course_registration_result <= 0) {
                if ($course_registration_result == 0) {
                    $response['message'] = 'Invalid code';
                } elseif ($course_registration_result < 0) {
                    $response['message'] = 'Code already used';
                }
                $response['success'] = false;
            } else {
                $response['message'] = 'User has been subscribed to the course';
            }
        }

        return $response;
    }

    /**
     * dev: LRZ
     * Get certificate by username.
     *
     * @param $params
     * - username
     * - course_id (optional)
     *
     * @return array
     */
    public function getCertificateByUser($params)
    {
        $response = [];

        $response['success'] = true;

        if (empty($params['username'])) {
            return false;
        } else {
            $username = $params['username'];
        }

        if (!empty($params['course_id'])) {
            $id_course = (int) $params['course_id'];
        }

        $db = \FormaLms\db\DbConn::getInstance();
        $qtxt = "SELECT idst, firstname, lastname  FROM core_user 
				WHERE userid='/" . $username . "' ";
        $q = $db->query($qtxt);
        $user_info = $db->fetch_assoc($q);

        $response['idst'] = (int) $user_info['idst'];
        $response['firstname'] = $user_info['firstname'];
        $response['lastname'] = $user_info['lastname'];
        $response['userid'] = $username;
        if ($response['idst'] == 0) {
            $response['message'] = 'User not found';
        }

        $qcert = 'select id_course, name, code, on_date, cert_file from  learning_certificate_assign, learning_course  where id_user=' . $response['idst'] . ' and idCourse=id_course';
        if ($id_course > 0) {
            $qcert = $qcert . ' and id_course=' . $id_course;
        }
        $qcert = $qcert . ' order by on_date desc';

        $response['certificate_list'] = [];

        $result = $db->query($qcert);
        foreach ($result as $row) {
            $response['certificate_list'][] = ['course_id' => $row['id_course'],
                'course_code' => $row['code'],
                'course_name' => $row['name'],
                'date_generate' => $row['on_date'],
                'cert_file' => FormaLms\lib\Get::site_url() . 'api/user/downloadCertificate/' . (SSLEncryption::encrpytDownloadUrl($row['cert_file'])),
           //     'cert_file' => FormaLms\lib\Get::site_url() . 'files/appLms/certificate/' . $row['cert_file'],
            ];
        }

        return $response;
    }

    /**
     * dev: LRZ
     * Get certificate by id_course.
     *
     * @param $params
     * - username  (optional)
     * - course_id
     *
     * @return array
     */
    public function getCertificateByCourse($params)
    {
        $response = [];

        $response['success'] = true;

        if (empty($params['course_id'])) {
            return false;
        } else {
            $id_course = $params['course_id'];
        }

        if (!empty($params['username'])) {
            $username = $params['username'];
        }

        $db = \FormaLms\db\DbConn::getInstance();
        $qtxt = 'SELECT idCourse, code, name, box_description  FROM learning_course 
				WHERE idCourse=' . (int) $id_course;
        $q = $db->query($qtxt);
        $course_info = $db->fetch_assoc($q);

        $response['course_id'] = (int) $id_course;
        $response['course_code'] = $course_info['code'];
        $response['course_name'] = $course_info['name'];
        // if ( strlen($course_info['box_description']) >=50 ){
        //     $course_info['box_description'] = substr($course_info['box_description'], 0, 47) . '...';
        // }
        $response['box_description'] = $course_info['box_description'];
        if ((int) $course_info['idCourse'] == 0) {
            $response['message'] = 'Course not found';
        }

        $qcert = 'select id_course, firstname, lastname, userid, idst, on_date, cert_file from  learning_certificate_assign, %adm_user   where id_course=' . $response['course_id'] . ' and id_user=idst';
        if ($username != '') {
            $qcert = $qcert . " and userid = '/" . $username . "'";
        }
        $qcert = $qcert . ' order by on_date desc';

        $response['certificate_list'] = [];

        $result = $db->query($qcert);
        foreach ($result as $row) {
            $field_man = new FieldList();
            $field_data = $field_man->getFieldsAndValueFromUser($row['idst'], false, true);

            $fields = [];
            foreach ($field_data as $field_id => $value) {
                $fields[] = ['id' => $field_id, 'name' => $value[0], 'value' => $value[1]];
            }

            $response['certificate_list'][] = [
                'idst' => $row['idst'],
                'firstname' => $row['firstname'],
                'lastname' => $row['lastname'],
                'userid' => $row['userid'],
                'date_generate' => $row['on_date'],

                'cert_file' => FormaLms\lib\Get::site_url() . 'api/user/downloadCertificate/' . (SSLEncryption::encrpytDownloadUrl($row['cert_file'])),

                'custom_fields' => $fields,
            ];
        }

        return $response;
    }

    // ---------------------------------------------------------------------------
    // LRZ
    // Adding Course Category:
    // Input param:
    // category_id: category id of the parent category; category is created on root if no parent ID passed
    // node_name: category name
    public function addCategory($params)
    {
        $category_id = isset($params['category_id']) ? (int) $params['category_id'] : 0;
        $category_name = isset($params['name']) ? $params['name'] : false;

        if ($category_name == false) {
            $response = ['success' => false, 'message' => 'Wrong parameters'];
        } else {
            $treecat = new Categorytree();

            $new_category_id = $treecat->addFolderById($category_id, $category_name);
            if ($new_category_id != false && $new_category_id > 0) {
                $response = ['success' => true, 'category_id' => $new_category_id, 'parent_category_id' => $params['category_id']];
            } else {
                $response = ['success' => false, 'message' => 'Cannot create category'];
            }
        }

        return $response;
    }

    private function getInfoCourseAdd()
    {
        $db = \FormaLms\db\DbConn::getInstance();
        $qtxt = 'SELECT max(idCourse) as max_id  FROM learning_course ';
        $q = $db->query($qtxt);
        $course_info = $db->fetch_assoc($q);

        return $course_info['max_id'];
    }

    public function addCourse($params)
    {
        $response = [];
        $response['success'] = true;

        $params['advance'] = isset($params['advance']) ? $params['advance'] : '';
        $params['allow_overbooking'] = isset($params['allow_overbooking']) ? 1 : 0;
        $params['selected_menu'] = isset($params['selected_menu']) ? $params['selected_menu'] : 11;
        if (empty($params['allow_overbooking'])) {
            unset($params['allow_overbooking']);
        }
        $params['auto_unsubscribe'] = isset($params['auto_unsubscribe']) ? 1 : 0;
        if (empty($params['auto_unsubscribe'])) {
            unset($params['auto_unsubscribe']);
        }
        $params['can_subscribe'] = isset($params['can_subscribe']) ? $params['can_subscribe'] : false;
        $params['course_advanced'] = isset($params['course_advanced']) ? 1 : 0;
        if (empty($params['course_advanced'])) {
            unset($params['course_advanced']);
        }
        $params['course_autoregistration_code'] = isset($params['course_autoregistration_code']) ? $params['course_autoregistration_code'] : false;
        $params['course_code'] = isset($params['course_code']) ? $params['course_code'] : false;
        $params['course_date_begin'] = isset($params['course_date_begin']) ? $params['course_date_begin'] : false;
        $params['course_date_end'] = isset($params['course_date_end']) ? $params['course_date_end'] : false;
        $params['course_day_of'] = isset($params['course_day_of']) ? $params['course_day_of'] : false;
        $params['course_descr'] = isset($params['course_descr']) ? $params['course_descr'] : false;
        $params['course_difficult'] = isset($params['course_difficult']) ? $params['course_difficult'] : false;
        $params['course_edition'] = isset($params['course_edition']) ? 1 : 0;
        if (empty($params['course_edition'])) {
            unset($params['course_edition']);
        }
        $params['course_em'] = isset($params['close_lo_perm']) ? 1 : 0;
        if (empty($params['course_em'])) {
            unset($params['course_em']);
        }
        $params['course_lang'] = isset($params['course_lang']) ? $params['course_lang'] : 'italian';
        $params['course_medium_time'] = isset($params['course_medium_time']) ? $params['course_medium_time'] : false;
        $params['send_calendar'] = isset($params['send_calendar']) ? $params['send_calendar'] : false;
        $params['course_name'] = isset($params['course_name']) ? $params['course_name'] : false;
        $params['course_prize'] = isset($params['course_prize']) ? $params['course_prize'] : false;
        $params['course_progress'] = isset($params['course_progress']) ? 1 : 0;
        if (empty($params['course_progress'])) {
            unset($params['course_progress']);
        }
        $params['course_quota'] = isset($params['course_quota']) ? $params['course_quota'] : false;
        $params['course_sell'] = isset($params['course_sell']) ? 1 : 0;
        if (empty($params['course_sell'])) {
            unset($params['course_sell']);
        }
        $params['course_show_rules'] = isset($params['course_show_rules']) ? $params['course_show_rules'] : false;
        $params['course_sponsor_link'] = isset($params['course_sponsor_link']) ? $params['course_sponsor_link'] : false;
        $params['course_status'] = isset($params['course_status']) && $params['course_status'] ? $params['course_status'] : 2;
        $params['course_subs'] = isset($params['course_subs']) ? $params['course_subs'] : false;
        $params['course_time'] = isset($params['course_time']) ? 1 : 0;
        if (empty($params['course_time'])) {
            unset($params['course_time']);
        }
        $params['course_type'] = isset($params['course_type']) ? $params['course_type'] : false;
        $params['credits'] = isset($params['credits']) ? $params['credits'] : false;
        $params['direct_play'] = isset($params['direct_play']) ? $params['direct_play'] : false;
        if (empty($params['direct_play'])) {
            unset($params['direct_play']);
        }
        $params['idCategory'] = isset($params['idCategory']) ? $params['idCategory'] : false;
        $params['inherit_quota'] = isset($params['inherit_quota']) ? 1 : 0;
        if (empty($params['inherit_quota'])) {
            unset($params['inherit_quota']);
        }
        $params['max_num_subscribe'] = isset($params['max_num_subscribe']) ? $params['max_num_subscribe'] : false;
        $params['min_num_subscribe'] = isset($params['min_num_subscribe']) ? $params['min_num_subscribe'] : false;
        $params['random_course_autoregistration_code'] = isset($params['random_course_autoregistration_code']) ? $params['random_course_autoregistration_code'] : false;
        $params['show_result'] = isset($params['show_result']) ? 1 : 0;
        if (empty($params['show_result'])) {
            unset($params['show_result']);
        }
        $params['show_who_online'] = isset($params['show_who_online']) ? 1 : 0;
        $params['sub_end_date'] = isset($params['sub_end_date']) ? $params['sub_end_date'] : false;
        $params['sub_start_date'] = isset($params['sub_start_date']) ? $params['sub_start_date'] : false;
        $params['unsubscribe_date_limit'] = isset($params['unsubscribe_date_limit']) ? $params['unsubscribe_date_limit'] : false;
        $params['use_logo_in_courselist'] = isset($params['use_logo_in_courselist']) ? 1 : 0;
        if (empty($params['use_logo_in_courselist'])) {
            unset($params['use_logo_in_courselist']);
        }
        $params['use_unsubscribe_date_limit'] = isset($params['use_unsubscribe_date_limit']) ? 1 : 0;
        if (empty($params['use_unsubscribe_date_limit'])) {
            unset($params['use_unsubscribe_date_limit']);
        }

        $course = new CourseAlms();
        $res = $course->insCourse($params);

        if ($res['res'] == '_ok_course') {
            $id_course = $this->getInfoCourseAdd();
            $response['message'] = $res['res'] . ' -  ' . $params['course_type'];
            $response['course_id'] = $id_course;
        } else {
            $response['success'] = false;
            $response['message'] = 'Creation failed';
        }

        return $response;
    }

    public function addClassroom($params)
    {
        $response = $this->getAndValidateCourseIdCourseFromParams($params);
        $courseId = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $classroomManager = new DateManager();

        $params['classroom_sub_start_date'] = substr(Format::dateDb($params['classroom_sub_start_date'], 'date'), 0, 10);
        $params['classroom_sub_end_date'] = substr(Format::dateDb($params['classroom_sub_end_date'], 'date'), 0, 10);
        $params['classroom_unsubscribe_date_limit'] = substr(Format::dateDb($params['classroom_unsubscribe_date_limit'], 'date'), 0, 10);

        $res = $classroomManager->insDate(
            $courseId,
            $params['classroom_code'] ? $params['classroom_code'] : false,
            $params['classroom_name'] ? $params['classroom_name'] : false,
            $params['classroom_descr'] ? $params['classroom_descr'] : false,
            $params['classroom_medium_time'] ? $params['classroom_medium_time'] : false,
            $params['classroom_max_users'] ? $params['classroom_max_users'] : false,
            $params['classroom_price'] ? $params['classroom_price'] : false,
            $params['classroom_allow_overbooking'] ? $params['classroom_allow_overbooking'] : false,
            $params['classroom_status'] ? $params['classroom_status'] : 0,
            $params['classroom_test_type'] ? $params['classroom_test_type'] : 0,
            $params['classroom_sub_start_date'] ? $params['classroom_sub_start_date'] : null,
            $params['classroom_sub_end_date'] ? $params['classroom_sub_end_date'] : null,
            $params['classroom_unsubscribe_date_limit'] ? $params['classroom_unsubscribe_date_limit'] : false
        );

        if ($res) {
            $response['success'] = true;
            $response['id_date'] = $res;
        } else {
            $response['success'] = false;
            $response['message'] = 'Error creating classroom';
        }

        return $response;
    }

    private function getMaxDateDay($idDate)
    {
        $db = \FormaLms\db\DbConn::getInstance();
        $query = 'select max(id_day) as max_id FROM learning_course_date_day '
            . ' WHERE    ID_DATE = ' . $idDate . ' AND deleted = 0';
        $q = $db->query($query);
        $course_info = $db->fetch_assoc($q);

        if ($course_info['max_id'] == null) {
            return -1;
        }

        return $course_info['max_id'];
    }

    private function insDateDayfromParams($idDate, $day_info)
    {
        $res = false;

        $index = $this->getMaxDateDay($idDate);
        $idDay = $index + 1;

        $query = 'INSERT INTO %lms_course_date_day'
            . ' (id_day, id_date, classroom, date_begin, date_end, pause_begin, pause_end)';

        $query .= ' VALUES (' . $idDay . ', ' . $idDate . ', ' . $day_info[0]['classroom'] . ", '" . $day_info[0]['date_begin'] . "', '" . $day_info[0]['date_end'] . "', '" . $day_info[0]['pause_begin'] . "', '" . $day_info[0]['pause_end'] . "')";

        $res = sql_query($query);

        if ($res) {
            return $idDay;
        } else {
            return false;
        }
    }

    private function saveNewDateFromParams($date_info, $arrayDay)
    {
        $course_man = new Man_Course();

        $sub_start_date = trim($date_info['sub_start_date']);
        $sub_end_date = trim($date_info['sub_end_date']);
        $unsubscribe_date_limit = trim($date_info['unsubscribe_date_limit']);

        $sub_start_date = (!empty($sub_start_date) ? Format::dateDb($sub_start_date, 'date') : '1970-01-01') . ' 00:00:00';
        $sub_end_date = (!empty($sub_end_date) ? Format::dateDb($sub_end_date, 'date') : '1970-01-01') . ' 00:00:00';
        $unsubscribe_date_limit = (!empty($unsubscribe_date_limit) ? Format::dateDb($unsubscribe_date_limit, 'date') : '1970-01-01') . ' 00:00:00';

        $idDate = $date_info['id_date'];

        if ($idDate) {
            return $this->insDateDayfromParams($idDate, $arrayDay);
        } else {
            return false;
        }
    }

    public function addDay($params)
    {
        require_once _lms_ . '/admin/models/ClassroomAlms.php';

        $response = $this->getAndValidateSendCalendarFromParams($params);
        $sendCalendar = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->getAndValidateIdDateFromParams($params);
        $idDate = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->validateIdDateExistsInCourseFromParams($params);
        if (!$response['success']) {
            return $response;
        }

        $error = false;

        $model = new ClassroomAlms($courseId, $idDate);

        if (!empty($params['edition_date_selected']) && ($this->_validateDate($params['edition_date_selected']) || $this->_validateDate($params['edition_date_selected'], 'Y-m-d'))) {
            if ($this->_validateDate($params['edition_date_selected'], 'Y-m-d')) {
                $dateSelected = $params['edition_date_selected'];
            } else {
                $dateSelected = substr(Format::dateDb($params['edition_date_selected'], 'date'), 0, 10);
            }

            $dateBeginHours = array_key_exists('edition_b_hours', $params) && !empty($params['edition_b_hours']) && is_numeric($params['edition_b_hours']) ? $params['edition_b_hours'] : '00';
            $dateBeginMinutes = array_key_exists('edition_b_minutes', $params) && !empty($params['edition_b_minutes']) && is_numeric($params['edition_b_minutes']) ? $params['edition_b_minutes'] : '00';

            $datePauseBeginHours = array_key_exists('edition_pb_hours', $params) && !empty($params['edition_pb_hours']) && is_numeric($params['edition_pb_hours']) ? $params['edition_pb_hours'] : '00';
            $datePauseBeginMinutes = array_key_exists('edition_pb_minutes', $params) && !empty($params['edition_pb_minutes']) && is_numeric($params['edition_pb_minutes']) ? $params['edition_pb_minutes'] : '00';

            $datePauseEndHours = array_key_exists('edition_pe_hours', $params) && !empty($params['edition_pe_hours']) && is_numeric($params['edition_pe_hours']) ? $params['edition_pe_hours'] : '00';
            $datePauseEndMinutes = array_key_exists('edition_pe_minutes', $params) && !empty($params['edition_pe_minutes']) && is_numeric($params['edition_pe_minutes']) ? $params['edition_pe_minutes'] : '00';

            $dateEndHours = array_key_exists('edition_e_hours', $params) && !empty($params['edition_e_hours']) && is_numeric($params['edition_e_hours']) ? $params['edition_e_hours'] : '00';
            $dateEndMinutes = array_key_exists('edition_e_minutes', $params) && !empty($params['edition_e_minutes']) && is_numeric($params['edition_e_minutes']) ? $params['edition_e_minutes'] : '00';

            $classRoom = array_key_exists('edition_classroom', $params) && !empty($params['edition_classroom']) && is_numeric($params['edition_classroom']) ? $params['edition_classroom'] : '0';

            $arrayDay['date_begin'] = $dateSelected . ' ' . $dateBeginHours . ':' . $dateBeginMinutes . ':00';
            $arrayDay['pause_begin'] = $dateSelected . ' ' . $datePauseBeginHours . ':' . $datePauseBeginMinutes . ':00';
            $arrayDay['pause_end'] = $dateSelected . ' ' . $datePauseEndHours . ':' . $datePauseEndMinutes . ':00';
            $arrayDay['date_end'] = $dateSelected . ' ' . $dateEndHours . ':' . $dateEndMinutes . ':00';
            $arrayDay['classroom'] = $classRoom;

            $arrayDays = $model->classroom_man->getDateDay($idDate);

            foreach ($arrayDays as $day) {
                $dateBegin = new DateTime($day['date_begin']);

                if ($dateSelected === $dateBegin->format('Y-m-d')) {
                    $error = true;
                    $response['success'] = false;
                    $response['message'] = 'Day already Exists';
                    break;
                }
            }

            if (!$error) {
                $arrayDays[] = $arrayDay;

                $classroom_man = new DateManager();
                $result = $classroom_man->updateDateDay($idDate, $arrayDays);

                if ($result !== false) {
                    $response['success'] = true;
                    if ($sendCalendar) {
                        $model->sendCalendarToAllSubscribers();
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Error creating day';
                }
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Date Selected is not valid';
        }

        $response['days'] = $model->classroom_man->getDateDay($idDate);
        $arrayDays = $model->classroom_man->getDateDay($idDate);

        foreach ($arrayDays as $day) {
            $dateBegin = new DateTime($day['date_begin']);

            if ($dateSelected === $dateBegin->format('Y-m-d')) {
                $response['id'] = $day['id'];
                break;
            }
        }
        unset($response['data']);

        return $response;
    }

    public function deleteDay($params)
    {
        require_once _lms_ . '/admin/models/ClassroomAlms.php';

        $response = $this->getAndValidateSendCalendarFromParams($params);
        $sendCalendar = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->getAndValidateIdDayFromParams($params);
        $idDay = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->getAndValidateIdDateFromParams($params);
        $idDate = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->getAndValidateCourseIdCourseFromParams($params);
        $courseId = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $model = new ClassroomAlms($courseId, $idDate);

        $arrayDays = $model->classroom_man->getDateDay($idDate);

        $dayExists = array_search($idDay, array_column($arrayDays, 'id'));

       
        if ($dayExists) {
            //unset($arrayDays[$idDay]);
            $result = $model->classroom_man->removeDateDay($idDate, [
                [
                    'id' => $idDay,
                ],
            ]);

          

            /*sort($arrayDays);

            $classroom_man = new DateManager();
            $result = $classroom_man->updateDateDay($idDate, $arrayDays);
            */
            if ($result) {
                $response['success'] = true;
                $response['day_delete'] = $idDay;
                $response['id_date'] = $idDate;

                if ($sendCalendar) {
                    $model->sendCalendarToAllSubscribers();
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Error deleting day';
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Day does not exists';
        }

        $response['days'] = $model->classroom_man->getDateDay($idDate);
        unset($response['data']);

        return $response;
    }

    public function updateCourse($params)
    {
        $response = $this->getAndValidateCourseIdCourseFromParams($params);
        $courseId = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $course = new CourseAlms();
        $course_info = $course->getInfo($courseId);
        $params['advance'] = $params['advance'] ? $params['advance'] : $course_info['advance'];
        $params['allow_overbooking'] = $params['allow_overbooking'] ? 1 : 0;
        $params['allow_overbooking'] = $params['allow_overbooking'] ? $params['allow_overbooking'] : $course_info['allow_overbooking'];
        if (empty($params['allow_overbooking'])) {
            unset($params['allow_overbooking']);
        }
        $params['auto_unsubscribe'] = $params['auto_unsubscribe'] ? 1 : 0;
        $params['auto_unsubscribe'] = $params['auto_unsubscribe'] ? $params['auto_unsubscribe'] : $course_info['auto_unsubscribe'];
        if (empty($params['auto_unsubscribe'])) {
            unset($params['auto_unsubscribe']);
        }
        $params['can_subscribe'] = $params['can_subscribe'] ? $params['can_subscribe'] : $course_info['can_subscribe'];
        $params['course_advanced'] = $params['course_advanced'] ? 1 : 0;
        $params['course_advanced'] = $params['course_advanced'] ? $params['course_advanced'] : $course_info['show_extra_info'];
        if (empty($params['course_advanced'])) {
            unset($params['course_advanced']);
        }
        $params['course_autoregistration_code'] = $params['course_autoregistration_code'] ? $params['course_autoregistration_code'] : $course_info['autoregistration_code'];
        $params['course_code'] = $params['course_code'] ? $params['course_code'] : $course_info['code'];
        $params['course_date_begin'] = $params['course_date_begin'] ? $params['course_date_begin'] : false;
        $params['course_date_end'] = $params['course_date_end'] ? $params['course_date_end'] : false;
        $params['course_day_of'] = $params['course_day_of'] ? $params['course_day_of'] : $course_info['valid_time'];
        $params['course_descr'] = $params['course_descr'] ? $params['course_descr'] : $course_info['description'];
        $params['course_difficult'] = $params['course_difficult'] ? $params['course_difficult'] : $course_info['difficult'];
        $params['course_edition'] = $params['course_edition'] ? 1 : 0;
        $params['course_edition'] = $params['course_edition'] ? $params['course_edition'] : $course_info['course_edition'];
        if (empty($params['course_edition'])) {
            unset($params['course_edition']);
        }
        $params['course_em'] = $params['close_lo_perm'] ? 1 : 0;
        $params['course_em'] = $params['course_em'] ? $params['course_em'] : $course_info['permCloseLO'];
        if (empty($params['course_em'])) {
            unset($params['course_em']);
        }
        $params['course_lang'] = $params['course_lang'] ? $params['course_lang'] : $course_info['lang_code'];
        $params['course_medium_time'] = $params['course_medium_time'] ? $params['course_medium_time'] : $course_info['mediumTime'];
        $params['send_calendar'] = $params['send_calendar'] ? $params['send_calendar'] : $course_info['sendCalendar'];
        $params['course_name'] = $params['course_name'] ? $params['course_name'] : $course_info['name'];
        $params['course_prize'] = $params['course_price'] ? $params['course_price'] : $course_info['prize'];
        $params['course_progress'] = $params['course_progress'] ? 1 : 0;
        $params['course_progress'] = $params['course_progress'] ? $params['course_progress'] : $course_info['show_progress'];
        if (empty($params['course_progress'])) {
            unset($params['course_progress']);
        }
        $params['course_quota'] = $params['course_quota'] ? $params['course_quota'] : $course_info['course_quota'];
        $params['course_sell'] = $params['course_sell'] ? 1 : 0;
        $params['course_sell'] = $params['course_sell'] ? $params['course_sell'] : $course_info['selling'];
        if (empty($params['course_sell'])) {
            unset($params['course_sell']);
        }
        $params['course_show_rules'] = $params['course_show_rules'] ? $params['course_show_rules'] : $course_info['show_rules'];
        $params['course_sponsor_link'] = $params['course_sponsor_link'] ? $params['course_sponsor_link'] : $course_info['linkSponsor'];
        $params['course_status'] = $params['course_status'] ? $params['course_status'] : $course_info['status'];
        $params['course_subs'] = $params['course_subs'] ? $params['course_subs'] : $course_info['subscribe_method'];
        $params['course_time'] = $params['course_time'] ? 1 : 0;
        $params['course_time'] = $params['course_time'] ? $params['course_time'] : $course_info['show_time'];
        if (empty($params['course_time'])) {
            unset($params['course_time']);
        }
        $params['course_type'] = $params['course_type'] ? $params['course_type'] : $course_info['course_type'];
        $params['credits'] = $params['credits'] ? $params['credits'] : $course_info['credits'];
        $params['direct_play'] = $params['direct_play'] ? 1 : 0;
        $params['direct_play'] = $params['direct_play'] ? $params['direct_play'] : $course_info['direct_play'];
        if (empty($params['direct_play'])) {
            unset($params['direct_play']);
        }
        $params['idCategory'] = $params['idCategory'] ? $params['idCategory'] : $course_info['idCategory'];
        $params['inherit_quota'] = $params['inherit_quota'] ? 1 : 0;
        $params['inherit_quota'] = $params['inherit_quota'] ? $params['inherit_quota'] : $course_info['inherit_quota'];
        if (empty($params['inherit_quota'])) {
            unset($params['inherit_quota']);
        }
        $params['max_num_subscribe'] = $params['max_num_subscribe'] ? $params['max_num_subscribe'] : $course_info['max_num_subscribe'];
        $params['min_num_subscribe'] = $params['min_num_subscribe'] ? $params['min_num_subscribe'] : $course_info['min_num_subscribe'];
        $params['random_course_autoregistration_code'] = $params['random_course_autoregistration_code'] ? $params['random_course_autoregistration_code'] : false;
        $params['show_result'] = $params['show_result'] ? 1 : 0;
        $params['show_result'] = $params['show_result'] ? $params['show_result'] : $course_info['show_result'];
        if (empty($params['show_result'])) {
            unset($params['show_result']);
        }
        $params['show_who_online'] = $params['show_who_online'] ? 1 : 0;
        $params['show_who_online'] = $params['show_who_online'] ? $params['show_who_online'] : $course_info['show_who_online'];
        $params['sub_end_date'] = $params['sub_end_date'] ? $params['sub_end_date'] : $course_info['sub_end_date'];
        $params['sub_start_date'] = $params['sub_start_date'] ? $params['sub_start_date'] : $course_info['sub_start_date'];
        $params['unsubscribe_date_limit'] = $params['unsubscribe_date_limit'] ? $params['unsubscribe_date_limit'] : $course_info['unsubscribe_date_limit'];
        $params['use_logo_in_courselist'] = $params['use_logo_in_courselist'] ? 1 : 0;
        $params['use_logo_in_courselist'] = $params['use_logo_in_courselist'] ? $params['use_logo_in_courselist'] : $course_info['use_logo_in_courselist'];
        if (empty($params['use_logo_in_courselist'])) {
            unset($params['use_logo_in_courselist']);
        }
        $params['use_unsubscribe_date_limit'] = $params['use_unsubscribe_date_limit'] ? 1 : 0;
        if (empty($params['use_unsubscribe_date_limit'])) {
            unset($params['use_unsubscribe_date_limit']);
        }

        $res = $course->upCourse($courseId, $params);

        $response['course_id'] = $courseId;

        if ($res['res'] == '_ok_course') {
        } else {
            $response['success'] = false;
            $response['message'] = 'Update failed';
        }

        return $response;
    }

    // update an appointment related to an edition
    public function updateDay($params)
    {
        require_once _lms_ . '/admin/models/ClassroomAlms.php';

        $response = $this->getAndValidateSendCalendarFromParams($params);
        $sendCalendar = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->getAndValidateIdDayFromParams($params);
        $idDay = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->getAndValidateIdDateFromParams($params);
        $idDate = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->getAndValidateCourseIdCourseFromParams($params);
        $courseId = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->validateIdDateExistsInCourseFromParams($params);
        if (!$response['success']) {
            return $response;
        }

        $model = new ClassroomAlms($courseId, $idDate);

        $arrayDays = $model->classroom_man->getDateDay($idDate);

        $error = false;
        $dayExists = array_key_exists($idDay, $arrayDays);
        if ($dayExists) {
            if (!empty($params['edition_date_selected']) && ($this->_validateDate($params['edition_date_selected']) || $this->_validateDate($params['edition_date_selected'], 'Y-m-d'))) {
                if ($this->_validateDate($params['edition_date_selected'], 'Y-m-d')) {
                    $dateSelected = $params['edition_date_selected'];
                } else {
                    $dateSelected = substr(Format::dateDb($params['edition_date_selected'], 'date'), 0, 10);
                }

                foreach ($arrayDays as $day) {
                    $dateBegin = new DateTime($day['date_begin']);

                    if ($dateSelected === $dateBegin->format('Y-m-d')) {
                        $error = true;
                        $response['success'] = false;
                        $response['message'] = 'Day already Exists';
                        break;
                    }
                }

                if (!$error) {
                    $dateBeginHours = array_key_exists('edition_b_hours', $params) && !empty($params['edition_b_hours']) && is_numeric($params['edition_b_hours']) ? $params['edition_b_hours'] : '00';
                    $dateBeginMinutes = array_key_exists('edition_b_minutes', $params) && !empty($params['edition_b_minutes']) && is_numeric($params['edition_b_minutes']) ? $params['edition_b_minutes'] : '00';

                    $datePauseBeginHours = array_key_exists('edition_pb_hours', $params) && !empty($params['edition_pb_hours']) && is_numeric($params['edition_pb_hours']) ? $params['edition_pb_hours'] : '00';
                    $datePauseBeginMinutes = array_key_exists('edition_pb_minutes', $params) && !empty($params['edition_pb_minutes']) && is_numeric($params['edition_pb_minutes']) ? $params['edition_pb_minutes'] : '00';

                    $datePauseEndHours = array_key_exists('edition_pe_hours', $params) && !empty($params['edition_pe_hours']) && is_numeric($params['edition_pe_hours']) ? $params['edition_pe_hours'] : '00';
                    $datePauseEndMinutes = array_key_exists('edition_pe_minutes', $params) && !empty($params['edition_pe_minutes']) && is_numeric($params['edition_pe_minutes']) ? $params['edition_pe_minutes'] : '00';

                    $dateEndHours = array_key_exists('edition_e_hours', $params) && !empty($params['edition_e_hours']) && is_numeric($params['edition_e_hours']) ? $params['edition_e_hours'] : '00';
                    $dateEndMinutes = array_key_exists('edition_e_minutes', $params) && !empty($params['edition_e_minutes']) && is_numeric($params['edition_e_minutes']) ? $params['edition_e_minutes'] : '00';

                    $classRoom = array_key_exists('edition_classroom', $params) && !empty($params['edition_classroom']) && is_numeric($params['edition_classroom']) ? $params['edition_classroom'] : '0';

                    $arrayDays[$idDay]['date_begin'] = $dateSelected . ' ' . $dateBeginHours . ':' . $dateBeginMinutes . ':00';
                    $arrayDays[$idDay]['pause_begin'] = $dateSelected . ' ' . $datePauseBeginHours . ':' . $datePauseBeginMinutes . ':00';
                    $arrayDays[$idDay]['pause_end'] = $dateSelected . ' ' . $datePauseEndHours . ':' . $datePauseEndMinutes . ':00';
                    $arrayDays[$idDay]['date_end'] = $dateSelected . ' ' . $dateEndHours . ':' . $dateEndMinutes . ':00';
                    $arrayDays[$idDay]['classroom'] = $classRoom;

                    $classroom_man = new DateManager();
                    $result = $classroom_man->updateDateDay($idDate, $arrayDays);

                    if ($result) {
                        $response['success'] = true;
                        $response['id_date'] = $idDate;
                        $response['id_day'] = $idDay;

                        if ($sendCalendar) {
                            $model->sendCalendarToAllSubscribers();
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Error during update day ';
                    }
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Date Selected is not valid';
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Day does not exists';
        }

        $response['days'] = $model->classroom_man->getDateDay($idDate);
        unset($response['data']);

        return $response;
    }

    // update date
    public function updateClassroom($params)
    {
        $response = $this->getAndValidateIdDateFromParams($params);
        $idDate = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->getAndValidateCourseIdCourseFromParams($params);
        $courseId = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->validateIdDateExistsInCourseFromParams($params);
        if (!$response['success']) {
            return $response;
        }

        $params['classroom_sub_start_date'] = substr(Format::dateDb($params['classroom_sub_start_date'], 'date'), 0, 10);
        $params['classroom_sub_end_date'] = substr(Format::dateDb($params['classroom_sub_end_date'], 'date'), 0, 10);
        $params['classroom_unsubscribe_date_limit'] = substr(Format::dateDb($params['classroom_unsubscribe_date_limit'], 'date'), 0, 10);

        $res = $this->updateDate(
            $idDate,
            !empty($params['classroom_code']) ? $params['classroom_code'] : false,
            !empty($params['classroom_name']) ? $params['classroom_name'] : false,
            !empty($params['classroom_descr']) ? $params['classroom_descr'] : false,
            !empty($params['classroom_medium_time']) ? $params['classroom_medium_time'] : false,
            !empty($params['classroom_max_users']) ? $params['classroom_max_users'] : 0,
            !empty($params['classroom_price']) ? $params['classroom_price'] : null,
            !empty($params['classroom_allow_overbooking']) ? $params['classroom_allow_overbooking'] : 0,
            !empty($params['classroom_status']) ? $params['classroom_status'] : 0,
            !empty($params['classroom_test_type']) ? $params['classroom_test_type'] : 0,
            !empty($params['classroom_sub_start_date']) ? $params['classroom_sub_start_date'] : false,
            !empty($params['classroom_sub_end_date']) ? $params['classroom_sub_end_date'] : false,
            !empty($params['classroom_unsubscribe_date_limit']) ? $params['classroom_unsubscribe_date_limit'] : false
        );

        if ($res) {
            $response['success'] = true;
            $response['id_date'] = $idDate;
        } else {
            $response['success'] = false;
            $response['message'] = 'Error updating classroom<br>' . $idDate . '<br>- ' . $params['classroom_code'] . '<br>- ' . $params['classroom_name'];
        }

        return $response;
    }

    private function updateDate($idDate, $code, $name, $description, $medium_time, $max_par, $price, $overbooking, $status, $test_type, $sub_start_date, $sub_end_date, $unsubscribe_date_limit)
    {
        $query = 'UPDATE %lms_course_date'
            . " SET code = '" . $code . "',"
            . " name = '" . $name . "',"
            . " description = '" . $description . "',"
            . " medium_time = '" . $medium_time . "',"
            . " max_par = '" . $max_par . "',"
            . " price = '" . $price . "',"
            . ' overbooking = ' . $overbooking . ','
            . ' test_type = ' . $test_type . ','
            . ' status = ' . $status . ','
            . " sub_start_date = '" . $sub_start_date . "',"
            . " sub_end_date = '" . $sub_end_date . "',"
            . " unsubscribe_date_limit = '" . $unsubscribe_date_limit . "'"
            . ' WHERE id_date = ' . $idDate;

        return sql_query($query);
    }

    public function deleteCourse($params)
    {
        $response = $this->getAndValidateCourseIdCourseFromParams($params);
        $courseId = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $course = new CourseAlms();

        $res = false;
        $info = $course->getInfo($courseId);

        if (!empty($info)) {
            $res = $this->_delCourse($courseId);
        }

        if ($res) {
            $response['success'] = true;
            $response['course_id'] = $courseId;
        } else {
            $response['success'] = false;
            $response['message'] = 'Delete Failed';
        }

        return $response;
    }

    /* Delete a course
    *  Input param:
    *  $id_course: id of the course to delete
    */
    private function _delCourse($id_course)
    {
        if ((int) $id_course <= 0) {
            return false;
        }

        $course_man = new Man_Course();

        $course = new FormaCourse($id_course);
        if (!$course->getAllInfo()) {
            return false;
        }

        Events::trigger('lms.course.deleting', ['id_course' => $id_course, 'course' => $course]);

        //remove course subscribed------------------------------------------

        $levels = &$course_man->getCourseIdstGroupLevel($id_course);
        foreach ($levels as $lv => $idst) {
            \FormaLms\lib\Forma::getAclManager()->deleteGroup($idst);
        }

        $alluser = getIDGroupAlluser($id_course);
        \FormaLms\lib\Forma::getAclManager()->deleteGroup($alluser);
        $course_man->removeCourseRole($id_course);
        $course_man->removeCourseMenu($id_course);

        $query = "DELETE FROM %lms_courseuser WHERE idCourse = '" . (int) $id_course . "'";
        $qres = sql_query($query);
        if (!$qres) {
            return false;
        }

        //--- remove course data ---------------------------------------------------

        $query_course = "SELECT imgSponsor, img_course, img_material, img_othermaterial, course_demo, course_type, has_editions
            FROM %lms_course
            WHERE idCourse = '" . (int) $id_course . "'";
        $qres = sql_query($query_course);
        list($file_sponsor, $file_logo, $file_material, $file_othermaterial, $file_demo, $course_type, $course_edition) = sql_fetch_row($qres);

        $path = '/appLms/' . FormaLms\lib\Get::sett('pathcourse');
        if (substr($path, -1) != '/' && substr($path, -1) != '\\') {
            $path .= '/';
        }
        sl_open_fileoperations();
        if ($file_sponsor != '') {
            sl_unlink($path . $file_sponsor);
        }
        if ($file_logo != '') {
            sl_unlink($path . $file_logo);
        }
        if ($file_material != '') {
            sl_unlink($path . $file_material);
        }
        if ($file_othermaterial != '') {
            sl_unlink($path . $file_othermaterial);
        }
        if ($file_demo != '') {
            sl_unlink($path . $file_demo);
        }
        sl_close_fileoperations();

        //if the scs exist delete course rooms
        if ($GLOBALS['where_scs'] !== false) {
            require_once _scs_ . '/lib/lib.room.php';
            $re = deleteRoom(false, 'course', $id_course);
        }

        //--- delete classroom or editions -----------------------------------------
        if ($course_type == 'classroom') {
            require_once _lms_ . '/admin/models/ClassroomAlms.php';
            $classroom_model = new ClassroomAlms($id_course);

            $classroom = $classroom_model->classroom_man->getDateIdForCourse($id_course);

            foreach ($classroom as $idDate) {
                if (!$classroom_model->classroom_man->delDate($idDate)) {
                    return false;
                }
            }
        } elseif ($course_edition == 1) {
            require_once _lms_ . '/admin/models/EditionAlms.php';
            $edition_model = new EditionAlms($id_course);

            $editions = $edition_model->classroom_man->getEditionIdFromCourse($id_course);

            foreach ($editions as $id_edition) {
                if (!$edition_model->edition_man->delEdition($id_edition)) {
                    return false;
                }
            }
        }
        //--- end classrooms or editions -------------------------------------------

        //--- clear LOs ------------------------------------------------------------

        require_once _lms_ . '/lib/lib.module.php';
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.param.php');
        require_once _lms_ . '/class.module/track.object.php';

        $arr_lo_param = [];
        $arr_lo_track = [];
        $arr_org_access = [];

        $query = 'SELECT * FROM %lms_organization WHERE idCourse = ' . (int) $id_course;
        $ores = sql_query($query);

        while ($obj = sql_fetch_object($ores)) {
            $deleted = true;

            if ($obj->idResource != 0 && $obj->objectType != '') {
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
        $query = 'DELETE FROM %lms_organization WHERE idCourse = ' . (int) $id_course;
        $res = sql_query($query);

        $query = 'DELETE FROM %lms_course WHERE idCourse = ' . (int) $id_course;
        $res = sql_query($query);

        //delete LOs trackings

        if (!empty($arr_lo_track)) {
            $track_object = new Track_Object(false, 'course_lo');
            $track_object->delIdTrackFromCommon($arr_lo_track);
        }

        //delete org accesses
        if (!empty($arr_org_access)) {
            $query = 'DELETE FROM %lms_organization_access
                WHERE idOrgAccess IN (' . implode(',', $arr_org_access) . ')';
            $res = sql_query($query);
        }

        //delete lo params
        if (!empty($arr_lo_param)) {
            $query = 'DELETE FROM %lms_lo_param
                WHERE idParam IN (' . implode(',', $arr_lo_param) . ')';
        }

        //--- end LOs --------------------------------------------------------------

        //--- clear coursepath references ------------------------------------------
        require_once _lms_ . '/lib/lib.coursepath.php';
        $cman = new CoursePath_Manager();
        $cman->deleteCourseFromCoursePaths($id_course);
        //--- end coursepath references --------------------------------------------

        //--- clear certificates assignments ---------------------------------------
        $cman = new Certificate();
        $cman->deleteCourseCertificateAssignments($id_course);
        //--- end certificates assignments -----------------------------------------

        //--- clear labels ---------------------------------------------------------
        $lmodel = new LabelAlms();
        $lmodel->clearCourseLabel($id_course);
        //--- end labels -----------------------------------------------------------

        //--- clear advices --------------------------------------------------------
        require_once _lms_ . '/lib/lib.advice.php';
        $aman = new Man_Advice();
        $aman->deleteAllCourseAdvices($id_course);
        //--- end advices ----------------------------------------------------------

        //--- clear coursereports --------------------------------------------------
        require_once _lms_ . '/lib/lib.coursereport.php';
        $cman = new CourseReportManager($id_course);
        $cman->deleteAllReports($id_course);
        //--- end coursereports ----------------------------------------------------

        //--- clear competences ----------------------------------------------------
        $cmodel = new CompetencesAdm();
        $cmodel->deleteAllCourseCompetences($id_course);
        //--- end competences ------------------------------------------------------

        //remove customfield
        if (!sql_query('DELETE FROM ' . $GLOBALS['prefix_fw'] . "_customfield_entry WHERE id_field IN (SELECT id_field FROM core_customfield WHERE area_code = 'COURSE') AND id_obj = '" . $id_course . "'")) {
            return false;
        }

        //--- finally delete course from courses table -----------------------------
        if (!sql_query("DELETE FROM %lms_course WHERE idCourse = '" . $id_course . "'")) {
            return false;
        }

        // $event = new \appLms\Events\Lms\CourseDeletedEvent($course);
        // \appCore\Events\DispatcherManager::dispatch($event::EVENT_NAME, $event);

        Events::trigger('lms.course.deleted', ['id_course' => $id_course, 'course' => $course]);

        return true;
    }

    /* Update the name of a course category
    *  Input param:
    *  idCategory: id of the category to update
    *  name: new name
    */
    public function updateCategory($params)
    {
        $idCategory = isset($params['idCategory']) ? $params['idCategory'] : '';
        $name = isset($params['name']) ? $params['name'] : '';

        $response = [];

        if (empty($idCategory)) {
            $response['success'] = false;
            $response['message'] = 'Missing category ID: ' . $params['idCategory'];

            return $response;
        }

        if (empty($name)) {
            $response['success'] = false;
            $response['message'] = 'Missing category name: ' . $params['idCategory'];

            return $response;
        }

        require_once _lms_ . '/lib/category/class.categorytree.php';
        require_once _base_ . '/lib/lib.treedb.php';
        $catClass = new CategoryTree();
        $classFolder = new Folder();

        $res = $catClass->renameFolder($catClass->getFolderById($idCategory), $name);

        if ($res) {
            $response['success'] = true;
            $response['idCategory'] = $params['idCategory'];
            $response['message'] = 'success updating name category name ' . $params['idCategory'];
        } else {
            $response['success'] = false;
            $response['message'] = 'Error while update: ' . $params['idCategory'];
        }

        return $response;
    }

    // delete ILT Classroom edition
    public function deteleClassroom($params)
    {
        $response = $this->getAndValidateIdDateFromParams($params);
        $idDate = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->getAndValidateCourseIdCourseFromParams($params);
        $courseId = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        require_once _lms_ . '/admin/models/CourseAlms.php';
        $model = new ClassroomAlms($courseId, $idDate);

        $res = ['success' => $model->delClassroom()];

        if ($res) {
            $response['success'] = true;
            $response['id_date'] = $idDate;
            $response['message'] = 'success delete edition classroom';
        } else {
            $response['success'] = false;
            $response['id_date'] = $idDate;
            $response['message'] = 'Error delete edition classroom';
        }

        return $response;
    }

    /**
     * list of lo for a course.
     *
     * @param <type> $id_course
     * GRIFO:LRZ
     */
    private function getLo($params)
    {
        $response = [];
        $query = 'SELECT lo.title,lo.idOrg,lo.idCourse,lo.visible,lo.objectType , lc.name
                     FROM learning_organization lo, learning_course lc
                     WHERE lo.idCourse = ' . $params['course_id'] . ' and lo.idCourse=lc.idCourse';

        $res = $this->db->query($query);

        $response['success'] = true;
        //$output['query'] = $query;
        foreach ($res as $row) {
            $response['lo_course'][] = [
                'nome_lo' => $row['title'],
                'nome_corso' => $row['name'],
                'id_item' => $row['idOrg'],
                'id_corso' => $row['idCourse'],
                'visibile' => $row['visible'],
                'tipo' => $row['objectType'],
                'src' => 'appLms/index.php?modname=organization&amp;op=custom_playitem&amp;id_item=' . $row['idOrg'],
                'id_item' => $row['idOrg'],
            ];
        }

        return $response;
    }

    public function renameLearningObject($params)
    {
        require_once _lms_ . '/class.module/class.definition.php';
        require_once _lms_ . '/lib/lib.module.php';
        //require_once(_lms_ . '/lib/lib.permission.php');
        $response = $this->validateRenameParams($params);

        if ($response['success']) {
            $fromType = $params['fromType'];
            $idCourse = $params['idCourse'];
            $newName = $params['newName'];
            $learningObjectId = $params['learningObjectId'];
            $this->session->set('idCourse', $idCourse);
            $this->session->save();

            $idUser = false;
            if (array_key_exists('idUser', $params) && !empty($params['idUser'])) {
                $idUser = $params['idUser'];
                $checkAuth = $this->authenticateUserById($idUser);
                if (!$checkAuth) {
                    return $response['error'] = 'Not authenitcated user found';
                }
            }

            $model = new LomanagerLms();

            $model->setTdb($fromType, $idCourse, $idUser);
            $result = $model->renameFolder($learningObjectId, $newName);
            $response['learningObjectIds'][] = [
                'fromType' => $fromType,
                'fromId' => $learningObjectId,
                'idCourse' => $idCourse,
                'idUser' => $idUser,
                'success' => $result,
            ];
        }

        return $response;
    }

    public function authenticateUserById($idUser)
    {
        $user_manager = new FormaACLManager();
        $user_info = $user_manager->getUser($idUser, false);

        if ($user_info != false) {
            $username = $user_info[ACL_INFO_USERID];
            $du = new \FormaLms\lib\FormaUser($username, $prefix);
            $this->session->set('last_enter', $user_info[ACL_INFO_LASTENTER]);
            $du->setLastEnter(date('Y-m-d H:i:s'));
            $this->session->set('user_enter_mark', time());
            $du->loadUserSectionST();
            $du->saveInSession();
            $this->session->set('user', $du);
            $this->session->save();

            return $user_info;
        } else {
            return false;
        }
    }

    public function deleteLearningObjects($params)
    {
        require_once _lms_ . '/class.module/class.definition.php';
        require_once _lms_ . '/lib/lib.module.php';
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.permission.php');
        $response = $this->validateDeleteParams($params);

        if ($response['success']) {
            $fromType = $params['fromType'];
            $idCourse = $params['idCourse'];
            $this->session->set('idCourse', $idCourse);
            $this->session->save();

            $idUser = false;
            if (array_key_exists('idUser', $params) && !empty($params['idUser'])) {
                $idUser = $params['idUser'];
                $checkAuth = $this->authenticateUserById($idUser);
                if (!$checkAuth) {
                    return $response['error'] = 'Not authenticated user found';
                }
            }
            $learningObjectIds = explode(',', $params['learningObjectIds']);

            if (count($learningObjectIds) > 0) {
                $model = new LomanagerLms();
                foreach ($learningObjectIds as $learningObjectId) {
                    $model->setTdb($fromType, $idCourse, $idUser);
                    $result = $model->deleteFolder($learningObjectId);
                    $response['learningObjectIds'][] = [
                        'fromType' => $fromType,
                        'fromId' => $learningObjectId,
                        'idCourse' => $idCourse,
                        'idUser' => $idUser,
                        'success' => $result,
                    ];
                }
            }
        }

        return $response;
    }

    public function copyLearningObjects($params)
    {
        require_once _lms_ . '/class.module/class.definition.php';
        require_once _lms_ . '/lib/lib.module.php';
        $response = $this->validateCopyParams($params);

        if ($response['success']) {
            $fromType = $params['fromType'];
            $newtype = $params['toType'];
            $idCourse = $params['idCourse'];
            $this->session->set('idCourse', $idCourse);
            $this->session->save();

            $idUser = false;
            if (array_key_exists('idUser', $params) && !empty($params['idUser'])) {
                $idUser = $params['idUser'];
            }
            $learningObjectIds = explode(',', $params['learningObjectIds']);

            if (count($learningObjectIds) > 0) {
                $model = new LomanagerLms();
                foreach ($learningObjectIds as $learningObjectId) {
                    $model->setTdb($fromType, $idCourse, $idUser);
                    if ($learningObjectId > 0 && $model->copy($learningObjectId, $fromType)) {
                        $model->setTdb($newtype, $idCourse, $idUser);
                        $idReference = $model->paste(0);

                        $response['learningObjectIds'][] = [
                            'fromType' => $fromType,
                            'fromId' => $learningObjectId,
                            'toType' => $newtype,
                            'toId' => $idReference,
                            'idCourse' => $idCourse,
                            'idUser' => $idUser,
                            'success' => $idReference > 0,
                        ];
                    }
                }
            }
        }

        return $response;
    }

    private function validateCopyParams(array $params)
    {
        $response = [];
        $response['success'] = true;

        if (!$this->validateType($params['fromType'])) {
            $response['success'] = false;
            $response['message'] = 'From Type is not valid:' . $params['fromType'];
        }

        if (!$this->validateType($params['toType'])) {
            $response['success'] = false;
            $response['message'] = 'To Type is not valid :' . $params['toType'];
        }

        if (($params['fromType'] === LomanagerLms::HOMEREPODIRDB || $params['toType'] === LomanagerLms::HOMEREPODIRDB) && (!array_key_exists('idUser', $params) || empty($params['idUser']))) {
            $response['success'] = false;
            $response['message'] = 'To use ' . LomanagerLms::HOMEREPODIRDB . ' is necessary to send idUser param';
        }

        return $response;
    }

    private function validateDeleteParams(array $params)
    {
        $response = [];
        $response['success'] = true;

        if (!$this->validateType($params['fromType'])) {
            $response['success'] = false;
            $response['message'] = 'From Type is not valid:' . $params['fromType'];
        }

        if (!isset($params['learningObjectIds'])) {
            $response['success'] = false;
            $response['message'] = 'Learning Objects not specified on deleting';
        }

        if (!isset($params['idCourse'])) {
            $response['success'] = false;
            $response['message'] = 'Course not specified on deleting';
        } else {
            $course_man = new Man_Course();
            $courseExists = $course_man->courseExists((int) $params['idCourse']);
            if (!$courseExists) {
                $response['success'] = false;
                $response['message'] = 'Course not found';
            }
        }

        if (!isset($params['idUser'])) {
            $response['success'] = false;
            $response['message'] = 'User not specified on deleting';
        }

        if ($params['fromType'] === LomanagerLms::HOMEREPODIRDB && (!array_key_exists('idUser', $params) || empty($params['idUser']))) {
            $response['success'] = false;
            $response['message'] = 'To use ' . LomanagerLms::HOMEREPODIRDB . ' is necessary to send idUser param';
        }

        return $response;
    }

    private function validateRenameParams(array $params)
    {
        $response = [];
        $response['success'] = true;

        if (!isset($params['newName'])) {
            $response['success'] = false;
            $response['message'] = 'New name not specified on renaming';
        }

        if (!isset($params['learningObjectId']) || !(int) $params['learningObjectId'] > 0) {
            $response['success'] = false;
            $response['message'] = 'Learning Object not specified on renaming';
        }

        if (!$this->validateType($params['fromType'])) {
            $response['success'] = false;
            $response['message'] = 'From Type is not valid:' . $params['fromType'];
        }

        if (!isset($params['idCourse'])) {
            $response['success'] = false;
            $response['message'] = 'Course not specified on deleting';
        } else {
            $course_man = new Man_Course();
            $courseExists = $course_man->courseExists((int) $params['idCourse']);
            if (!$courseExists) {
                $response['success'] = false;
                $response['message'] = 'Course not found';
            }
        }

        if (!isset($params['idUser'])) {
            $response['success'] = false;
            $response['message'] = 'User not specified on deleting';
        }

        if ($params['fromType'] === LomanagerLms::HOMEREPODIRDB && (!array_key_exists('idUser', $params) || empty($params['idUser']))) {
            $response['success'] = false;
            $response['message'] = 'To use ' . LomanagerLms::HOMEREPODIRDB . ' is necessary to send idUser param';
        }

        return $response;
    }

    private function validateType(string $type)
    {
        switch ($type) {
            case LomanagerLms::HOMEREPODIRDB:
            case LomanagerLms::ORGDIRDB:
            case LomanagerLms::REPODIRDB:
                return true;
            default:
                return false;
        }
    }

    /**
     * answer of lerning object type test.
     *
     * @param <type> $id_course
     * @param <type> $id_org
     * GRIFO:LRZ
     */
    public function getAnswerTest($params)
    {
        $response = [];
        $courseResponse = [];

        $idUsers = $params['id_user'] ? $params['id_user'] : $params['id_users'];

        // recupera TRACK della risposta del test
        $db = \FormaLms\db\DbConn::getInstance();
        $qtxt = 'SELECT lt.idTrack, lt.idTest, lt.date_end_attempt, lt.idUser, lo.idCourse
                FROM learning_testtrack lt
                JOIN learning_organization lo ON lo.idOrg = lt.idReference 
                WHERE lt.idReference=' . $params['id_org'] . ' 
                and lt.idUser in (' . $idUsers . ')';
        $courseInfoResult = $db->query($qtxt);

        $course_man = new Man_Course();

        foreach ($courseInfoResult as $courseInfo) {
            $courseNodeInfo = $course_man->getCourseWithMoreInfo($courseInfo['idCourse']);

            $courseNodeInfo['dates'] = [];

            $idUser = $courseInfo['idUser'];
            $idTrack = $courseInfo['idTrack'];
            $idTest = $courseInfo['idTest'];
            $date_end_attempt = $courseInfo['date_end_attempt'];

            //user Infos
            $userResponse['date_end_attempt'] = $date_end_attempt;
            $userResponse['id_user'] = $idUser;
            $userResponse['id_date'] = null;

            if ($courseNodeInfo['course_type'] === 'classroom') {
                $classroom_man = new DateManager();
                $course_dates = $classroom_man->getCourseDate($courseNodeInfo['idCourse']);

                foreach ($course_dates as $key => $course_date) {
                    if ($course_date['usersids'] && in_array($idUser, explode(',', $course_date['usersids']))) {
                        $userResponse['id_date'] = $course_date['id_date'];
                    }
                    $classroomModel = new ClassroomAlms($courseNodeInfo['idCourse'], $course_date['id_date']);
                    unset($course_dates[$key]['id_course']);
                    $course_dates[$key]['days'] = array_values($classroom_man->getAllDateDay($course_date['id_date'], ['id_day', 'id_date']));

                    $courseNodeInfo['dates'] = array_values($course_dates);
                }
            }

            $q_test = 'select lta.idQuest, lta.idAnswer , title_quest, score_assigned  , lta.idTrack as idTrack
                    from learning_testtrack_answer lta, learning_testquest ltq
                    where lta.idTrack=' . $idTrack . ' 
                    and lta.idQuest=ltq.idQuest and lta.user_answer=1';

            $response['success'] = true;
            $response['id_users'] = explode(',', $idUsers);
            $response['id_org'] = $params['id_org'];
            $response['id_test'] = $idTest;
            $courseResponse['course_id'] = $courseNodeInfo['idCourse'];
            $courseResponse['code'] = str_replace('&', '&amp;', $courseNodeInfo['code']);
            $courseResponse['course_name'] = str_replace('&', '&amp;', $courseNodeInfo['name']);
            //remove course description in xml response because corrupt XML
            switch ($GLOBALS['REST_API_ACCEPT']) {
                case _REST_OUTPUT_JSON:
                    $courseResponse['course_description'] = str_replace('&', '&amp;', $courseNodeInfo['description']);
                    $courseResponse['course_box_description'] = str_replace('&', '&amp;', $courseNodeInfo['box_description']);
                    break;
                case _REST_OUTPUT_XML:
                default:
                    break;
            }
            $courseResponse['status'] = $courseNodeInfo['status'];
            $courseResponse['selling'] = $courseNodeInfo['selling'];
            $courseResponse['price'] = $courseNodeInfo['prize'];
            $courseResponse['subscribe_method'] = $courseNodeInfo['subscribe_method'];
            $courseResponse['course_edition'] = $courseNodeInfo['course_edition'];
            $courseResponse['course_type'] = $courseNodeInfo['course_type'];
            $courseResponse['can_subscribe'] = $courseNodeInfo['can_subscribe'];
            $courseResponse['sub_start_date'] = $courseNodeInfo['sub_start_date'];
            $courseResponse['sub_end_date'] = $courseNodeInfo['sub_end_date'];
            $courseResponse['date_begin'] = $courseNodeInfo['date_begin'];
            $courseResponse['date_end'] = $courseNodeInfo['date_end'];
            $courseResponse['course_link'] = FormaLms\lib\Get::site_url() . _folder_lms_ . "/index.php?modname=course&amp;op=aula&amp;idCourse={$courseNodeInfo['idCourse']}";
            $courseResponse['img_course'] = $courseNodeInfo['img_course'] ? FormaLms\lib\Get::site_url() . _folder_files_ . '/' . _folder_lms_ . '/' . FormaLms\lib\Get::sett('pathcourse') . $courseNodeInfo['img_course'] : '';
            $courseResponse['category_id'] = $courseNodeInfo['idCategory'];
            $courseResponse['dates'] = $courseNodeInfo['dates'];

            $result = $db->query($q_test);

            foreach ($result as $row) {
                $resEsito = 'wrong';
                if ($row['score_assigned'] > 0) {
                    $resEsito = 'correct';
                }

                $userResponse['quest_list'][] = [
                    'id_quest' => $row['idQuest'],
                    'title_quest' => $row['title_quest'],
                    'score_assigned' => $row['score_assigned'],
                    'answer' => $this->getAnswerQuest($row['idQuest'], $row['idAnswer']),
                    'response' => $this->getTrackAnswer($row['idTrack'], $row['idQuest']),
                    'esito' => $resEsito,
                ];
            }

            $response['tests'][] = $userResponse;
        }

        $response['course_info'] = $courseResponse;
        if (count(explode(',', $idUsers)) === 1) {
            $response['id_user'] = $response['id_users'];
            unset($response['id_users']);
        }

        return $response;
    }

    private function getTrackAnswer($idTrack, $idQuest)
    {
        $db = \FormaLms\db\DbConn::getInstance();
        $sql = 'select idAnswer, more_info from learning_testtrack_answer where idTrack=' . $idTrack . ' and idQuest=' . $idQuest;

        $result = $db->query($sql);
        $response = [];
        foreach ($result as $row) {
            if ($row['idAnswer'] > 0) {
                $response[] = $row['idAnswer'];
            } else {
                $response[] = $row['more_info'];
            }
        }

        return $response;
    }

    public function getAnswerQuest($idQuest, $idAnsw)
    {
        $db = \FormaLms\db\DbConn::getInstance();
        $out = [];
        $q_ans = 'select idAnswer, sequence, is_correct, answer, score_correct, score_incorrect from learning_testquestanswer where idQuest=' . $idQuest . ' order by sequence';

        $vett_answer = [];

        $result = $db->query($q_ans);

        foreach ($result as $rowAnswer) {
            $answer = [];
            $answer['id_answer'] = $rowAnswer['idAnswer'];
            $answer['sequence'] = $rowAnswer['sequence'];
            $answer['answer'] = $rowAnswer['answer'];
            $answer['is_correct'] = $rowAnswer['is_correct'];

            $out[] = $answer;
        }

        return $out;
    }

    // copia corso partendo dal course_id
    public function copyCourse($params)
    {
        $id_dupcourse = $params['course_id'];

        // read the old course info dalla sorgente del corso selezionato
        $query_sel = "SELECT * FROM %lms_course WHERE idCourse = '" . $id_dupcourse . "' ";
        $result_sel = sql_query($query_sel);
        $list_sel = sql_fetch_array($result_sel);

        foreach ($list_sel as $k => $v) {
            $list_sel[$k] = sql_escape_string($v);
        }

        $new_course_dup = 0;

        $new_file_array = [];

        if ($params['image'] == true) {
            $new_name_array = explode('_', str_replace('course_logo_', '', $list_sel['img_course']));
            //$filename = 'course_logo_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_logo_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['img_course']);
            $filename = $list_sel['img_course'];

            $new_file_array[1]['old'] = $list_sel['img_course'];
            $new_file_array[1]['new'] = $filename;
            $list_sel['img_course'] = $filename;
        }

        if ($params['advice'] == true) {
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
                ( '" . $list_sel['idCategory'] . "', '" . $list_sel['code'] . "', '" . 'Copia di ' . $list_sel['name'] . "', '" . $list_sel['description'] . "', '" . $list_sel['lang_code'] . "', '" . $list_sel['status'] . "', '" . $list_sel['level_show_user'] . "',
                '" . $list_sel['subscribe_method'] . "', '" . $list_sel['linkSponsor'] . "', '" . $list_sel['imgSponsor'] . "', '" . $list_sel['img_course'] . "', '" . $list_sel['img_material'] . "', '" . $list_sel['img_othermaterial'] . "',
                '" . $list_sel['course_demo'] . "', '" . $list_sel['mediumTime'] . "', '" . $list_sel['permCloseLO'] . "', '" . $list_sel['userStatusOp'] . "', '" . $list_sel['difficult'] . "', '" . $list_sel['show_progress'] . "', '" . $list_sel['show_time'] . "', '" . $list_sel['show_extra_info'] . "',
                '" . $list_sel['show_rules'] . "', '" . $list_sel['valid_time'] . "', '" . $list_sel['max_num_subscribe'] . "', '" . $list_sel['min_num_subscribe'] . "',
                '" . $list_sel['max_sms_budget'] . "', '" . $list_sel['selling'] . "', '" . $list_sel['prize'] . "', '" . $list_sel['course_type'] . "', '" . $list_sel['policy_point'] . "', '" . $list_sel['point_to_all'] . "', '" . $list_sel['course_edition'] . "', '" . $list_sel['classrooms'] . "', '" . $list_sel['certificates'] . "',
                '" . date('Y-m-d H:i:s') . "', '" . $list_sel['security_code'] . "', '" . $list_sel['imported_from_connection'] . "', '" . $list_sel['course_quota'] . "', '" . $list_sel['used_space'] . "', '" . $list_sel['course_vote'] . "', '" . $list_sel['allow_overbooking'] . "', '" . $list_sel['can_subscribe'] . "',
                '" . $list_sel['sub_start_date'] . "', '" . $list_sel['sub_end_date'] . "', '" . $list_sel['advance'] . "', '" . $list_sel['show_who_online'] . "', '" . $list_sel['direct_play'] . "', '" . $list_sel['autoregistration_code'] . "', '" . $list_sel['use_logo_in_courselist'] . "' )";
        $result_ins = sql_query($query_ins);

        // the id of the new course created
        $new_id_course = $new_course_dup = sql_insert_id();

        if (!$result_ins) {
            ob_clean();
            ob_start();
            $response['success'] = false;

            return $response;
        }

        //--- copy menu data -----------------------------------------------------

        // copy the old course menu into the new one
        $query_selmen = "SELECT * FROM %lms_menucourse_main WHERE idCourse = '" . $id_dupcourse . "' ";
        $result_selmen = sql_query($query_selmen);
        while ($list_selmen = sql_fetch_array($result_selmen)) {
            $query_dupmen = 'INSERT INTO %lms_menucourse_main ' .
                ' (idCourse, sequence, name, image) ' .
                ' VALUES ' .
                " ( '" . $new_course_dup . "', '" . $list_selmen['sequence'] . "', '" . $list_selmen['name'] . "', '" . $list_selmen['image'] . "' )";
            $result_dupmen = sql_query($query_dupmen);
            $array_seq[$list_selmen['idMain']] = sql_insert_id();
        }

        $query_insert_list = [];
        $query_selmenun = "SELECT * FROM %lms_menucourse_under WHERE idCourse = '" . $id_dupcourse . "' ";
        $result_selmenun = sql_query($query_selmenun);
        while ($new_org = sql_fetch_array($result_selmenun)) {
            $valore_idn = $new_org['idMain'];
            $_idMain = $array_seq[$valore_idn];

            $query_insert_list[] = "('" . $_idMain . "', '" . $new_course_dup . "', '" . $new_org['sequence'] . "', '" . $new_org['idModule'] . "', '" . $new_org['my_name'] . "')";
        }
        $result_dupmen = true;
        if (!empty($query_insert_list)) {
            $query_dupmen = 'INSERT INTO %lms_menucourse_under
                    (idMain, idCourse, sequence, idModule, my_name)
                    VALUES ' . implode(',', $query_insert_list);
            $result_dupmen = sql_query($query_dupmen);
        }

        function &getCourseLevelSt($id_course)
        {
            $map = [];
            $levels = CourseLevel::getTranslatedLevels();

            // find all the group created for this menu custom for permission management
            foreach ($levels as $lv => $name_level) {
                $group_info = \FormaLms\lib\Forma::getAclManager()->getGroup(false, '/lms/course/' . $id_course . '/subscribed/' . $lv);
                $map[$lv] = $group_info[ACL_INFO_IDST];
            }

            return $map;
        }

        $formaCourse = new FormaCourse($id_dupcourse);
        $subscribe_man = new CourseSubscribe_Manager();

        $group_idst = FormaCourse::createCourseLevel($new_course_dup);
        $group_of_from = $formaCourse->getCourseLevel($id_dupcourse);
        $perm_form = createPermForDuplicatedCourse($group_of_from, $new_course_dup, $id_dupcourse);
        $levels = $subscribe_man->getUserLevel();

        foreach ($levels as $lv => $name_level) {
            foreach ($perm_form[$lv] as $idrole => $v) {
                if ($group_idst[$lv] != 0 && $idrole != 0) {
                    \FormaLms\lib\Forma::getAclManager()->addToRole($idrole, $group_idst[$lv]);
                }
            }
        }

        if ($params['certificate'] == true) {
            // duplicate the certificate assigned
            $query_insert_list = [];
            $query_selmenun = "SELECT * FROM %lms_certificate_course WHERE id_course = '" . $id_dupcourse . "' ";
            $result_selmenun = sql_query($query_selmenun);
            while ($new_org = sql_fetch_assoc($result_selmenun)) {
                $query_insert_list[] = "('" . $new_org['id_certificate'] . "', '" . $new_course_dup . "', 
                        '" . $new_org['available_for_status'] . "', '" . $new_org['point_required'] . "' )";
            }
            $result_dupmen = true;
            if (!empty($query_insert_list)) {
                $query_dupmen = 'INSERT INTO %lms_certificate_course
                        (id_certificate, id_course, available_for_status, point_required)
                        VALUES ' . implode(',', $query_insert_list);
                $result_dupmen = sql_query($query_dupmen);
            }
        }

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/organization/orglib.php');
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.param.php');
        require_once _lms_ . '/class.module/track.object.php';
        require_once _lms_ . '/class.module/learning.object.php';

        $nullVal = null;
        $id_orgs = [];
        $map_org = [];

        $response['lo'] = $params['lo'];

        if ($params['lo'] == 'true' || $params['lo'] == true) {
            $response['lo'] = true;

            $org_map = [];
            $id_orgs = [];
            $prereq_map = [];

            // retrive all the folders and object, the order by grant that folder are created before the elements contained in them
            $query = 'SELECT * FROM %lms_organization WHERE idCourse = ' . (int) $id_dupcourse . ' ORDER BY path ASC';
            $source_res = sql_query($query);

            // Go trough all the entry of learning_organization
            while ($source = sql_fetch_object($source_res)) {
                // if it's an object we must make a copy, if it's a folder we can create a row
                // inside learning_orgation and save the id for later use

                if ($source->objectType == '') {
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
                                ('" . (isset($id_orgs[$source->idParent]) ? $id_orgs[$source->idParent] : 0) . "',
                                '" . $source->path . "', '" . $source->lev . "', '" . sql_escape_string($source->title) . "',
                                '" . $source->objectType . "', '" . $source->idResource . "', '" . $source->idCategory . "', '" . $source->idUser . "',
                                '" . $source->idAuthor . "', '" . $source->version . "', '" . $source->difficult . "', '" . sql_escape_string($source->description) . "',
                                '" . $source->language . "', '" . $source->resource . "', '" . $source->objective . "', '" . $source->dateInsert . "',
                                '" . $new_id_course . "', '" . $source->prerequisites . "', '" . $source->isTerminator . "', '" . $source->idParam . "',
                                '" . $source->visible . "', '" . $source->milestone . "')";
                    $re_new_org = sql_query($query_new_org);
                    $new_id_reference = sql_insert_id();

                    // map for later use
                    $org_map['folder'][$source->idOrg] = $new_id_reference;
                } else {
                    // is an object
                    // make a copy
                    $lo = $this->_createLO($source->objectType);
                    $new_id_resource = $lo->copy($source->idResource);

                    // create a new row in learning_organization
                    $query_new_org = "INSERT INTO %lms_organization (
                                idParent, path, lev, title,
                                objectType, idResource, idCategory, idUser,
                                idAuthor, version, difficult, description,
                                language, resource, objective, dateInsert,
                                idCourse, prerequisites, isTerminator, idParam,
                                visible, milestone)
                                VALUES
                                ('" . (isset($id_orgs[$source->idParent]) ? $id_orgs[$source->idParent] : 0) . "',
                                '" . $source->path . "', '" . $source->lev . "', '" . sql_escape_string($source->title) . "',
                                '" . $source->objectType . "', '" . $new_id_resource . "', '" . $source->idCategory . "', '" . $source->idUser . "',
                                '" . $source->idAuthor . "', '" . $source->version . "', '" . $source->difficult . "', '" . sql_escape_string($source->description) . "',
                                '" . $source->language . "', '" . $source->resource . "', '" . $source->objective . "', '" . $source->dateInsert . "',
                                '" . $new_id_course . "', '" . $source->prerequisites . "', '" . $source->isTerminator . "', '0',
                                '" . $source->visible . "', '" . $source->milestone . "')";
                    $re_new_org = sql_query($query_new_org);
                    $new_id_reference = sql_insert_id();

                    // for a learning_object we have to create a row in lo_param as well
                    // with 4.1 or 4.2 we plan to remove this table, but until then we need this
                    $query_lo_par = "INSERT INTO %lms_lo_param (param_name, param_value) VALUES ('idReference', '" . $new_id_reference . "') ";
                    $result_lo_par = sql_query($query_lo_par);
                    $id_lo_par = sql_insert_id();

                    $query_up_lo = "UPDATE %lms_lo_param SET idParam = '" . $id_lo_par . "' WHERE id = '" . $id_lo_par . "' ";
                    $result_up_lo = sql_query($query_up_lo);

                    $query_up_or = "UPDATE %lms_organization SET idParam = '" . $id_lo_par . "' WHERE idOrg = '" . $new_id_reference . "' ";
                    $result_up_or = sql_query($query_up_or);

                    // map for later use
                    $org_map[$source->objectType][$source->idResource] = $new_id_resource;
                }
                // create a map for the olds and new idReferences
                $id_orgs[$source->idOrg] = $new_id_reference;
                if ($source->prerequisites != '') {
                    $prereq_map[$new_id_reference] = $source->prerequisites;
                }
            }

            // updates prerequisites
            foreach ($prereq_map as $new_id_reference => $old_prerequisites) {
                $new_prerequisites = [];
                $old_prerequisites = explode(',', $old_prerequisites);
                foreach ($old_prerequisites as $old_p) {
                    //a prerequisite can be a pure number or something like 7=NULL, or 7=incomplete
                    $old_id = intval($old_p);
                    if (isset($id_orgs[$old_id])) {
                        $new_prerequisites[] = str_replace($old_id, $id_orgs[$old_id], $old_p);
                    }
                }
                if (!empty($new_prerequisites)) {
                    $query_updcor = 'UPDATE %lms_organization '
                        . "SET prerequisites = '" . implode(',', $new_prerequisites) . "' "
                        . 'WHERE idOrg = ' . $new_id_reference . ' ';
                    $result_upcor = sql_query($query_updcor);
                }
            }

            //--- copy htmlfront data ----------------------------------------------
            $query_insert_list = [];
            $query_selmenun = "SELECT * FROM %lms_htmlfront WHERE id_course = '" . $id_dupcourse . "' ";
            $result_selmenun = sql_query($query_selmenun);
            while ($new_org = sql_fetch_array($result_selmenun)) {
                $query_insert_list[] = "('" . $new_course_dup . "', '" . sql_escape_string($new_org['textof']) . "')";
            }

            $result_dupmen = true;
            if (!empty($query_insert_list)) {
                $query_dupmen = 'INSERT INTO %lms_htmlfront
                        (id_course, textof)
                        VALUES ' . implode(',', $query_insert_list);
                $result_dupmen = sql_query($query_dupmen);
            }

            //--- end htmlfront ----------------------------------------------------
        }

        $response['success'] = true;
        $response['from_course_id'] = $id_dupcourse;
        $response['new_course_id'] = $new_id_course;
        $response['new_course_name'] = 'Copia di ' . $list_sel['name'];

        return $response;
    }

    public function _createLO($objectType, $idResource = null)
    {
        $lo_types_cache = [];
        $query = 'SELECT objectType, className, fileName FROM %lms_lo_types';
        $rs = sql_query($query);
        while (list($type, $className, $fileName) = sql_fetch_row($rs)) {
            $lo_types_cache[$type] = [$className, $fileName];
        }

        if (!isset($lo_types_cache[$objectType])) {
            return null;
        }
        list($className, $fileName) = $lo_types_cache[$objectType];
        require_once _lms_ . '/class.module/' . $fileName;
        $lo = new $className($idResource);

        return $lo;
    }

    // assign meta certificate & course to user
    public function assignAggregateCertificateUsers($params)
    {
        $idAssociation = $params['association_id'] ?? '';
        $courses = (array_key_exists('courses', $params) && !empty($params['courses'])) ? explode(',', $params['courses']) : [];
        $users = (array_key_exists('users', $params) && !empty($params['users'])) ? explode(',', $params['users']) : [];

        $response = [];
        $response['success'] = true;

        if (empty($idAssociation)) {
            $response['success'] = false;
            $response['message'] = 'Missing association_id ' . $idAssociation;
        }

        if (empty($users)) {
            $response['success'] = false;
            $response['message'] = 'Missing users : ' . implode(',', $users);
        }

        if (empty($courses)) {
            $response['success'] = false;
            $response['message'] = 'Missing courses : ' . implode(',', $courses);
        }

        if (!$response['success']) {
            return $response;
        }

        $response['success'] = true;
        foreach ($users as $idUser) {
            foreach ($courses as $idCourse) {
                // assign course to user by association cert id
                try {
                    $query = 'INSERT INTO `%lms_aggregated_cert_course` (`idAssociation`, `idUser`, `idCourse`, `idCourseEdition`) VALUES ( ' . $idAssociation . ', ' . $idUser . ', ' . $idCourse . ', 0);';
                    sql_query($query);
                } catch (Exception $exception) {
                    $response['success'] = false;
                    $response['messages'][] = [
                        'association_id' => $idAssociation,
                        'user_id' => $idUser,
                        'course_id' => $idCourse,
                        'message' => $exception->getMessage(),
                    ];
                }
            }
        }

        return $response;
    }

    public function addAssociationAggregateCertificates($params)
    {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.aggregated_certificate.php');
        $response = [];
        $response['success'] = true;

        try {
            $aggregatedCertificatesRQ = $params['aggregated_certificates'] ?? [];

            if (count($aggregatedCertificatesRQ) > 0) {
                foreach ($aggregatedCertificatesRQ as $index => $aggregatedCertificatesRQItem) {
                    $certificateId = $aggregatedCertificatesRQItem['cert_id'] ?? '';
                    $nameAssociation = $aggregatedCertificatesRQItem['name_ass'] ?? '';
                    $descriptionAssociation = $aggregatedCertificatesRQItem['descr_ass'] ?? '';
                    $certificateType = (int) ($aggregatedCertificatesRQItem['type'] ?? 0);
                    $courses = (array_key_exists('courses', $aggregatedCertificatesRQItem) && !empty($aggregatedCertificatesRQItem['courses'])) ? explode(',', $aggregatedCertificatesRQItem['courses']) : [];
                    $coursesPaths = (array_key_exists('course_paths', $aggregatedCertificatesRQItem) && !empty($aggregatedCertificatesRQItem['course_paths'])) ? explode(',', $aggregatedCertificatesRQItem['course_paths']) : [];
                    $users = (array_key_exists('users', $aggregatedCertificatesRQItem) && !empty($aggregatedCertificatesRQItem['users'])) ? explode(',', $aggregatedCertificatesRQItem['users']) : [];

                    if (empty($certificateId)) {
                        $response['success'] = false;
                        $response['messages'][$index][] = 'Missing cert_id' . $certificateId;
                    }

                    if (empty($nameAssociation)) {
                        $response['success'] = false;
                        $response['messages'][$index][] = 'Missing name_ass ' . $nameAssociation;
                    }

                    if ($certificateType === AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE && empty($courses)) {
                        $response['success'] = false;
                        $response['messages'][$index][] = 'Missing courses : ' . implode(',', $courses);
                    }

                    if ($certificateType === AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH && empty($coursesPaths)) {
                        $response['success'] = false;
                        $response['messages'][$index][] = 'Missing courses paths : ' . implode(',', $coursesPaths);
                    }

                    if (!$response['success']) {
                        return $response;
                    }

                    // add association to meta cert id
                    try {
                        $queryMeta = 'INSERT INTO %lms_aggregated_cert_metadata ( idCertificate, title, description) VALUES (' . $certificateId . ",'" . $nameAssociation . "','" . $descriptionAssociation . "')";
                        sql_query($queryMeta);
                        // get id new association
                        $queryAssociation = 'select max(idAssociation) as id_meta from %lms_aggregated_cert_metadata';
                        $qres = sql_query($queryAssociation);
                        [$idAssociation] = sql_fetch_row($qres);

                        switch ($certificateType) {
                            case AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE:
                                foreach ($courses as $idCourse) {
                                    $query = 'INSERT INTO `%lms_aggregated_cert_course` (`idAssociation`, `idUser`, `idCourse`, `idCourseEdition`) VALUES ( ' . $idAssociation . ', 0, ' . $idCourse . ', 0);';
                                    sql_query($query);

                                    foreach ($users as $idUser) {
                                        $query = 'INSERT INTO `%lms_aggregated_cert_course` (`idAssociation`, `idUser`, `idCourse`, `idCourseEdition`) VALUES ( ' . $idAssociation . ', ' . $idUser . ', ' . $idCourse . ', 0);';
                                        sql_query($query);
                                    }
                                }
                                break;
                            case AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH:
                                foreach ($coursesPaths as $idCoursePath) {
                                    $query = 'INSERT INTO `%lms_aggregated_cert_coursepath` (`idAssociation`, `idUser`, `idCoursePath`) VALUES ( ' . $idAssociation . ', 0, ' . $idCoursePath . ')';
                                    sql_query($query);

                                    foreach ($users as $idUser) {
                                        $query = 'INSERT INTO `%lms_aggregated_cert_coursepath` (`idAssociation`, `idUser`, `idCoursePath`) VALUES ( ' . $idAssociation . ', ' . $idUser . ', ' . $idCoursePath . ')';
                                        sql_query($query);
                                    }
                                }
                                break;
                            default:
                                break;
                        }

                        $response['id_new_associations'][] = [
                            'cert_id' => $certificateId,
                            'name_ass' => $nameAssociation,
                            'descr_ass' => $descriptionAssociation,
                            'type' => $certificateType,
                            'courses' => $courses,
                            'course_paths' => $coursesPaths,
                            'users' => $users,
                            'id_new_association' => $idAssociation,
                        ];
                    } catch (Exception $exception) {
                        $response['success'] = false;
                        $response['messages'][$index][] = $exception->getMessage();
                    }
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Aggregated Certificates must be more than or equal to one',
                ];
            }
        } catch (Exception $exception) {
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }

        return $response;
    }

    public function removeAssociationCertificates($params)
    {
        $response = [];
        $response['success'] = true;
        try {
            $idCourse = $params['course_id'] ?? '';

            $course_man = new Man_Course();
            $courseExists = $course_man->courseExists($idCourse);

            if ($courseExists) {
                $cert = new Certificate();
                $result = $cert->deleteCourseCertificateAssignments($idCourse);

                if ($result === false) {
                    $response = [
                        'success' => false,
                        'message' => 'Error during remove certificate assign',
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Course does not exists :' . $idCourse,
                ];
            }
        } catch (\Exception $exception) {
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }

        return $response;
    }

    // add association to meta certificate
    public function addAssociationCertificates($params)
    {
        $response = [];
        $response['success'] = true;
        try {
            $idCourse = $params['course_id'] ?? '';
            $certificatesRQ = $params['certificates'] ?? [];

            $course_man = new Man_Course();
            $courseExists = $course_man->courseExists($idCourse);

            if ($courseExists) {
                $certificates = [];

                $excellenceCertificates = [];

                $certificatesAssignMinutes = [];

                if (count($certificatesRQ) > 0) {
                    foreach ($certificatesRQ as $certificatesRQItem) {
                        if (array_key_exists('id', $certificatesRQItem)) {
                            $certificates[$certificatesRQItem['id']] = $certificatesRQItem['status'] ?? 0;
                            $certificatesAssignMinutes[$certificatesRQItem['id']] = $certificatesRQItem['minutes'] ?? 0;
                        }
                    }
                    $cert = new Certificate();
                    $requiredPoint = 0;
                    $result = $cert->updateCertificateCourseAssign($idCourse,
                        $certificates,
                        $excellenceCertificates,
                        $requiredPoint,
                        $certificatesAssignMinutes);

                    if ($result === false) {
                        $response = [
                            'success' => false,
                            'message' => 'Error during update certificate assign',
                        ];
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Certificates must be more than or equal to one',
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Course does not exists :' . $idCourse,
                ];
            }
        } catch (\Exception $exception) {
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }

        return $response;
    }

    /**
     * put introduction of course.
     *
     * @param <type> course_id
     * @param <type> text_intro
     * GRIFO:LRZ
     */
    public function putIntroductionCourse($params)
    {
        $courseId = $params['course_id'] ?? '';
        $html = $_REQUEST['text_intro'] ?? '';

        $response = [];
        $response['success'] = true;
        $response['course_id'] = $courseId;

        $course_man = new Man_Course();
        $courseExists = $course_man->courseExists($courseId);

        if ($courseExists) {
            $courseLms = new CourseLms($courseId);

            try {
                $result = $courseLms->saveHtmlFront($html);
                if (!$result) {
                    $response['success'] = false;
                    $response['message'] = 'Error during insert/update intro page';
                }
            } catch (\Exception $exception) {
                $response['success'] = false;
                $response['message'] = $exception->getMessage();
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Course does not exists : ' . $courseId,
            ];
        }

        return $response;
    }

    public function deleteIntroductionCourse($params)
    {
        $courseId = $params['course_id'] ?? '';
        $response = [];
        $response['success'] = true;
        $response['course_id'] = $courseId;
        $course_man = new Man_Course();
        $courseExists = $course_man->courseExists($courseId);

        if ($courseExists) {
            $courseLms = new CourseLms($courseId);
            try {
                $result = $courseLms->deleteHtmlFront();
                if (!$result) {
                    $response['success'] = false;
                    $response['message'] = 'Error during remove intro page';
                }
            } catch (\Exception $exception) {
                $response['success'] = false;
                $response['message'] = $exception->getMessage();
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Course does not exists : ' . $courseId,
            ];
        }

        return $response;
    }

    /**
     * copy image cover from another course.
     *
     * @param <type> course_id_from
     * @param <type> course_id_to
     * GRIFO:LRZ
     */
    public function copyImgFromCourse($params)
    {
        $response = [];
        $response['success'] = true;

        $courseIdFrom = $params['course_id_from'] ?? '';
        $courseIdTo = $params['course_id_to'] ?? '';

        $course_man = new Man_Course();
        $courseFromExists = $course_man->courseExists($courseIdFrom);
        $courseToExists = $course_man->courseExists($courseIdTo);

        if (empty($courseFromExists)) {
            return [
                'success' => false,
                'message' => 'Course From does not exists :' . $courseIdFrom,
            ];
        }

        if (empty($courseToExists)) {
            return [
                'success' => false,
                'message' => 'Course To does not exists :' . $courseIdTo,
            ];
        }

        // if $course_id_from equal zero, l'img of course destination is image default
        $img_course = '';

        // get exist image course source

        $sql_img = 'select img_course from learning_course where idCourse=' . $courseIdFrom;
        $res = sql_query($sql_img);
        list($img_course) = sql_fetch_row($res);

        // associate img_course to course destination
        $sql_img = "update learning_course set img_course = '" . $img_course . "' where idCourse=" . $courseIdTo;

        try {
            $res = sql_query($sql_img);
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $response['course_id_from'] = $courseIdFrom;
        $response['course_id_to'] = $courseIdTo;

        return $response;
    }

    public function getCalendar($params)
    {
        $response = $this->getAndValidateIdDateFromParams($params);
        $idDate = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $response = $this->getAndValidateCourseIdCourseFromParams($params);
        $courseId = $response['data'];
        if (!$response['success']) {
            return $response;
        }

        $calendarContainer = CalendarManager::getCalendarDataContainerForDateDays($courseId, $idDate);

        $response['data'] = $calendarContainer->getFileUrl();

        return $response;
    }

    // ---------------------------------------------------------------------------

    public function call($name, $params)
    {
        $response = false;

        if (!empty($params[0]) && !isset($params['idst'])) {
            $params['idst'] = $params[0]; //params[0] should contain user idst
        }

        switch ($name) {
            case 'listCourses':
            case 'courses':
                $response = $this->getCourses($params);

                break;

            //e-learning editions
            case 'listEditions':
            case 'editions':
                $response = $this->getEditions($params);

                break;

            case 'listClassrooms':
            case 'classrooms':
                $response = $this->getClassrooms($params);

                break;

            case 'addUserSubscription':
            case 'addusersubscription':
                if (!isset($params['ext_not_found'])) {
                    $response = $this->addUserSubscription($params);
                }

                break;

            case 'updateUserSubscription':
            case 'updateusersubscription':
                if (!isset($params['ext_not_found'])) {
                    $response = $this->updateUserSubscription($params);
                }

                break;

            case 'deleteUserSubscription':
            case 'deleteusersubscription':
                if (!isset($params['ext_not_found'])) {
                    $response = $this->deleteUserSubscription($params);
                }

                break;
            case 'getUsersSubscription':
            case 'getuserssubscription':
                if (!isset($params['ext_not_found'])) {
                    $response = $this->getUsersSubscription($params);
                }
                break;
            case 'subscribeUserWithCode':
            case 'subscribeuserwithcode':
                if (!isset($params['ext_not_found'])) {
                    $response = $this->subscribeUserWithCode($params);
                }

                break;

            case 'getCertificateByUser':
            case 'getcertificatebyuser':
                if (!isset($params['ext_not_found'])) {
                    $response = $this->getCertificateByUser($params);
                }

                break;

            case 'getCertificateByCourse':
            case 'getcertificatebycourse':
                if (!isset($params['ext_not_found'])) {
                    $response = $this->getCertificateByCourse($params);
                }

                break;

            // LRZ
            case 'addCategory':
            case 'addcategory':
                $response = $this->addCategory($params);

                break;

            case 'updateCategory':
            case 'updatecategory':
                $response = $this->updateCategory($params);

                break;

            // COURSE API
            // add elearning or ILT course
            case 'addCourse':
            case 'addcourse':
                $response = $this->addCourse($params);

                break;

            case 'updateCourse':
            case 'updatecourse':
                $response = $this->updateCourse($params);

                break;

            case 'deleteCourse':
            case 'deletecourse':
                $response = $this->deleteCourse($params);

                break;

            case 'copyCourse':
            case 'copycourse':
                $response = $this->copyCourse($params);

                break;

            // CLASSROOM (ILT) API
            // add (ILT) classroom edition
            case 'addClassroom':
            case 'addclassroom':
                $response = $this->addClassroom($params);

                break;

            // update (ILT) classroom edition
            case 'updateClassroom':
            case 'updateclassroom':
                $response = $this->updateClassroom($params);

                break;

            case 'deleteClassroom':
            case 'deleteclassroom':
                $response = $this->deteleClassroom($params);

                break;

            // add appointment for classroom edition
            case 'addDay':
            case 'addday':
                $response = $this->addDay($params);

                break;

            // delete appointment for classroom edition
            case 'deleteDay':
            case 'deleteday':
                $response = $this->deleteDay($params);

                break;

            // update appointment for classroom edition
            case 'updateDay':
            case 'updateday':
                $response = $this->updateDay($params);

                break;

            // LO API
            case 'getLO':
            case 'getlo':
                $response = $this->getLo($params);

                break;
            case 'copyLearningObjects':
                $response = $this->copyLearningObjects($params);

                break;
            case 'renameLearningObject':
                $response = $this->renameLearningObject($params);

                break;
            case 'deleteLearningObjects':
                $response = $this->deleteLearningObjects($params);

                break;
            case 'getAnswerTest':
            case 'getanswertest':
                $response = $this->getAnswerTest($params);

                break;

            //META CERTIFICATE
            case 'assignAggregateCertificateUsers':
            case 'assignaggregatecertificateusers':
                $response = $this->assignAggregateCertificateUsers($params);

                break;

            case 'addAggregateCertificates':
            case 'addaggregatecertificates':
                $response = $this->addAssociationAggregateCertificates($params);

                break;

            case 'addCertificates':
            case 'addcertificates':
                $response = $this->addAssociationCertificates($params);

                break;
            case 'removeCertificates':
            case 'removecertificates':
                $response = $this->removeAssociationCertificates($params);

                break;

            // manage introduction module of course
            case 'putintroductioncourse':
            case 'putIntroductionCourse':
                $response = $this->putIntroductionCourse($params);

                break;
            case 'deleteintroductioncourse':
            case 'deleteIntroductionCourse':
                $response = $this->deleteIntroductionCourse($params);

                break;

            // copy image from course source
            case 'copyimgfromcourse':
            case 'copyImgFromCourse':
                $response = $this->copyImgFromCourse($params);

                break;
            case 'getCalendar':
            case 'getcalendar':
                $response = $this->getCalendar($params);

                break;
            default:
                $response = parent::call($name, $params);
        }

        return $response;
    }
}
