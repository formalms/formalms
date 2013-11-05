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
 * @package  DoceboLms
 * @version  $Id: class.course.php 1003 2007-03-31 13:59:46Z fabio $
 * @category Category
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Public_CoursePanel extends LmsModule {

	function loadBody() {
		require_once(dirname(__FILE__).'/../modules/public_coursepanel/public_coursepanel.php');
		publicCoursePanelDispatch($GLOBALS['op']);
	}

	// Function for permission managment

	function getAllToken($op) {
		return array(
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'),
			'mod' => array( 	'code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/mod.gif'),
			'subscribe' => array( 'code' => 'subscribe',
								'name' => '_SUBSCRIBE',
								'image' => 'subscribe/add_subscribe.gif'),
			'moderate' => array( 	'code' => 'moderate',
								'name' => '_MODERATE',
								'image' => 'standard/moderate.gif'),
		);
	}
}

?>