<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class AlmsController extends Controller {

	protected $_mvc_name = 'almscontroller';

	public function viewPath() {

		return _lms_.'/admin/views';
	}

    public function templatePath(){
        return _templates_ . "/".getTemplate()."/layout";
    }

	public function init() {
		parent::init();
		checkPerm('view', false, $this->_mvc_name, 'lms');
	}


}
