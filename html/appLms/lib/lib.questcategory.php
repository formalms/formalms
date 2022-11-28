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

class Questcategory
{
    public function __construct()
    {
    }

    public static function getCategory()
    {
        //search query
        $query_quest_cat = '
		SELECT idCategory, name 
		FROM %lms_quest_category
		ORDER BY name';
        $categories = [0 => Lang::t('_NONE', 'test')];
        $re_quest_cat = sql_query($query_quest_cat);
        while (list($id, $title) = sql_fetch_row($re_quest_cat)) {
            $categories[$id] = $title;
        }

        return $categories;
    }

    public function getTestQuestionsCategories($id_test)
    {
        if (!$id_test) {
            return false;
        }

        $categories = [0 => Lang::t('_NONE', 'test')];
        //retrieve the categories of the test's questions
        $query = 'SELECT DISTINCT(idCategory) FROM %lms_testquest WHERE idTest = ' . (int) $id_test;
        $res = sql_query($query);
        if ($res && sql_num_rows($res) > 0) {
            $list = [];
            while (list($id_category) = sql_fetch_row($res)) {
                $list[] = (int) $id_category;
            }
            if (!empty($list)) {
                $query_quest_cat = 'SELECT idCategory, name FROM %lms_quest_category WHERE idCategory IN (' . implode(',', $list) . ') ORDER BY name';
                $re_quest_cat = sql_query($query_quest_cat);
                while (list($id, $title) = sql_fetch_row($re_quest_cat)) {
                    if ($id > 0) {
                        $categories[$id] = $title;
                    }
                }
            }
        }

        return $categories;
    }

    public function getInfoAboutCategory($category)
    {
        //search query
        $query_quest_cat = '
		SELECT idCategory, name 
		FROM %lms_quest_category 
		WHERE idCategory IN ( ' . implode(',', $category) . ' ) 
		ORDER BY name';
        $categories = [0 => Lang::t('_NONE', 'test')];
        $re_quest_cat = sql_query($query_quest_cat);
        while (list($id, $title) = sql_fetch_row($re_quest_cat)) {
            $categories[$id] = $title;
        }

        return $categories;
    }
}
