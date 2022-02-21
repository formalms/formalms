<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @version  $Id: $
 */
// ----------------------------------------------------------------------------


require_once($GLOBALS["where_framework"]."/lib/resources/lib.resource_model.php");


class ResourceClassroom extends ResourceModel {


	function ResourceClassroom($prefix=FALSE, $dbconn=NULL) {
		$this->setResourceCode("classroom");
		parent::ResourceModel($prefix, $dbconn);
	}


	function checkAvailability($resource_id, $start_date=FALSE, $end_date=FALSE) {
		$res=FALSE;

		$found=$this->getResourceEntries((int)$resource_id, $start_date, $end_date);

		if (count($found) < $this->getAllowedSimultaneously())
			$res=TRUE;

		return $res;
	}


}





?>
