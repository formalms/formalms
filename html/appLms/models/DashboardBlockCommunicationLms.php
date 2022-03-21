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

/**
 * Class DashboardBlockNewsLms.
 */
class DashboardBlockCommunicationLms extends DashboardBlockLms
{
    public const DEFAULT_RECORDS = 5;

    public function __construct($jsonConfig)
    {
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

    public function getViewData()
    {
        $data = $this->getCommonViewData();
        $limit = $this->data['max_last_records'] ? (int) $this->data['max_last_records'] : self::DEFAULT_RECORDS;

        $onlyToRead = $this->data['showread'];

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

        $communication = $this->findAllUnread($limit, Docebo::user()->getId(), ['viewer' => Docebo::user()->getArrSt()], $only_to_read);

        return $communication;
    }

    public function findAllUnread($limit, $reader, $filter = false, $only_to_read)
    {
        $qtxt = 'SELECT c.id_comm, title, description, publish_date, type_of, id_resource, COUNT(ca.id_comm) as access_entity, ct.status, ct.dateAttempt '
            . ' FROM ( learning_communication AS c '
            . '    JOIN learning_communication_access AS ca ON (c.id_comm = ca.id_comm) ) '
            . '    LEFT JOIN learning_communication_track AS ct ON (c.id_comm = ct.idReference AND ct.idUser = ' . (int) $reader . '  )'

            . ' WHERE 1 '
            . (!empty($filter['text']) ? " AND ( title LIKE '%" . $filter['text'] . "%' OR description LIKE '%" . $filter['text'] . "%' ) " : '')
            . (!empty($filter['viewer']) ? ' AND ca.idst IN ( ' . implode(',', $filter['viewer']) . ' ) ' : '')
            . (!empty($_categories) ? ' AND c.id_category IN (' . implode(',', $_categories) . ') ' : '')

            . ' GROUP BY c.id_comm, c.title, description, publish_date ,  type_of, id_resource, ct.status  order by publish_date DESC
            LIMIT 0,' . $limit;

        if ($only_to_read) {
            $qtxt = 'SELECT c.id_comm, title, description, publish_date, type_of, id_resource, COUNT(ca.id_comm) as access_entity, ct.status, ct.dateAttempt '
                . ' FROM ( learning_communication AS c '
                . '    JOIN learning_communication_access AS ca ON (c.id_comm = ca.id_comm) ) '
                . '    LEFT JOIN learning_communication_track AS ct ON (c.id_comm = ct.idReference AND ct.idUser = ' . (int) $reader . '  )'
                . " WHERE ( ct.status = 'failed' OR  ct.status = 'ab-initio' OR  ct.status = 'attempted' OR ct.idReference IS NULL  ) "
                . (!empty($filter['text']) ? " AND ( title LIKE '%" . $filter['text'] . "%' OR description LIKE '%" . $filter['text'] . "%' ) " : '')
                . (!empty($filter['viewer']) ? ' AND ca.idst IN ( ' . implode(',', $filter['viewer']) . ' ) ' : '')
                . (!empty($_categories) ? ' AND c.id_category IN (' . implode(',', $_categories) . ') ' : '')

                . ' GROUP BY c.id_comm, c.title, description, publish_date ,  type_of, id_resource, ct.status, ct.dateAttempt  order by publish_date DESC
            LIMIT 0,' . $limit;
        }

        $res = sql_query($qtxt);

        $results = [];
        while ($row = sql_fetch_assoc($res)) {
            $results[] = $row;
        }

        return $results;
    }
}
