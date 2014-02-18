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

require_once(_base_.'/lib/lib.event.php' );

/**
 * This is the class for ClassEvents in Docebo
 * 
 * @package admin-core
 * @subpackage event
 * @version  $Id: lib.coursenotifier.php 113 2006-03-08 18:08:42Z ema $
 */
class DoceboCourseNotifier extends DoceboEventConsumer {

	function _getConsumerName() {
		return "DoceboUserNotifier";
	}

	function actionEvent( &$event ) {
		
		require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');
		
		parent::actionEvent($event);
		
		$acl_man =& Docebo::user()->getACLManager();
		
		// recover event information
		$id_user 	= $event->getProperty('userdeleted');
		
		
		$man_subs = new CourseSubscribe_Management();
		$man_subs->unsubscribeUserFromAllCourses($id_user);
		return true;
		
	}
	
}

?>
