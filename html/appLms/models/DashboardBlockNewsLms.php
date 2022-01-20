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
 * Class DashboardBlockNewsLms
 */
class DashboardBlockNewsLms extends DashboardBlockLms
{

    public function __construct($jsonConfig)
    {
        parent::__construct($jsonConfig);
	}

    public function parseConfig($jsonConfig)
	{
		$this->parseBaseConfig($jsonConfig);
	}

    public function getAvailableTypesForBlock(){
        return [
            DashboardBlockLms::TYPE_1COL,
            DashboardBlockLms::TYPE_2COL,
            DashboardBlockLms::TYPE_3COL,
            DashboardBlockLms::TYPE_4COL
        ];
    }

	public function getViewData(){
		$data = $this->getCommonViewData();
		$data['news'] = $this->getNews();

		return $data;
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

	private function getNews()
	{
		$news = [];
		$ma = new Man_MiddleArea ();

		if ($ma->currentCanAccessObj('news')) {
			$user_assigned = Docebo::user()->getArrSt();
			$query_news = "
            SELECT idNews, publish_date, title, short_desc,long_desc, important, viewer
            FROM %lms_news_internal
            WHERE language = '" . getLanguage() . "'
            OR language = 'all'
            ORDER BY important DESC, publish_date DESC ";
			$re_news = sql_query($query_news);

			while (list($idNews, $publishDate, $title, $shortDesc, $longDesc, $important, $viewer) = sql_fetch_row($re_news)) {

				$viewer = (is_string($viewer) && $viewer != false ? unserialize($viewer) : []);
				$intersect = array_intersect($user_assigned, $viewer);
				if (!empty ($intersect) || empty ($viewer)) {

					$news[] = [
						'idNews' => $idNews,
						'title' => $title,
						'shortDescriptions' => $shortDesc,
						'longDescription' => $longDesc,
						'important' => $important,
						'date' => Format::date($publishDate, 'date')
					];
				}
			}
		}
		return $news;
	}


}
