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
 * Class DashboardBlockWelcomeLms
 */
class DashboardBlockWelcomeLms extends DashboardBlockLms
{

    public function __construct($jsonConfig)
    {
        parent::__construct($jsonConfig);
	}

    public function parseConfig($jsonConfig) {

    }

    public function getAvailableTypesForBlock(){
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
            DashboardBlockForm::getFormItem($this,'image', DashboardBlockForm::FORM_TYPE_IMAGE),
        ];
    }

    public function getViewData(){
		return $this->getCommonViewData();
	}

	/**
	 * @return string
	 */
	public function getViewPath(){
		return $this->viewPath;
	}

	/**
	 * @return string
	 */
	public function getViewFile(){
		return $this->viewFile;
	}


	public function getLink(){
		return '#';
	}

	public function getRegisteredActions(){
		return [];
	}

}
