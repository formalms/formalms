<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @version  $Id: $
 */
// ----------------------------------------------------------------------------


require_once($GLOBALS["where_framework"]."/lib/resources/lib.resource_model.php");


Class ResourceCourse extends ResourceModel {


	function ResourceCourse($prefix=FALSE, $dbconn=NULL) {
		$this->setResourceCode("course");
		parent::ResourceModel($prefix, $dbconn);
	}


	function checkAvailability($resource_id, $start_date=FALSE, $end_date=FALSE) {
		/* $res=FALSE;

		$found=$this->getResourceEntries((int)$resource_id, $start_date, $end_date);

		if (count($found) < $this->getAllowedSimultaneously())
			$res=TRUE; */

		$res=TRUE;

		return $res;
	}


}





?>
