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

class PluginsLmsController extends LmsController {

	public $name = 'elearning';

	public $ustatus = array();
	public $cstatus = array();

	public $levels = array();

	public $path_course = '';

	protected $_default_action = 'show';

	public $info = array();

	public function isTabActive($tab_name) {

		/*switch($tab_name) {
			case "new" : {
				if(!isset($this->info['elearning'][0])) return false;
			};break;
			case "inprogress" : {
				if(!isset($this->info['elearning'][1])) return false;
			};break;
			case "completed" : {
				if(!isset($this->info['elearning'][2])) return false;
			};break;
		}*/
		return true;
	}

	public function init() {
		
	}

	public function showTask() {

		require_once(_lms_.'/lib/lib.middlearea.php');
		$ma = new Man_MiddleArea();
		$block_list = array();
		//if($ma->currentCanAccessObj('user_details_short')) $block_list['user_details_short'] = true;
		if($ma->currentCanAccessObj('user_details_full')) $block_list['user_details_full'] = true;
		if($ma->currentCanAccessObj('credits')) $block_list['credits'] = true;
		if($ma->currentCanAccessObj('news')) $block_list['news'] = true;
		$tb_label = $ma->currentCanAccessObj('tb_label');
		if(!$tb_label)
			$_SESSION['id_common_label'] = 0;
		else
		{
			$id_common_label = Get::req('id_common_label', DOTY_INT, -1);

			if($id_common_label >= 0)
				$_SESSION['id_common_label'] = $id_common_label;
			elseif($id_common_label == -2)
				$_SESSION['id_common_label'] = -1;

			$block_list['labels'] = true;
		}

		if($tb_label && $_SESSION['id_common_label'] == -1)
		{
			require_once(_lms_.'/admin/models/LabelAlms.php');
			$label_model = new LabelAlms();

			$user_label = $label_model->getLabelForUser(Docebo::user()->getId());

			$this->render('_labels',array(	'block_list' => $block_list,
											'label' => $user_label));
		}
		else
		{
			if(!empty($block_list))
				$this->render('_tabs_block', array('block_list' => $block_list));
			else
				$this->render('_tabs', array());
		}

		require_once(_lms_.'/lib/lib.middlearea.php');
		$ma = new Man_MiddleArea();
		$this->render('pluginslist', array(
			'pluginslist' => $pluginslist
		));
	}

	public function active() {
		require_once(_adm_."/models/PluginAdm.php");
		$plugin_adm=new PluginAdm();
		
		$active_plugins=$plugin_adm->getMainView('all');
		
	}
	
	

	

}
