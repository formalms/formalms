<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

class AlmsController extends Controller {

	protected $_mvc_name = 'almscontroller';

	public function viewPath() {

		return _lms_.'/admin/views';
	}


	public function init() {
		parent::init();
		checkPerm('view', false, $this->_mvc_name, 'lms');
	}


}
