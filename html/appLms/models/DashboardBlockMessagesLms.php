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
 * Class DashboardBlockMessagesLms.
 */
class DashboardBlockMessagesLms extends DashboardBlockLms
{
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

        $ma = new Man_MiddleArea();
        $data['perm'] = $ma->currentCanAccessObj('mo_message');

        $data['messages'] = $this->getMessages();

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
        return 'index.php?r=message/show';
    }

    public function getRegisteredActions()
    {
        return [];
    }

    private function getMessages()
    {
        if (!$limit = (int) $this->data['max_last_records']) {
            return;
        }

        return $this->getMessagesForBlock($limit);
    }

    private function getMessagesForBlock($limit = 0)
    {
        $id_user = Forma::user()->idst;

        $query = "SELECT 
                m.idMessage, 
                CONCAT(u.firstname, ' ', u.lastname) AS sender, 
                m.posted, 
                m.attach, 
                m.title, 
                m.textof, 
                m.priority, 
                mu.read,
                c.name AS course,
                c.code AS course_code
            FROM %adm_message AS m 
            JOIN %adm_message_user AS mu
            INNER JOIN %adm_user u ON u.idst = m.sender
            LEFT JOIN %lms_course c ON c.idCourse = m.idCourse
            WHERE m.idMessage = mu.idMessage
                AND m.sender <> $id_user
                AND mu.idUser = $id_user
            ORDER BY m.priority DESC, m.posted DESC";

        if ($limit > 0) {
            $query .= " LIMIT $limit";
        }

        $res = sql_query($query);

        $results = [];
        while ($row = sql_fetch_assoc($res)) {
            $results[] = $row;
        }

        return $results;
    }
}
