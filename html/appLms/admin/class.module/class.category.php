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
