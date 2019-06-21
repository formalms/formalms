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
class DashboardBlockCourseNewsLms extends DashboardBlockLms
{

	public function __construct()
	{
		parent::__construct();
		$this->setEnabled(true);
		$this->setType(DashboardBlockLms::TYPE_MEDIUM);
	}

	public function getViewData(): array
	{
		$data = $this->getCommonViewData();
		$data['news'] = $this->getNews();

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

				$viewer = (is_string($viewer) && $viewer != false ? unserialize($viewer) : array());
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

	public function getLink(): string
	{
		return '#';
	}
}
