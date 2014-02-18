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
 * @package admin-core
 * @subpackage field
 */
 
require_once($GLOBALS["where_framework"]."/class/class.fieldmap.php");

class FieldMapChistory extends FieldMap {

	var $lang=NULL;

	/**
	 * class constructor
	 */
	function FieldMapChistory() {

		$this->lang=& DoceboLanguage::createInstance("company", "crm");

		parent::FieldMap();
	}


	function _getMainTable() {

	}


	function getPrefix() {
		return "chistory_";
	}


	function getPredefinedFieldLabel($field_id) {

		$res["description"]=$this->lang->def("_CHISTORY_DESCRIPTION");

		return $res[$field_id];
	}


	function getRawPredefinedFields() {
		return array("description");
	}


	/**
	 * @param array $predefined_data
	 * @param array $custom_data
	 * @param integer $id company id; if 0 a new company will be created
	 * @param boolean $dropdown_id if true will take dropdown values as id;
	 *                             else will search the id starting from the value.
	 */
	function saveFields($predefined_data, $custom_data=FALSE, $id=0, $dropdown_id=TRUE) {
		require_once($GLOBALS["where_crm"]."/modules/contacthistory/lib.contacthistory.php");

		$chdm=new ContactHistoryDataManager();
		$data=array();


		$company_id=(int)$predefined_data["company_id"];

		$data["contact_id"]=(int)$id;
		$data["title"]=$predefined_data["title"];
		$data["description"]=$predefined_data["description"];
		$data["reason"]=0;
		$data["type"]=$predefined_data["type"];

		if (isset($predefined_data["meeting_date"])) {
			$data["meeting_date"]=$predefined_data["meeting_date"];
		}
		else {
			$data["meeting_date"]=date("Y-m-d H:i:s");
		}


		$chistory_id=$chdm->saveContactHistory($company_id, $data);

		return $chistory_id;
	}


}




?>