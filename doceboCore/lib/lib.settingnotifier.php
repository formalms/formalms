<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

require_once(_base_.'/lib/lib.event.php' );

/**
 * This is the class for ClassEvents in Docebo
 *
 * @package admin-core
 * @subpackage event
 * @version  $Id:$
 */
class DoceboSettingNotifier extends DoceboEventConsumer {

	function _getConsumerName() {
		return "DoceboSettingNotifier";
	}

	function actionEvent( &$event ) {

		parent::actionEvent($event);

		$event_throw = $event->getClassName();
		switch($event_throw) {
			case "SettingUpdate" : {
				$field_saved =explode(" - ", $event->getProperty('field_saved'));
			} break;
		}
		return true;
	}

}

?>