<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class LmsController extends Controller {

	protected $_mvc_name = 'lmscontroller';

	public function viewPath() {

		return _lms_.'/views';
	}

    public function templatePath(){
        return _templates_ . "/".getTemplate()."/layout/appLms";
    }
}
