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
 * @version  $Id:$
 */
class DoceboOrgchartNotifier extends DoceboEventConsumer {

	function _getConsumerName() {
		return "DoceboOrgchartNotifier";
	}

	function actionEvent( &$event ) {
		
		parent::actionEvent($event);
		
		$event_throw = $event->getClassName();
		switch($event_throw) {
			case "UserDel" : {
				$id_user 	= $event->getProperty('userdeleted');
				// remove user from associated
				$acl_man =& Docebo::user()->getAclmanager();
				$acl_man->removeFromAllGroup($id_user);
			};break;
		}
		return true;
	}
	
}

?>