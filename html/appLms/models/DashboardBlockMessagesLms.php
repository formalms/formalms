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
	public function __construct()
	{
		parent::__construct();
		$this->setEnabled(true);
		$this->setType(DashboardBlockLms::TYPE_BUTTON);
	}

	public function getViewData(): array
	{
		$data = $this->getCommonViewData();
		$data['messages'] = $this->getMessages();

		return $data;
	}

	/**
	 * @return string
	 */
	public function getViewPath(): string
	{
		return $this->viewPath;
	}

	/**
	 * @return string
	 */
	public function getViewFile(): string
	{
		return $this->viewFile;
	}

	public function getLink(): string
	{
		return 'index.php?r=message/show';
	}

	public function getRegisteredActions(): array
	{
		return [];
	}

	private function getMessages(){

		$data = [];

		return $data;
	}
}
