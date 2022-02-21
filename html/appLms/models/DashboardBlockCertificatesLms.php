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

require_once _lms_ . '/lib/lib.middlearea.php';
require_once _lms_ . '/lib/lib.certificate.php';
/**
 * Class DashboardBlockCertificatesLms.
 */
class DashboardBlockCertificatesLms extends DashboardBlockLms
{
    public const MAX_CERTIFICATES = 3;
    protected $certificate;

    public function __construct($jsonConfig)
    {
        $this->certificate = new Certificate();
        parent::__construct($jsonConfig);
    }

    public function parseConfig($jsonConfig)
    {
        $this->parseBaseConfig($jsonConfig);
    }

    public function getAvailableTypesForBlock()
    {
        return [
            DashboardBlockLms::TYPE_1COL,
            DashboardBlockLms::TYPE_2COL,
            DashboardBlockLms::TYPE_3COL,
            DashboardBlockLms::TYPE_4COL,
        ];
    }

    public function getForm()
    {
        $form = parent::getForm();

        array_push(
            $form,
            DashboardBlockForm::getFormItem($this, 'alternative_text', DashboardBlockForm::FORM_TYPE_TEXT, false),
            DashboardBlockForm::getFormItem($this, 'show_button', DashboardBlockForm::FORM_TYPE_CHECKBOX, false, [1 => Lang::t('_SHOW_BUTTON', 'dashboardsetting')]),
            DashboardBlockForm::getFormItem($this, 'max_last_records', DashboardBlockForm::FORM_TYPE_NUMBER, false)
        );

        return $form;
    }

    public function getViewData()
    {
        $data = $this->getCommonViewData();
        $data['certificates'] = $this->getCertificates();

        $ma = new Man_MiddleArea();
        $data['perm'] = $ma->currentCanAccessObj('mo_7');

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
        return 'index.php?r=lms/mycertificate/show';
    }

    public function getRegisteredActions()
    {
        return [];
    }

    private function getCertificates()
    {
        if (!$limit = (int) $this->data['max_last_records']) {
            $limit = self::MAX_CERTIFICATES;
        }

        return $this->getCertificatesForBlock($limit);
    }

    private function getCertificatesForBlock($limit = 1)
    {
        $id_user = Docebo::user()->idst;

        $filter['id_user'] = $id_user;
        $pagination['startIndex'] = 0;
        $pagination['rowsPerPage'] = $limit;
        $results = $this->certificate->getAssignment($filter, $pagination);
        // $query = "SELECT cu.date_complete, ca.on_date, cu.idUser as id_user,"
        //     . " cu.status , cu.idCourse, cc.id_certificate, c.name AS name_certificate,"
        //     . " ca.cert_file, courses.name AS course_name, courses.code AS course_code"
        //     . " FROM ( %adm_user AS u JOIN %lms_courseuser AS cu ON (u.idst = cu.idUser) )"
        //     . " JOIN %lms_certificate_course AS cc ON cc.id_course = cu.idCourse"
        //     . " JOIN %lms_course AS courses ON courses.idCourse = cu.idCourse"
        //     . " JOIN %lms_certificate AS c ON c.id_certificate = cc.id_certificate"
        //     . " LEFT JOIN %lms_certificate_assign AS ca ON"
        //     . " ( ca.id_course = cu.idCourse AND ca.id_user=cu.idUser AND ca.id_certificate = cc.id_certificate )"
        //     . " WHERE cu.idUser = " . $id_user
        //     . " ORDER BY ca.on_date DESC";

        return $results;
    }
}
