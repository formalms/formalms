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
 * @version  $Id: class.certificate_assign.php,v 1
 * @category Certification management
 * @author	 Claudio Demarinis <claudiodema [at] docebo [dot] com>
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Certificate_Release extends LmsAdminModule {


	function loadBody() {

		require_once(Forma::inc(_lms_.'/admin/modules/'.$this->module_name.'/'.$this->module_name.'.php'));
		certificateDispatch($GLOBALS['op']);
	}

	// Function for permission managment
	function getAllToken($op) {
		return array(
			'mod' => array( 	'code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/mod.png'),
		);
	}
}

?>