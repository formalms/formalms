<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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


class HomeLmsController extends LmsController {


	public function show() {

        require_once(_base_.'/lib/lib.navbar.php');
        require_once(_lms_.'/lib/lib.middlearea.php');

        $ma = new Man_MiddleArea();

		$block_list = array();
		//if($ma->currentCanAccessObj('user_details_short')) $block_list['user_details_short'] = true;
		if($ma->currentCanAccessObj('user_details_full')) $block_list['user_details_full'] = true;
		if($ma->currentCanAccessObj('credits')) $block_list['credits'] = true;
		if($ma->currentCanAccessObj('news')) $block_list['news'] = true;

        $query_home = "SELECT title, description FROM learning_webpages where publish=1 and in_home = 1 AND language = '".getLanguage()."' LIMIT 1";
		$re_home = sql_query($query_home);
		list($titolo, $descrizione) = sql_fetch_row($re_home);

		if(!empty($block_list))
			$this->render('_tabs_block', array(
				'active_tab' => 'home',
				'_content' => "<div id=\"tabhome_title\"><h1>".$titolo."</h1></div><div id=\"tabhome_description\">".$descrizione."</div>",
				'block_list' => $block_list));
		else
			$this->render('_tabs', array(
				'active_tab' => 'home',
				'_content' => "<div id=\"tabhome_title\"><h1>".$titolo."</h1></div><div id=\"tabhome_description\">".$descrizione."</div>")
			);
	}
}