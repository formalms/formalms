<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
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
class DashboardBlockNewsLms extends DashboardBlockLms
{
    public function parseConfig($jsonConfig)
    {
        $this->parseBaseConfig($jsonConfig);
    }

    public function getAvailableTypesForBlock()
    {
        return self::ALLOWED_TYPES;
    }

    public function getViewData()
    {
        $data = $this->getCommonViewData();
        $data['news'] = $this->getNews();

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

    private function getNews()
    {
        $news = [];
        $ma = new Man_MiddleArea();

        if ($ma->currentCanAccessObj('news')) {
            $user_assigned = Docebo::user()->getArrSt();
            $query_news = "
            SELECT idNews, publish_date, title, short_desc,long_desc, important, viewer
            FROM %lms_news_internal
            WHERE language = '" . Docebo::user()->preference->getLanguage() . "'
            OR language = 'all'
            ORDER BY important DESC, publish_date DESC ";
            $re_news = sql_query($query_news);

            while (list($idNews, $publishDate, $title, $shortDesc, $longDesc, $important, $viewer) = sql_fetch_row($re_news)) {
                $viewer = (is_string($viewer) && $viewer != false ? unserialize($viewer) : []);
                $intersect = array_intersect($user_assigned, $viewer);

                if (!empty($intersect) || empty($viewer)) {
                    $news[] = [
                        'idNews' => $idNews,
                        'title' => $title,
                        'shortDescriptions' => $shortDesc,
                        'longDescription' => $longDesc,
                        'important' => $important,
                        'date' => Format::date($publishDate, 'date'),
                    ];
                }
            }
        }

        return $news;
    }
}
