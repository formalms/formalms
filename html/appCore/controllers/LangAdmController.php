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

class LangAdmController extends AdmController {

	protected $json = false;
	protected $model = false;

	protected $perm = array();

	public function init() {
		parent::init();

		$this->json = new Services_JSON();
		$this->model = new LangAdm();
		$this->perm = array(
			'view' => checkPerm('view', true, 'lang', 'framework'),
			'mod' => checkPerm('mod', true, 'lang', 'framework')
		);
	}

	public function showTask() {

		$this->render('show', array());
	}

	public function getlang() {
		
		$sortable = array('lang_code', 'lang_description', 'lang_direction', 'lang_stats');

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results	= Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$sort		= Get::req('sort', DOTY_STRING, "");
		$dir		= Get::req('dir', DOTY_STRING, "asc");

		if(!in_array($sort, $sortable)) $sort = 'lang_code';
		switch ($dir) {
			case "desc" : { $dir = 'desc'; };break;
			default : { $dir = 'asc'; };break;
		}

		$lang_list = $this->model->getLangList($startIndex, $results, $sort, $dir);
		$total = $this->model->getLangTotal();

		while(list($i, $lang) = each($lang_list)) {

			$lang->lang_translate = 'index.php?r=adm/lang/list&amp;lang_code='.$lang->lang_code;
			$lang->lang_export = 'index.php?r=adm/lang/export&amp;lang_code='.$lang->lang_code;
			$lang->lang_mod = 'ajax.adm_server.php?r=adm/lang/mod&amp;lang_code='.$lang->lang_code;
			$lang->lang_del = 'ajax.adm_server.php?r=adm/lang/del&amp;lang_code='.$lang->lang_code;
			$lang_list[$i] = $lang;
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => 25,
			'results' => count($lang_list),
			'records' => array_values($lang_list)
		);
		echo $this->json->encode($output);
	}

	public function addmask() {

		$lang = new stdClass();
		$lang->lang_code = '';
		$lang->lang_description = '';
		$lang->lang_direction = 'ltr';
		$lang->lang_browsercode = '';
		
		$this->render('lang_form', array('lang' => $lang));

		$params = array(
			'success' => true,
			'header' => Lang::t('_ADD', 'standard'),
			'body' =>  ob_get_clean()
		);
		@ob_start();
		echo $this->json->encode($params);
	}

	public function insertlang() {

		$lang_code = Get::req('lang_code', DOTY_STRING, '' );
		$lang_description = Get::req('lang_description', DOTY_STRING, '' );
		$lang_direction = Get::req('lang_direction', DOTY_STRING, 'ltr' );
		$lang_browsercode = Get::req('lang_browsercode', DOTY_STRING, '' );
		
		if($lang_code == '') {
			$result = array( 'success' => false, 'message' => Lang::t('_NO_TITLE', 'standard') );
			echo $this->json->encode($result);
			return;
		}
		$re = $this->model->newLanguage($lang_code, $lang_description, $lang_direction, $lang_browsercode);

		$result = array(
			'success' => $re,
			'message' => ( $re ? '' : Lang::t('_OPERATION_FAILED', 'standard') )
		);
		echo $this->json->encode($result);
	}

	public function updatelang() {

		$lang_code = Get::req('lang_code', DOTY_STRING, '' );
		$lang_description = Get::req('lang_description', DOTY_STRING, '' );
		$lang_direction = Get::req('lang_direction', DOTY_STRING, 'ltr' );
		$lang_browsercode = Get::req('lang_browsercode', DOTY_STRING, '' );

		$answ = $this->model->updateLanguage($lang_code, $lang_description, $lang_direction, $lang_browsercode);

		$result = array(
			'success' => $answ,
			'message' => ( $answ ? '' : Lang::t('_OPERATION_FAILED', 'standard') )
		);
		echo $this->json->encode($result);
	}

	public function mod() {

		$lang_code = Get::req('lang_code', DOTY_STRING, '' );
		$lang = $this->model->getLanguage($lang_code);

		$this->render('edit_form', array('lang' => $lang));
		$params = array(
			'success' => true,
			'header' => Lang::t('_MOD', 'standard'),
			'body' =>  ob_get_clean()
		);
		@ob_start();
		echo $this->json->encode($params);
	}

	public function savemask() {

		$lang = new stdClass();
		$lang->lang_code = '';
		$lang->lang_description = '';
		$lang->lang_direction = 'ltr';
		$lang->lang_browsercode = '';

		$this->render('lang_form', array('lang' => $lang));

		$params = array(
			'success' => true,
			'header' => Lang::t('_ADD', 'standard'),
			'body' =>  ob_get_clean()
		);
		@ob_start();
		echo $this->json->encode($params);
	}

	public function delTask() {
		$lang_code = Get::req('lang_code', DOTY_STRING, '' );

		$re = false;
		if($lang_code != '') {

			$re = $this->model->delLanguage($lang_code);
		}
		$result = array(
			'success' => $re,
			'message' => ( $re ? '' : Lang::t('_OPERATION_FAILED', 'standard') )
		);
		echo $this->json->encode($result);
	}

	public function exportTask() {
		$lang_code = Get::req('lang_code', DOTY_STRING, '' );

		$model = new LangAdm();
		$model->exportTranslation($lang_code);
	}

