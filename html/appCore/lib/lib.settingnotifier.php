<?php defined("IN_FORMA") or die('Direct access is forbidden.');



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