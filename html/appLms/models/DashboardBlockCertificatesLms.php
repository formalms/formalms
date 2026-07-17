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
        return self::ALLOWED_TYPES;
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
        $limit = self::MAX_CERTIFICATES;
        $certificates = [];

        if (!empty($this->data) && (int)array_key_exists('max_last_records', $this->data)) {
            $limit = $this->data['max_last_records'];
        }

        if ($limit > 0) {
            $certificates = $this->getCertificatesForBlock($limit);
        }

        return $certificates;
    }

    private function getCertificatesForBlock($limit = self::MAX_CERTIFICATES)
    {
        $id_user = \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt();

        $filter['id_user'] = $id_user;
        $pagination['startIndex'] = 0;
        $pagination['rowsPerPage'] = $limit;
        $results = $this->certificate->getAssignment($filter, $pagination);

        return $results;
    }
}
