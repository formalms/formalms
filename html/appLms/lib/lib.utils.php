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

/**
 * @package 	DoceboLMS
 * @category 	Utilities
 * @author 		Fabio Pirovano <fabio@docebo.com>
 * @version 	$Id: lib.utils.php 793 2006-11-21 15:43:19Z fabio $
 */

/**
 * @param 	int $idMain if passed return the first voice of the relative menu
 *
 * @return 	array 	with three element modulename and op that contains the first accessible menu element 
 *					indicate in idMain  array( [idMain], [modulename], [op] )
 **/
function firstPage( $idMain = false ) {
	
	$query_main = "
	SELECT module.idModule, main.idMain, module.module_name, module.default_op, module.token_associated 
	FROM ( ".$GLOBALS['prefix_lms']."_menucourse_main AS main JOIN
		".$GLOBALS['prefix_lms']."_menucourse_under AS un ) JOIN
		".$GLOBALS['prefix_lms']."_module AS module
	WHERE main.idMain = un.idMain AND un.idModule = module.idModule 
		AND main.idCourse = '".(int)$_SESSION['idCourse']."'
		AND un.idCourse = '".(int)$_SESSION['idCourse']."'
		".( $idMain !== false ? " AND main.idMain='".$idMain."' "  : '' )."
	ORDER BY main.sequence, un.sequence";
	$re_main = sql_query($query_main);
	
	while(list($id_module, $main, $module_name, $default_op, $token) = sql_fetch_row($re_main)) {
		
		if(checkPerm($token, true, $module_name)) {
		
			return array('idModule'=> $id_module, 'idMain' => $main, 'modulename' => $module_name, 'op' => $default_op);
		}
	}
}

function getLmsLangFlags() {
	
	$lang=& DoceboLanguage::createInstance('blind_navigation');
	$blind_link="<li><a href=\"#lang_box\">".$lang->def("_LANG_SELECT")."</a></li>";
	$GLOBALS["page"]->add($blind_link, "blind_navigation");

	$all_lang = Docebo::langManager()->getAllLangCode();

	if(!is_array($all_lang)) return '';
	$res = '<ul id="lang_box">';
	foreach($all_lang as $k => $lang_code) {
		
		$res.= '<a href="index.php?sop=changelang&amp;new_lang='.$lang_code.'" title="'.$lang_code.'">'
			.'<img src="'.getPathImage('fw').'language/'.$lang_code.'.png" alt="'.$lang_code.'" /></a>';
	}
	$res .= '</ul>';
	return $res;
}

?>
