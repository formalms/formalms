<?php defined("IN_FORMA") or die('Direct access is forbidden.');



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

    public function templatePath(){
        return _templates_ . "/".getTemplate()."/layout/appCore";
    }
	
}
