<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

class AdmController extends Controller {

	protected $_mvc_name = 'admcontroller';

	public function viewPath() {

		return _adm_.'/views';
	}


	public function init() {
		parent::init();
		if (!defined("CORE")) {
			checkRole('/framework/admin/'.$this->_mvc_name.'/view', false);
		} else {
			checkPerm('view', false, $this->_mvc_name, 'framework');
		}
	}
	
	
}