	public function importTask() {
		$error = Get::req('error', DOTY_INT, 0);
		if($error) UIFeedback::error (Lang::t('_ERROR_UPLOAD', 'standard'));

		$this->render('import_mask', array());
	}
	
	public function doimportTask() {
		
		if(!isset($_FILES['lang_file'])) Util::jump_to('index.php?r=adm/lang/import&error=1');
		if($_FILES['lang_file']['error'] != UPLOAD_ERR_OK) Util::jump_to('index.php?r=adm/lang/import&error=2');

		$lang_file	= $_FILES['lang_file']['tmp_name'];
		$overwrite	= Get::req('overwrite', DOTY_INT, 0 );
		$noadd_miss = Get::req('noadd_miss', DOTY_INT, 0);

		$model = new LangAdm();
		$re = $model->importTranslation($lang_file, $overwrite, $noadd_miss);
		
		Util::jump_to('index.php?r=adm/lang/show');
	}

	public function listTask() {
		YuiLib::load('table');

		$lang_code = Get::req('lang_code', DOTY_STRING, Lang::get() );

		$module_list = $this->model->getModuleList();
		array_unshift($module_list, Lang::t('_ALL'));

		$plugins_ids = $this->model->getPluginsList();
		$plugins_ids[0] = Lang::t('_NONE');
		ksort($plugins_ids);

		$language_list_diff = $language_list = $this->model->getLangCodeList();
		array_unshift($language_list_diff, Lang::t('_NONE'));

		$this->render('list', array(
			'lang_code' => $lang_code,
			'module_list' => $module_list,
			'language_list' => $language_list,
			'language_list_diff' => $language_list_diff,
			'plugins_ids' => $plugins_ids
		));
	}

	public function getTask() {
		$start_index	= Get::req('startIndex', DOTY_INT, 0);
		$results		= Get::req('results', DOTY_MIXED, Get::sett('visuItem', 250));
		$sort			= Get::req('sort', DOTY_MIXED, 'text_module');
		$dir			= Get::req('dir', DOTY_MIXED, 'asc');

		$la_module		= Get::req('la_module', DOTY_ALPHANUM, false);
		$la_text		= Get::req('la_text', DOTY_MIXED, false);
		$lang_code		= Get::req('lang_code', DOTY_ALPHANUM, false);
		$lang_code_diff = Get::req('lang_code_diff', DOTY_ALPHANUM, false);
		$only_empty		= Get::req('only_empty', DOTY_MIXED, 0);
		$plugin_id		= Get::req('plugin_id', DOTY_INT, false);
		if($only_empty == 'true') $only_empty = true;
		else $only_empty = false;

		$model = new LangAdm();
		$lang_list = $model->getAll($start_index, $results, $la_module, $la_text, $lang_code, $lang_code_diff, $only_empty, $sort, $dir, $plugin_id);

		$total_lang = $model->getCount($la_module, $la_text, $lang_code, $only_empty);

		$res = array(	'totalRecords' => $total_lang,
						'startIndex' => $start_index,
						'sort' => $sort,
						'dir' => $dir,
						'rowsPerPage' => $results,
						'results' => count($lang_list),
						'records' => $lang_list);

		echo $this->json->encode($res);
	}

	/**
	 * Inline editor server, here we will save the new trasnslation
	 */
	public function saveDataTask() {
		$id_text	= urldecode(Get::req('id_text', DOTY_INT, 0));
		$lang_code	= urldecode(Get::req('lang_code', DOTY_MIXED, Lang::get() ));
		$new_value	= urldecode(Get::req('new_value', DOTY_MIXED, ''));
		$old_value	= urldecode(Get::req('old_value', DOTY_MIXED, ''));


		$model = new LangAdm();
		$re = $model->saveTranslation($id_text, $lang_code, $new_value);
		$res = array(	'success' => $re,
						'new_value' => $new_value,
						'old_value' => $old_value);
		
		echo $this->json->encode($res);
	}

	public function translatemask() {
		$lang = new stdClass();
		$lang->lang_code = '';

		$this->render('translatemask', array());

		$params = array(
			'success' => true,
			'header' => Lang::t('_TRANSLATELANG', 'admin_lang'),
			'body' =>  ob_get_clean()
		);
		@ob_start();
		echo $this->json->encode($params);
	}

	public function insertkey() {

		$lang_module = Get::req('lang_module', DOTY_MIXED, '');
		$lang_key	 = Get::req('lang_key', DOTY_MIXED, '' );

		$model = new LangAdm();
		$id_text = $model->insertKey($lang_key, $lang_module, '');
		if(!$id_text) $re = false;
		else {
			$re = true;

			foreach($_POST['translation'] as $lang_code => $translation) {

				if($translation != '') $re &= $model->insertTranslation($id_text, $lang_code, $translation);
			}
		}
		$output = array(
			'success' => ( $re ? true : false ),
			'message' => ( $re ? Lang::t('_OPERATION_SUCCESSFUL', 'admin_lang') : Lang::t('_OPERATION_FAILURE', 'admin_lang') ),
		);
		echo $this->json->encode($output);
	}

	public function deleteKeyTask() {
		$id_text	= Get::req('id_text', DOTY_INT, 0);


		$model = new LangAdm();
		$re = $model->deleteKey($id_text);
		$res = array(	'success' => $re,
						'message' => Lang::t('_UNABLE_TO_DELETE', 'standard') );

		echo $this->json->encode($res);
	}

}
