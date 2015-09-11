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

class Lms_TabWidget extends Widget {

	public $id = "middlearea";

	public $sub_id = "tab_content";

	public $active = "elearning";
	
	public $show = "elearning,classroom,catalog,assessment,coursepath,games,communication,videoconference,kb";

	public $close = true;

	public $ma = null;

	public $updates = false;
	
	/**
	 * Constructor
	 * @param <string> $config the properties of the table
	 */
	public function __construct() {
		parent::__construct();
		$this->_widget = 'lms_tab';

		require_once(_lms_.'/lib/lib.middlearea.php');
		$this->ma = new Man_MiddleArea();

		$this->updates = new UpdatesLms();
	}

	public function isActive($tab_name) {

		return $this->ma->currentCanAccessObj('tb_'.$tab_name);
		//return (strpos($this->show, $tab_name) !== false);
	}

	public function selected($tab_name, $other_classes = false) {
		
		$class = '';
		if( $tab_name == $this->active ) $class .= 'active';
		if( $other_classes != false ) $class .= ' '.$other_classes;

		return ( $class ? ' class="'.$class.'" ' : '' );
	}

	public function run() {

		$u = $this->updates->getAll();

		$this->render('lms_tab', array(
			'elearning'			=> $u['elearning'],
			'classroom'			=> $u['classroom'],
			'assessment'		=> $u['assessment'],
			'catalog'			=> $u['catalog'],
			'coursepath'		=> $u['coursepath'],
			'games'				=> $u['games'],
			'communication'		=> $u['communication'],
			'videoconference'	=> $u['videoconference']
		));
		if($this->close) $this->render('lms_tab_close');
	}

	public function endWidget() {

		$this->render('lms_tab_close');
	}

}
