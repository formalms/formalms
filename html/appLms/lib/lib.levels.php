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

class CourseLevel {

	function getLevels() {

		$lang =& DoceboLanguage::createInstance('levels', 'lms');
		return array(
			7 => $lang->def('_LEVEL_7'),		//'Admin'
			6 => $lang->def('_LEVEL_6'),		//'Prof'
			5 => $lang->def('_LEVEL_5'),		//'Mentor'
			4 => $lang->def('_LEVEL_4'),		//'Tutor'
			3 => $lang->def('_LEVEL_3'),		//'Studente'
			2 => $lang->def('_LEVEL_2'),		//'Ghost' (no track)
			1 => $lang->def('_LEVEL_1'),		//'Guest'
		);
	}


	function isTeacher($level) {
		$res=((int)$level === 6 ? TRUE : FALSE);

		return $res;
	}

}

?>