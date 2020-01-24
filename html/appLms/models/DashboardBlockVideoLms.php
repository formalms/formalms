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
 * Class DashboardBlockVideoLms
 */
class DashboardBlockVideoLms extends DashboardBlockLms
{

    public function __construct($jsonConfig)
    {
        parent::__construct($jsonConfig);
	}

    public function parseConfig($jsonConfig) {

    }

    public function getAvailableTypesForBlock(): array {
        return [
            DashboardBlockLms::TYPE_1COL,
            DashboardBlockLms::TYPE_2COL,
            DashboardBlockLms::TYPE_3COL,
            DashboardBlockLms::TYPE_4COL
        ];
    }

	public function getViewData(): array
	{
		$data = $this->getCommonViewData();
		$data['dashboard_video_id'] = '12345'; // id che rappresenta il modulo. serve nel caso ci siano N video in dashboard
		$data['video_type'] = 'yt'; //può essere yt o vimeo
		$data['video'] = '3vBwRfQbXkg';
		$data['cover'] = '/templates/standard/static/images/banner.png';

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
		return '#';
	}

	public function getRegisteredActions(): array
	{
		return [];
	}

}
