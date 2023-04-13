<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once \FormaLms\lib\Forma::inc(_lms_ . '/models/DashboardBlockForm.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/models/DashboardBlockFormItem.php');

/**
 * Class DashboardLms.
 */
abstract class DashboardBlockLms extends Model
{
    public const TYPE_4COL = '4-col';
    public const TYPE_3COL = '3-col';
    public const TYPE_2COL = '2-col';
    public const TYPE_1COL = '1-col';

    public const ALLOWED_TYPES = [self::TYPE_4COL, self::TYPE_3COL, self::TYPE_2COL, self::TYPE_1COL];

    abstract public function getViewPath();

    abstract public function getViewFile();

    abstract public function getViewData();

    abstract public function getLink();

    abstract public function getRegisteredActions();

    abstract public function getAvailableTypesForBlock();

    abstract public function parseConfig($jsonConfig);

    /** @var bool|DbConn */
    protected $db;
    /**
     * @var string
     */
    private $type = '';

    /**
     * @var int
     */
    private $order = 0;

    /**
     * @var bool
     */
    private $enabled = false;

    protected $enabledActions = [];

    /** @var string */
    protected $viewPath;

    /** @var string */
    protected $viewFile;

    protected $data = [];

    /** @var bool */
    protected $firstInsert = '';

    public function __construct($jsonConfig)
    {
        parent::__construct();
        $this->db = \FormaLms\db\DbConn::getInstance();
        if (is_string($jsonConfig)) {
            $jsonConfig = json_decode($jsonConfig, true);
        }
        $this->parseBaseConfig($jsonConfig);
        $this->parseConfig($jsonConfig);

        $this->viewPath = _base_ . '/' . _folder_templates_ . '/' . getTemplate() . '/layout/appLms/dashboard/' . strtolower(str_replace('DashboardBlock', '', str_replace('Lms', '', get_class($this))));
        $this->viewFile = strtolower(str_replace('DashboardBlock', '', str_replace('Lms', '', get_class($this))));
    }

    public function getForm()
    {
        return [
            DashboardBlockForm::getFormItem($this, 'title', DashboardBlockForm::FORM_TYPE_TEXT, false),
        ];
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     *
     * @return DashboardBlockLms
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return DashboardBlockLms
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return DashboardBlockLms
     */
    public function setType($type)
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new LogicException(sprintf('Selected type is not allowed : %s', $type));
        }
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getEnabledActions()
    {
        return $this->enabledActions;
    }

