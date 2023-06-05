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
require_once(_lms_.'/admin/models/CommunicationAlms.php');
/**
 * Class DashboardBlockNewsLms.
 */
class DashboardBlockCommunicationLms extends DashboardBlockLms
{
    public const DEFAULT_RECORDS = 5;

    protected $model;

    public function __construct($jsonConfig)
    {
        parent::__construct($jsonConfig);

        $this->model = new CommunicationAlms();
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

    public function getViewData()
    {
        $data = $this->getCommonViewData();
        $limit = !empty($this->data) && array_key_exists('max_last_records', $this->data) ? (int) $this->data['max_last_records'] : self::DEFAULT_RECORDS;

        $onlyToRead = !empty($this->data) &&  array_key_exists('showread', $this->data) ? (int) $this->data['showread'] : false;

        $data['communication'] = $this->getCommunication($limit, $onlyToRead);


        return $data;
    }

    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    public function getLink()
    {
        return 'index.php?r=lms/mycourses/show&mycourses_tab=tb_communication&sop=unregistercourse';
    }

    public function getForm()
    {
        $form = parent::getForm();

        array_push(
            $form,
            DashboardBlockForm::getFormItem($this, 'showread', DashboardBlockForm::FORM_TYPE_CHECKBOX, false, [1]),
            DashboardBlockForm::getFormItem($this, 'show_button', DashboardBlockForm::FORM_TYPE_CHECKBOX, false, [1]),
            DashboardBlockForm::getFormItem($this, 'max_last_records', DashboardBlockForm::FORM_TYPE_NUMBER, false)
        );

        return $form;
    }

    /**
     * @return string
     */
    public function getViewFile()
    {
        return $this->viewFile;
    }

    public function getRegisteredActions()
    {
        return [];
    }

    private function getCommunication($limit, $only_to_read)
    {
        $communication = [];

        $communication = $this->model->findAllUnread(0, $limit, 'publish_date', 'DESC', \FormaLms\lib\FormaUser::getCurrentUser()->getId(), ['viewer' => \FormaLms\lib\FormaUser::getCurrentUser()->getArrSt(), 'only_to_read' => (bool) $only_to_read]);
     
        return $communication;
    }

    
}
