<?php

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

if(!defined('IN_FORMA')) die('You cannot access this file directly');

function questbank(&$url) {

	require_once(_lib_.'/lib.form.php');
	require_once(_lms_.'/lib/lib.quest_bank.php');

	$lang =& DoceboLanguage::createInstance('test', 'lms');
	// now add the yui for the table

	$qb_select 	= new QuestBank_Selector();
	$qb_select->selected_quest = 'selected_quest';
	$qb_select->item_per_page = 25;

	$qb_man 	= new QuestBankMan();

	$form = new Form();


	cout($qb_select->get_header(), 'page_head');
	//addCss('style_yui_docebo');

	cout('<script type="text/javascript">'
		.$qb_select->get_setup_js()
		.'</script>', 'page_head');

	cout( getTitleArea($lang->def('_QUEST_BANK', 'menu_course'))
		.'<div class="std_block yui-skin-docebo yui-skin-sam">', 'content');

	// -- search filter --------------------------------------------------

	$export_f = $qb_man->supported_format();

	cout($form->openForm('search_form', $url->getUrl(), false, 'POST')

		.'<input type="hidden" id="selected_quest" name="selected_quest" value="">'

		.'<div class="align_right">

			<input type="submit" id="export_quest" name="export_quest" value="'.$lang->def('_EXPORT').'">
			<select id="export_quest_select" name="export_quest_select">', 'content');
			cout('<option value="-2">'.Lang::t('_EXISTING_TEST', 'test').'</option>', 'content');
			cout('<option value="-1">'.Lang::t('_NEW_TEST', 'test').'</option>', 'content');
		foreach($export_f as $id_exp => $def) {
			cout('<option value="'.$id_exp.'">'.$def.'</option>', 'content');
		}
		cout('</select>
			<input type="submit" id="import_quest" name="import_quest" value="'.$lang->def('_IMPORT').'">
			<input type="submit" id="delete_quest" name="delete_quest" value="'.$lang->def('_DEL').'">
		</div>', 'content');

	cout($qb_select->get_filter(), 'content');

	cout($form->closeForm(), 'content');

	// -------------------------------------------------------------------

	cout($qb_select->get_selector(), 'content');

	$re_type = sql_query("
	SELECT type_quest
	FROM ".$GLOBALS['prefix_lms']."_quest_type
	WHERE type_quest <> 'break_page'
	ORDER BY sequence");

	cout('
	<div class="align_left">'
		.$form->openForm('add_quest_form', $url->getUrl('op=addquest'), 'GET').'
		<input type="submit" id="add_quest" name="add_quest" value="'.$lang->def('_ADD').'">
		<select id="add_test_quest" name="add_test_quest">', 'content');
	while(list($type_quest) = sql_fetch_row($re_type)) {

		cout('<option value="'.$type_quest.'">'
			.$lang->def('_QUEST_ACRN_'.strtoupper($type_quest)).' - '.$lang->def('_QUEST_'.strtoupper($type_quest))
			.'</option>', 'content');
	}
	cout('</select>'
		.$form->closeForm().'
	</div>', 'content');

	cout('</div>', 'content');
}

// XXX: addquest
function addquest(&$url) {
	checkPerm('view', false, 'storage');
	$lang =& DoceboLanguage::createInstance('test');

	$type_quest = Get::pReq('add_test_quest', DOTY_STRING, 'choice');

	require_once(_lms_.'/modules/question/question.php');

	quest_create($type_quest, 0, $url->getUrl());
}

function modquest(&$url) {
	$lang =& DoceboLanguage::createInstance('test');

	$id_quest = importVar('id_quest', true, 0);

	list($type_quest) = sql_fetch_row(sql_query("
	SELECT type_quest
	FROM ".$GLOBALS['prefix_lms']."_testquest
	WHERE idQuest = '".$id_quest."' AND idTest = 0"));

	require_once(_lms_.'/modules/question/question.php');

	quest_edit($type_quest, $id_quest, $url->getUrl());
}

function importquest(&$url) {

	require_once(_lib_.'/lib.form.php');

	$lang =& DoceboLanguage::createInstance('test');
	$form = new Form();

	require_once(_lms_.'/lib/lib.quest_bank.php');
	$qb_man = new QuestBankMan();
	$supported_format = array_flip($qb_man->supported_format());

	require_once(_lms_.'/lib/lib.questcategory.php');
	$quest_categories = array(
		0 => $lang->def('_NONE')
	);
	$cman = new Questcategory();
	$arr = $cman->getCategory();
	foreach ($arr as $id_category => $name_category) {
		$quest_categories[$id_category] = $name_category;
	}
	unset($arr);

	$title = array($url->getUrl() => $lang->def('_QUEST_BANK', 'menu_course'), $lang->def('_IMPORT'));
	cout(
		getTitleArea($title, 'quest_bank')
		.'<div class="std_block">'

		.$form->openForm('import_form', $url->getUrl('op=doimportquest'), false, false, 'multipart/form-data')

		.$form->openElementSpace()
		.$form->getFilefield($lang->def('_FILE'), 'import_file', 'import_file')
		.$form->getRadioSet($lang->def('_FILE_FORMAT'), 'file_format', 'file_format', $supported_format, 0)
		.$form->getTextfield($lang->def('_FILE_ENCODE'), 'file_encode', 'file_encode', 255, 'utf-8')
		.$form->getDropdown($lang->def('_QUEST_CATEGORY'), 'quest_category', 'quest_category', $quest_categories)
		.$form->closeElementSpace()

		.$form->openButtonSpace()
		.$form->getButton('undo','undo',$lang->def('_UNDO'))
		.$form->getButton('quest_search','quest_search',$lang->def('_IMPORT') )
		.$form->closeButtonSpace()
		.$form->closeForm()

		.'</div>', 'content');
}

function doimportquest(&$url) {

	require_once(_lms_.'/lib/lib.quest_bank.php');

	$lang_t =& DoceboLanguage::createInstance('test');

	$qb_man = new QuestBankMan();

	$file_format = Get::pReq('file_format', DOTY_INT, 0);
	$file_encode = Get::pReq('file_encode', DOTY_ALPHANUM, 'utf-8');
	$file_readed = file($_FILES['import_file']['tmp_name']);
	$quest_category = Get::req('quest_category', DOTY_INT, 0);

	addCss('style_yui_docebo');

	$title = array($url->getUrl() => $lang_t->def('_QUEST_BANK', 'menu_course'), $lang_t->def('_IMPORT'));
	cout( getTitleArea($title, 'quest_bank')
		.'<div class="std_block">'
		.getBackUi($url->getUrl(), $lang_t->def('_BACK')), 'content' );

	$import_result = $qb_man->import_quest($file_readed, $file_format, 0, $quest_category);

	cout('<table clasS="type-one" id="import_result">'
		.'<caption>'.$lang_t->def('_IMPORT').'</caption>', 'content');
	cout('<thead>', 'content');
	cout('<tr class="type-one-header">'
		.'<th>'.$lang_t->def('_QUEST_TYPE').'</th>'
		.'<th>'.$lang_t->def('_SUCCESS').'</th>'
		.'<th>'.$lang_t->def('_FAIL').'</th>'
		.'</tr>', 'content' );
	cout('</thead>', 'content');
	cout('<tbody>', 'content');
	foreach($import_result as $type_quest => $i_result) {

		cout('<tr>'
			.'<td>'.$lang_t->def('_QUEST_'.strtoupper($type_quest)).'</td>'
			.'<td>'.( isset($i_result['success']) ? $i_result['success'] : '' ).'</td>'
			.'<td>'.( isset($i_result['fail']) ? $i_result['fail'] : '' ).'</td>'
			.'</tr>', 'content' );
	}
	cout('</tbody>', 'content');
	cout('</table>', 'content');

	cout('</div>', 'content');
}

function exportquest(&$url) {

	require_once(_lms_.'/lib/lib.quest_bank.php');

	$lang =& DoceboLanguage::createInstance('test');

	$qb_man = new QuestBankMan();

	$file_format = Get::pReq('export_quest_select', DOTY_INT, 0);
	$quest_category 	= Get::pReq('quest_category', DOTY_INT);
	$quest_difficult 	= Get::pReq('quest_difficult', DOTY_INT);
	$quest_type 		= Get::pReq('quest_type', DOTY_ALPHANUM);

	$quest_selection 	= Get::req('selected_quest', DOTY_NUMLIST, '');

	$quest_selection = array_filter(preg_split('/,/', $quest_selection, -1, PREG_SPLIT_NO_EMPTY));

	if($file_format == -2)
	{
		$new_test_step = Get::pReq('new_test_step', DOTY_INT);
		$id_test = Get::pReq('test_sel', DOTY_INT, 0);
                
		if (Get::req('button_undo', DOTY_MIXED, false) !== false) {
			questbank($url);
			return;
		}

		if($new_test_step == 2)
		{
			$title = trim($_POST['title']);
			if ( $title == '' ) $title = $lang->def('_NOTITLE');

			if(is_array($quest_selection) && !empty($quest_selection))
			{
				if ($id_test != 0)
				{
					//Insert the question for the test
					$reQuest = sql_query("
					SELECT q.idQuest, q.type_quest, t.type_file, t.type_class
					FROM ".$GLOBALS['prefix_lms']."_testquest AS q JOIN ".$GLOBALS['prefix_lms']."_quest_type AS t
					WHERE q.idQuest IN (".implode(',', $quest_selection).") AND q.type_quest = t.type_quest");

					while( list($idQuest, $type_quest, $type_file, $type_class) = sql_fetch_row($reQuest) )
					{
						require_once(_lms_.'/modules/question/'.$type_file);
						$quest_obj = new $type_class( $idQuest );
						$new_id = $quest_obj->copy($id_test);
					}
				}

			}

			questbank($url);
		}
		else
		{
			if(is_array($quest_selection) && !empty($quest_selection))
			{
			require_once(_lib_.'/lib.form.php');
			
                        $form = new Form();
                        
                        require_once($GLOBALS['where_lms'].'/lib/lib.orgchart.php');
			$orgman = new OrganizationManagement($_SESSION['idCourse']);
			$test =& $orgman->getInfoWhereType('test', $_SESSION['idCourse']);

			cout(	getTitleArea( array( $lang->def('_QUEST_BANK', 'menu_course'), $lang->def('_EXPORT_QUESTIONS', 'storage') )    )
					.'<div class="std_block yui-skin-docebo yui-skin-sam">', 'content');
                        
                        cout(    '<label>'.$lang->def('_SELECTTEST', 'storage').'</label></br></br>'
                                , 'content');
                        
			cout(	$form->openForm('search_form', $url->getUrl(), false, 'POST')
					.$form->getHidden('new_test_step', 'new_test_step', '2')
					.$form->getHidden('export_quest', 'export_quest', $lang->def('_EXPORT'))
					.$form->getHidden('export_quest_select', 'export_quest_select', $file_format)
					.$form->getHidden('quest_category', 'quest_category', $quest_category)
					.$form->getHidden('quest_difficult', 'quest_difficult', $quest_difficult)
					.$form->getHidden('quest_type', 'quest_type', $quest_type)
					.$form->getHidden('selected_quest', 'selected_quest', $_POST['selected_quest'])
                                , 'content');
                        
                        
                        foreach ($test as $t) {
                            cout(        $form->openElementSpace()
                                        .$form->getInputRadio('test_sel_'.$t['id_resource'], 'test_sel', $t['id_resource'], false, '')
                                        .'<label for="test_sel_'.$t['id_resource'].'">'.$t["title"].'</label>'
                                        .$form->closeElementSpace()
                                    , 'content');
                        }

			cout(	         $form->openButtonSpace()
                                
					.$form->getButton('button_ins', 'button_ins', $lang->def('_SAVE'))
					.$form->getButton('button_undo', 'button_undo', $lang->def('_UNDO'))
					.$form->closeButtonSpace()
					.$form->closeForm()
                                , 'content');
                        
                        cout(	 '</div>', 'content');
			}
			else
			{
				$_SESSION['last_error'] = $lang->def('_EMPTY_SELECTION');
				questbank($url);
			}
		}
	} elseif($file_format == -1)
	{
		$new_test_step = Get::pReq('new_test_step', DOTY_INT);

		if (Get::req('button_undo', DOTY_MIXED, false) !== false) {
			questbank($url);
			return;
		}

		if($new_test_step == 2)
		{
			$title = trim($_POST['title']);
			if ( $title == '' ) $title = $lang->def('_NOTITLE');

			if(is_array($quest_selection) && !empty($quest_selection))
			{
				//Insert the test

				$ins_query = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_test
				( author, title, description )
					VALUES
				( '".(int)getLogUserId()."', '".$title."', '".$_POST['textof']."' )";
				//TODO:
				if( !sql_query($ins_query) )
				{
					$_SESSION['last_error'] = $lang->def('_OPERATION_FAILURE');
				}

				list($id_test) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));

				if ($id_test)
				{
					//Insert the question for the test

					$reQuest = sql_query("
					SELECT q.idQuest, q.type_quest, t.type_file, t.type_class
					FROM ".$GLOBALS['prefix_lms']."_testquest AS q JOIN ".$GLOBALS['prefix_lms']."_quest_type AS t
					WHERE q.idQuest IN (".implode(',', $quest_selection).") AND q.type_quest = t.type_quest");

					while( list($idQuest, $type_quest, $type_file, $type_class) = sql_fetch_row($reQuest) )
					{
						require_once(Docebo::inc(_folder_lms_.'/modules/question/'.$type_file));
						$quest_obj = new $type_class( $idQuest );
						$new_id = $quest_obj->copy($id_test);
					}

					//Adding the item to the tree

                    require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );

					$odb= new OrgDirDb($_SESSION['idCourse']);

					$odb->addItem(0, $title, 'test', $id_test, '0', '0', getLogUserId(), '1.0', '_DIFFICULT_MEDIUM', '', '', '', '', date('Y-m-d H:i:s'));
				}

			}

			questbank($url);
		}
		else
		{
			if(is_array($quest_selection) && !empty($quest_selection))
			{
			require_once(_lib_.'/lib.form.php');

			cout(	getTitleArea($lang->def('_QUEST_BANK', 'menu_course'))
					.'<div class="std_block yui-skin-docebo yui-skin-sam">', 'content');

			$form = new Form();

			cout(	$form->openForm('search_form', $url->getUrl(), false, 'POST')
					.$form->getHidden('new_test_step', 'new_test_step', '2')
					.$form->getHidden('export_quest', 'export_quest', $lang->def('_EXPORT'))
					.$form->getHidden('export_quest_select', 'export_quest_select', $file_format)
					.$form->getHidden('quest_category', 'quest_category', $quest_category)
					.$form->getHidden('quest_difficult', 'quest_difficult', $quest_difficult)
					.$form->getHidden('quest_type', 'quest_type', $quest_type)
					.$form->getHidden('selected_quest', 'selected_quest', $_POST['selected_quest'])
					.$form->openElementSpace()
					.$form->getTextfield($lang->def('_TITLE'), 'title', 'title', '255')
					.$form->getTextarea($lang->def('_DESCRIPTION'), 'textof', 'textof')
					.$form->closeElementSpace()
					.$form->openButtonSpace()
					.$form->getButton('button_ins', 'button_ins', $lang->def('_TEST_INSERT'))
					.$form->getButton('button_undo', 'button_undo', $lang->def('_UNDO'))
					.$form->closeButtonSpace()
					.$form->closeForm(), 'content');

			cout(	'</div>', 'content');
			}
			else
			{
				$_SESSION['last_error'] = $lang->def('_EMPTY_SELECTION');
				questbank($url);
			}
		}
	}
	else
	{
		$quests = $qb_man->getQuestFromId($quest_selection);
		$quest_export = $qb_man->export_quest($quests, $file_format);

		require_once(_lib_.'/lib.download.php');
		sendStrAsFile( $quest_export, 'export_'.date("Y-m-d").'.txt' );
	}
}

function deletequest(&$url) {

	require_once(_lms_.'/lib/lib.quest_bank.php');

	$lang =& DoceboLanguage::createInstance('test');

	$quest_selection 	= Get::req('selected_quest', DOTY_NUMLIST, '');
	$quest_selection = array_filter(preg_split('/,/', $quest_selection, -1, PREG_SPLIT_NO_EMPTY));

        if(is_array($quest_selection) && !empty($quest_selection))
        {
                //delete the question
                $reQuest = sql_query("
                SELECT q.idQuest, q.type_quest, t.type_file, t.type_class
                FROM ".$GLOBALS['prefix_lms']."_testquest AS q JOIN ".$GLOBALS['prefix_lms']."_quest_type AS t
                WHERE q.idQuest IN (".implode(',', $quest_selection).") AND q.type_quest = t.type_quest");

                while( list($idQuest, $type_quest, $type_file, $type_class) = sql_fetch_row($reQuest) )
                {
                        require_once(_lms_.'/modules/question/'.$type_file);
                        $quest_obj = new $type_class( $idQuest );
                        $new_id = $quest_obj->del();
                }

        }

        questbank($url);

}

function questbankDispatch($op) {

	require_once(_lib_.'/lib.urlmanager.php');
	$url =& UrlManager::getInstance();
	$url->setStdQuery('modname=quest_bank&op=main');

	if(isset($_POST['undo'])) $op = 'main';
	if(isset($_POST['import_quest'])) $op = 'importquest';
	if(isset($_POST['export_quest'])) $op = 'exportquest';
	if(isset($_POST['delete_quest'])) $op = 'deletequest';

	switch($op) {
		case "addquest" : {
			addquest($url);
		};break;
		case "modquest" : {
			modquest($url);
		};break;

		case "importquest" : {
			importquest($url);
		};break;
		case "doimportquest" : {
			doimportquest($url);
		};break;

		case "exportquest" : {
			exportquest($url);
		};break;

		case "deletequest" : {
			deletequest($url);
		};break;
		case "main" :
		default: {
			questbank($url);
		}
	}
}

?>