    /**
     * @param array $enabledActions
     *
     * @return DashboardBlockLms
     */
    public function setEnabledActions($enabledActions)
    {
        $this->enabledActions = $enabledActions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return DashboardBlockLms
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFirstInsert()
    {
        return $this->firstInsert;
    }

    protected function parseBaseConfig($jsonConfig)
    {
        if (is_array($jsonConfig)) {
            $this->enabled = array_key_exists('enabled', $jsonConfig) ? $jsonConfig['enabled'] : false;
            $this->type = array_key_exists('type', $jsonConfig) ? $jsonConfig['type'] : '';
            $this->enabledActions = array_key_exists('enabledActions', $jsonConfig) ? $jsonConfig['enabledActions'] : [];
            $this->data = array_key_exists('data', $jsonConfig) ? $jsonConfig['data'] : [];
            $this->firstInsert = array_key_exists('firstInsert', $jsonConfig) ? $jsonConfig['firstInsert'] : false;
        }
    }

    /**
     * @return array
     */
    public function getCommonViewData()
    {
        return [
            'view' => $this->getViewName(),
            'order' => $this->getOrder(),
            'type' => $this->getType(),
            'availableTypes' => $this->getAvailableTypesForBlock(),
            'enabled' => $this->isEnabled(),
            'link' => $this->getLink(),
            'ajaxUrl' => 'ajax.adm_server.php?r=lms/dashboard/ajaxAction',
            'postData' => [
                'block' => get_class($this),
                'signature' => Util::getSignature(),
            ],
            'data' => $this->getData(),
            'registeredActions' => $this->getRegisteredActions(),
            'enabledActions' => $this->getEnabledActions(),
            'templatePath' => getPathTemplate(),
            'allowedFileTypes' => $this->getAllowedFileTypes(),
            'allowedFileMimeTypes' => $this->getAllowedFileMimeTypes(),
        ];
    }

    public function getSettingsCommonViewData()
    {
        $data = $this->getCommonViewData();

        $data['form'] = $this->getFormArray();

        return $data;
    }

    protected function getViewName()
    {
        return sprintf('%s.html.twig', $this->getViewFile());
    }

    protected function getDataFromCourse($course)
    {
        $status_list = [
            0 => Lang::t('_CST_PREPARATION', 'course'),
            1 => Lang::t('_CST_AVAILABLE', 'course'),
            2 => Lang::t('_CST_CONFIRMED', 'course'),
            3 => Lang::t('_CST_CONCLUDED', 'course'),
            4 => Lang::t('_CST_CANCELLED', 'course'),
        ];

        $dateBegin = trim(str_replace('00:00:00', '', $course['course_date_begin']));
        if ($dateBegin === '0000-00-00') {
            $dateBegin = '';
        } else {
            $startDate = new DateTime($dateBegin);
        }

        $dateEnd = trim(str_replace('00:00:00', '', $course['course_date_end']));
        if ($dateEnd === '0000-00-00') {
            $dateEnd = '';
        } else {
            $endDate = new DateTime($dateEnd);
        }

        $hourBegin = $course['course_hour_begin'];
        $hourBeginString = '';
        if ($hourBegin === '-1' || $hourBegin === null) {
            $hourBegin = '';
        } else {
            $hourBegin .= ':00';
            $hourBeginString = $hourBegin;
        }

        $hourEnd = $course['course_hour_end'];
        $hourEndString = '';
        if ($hourEnd === '-1' || $hourEnd === null) {
            $hourEnd = '';
        } else {
            $hourEnd .= ':00';
            $hourEndString = $hourEnd;
        }

        $courseData = [
            'id' => $course['course_id'],
            'title' => $course['course_name'],
            'startDate' => !empty($dateBegin) ? $dateBegin : '',
            'endDate' => !empty($dateEnd) ? $dateEnd : '',
            'startDateString' => $startDate !== null ? $startDate->format('d/m/Y') : '',
            'endDateString' => $endDate !== null ? $endDate->format('d/m/Y') : '',
            'hourBeginString' => $course['course_hour_begin'],
            'hourEndString' => $course['course_hour_end'],
            'hourBegin' => $hourBegin,
            'hourEnd' => $hourEnd,
            'type' => $course['course_type'],
            'nameCategory' => $this->getCategory($course['course_category_id']),
            'courseStatus' => $course['course_status'],
            'courseStatusString' => $status_list[(int) $course['course_status']],
            'userStatus' => (int) $course['user_status'],
            'description' => $course['course_box_description'],
            'img' => (!empty($course['course_img_course']) ? FormaLms\lib\Get::site_url() . _folder_files_ . '/' . _folder_lms_ . '/' . FormaLms\lib\Get::sett('pathcourse') . $course['course_img_course'] : ''),
            'hours' => $hourBeginString . (!empty($hourEndString) ? ' - ' . $hourEndString : ''),
            'dates' => [],
        ];

        return $courseData;
    }

    protected function getDataFromReservation($reservation)
    {
        $dateBegin = $reservation['date_begin'];
        if ($dateBegin === '0000-00-00') {
            $dateBegin = '';
        }

        $dateEnd = $reservation['date_end'];
        if ($dateEnd === '0000-00-00') {
            $dateEnd = '';
        }

        $hourBegin = $reservation['hour_begin'];
        $hourBeginString = '';
        if ($hourBegin === '-1') {
            $hourBegin = '00:00:00';
        } else {
            $hourBegin .= ':00';
            $hourBeginString = $reservation['hour_begin'];
        }
        $hourEnd = $reservation['hour_end'];
        $hourEndString = '';
        if ($hourEnd === '-1') {
            $hourEnd = '23:59:59';
        } else {
            $hourEnd .= ':00';
            $hourEndString = $reservation['hour_end'];
        }

        $reservationData = [
            'title' => $reservation['name'],
            'start' => $dateBegin . 'T' . $hourBegin,
            'end' => $dateEnd . 'T' . $hourEnd,
            'type' => $reservation['course_type'],
            'status' => true,
            'description' => $reservation['box_description'],
            'hours' => $hourBeginString . ' ' . $hourEndString,
        ];

        $reservationData['course'] = $this->getCalendarDataFromCourse($reservation);

        return $reservationData;
    }

    protected function getCategory($idCat)
    {
        $query = 'select path from %lms_category where idCategory=' . $idCat;
        $res = $this->db->query($query);
        $path = '';
        if ($res && $this->db->num_rows($res) > 0) {
            list($path) = $this->db->fetch_row($res);
        }

        return $path;
    }

    protected function getUser()
    {
        $user = \FormaLms\lib\FormaUser::getCurrentUser();
        $acl_man = \FormaLms\lib\Forma::getAclManager();
        $user_info = $acl_man->getUser($user->getIdSt(), false);

        return [
            'userId' => $user->getIdSt(),
            'firstname' => $user_info[ACL_INFO_FIRSTNAME],
            'lastname' => $user_info[ACL_INFO_LASTNAME],
            'email' => $user_info[ACL_INFO_EMAIL],
            'avatar' => $user_info[ACL_INFO_AVATAR],
        ];
    }

    public function validate($data)
    {
        $form = $this->getForm();

        if (!empty($form)) {
            foreach ($form as $formItem) {
                //if ($formItem)
            }
        }

        return true;
    }

    public function getFormArray()
    {
        $result = [];

        $form = $this->getForm();

        /** @var DashboardBlockFormItem $formItem */
        foreach ($form as $formItem) {
            $result[] = $formItem->toArray();
        }

        return $result;
    }

    public function getAllowedFileTypes()
    {
        $upload_whitelist = FormaLms\lib\Get::sett('file_upload_whitelist', 'rar,exe,zip,jpg,gif,png,txt,csv,rtf,xml,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,pdf,xps,mp4,mp3,flv,swf,mov,wav,ogg,flac,wma,wmv,jpeg,m4v,mkv');
        $allowedFileTypes = explode(',', trim($upload_whitelist, ','));

        return $allowedFileTypes;
    }

    public function getAllowedFileMimeTypes()
    {
        require_once _lib_ . '/lib.mimetype.php';

        $allowedFileTypes = $this->getAllowedFileTypes();
        $mimetypeArray = [];
        if (!empty($allowedFileTypes)) {
            foreach ($allowedFileTypes as $k => $v) { // remove extra spaces and set lower case
                $ext = trim(strtolower($v));
                $mt = mimetype($ext);
                if ($mt) {
                    $mimetypeArray[] = $mt;
                }
                getOtherMime($ext, $mimetypeArray);
            }
            $mimetypeArray = array_unique($mimetypeArray);
        }

        return $mimetypeArray;
    }
}
