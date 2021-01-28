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
 * Class DashboardBlockBannerLms
 */
class DashboardBlockBannerLms extends DashboardBlockLms
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
            DashboardBlockForm::getFormItem($this, 'cover', DashboardBlockForm::FORM_TYPE_IMAGE, false),
            DashboardBlockForm::getFormItem($this, 'video_type', DashboardBlockForm::FORM_TYPE_SELECT, false, [
                'blank' => 'Select Video Type',
                'yt' => 'Youtube',
                'vimeo' => 'Vimeo'
            ]),
            DashboardBlockForm::getFormItem($this, 'video', DashboardBlockForm::FORM_TYPE_TEXT, false),
        ];
    }

    public function getViewData()
    {
        $data = $this->getCommonViewData();

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
}
