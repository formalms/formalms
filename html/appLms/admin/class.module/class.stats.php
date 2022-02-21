<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package  DoceboLms
 * @version  $Id: class.course.php 1003 2007-03-31 13:59:46Z fabio $
 * @category Category
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Stats extends LmsAdminModule {

	function loadBody() {

	}

	// Function for permission managment

	function getAllToken($op) {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png']
        ];
	}
}

?>
