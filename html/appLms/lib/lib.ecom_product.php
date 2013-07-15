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
 * @version $Id:$
 */

require_once($GLOBALS["where_ecom"]."/lib/lib.ecom_product.php");

class EcomProductCourse extends EcomProduct {

	var $subs_man;

	function EcomProductCourse() {
		
		require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');
		$this->subs_man = new CourseSubscribe_Management();
		
	}
	
	function doActivate() {
		
		$id_user = $this->product_info['id_user'];
		$id_course = end(explode('_', $this->product_info['id_prod']));
		//$res = $this->subs_man->subscribeUser(	$id_user, $id_course, '3');
		//if ($res)
		//{
			$query = "UPDATE ".$GLOBALS['prefix_lms']."_courseuser" .
					" SET status = '0'," .
					" waiting = '0'" .
					" WHERE idUser = '".$id_user."'" .
					" AND idCourse = '".$id_course."'";
			
			$res = sql_query($query);
		//}
		return $res;
	}
	

	function doDeactivate() {

		$id_user = $this->product_info['id_user'];
		$id_course = end(explode('_', $this->product_info['id_prod']));
		$res = $this->subs_man->unsubscribeUser($id_user, $id_course);
	}

}

class EcomProductCourseEdition extends EcomProduct {

	function EcomProductCourseEdition() {
	
		require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');
		$this->subs_man = new CourseSubscribe_Management();
	}
	
	function doActivate() {
		
		$id_user = $this->product_info['id_user'];
		$id_edition = end(explode('_', $this->product_info['id_prod']));		
		//$res = $this->subs_man->subscribeEditionUsers(	array($id_user), $id_edition, '3', false);
		//if ($res)
		//{
			$query = "UPDATE ".$GLOBALS['prefix_lms']."_courseuser" .
					" SET status = '0'," .
					" waiting = '0'" .
					" WHERE idUser = '".$id_user."'" .
					" AND edition_id = '".$id_edition."'";
			
			$res = sql_query($query);
		//}
		return $res;
	}
	

	function doDeactivate() {

		$id_user = $this->product_info['id_user'];
		$id_edition = end(explode('_', $this->product_info['id_prod']));
		$res = $this->subs_man->unsubscribeUserEd($id_user, $id_edition, false);
	}
	
}

?>