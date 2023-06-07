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

class Man_Forum
{
    public static function getCountUnreaded($id_user, $courses, $last_access)
    {
        /*
        //$time_start = getmicrotime();
        $unreaded = array();
        if(empty($courses)) return $unreaded;

        $reLast = sql_query("
        SELECT idCourse, new_forum_post
        FROM ".$GLOBALS['prefix_lms']."_courseuser
        WHERE idUser = '".$id_user."' AND idCourse IN ( ".implode(',', $courses)." ) ");
        while(list($id_c, $new_post) = sql_fetch_row($reLast)) {

            list($unreaded[$id_c]) = $new_post;
        }
        return $unreaded;*/

        //$time_start = getmicrotime();
        $unreaded = [];
        if (empty($courses)) {
            return $unreaded;
        }

        $reLast = sql_query('
		SELECT idCourse, new_forum_post
		FROM ' . $GLOBALS['prefix_lms'] . "_courseuser
		WHERE idUser = '" . $id_user . "' AND idCourse IN ( " . implode(',', $courses) . ' ) ');
        while (list($id_c, $new_post) = sql_fetch_row($reLast)) {
            list($unreaded[$id_c]) = $new_post;
        }

        return $unreaded;
    }

    public function getUserForumPostLms($id_user)
    {
        $query_forum_post = '
			SELECT COUNT(*)
			FROM ' . $GLOBALS['prefix_lms'] . "_forummessage
			WHERE author = '" . $id_user . "'";

        $forum_post = sql_fetch_row(sql_query($query_forum_post));

        return $forum_post[0];
    }
}
