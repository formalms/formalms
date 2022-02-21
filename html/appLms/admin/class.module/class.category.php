<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package  DoceboLms
 * @version  $Id: class.category.php 573 2006-08-23 09:38:54Z fabio $
 * @category Category
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Category extends LmsAdminModule {
		
	function loadBody() {
		
		require_once(dirname(__FILE__).'/../modules/category/category.php');
		categoryDispatch($GLOBALS['op']);
	}
}

?>
