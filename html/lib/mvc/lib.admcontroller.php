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

class AdmController extends Controller {

	protected $_mvc_name = 'admcontroller';

	public function viewPath() {

		return _adm_.'/views';
	}

	public function viewCustomscriptsPath() {

		return _base_.'/customscripts'.'/'._folder_adm_.'/views';
#		return _adm_.'/customscripts/views';
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
