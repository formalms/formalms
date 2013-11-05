<?php defined("IN_FORMA") or die("Direct access is forbidden");

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

class LabelAlmsController extends AlmsController
{
	protected $json;
	protected $acl_man;
	protected $model;
	protected $permissions;

	public function init()
	{
		parent::init();
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();
		$this->acl_man =& Docebo::user()->getAclManager();
		$this->model = new LabelAlms();
		$this->permissions = array(
			'view' => checkPerm('view', true, 'label', 'lms'),
			'add' => true, //checkPerm('mod', true, 'label', 'lms'),
			'mod' => true, //checkPerm('mod', true, 'label', 'lms'),
			'del' => true, //checkPerm('mod', true, 'label', 'lms')
		);
	}


	protected function _getMessage($code) {
		$message = "";
		switch ($code) {
			case "no permission": $message = ""; break;
		}
		return $message;
	}


	public function showTask()
	{
		if(isset($_GET['res']) && $_GET['res'] !== '')
			UIFeedback::info(Lang::t(strtoupper($_GET['res']), 'label'));

		if(isset($_GET['err']) && $_GET['err'] !== '')
			UIFeedback::error(Lang::t(strtoupper($_GET['err']), 'label'));

		$params = array(
			'model' => $this->model,
			'permissions' => $this->permissions
		);

		$this->render('show', $params);
	}


	protected function _formatDescription($description, $length = 200) {
		$description = Util::purge($description); //strip html tags
		$description = html_entity_decode($description, ENT_QUOTES);
		if (strlen($description)>$length) $description = substr($description, 0, $length-3).'...';
		$description = htmlentities($description, ENT_QUOTES);
		return $description;
	}

	public function getLabelsTask()
	{
		$start_index = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_MIXED, 'title');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');
		
		$labels = $this->model->getLabels($start_index, $results, $sort, $dir);
		$total_label = $this->model->getTotalLabelsCount();

		$list = array();

		$first = true;
		$count = count($labels);
		$counter = 0;

		foreach ($labels as $value) {
			$position = "";
			if ($first) {
				$position .= 'first';
				$first = false;
			}
			$counter++;
			if ($counter == $count) {
				$position .= 'last';
			}

			$list[] = array('id_common_label' => $value[LABEL_ID_COMMON],
							'title' => $value[LABEL_TITLE],
							'description' => $this->_formatDescription($value[LABEL_DESCRIPTION], 100),
							'position' => $position,
							'sequence' => $value[LABEL_SEQUENCE],
							'mod' => '<a href="index.php?r=alms/label/mod&amp;id_common_label='.$value[LABEL_ID_COMMON].'" title="'.Lang::t('_MOD', 'label').'">'.Get::img('standard/edit.png', Lang::t('_MOD', 'label')).'</a>',
							'del' => 'ajax.adm_server.php?r=alms/label/dellabel&id_common_label='.$value[LABEL_ID_COMMON]);
		}

		$result = array('totalRecords' => $total_label,
						'startIndex' => $start_index,
						'sort' => $sort,
						'dir' => $dir,
						'rowsPerPage' => $results,
						'results' => count($list),
						'records' => $list);

