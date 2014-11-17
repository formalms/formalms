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

class LmsController extends Controller {

	protected $_mvc_name = 'lmscontroller';

	public function viewPath() {

		return _lms_.'/views';
	}

	public function viewCustomscriptsPath() {

		return _base_.'/customscripts'.'/'._folder_lms_.'/views';
#		return _lms_.'/customscripts/views';
	}
}
