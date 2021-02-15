<?php


defined("IN_FORMA") or die('Direct access is forbidden.');

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


/**
 * Class DashboardBlockAnnouncementLms
 */
class DashboardBlockMessagesLms extends DashboardBlockLms
{
    public function __construct($jsonConfig)
    {
        parent::__construct($jsonConfig);
    }

    public function parseConfig($jsonConfig)
    {
        return parent::parseBaseConfig($jsonConfig);
    }

    public function getAvailableTypesForBlock()
    {
        return [
            DashboardBlockLms::TYPE_1COL,
            DashboardBlockLms::TYPE_2COL,
            DashboardBlockLms::TYPE_3COL,
            DashboardBlockLms::TYPE_4COL
        ];
    }

    public function getForm()
    {
        return [
            DashboardBlockForm::getFormItem($this, 'alternative_text', DashboardBlockForm::FORM_TYPE_TEXT, false),
            DashboardBlockForm::getFormItem($this, 'show_button', DashboardBlockForm::FORM_TYPE_CHECKBOX, false, [1 => Lang::t('_SHOW_BUTTON', 'dashboardsetting')]),
            DashboardBlockForm::getFormItem($this, 'max_last_records', DashboardBlockForm::FORM_TYPE_NUMBER, false),
        ];
    }

    public function getViewData()
    {
        $data = $this->getCommonViewData();
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
        if (!$limit = (int)$this->data['max_last_records']) {
            return;
        }

        return $this->getMessagesForBlock($limit);
    }

    private function getMessagesForBlock($limit = 0)
    {
        $id_user = Docebo::user()->idst;

        $query = "SELECT m.idMessage, m.idCourse, m.sender, m.posted, m.attach, m.title, m.priority, user.read
            FROM %adm_message AS m JOIN
                %adm_message_user AS user
            WHERE m.idMessage = user.idMessage AND
                m.sender <> $id_user AND
                user.idUser = $id_user AND
            ORDER BY m.posted DESC";

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