		echo $this->json->encode($result);
	}

	public function move() {
		$id_common_label = Get::req('id_common_label', DOTY_INT, 0);
		$direction = Get::req('dir', DOTY_MIXED, 'down');
		if($direction == 'up') {

			$re = $this->model->move_up($id_common_label);
		} else {

			$re = $this->model->move_down($id_common_label);
		}
		$res = array('success' => $re);
		echo $this->json->encode($res);
	}

	public function add()
	{
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/label/show'
			));
			return;
		}

		if(isset($_POST['undo']))
			Util::jump_to('index.php?r=alms/label/show');

		$all_languages = Docebo::langManager()->getAllLangCode();

		$res = true;

		if(isset($_POST['insert']))
		{
			$id_common_label = $this->model->getNewIdCommon();
			$file_name = '';
			
			require_once(_base_.'/lib/lib.upload.php');
			if($_FILES['label_image']['error'] == 0) {
				$extension = end(explode('.', $_FILES['label_image']['name']));
				$file_name = 'label_image_'.$id_common_label.'.'.$extension;

				$path = '/appLms/label/';

				sl_open_fileoperations();

				if(sl_file_exists($path.$file_name))
					sl_unlink($path.$file_name);
				if(is_writable(_files_.$path)) echo 'writable';
				else echo 'not writable';
				sl_upload($_FILES['label_image']['tmp_name'], $path.$file_name);
				
				sl_close_fileoperations();
			}
					
			foreach($all_languages as $lang_code)
			{
				$title = Get::req($lang_code.'_title', DOTY_MIXED, '');
				$description = Get::req($lang_code.'_description', DOTY_MIXED, '');


				$tmp_res = $this->model->insertLabel($id_common_label, $lang_code, $title, $description, $file_name);

				if(!$tmp_res)
					$res = false;
			}

			if($res)
				Util::jump_to('index.php?r=alms/label/show&res=_ok_insert');
			Util::jump_to('index.php?r=alms/label/show&err=_err_insert');
		}
		
		$params = array('model' => $this->model,
						'all_languages' => $all_languages);

		$this->render('add', $params);
	}

	public function mod()
	{
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/label/show'
			));
			return;
		}

		$id_common_label = Get::req('id_common_label', DOTY_INT, 0);

		if(isset($_POST['undo']))
			Util::jump_to('index.php?r=alms/label/show');

		$all_languages = Docebo::langManager()->getAllLangCode();

		$res = true;

		if(isset($_POST['update']))
		{
			require_once(_base_.'/lib/lib.upload.php');
			$path = '/appLms/label/';

			if(isset($_POST['del_label_image']))
			{
				$file_name = $this->model->getLabelFile($id_common_label);

				if($file_name !== '' && sl_file_exists($path.$file_name))
				{
					sl_open_fileoperations();
					sl_unlink($path.$file_name);
					sl_close_fileoperations();
				}

				$file_name = '';
			}
			else
				$file_name = $this->model->getLabelFile($id_common_label);

			if($_FILES['label_image']['error'] == 0)
			{
				$extension = end(explode('.', $_FILES['label_image']['name']));
				$file_name = 'label_image_'.$id_common_label.'.'.$extension;

				sl_open_fileoperations();

				$file_name_del = $this->model->getLabelFile($id_common_label);

				if($file_name_del !== '' && sl_file_exists($path.$file_name_del))
					sl_unlink($path.$file_name_del);

				sl_upload($_FILES['label_image']['tmp_name'], $path.$file_name);

				sl_close_fileoperations();
			}
			
			foreach($all_languages as $lang_code)
			{
				$title = Get::req($lang_code.'_title', DOTY_MIXED, '');
				$description = Get::req($lang_code.'_description', DOTY_MIXED, '');
				
				$tmp_res = $this->model->updateLabel($id_common_label, $lang_code, $title, $description, $file_name);

				if(!$tmp_res)
					$res = false;
			}

			if($res)
				Util::jump_to('index.php?r=alms/label/show&res=_ok_mod');
			Util::jump_to('index.php?r=alms/label/show&err=_err_mod');
		}

		$label_info = $this->model->getLabelInfo($id_common_label);

		$params = array('model' => $this->model,
						'all_languages' => $all_languages,
						'label_info' => $label_info,
						'id_common_label' => $id_common_label);

		$this->render('mod', $params);
	}

	protected function dellabel()
	{
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		$id_common_label = Get::req('id_common_label', DOTY_INT, 0);

		$res = array('success' => $this->model->delLabel($id_common_label));

		$this->data = $this->json->encode($res);

		echo $this->data;
	}
}
?>