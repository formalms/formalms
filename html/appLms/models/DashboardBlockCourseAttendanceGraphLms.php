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
require_once Forma::include(_lms_ . '/lib/', 'lib.subscribe.php');
/**
 * Class DashboardBlockAnnouncementsLms.
 */
class DashboardBlockCourseAttendanceGraphLms extends DashboardBlockLms
{
    protected CourseSubscribe_Manager $subscribeManager;

    public const COLORS = [
            -2 => 'F0F8FF',
            -1 => 'FAEBD7',
            0 => '3CAAD1',
            1 => 'ED6D05',
            2 => '60B567',
            3 => 'F5F5DC',
            4 => '669900',
        ];

    public function __construct($jsonConfig)
    {
        $this->subscribeManager = new CourseSubscribe_Manager();

        parent::__construct($jsonConfig);
    }

    public function parseConfig($jsonConfig)
    {
        $this->parseBaseConfig($jsonConfig);
    }

    public function getAvailableTypesForBlock()
    {
        return self::ALLOWED_TYPES;
    }

    public function getViewData()
    {
        $data = $this->getCommonViewData();
        $data['coursesInfo'] = $this->getCourseInfo();

        return $data;
    }

    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * @return string
     */
    public function getViewFile()
    {
        return $this->viewFile;
    }

    public function getLink()
    {
        return '#';
    }

    public function getRegisteredActions()
    {
        return [];
    }

    private function getCourseInfo()
    {
        $result = [];
        $defaultLabels = $this->subscribeManager->getUserStatus();

        $query = 'SELECT cu.status, count(cu.idUser) as cnt'
            . ' FROM ' . $this->subscribeManager->getSubscribeUserTable() . ' cu'
            . ' WHERE cu.iduser = ' . Docebo::user()->getId() . ' '
            . ' GROUP BY cu.status';

        $resultQuery = $this->db->query($query);

        foreach ($resultQuery as $data) {
            $result['data'][] = (int) $data['cnt'];
            $result['labels'][] = $defaultLabels[$data['status']];
            $result['colors'][] = '#' . self::COLORS[$data['status']];
        }

        return $result;
    }

    private function findEnrolledCourses()
    {
    }

    private function getUserCoursePathCourses($id_user)
    {
        require_once _lms_ . '/lib/lib.coursepath.php';
        $cp_man = new Coursepath_Manager();
        $output = [];
        $cp_list = $cp_man->getUserSubscriptionsInfo($id_user);
        if (!empty($cp_list)) {
            $cp_list = array_keys($cp_list);
            $output = $cp_man->getAllCourses($cp_list);
        }

        return $output;
    }
}
