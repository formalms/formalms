<?php defined ("IN_FORMA") or die('Direct access is forbidden.');

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

if (Docebo::user ()->isAnonymous ()) die("You can't access");

// XXX: save status in session
function saveTestStatus ($save_this)
{
    require_once ($GLOBALS[ 'where_framework' ] . '/lib/lib.sessionsave.php');
    $save = new Session_Save();
    $save_name = $save->getName ('test');

    $save->save ($save_name , $save_this);
    return $save_name;
}

function &loadTestStatus ($save_name)
{
    require_once ($GLOBALS[ 'where_framework' ] . '/lib/lib.sessionsave.php');
    $save = new Session_Save();

    return $save->load ($save_name);
}

// XXX: addtest
function addtest ($object_test)
{
    checkPerm ('view' , false , 'storage');

    $lang =& DoceboLanguage::createInstance ('test');
    if (! is_a ($object_test , 'Learning_Test')) {
        $_SESSION[ 'last_error' ] = $lang->def ('_OPERATION_FAILURE');
        Util::jump_to ('' . $object_test->back_url . '&amp;create_result=0');
    }

    require_once (_base_ . '/lib/lib.form.php');
    $url_encode = htmlentities (urlencode ($object_test->back_url));


    $GLOBALS[ 'page' ]->add (getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
        . '<div class="std_block">'
        . getBackUi (Util::str_replace_once ('&' , '&amp;' , $object_test->back_url) . '&amp;create_result=0' , $lang->def ('_BACK'))
        . Form::getFormHeader ($lang->def ('_TEST_ADD_FORM'))
        . Form::OpenForm ('addtest_form' , 'index.php?modname=test&amp;op=instest')

        . Form::openElementSpace ()
        . Form::getHidden ('back_url' , 'back_url' , htmlentities (urlencode ($object_test->back_url)))
        . Form::getHidden ('obj_type' , 'obj_type' , $object_test->getObjectType ())
        . Form::getTextfield ($lang->def ('_TITLE') , 'title' , 'title' , '255')
        . Form::getTextarea ($lang->def ('_DESCRIPTION') , 'textof' , 'textof')
        . Form::closeElementSpace ()

        . Form::openButtonSpace ()
        . Form::getButton ('button_ins' , 'button_ins' , $lang->def ('_TEST_INSERT'))
        . Form::closeButtonSpace ()

        . Form::closeForm ()
        . '</div>' , 'content');
}

// XXX: instest
function instest ()
{
    checkPerm ('view' , false , 'storage');

    require_once (Docebo::inc (_folder_lms_ . '/class.module/learning.test.php'));

    $lang =& DoceboLanguage::createInstance ('test');

    if (trim ($_POST[ 'title' ]) == '') $_POST[ 'title' ] = $lang->def ('_NOTITLE');

    $ins_query = "
	INSERT INTO " . $GLOBALS[ 'prefix_lms' ] . "_test
	( author, title, description, obj_type)
		VALUES 
	( '" . (int) getLogUserId () . "', '" . $_POST[ 'title' ] . "', '" . $_POST[ 'textof' ] . "', '" . $_POST[ 'obj_type' ] . "' )";


    if (! sql_query ($ins_query)) {

        $_SESSION[ 'last_error' ] = $lang->def ('_OPERATION_FAILURE');
        Util::jump_to ('' . urldecode ($_POST[ 'back_url' ]) . '&create_result=0');
    }

    list($id_test) = sql_fetch_row (sql_query ("SELECT LAST_INSERT_ID()"));

    $test = Learning_Test::load ($id_test);

    $event = new \appLms\Events\Lms\TestCreateEvent($test , $lang);

    \appCore\Events\DispatcherManager::dispatch (\appLms\Events\Lms\TestCreateEvent::EVENT_NAME , $event);

    if ($id_test > 0) Util::jump_to ('' . urldecode ($_POST[ 'back_url' ]) . '&id_lo=' . $id_test . '&create_result=1');
    else Util::jump_to ('' . urldecode ($_POST[ 'back_url' ]) . '&create_result=0');
}

// XXX: modtest
function modtest ()
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');

    require_once (_base_ . '/lib/lib.form.php');
    $id_test = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $url_encode = htmlentities (urlencode ($back_url));

    list($test_title , $textof) = sql_fetch_row (sql_query ("
	SELECT title, description
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_test
	WHERE idTest = '" . $id_test . "'"));

    $GLOBALS[ 'page' ]->add (
        getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
        . '<div class="std_block">'
        . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $id_test . '&amp;back_url=' . $url_encode , $lang->def ('_BACK'))
        . Form::OpenForm ('addtest_form' , 'index.php?modname=test&amp;op=uptest')

        . Form::openElementSpace ()
        . Form::getHidden ('idTest' , 'idTest' , $id_test)
        . Form::getHidden ('back_url' , 'back_url' , $url_encode)
        . Form::getTextfield ($lang->def ('_TITLE') , 'title' , 'title' , '255' , $test_title)
        . Form::getTextarea ($lang->def ('_DESCRIPTION') , 'textof' , 'textof' , $textof)
        . Form::closeElementSpace ()

        . Form::openButtonSpace ()
        . Form::getButton ('button_ins' , 'button_ins' , $lang->def ('_SAVE'))
        . Form::closeButtonSpace ()

        . Form::closeForm ()
        . '</div>' , 'content');
}

// XXX: uptest
function uptest (Learning_Test $obj_test = null)
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');

    $back_url = urldecode (importVar ('back_url'));
    $url_encode = htmlentities (urlencode ($back_url));

    if (trim ($_POST[ 'title' ]) == '') $_POST[ 'title' ] = $lang->def ('_NOTITLE');

    if (isset($obj_test)) {
        $id_test = $obj_test->getId ();

        $mod_query = "
			UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_test
			SET title = '" . $_POST[ 'title' ] . "',
				description = '" . $_POST[ 'textof' ] . "'
			WHERE idTest = '" . $id_test . "'";

        if (! sql_query ($mod_query)) {

            errorCommunication ($lang->def ('_OPERATION_FAILURE')
                . getBackUi ('index.php?modname=test&amp;op=modtest&amp;idTest=' . $id_test . '&amp;back_url=' . $url_encode));
            return;
        }
        require_once ($GLOBALS[ 'where_lms' ] . '/class.module/track.object.php');
        Track_Object::updateObjectTitle ($id_test , $obj_test->getObjectType () , $_POST[ 'title' ]);
    }

    $test = Learning_Test::load($id_test);

    $event = new \appLms\Events\Lms\TestUpdateEvent($test, $lang);

    \appCore\Events\DispatcherManager::dispatch(\appLms\Events\Lms\TestUpdateEvent::EVENT_NAME, $event);


    Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $id_test . '&back_url=' . $url_encode);
}

// XXX: modtestgui
function modtestgui ($object_test)
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');

    YuiLib::load ('table');
    Util::get_js (_folder_lms_ . '/modules/quest_bank/ajax.quest_bank.js' , true , true);

    // ----------------------------------------------------------------------------------------

    if (! is_a ($object_test , 'Learning_Test')) {
        $_SESSION[ 'last_error' ] = $lang->def ('_OPERATION_FAILURE');
        Util::jump_to ('' . $object_test->back_url . '&amp;create_result=0');
    }

    require_once (_base_ . '/lib/lib.table.php');
    require_once (_base_ . '/lib/lib.form.php');
    $url_encode = htmlentities (urlencode ($object_test->back_url));

    list($test_title) = sql_fetch_row (sql_query ("
	SELECT title 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_test
	WHERE idTest = '" . $object_test->getId () . "'"));

    $re_quest = sql_query ("
	SELECT idQuest, type_quest, title_quest, sequence, page 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '" . $object_test->getId () . "'
	ORDER BY sequence");

    $num_quest = sql_num_rows ($re_quest);
    list($num_page) = sql_fetch_row (sql_query ("
	SELECT MAX(page) 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '" . $object_test->getId () . "'"));
    $num_page = (int) $num_page;

    $GLOBALS[ 'page' ]->add (
        getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
        . '<div class="std_block yui-skin-docebo yui-skin-sam">'
        . getBackUi (Util::str_replace_once ('&' , '&amp;' , $object_test->back_url) , $lang->def ('_BACK')) , 'content');
    if (isset($_GET[ 'mod_operation' ])) {
        if ($_GET[ 'mod_operation' ]) $GLOBALS[ 'page' ]->add (getResultUi ($lang->def ('_OPERATION_SUCCESSFUL')) , 'content');
        else $GLOBALS[ 'page' ]->add (getResultUi ($lang->def ('_QUEST_ERR_MODIFY')) , 'content');
    }
    //other areas

    $GLOBALS[ 'page' ]->add ('<b>' . $lang->def ('_TITLE') . ' :</b> '
        . '<a href="index.php?modname=test&amp;op=modtest&amp;idTest=' . $object_test->getId () . '&amp;back_url=' . $url_encode . '"'
        . ' class="ico-wt-sprite subs_mod" title="' . $lang->def ('_MOD_TITLE' , 'standard') . '"><span>'
        . $test_title . '</span></a><br /><br />'
        , 'content');

    $event = new \appLms\Events\Lms\TestConfigurationTabsRenderEvent($object_test , $url_encode , $lang);

    $event->addTab ('_TEST_MODALITY' , '<li>' . '<a href="index.php?modname=test&amp;op=defmodality&amp;idTest='
        . $object_test->getId () . '&amp;back_url=' . $url_encode . '" title="' . $lang->def ('_TEST_MODALITY') . '">'
        . $lang->def ('_TEST_MODALITY') . '</a>' . '</li>');
    $event->addTab ('_TEST_COMPILE_TIME' , '<li>' . '<a href="index.php?modname=test&amp;op=deftime&amp;idTest='
        . $object_test->getId () . '&amp;back_url=' . $url_encode . '" title="' . $lang->def ('_TEST_COMPILE_TIME') . '">'
        . $lang->def ('_TEST_COMPILE_TIME') . '</a>' . '</li>');
    $event->addTab ('_TEST_POINT_ASSIGNEMENT' , '<li>' . '<a href="index.php?modname=test&amp;op=defpoint&amp;idTest='
        . $object_test->getId () . '&amp;back_url=' . $url_encode . '" title="' . $lang->def ('_TEST_POINT_ASSIGNEMENT') . '">'
        . $lang->def ('_TEST_POINT_ASSIGNEMENT') . '</a>' . '</li>');
    $event->addTab ('_FEEDBACK_MANAGEMENT' , '<li>' . '<a href="index.php?modname=test&amp;op=feedbackman&amp;idTest='
        . $object_test->getId () . '&amp;back_url=' . $url_encode . '" title="' . $lang->def ('_FEEDBACK_MANAGEMENT') . '">'
        . $lang->def ('_FEEDBACK_MANAGEMENT') . '</a>' . '</li>');

    /** REMOVED COURSE REPORT MANAGEMENT TAB */
    /*$event->addTab ('_COURSEREPORT_MANAGEMENT' , '<li>' . '<a href="index.php?modname=test&amp;op=coursereportman&amp;idTest='
        . $object_test->getId () . '&amp;back_url=' . $url_encode . '" title="' . $lang->def ('_COURSEREPORT_MANAGEMENT') . '">'
        . $lang->def ('_COURSEREPORT_MANAGEMENT') . '</a>' . '</li>');

    */
    \appCore\Events\DispatcherManager::dispatch (\appLms\Events\Lms\TestConfigurationTabsRenderEvent::EVENT_NAME , $event);

    $GLOBALS[ 'page' ]->add ('<ul class="link_list_inline">' , 'content');
    foreach ($event->getTabs () as $tab) {
        $GLOBALS[ 'page' ]->add ($tab , 'content');
    }
    $GLOBALS[ 'page' ]->add ('</ul>' , 'content');

    $caption = str_replace ('%tot_page%' , $num_page , str_replace ('%tot_element%' , $num_quest , $lang->def ('_TEST_CAPTION')));

    $tab = new Table(0 , $caption , $lang->def ('_TEST_SUMMARY'));

    $tab->setColsStyle (array ( 'image' , 'image' , '' , 'image' , 'image' , 'image' , 'image' , 'image' ));

    $i = 0;
    $correct_sequence = 1;
    $seq_error_detected = false;

    $quest_num = 1;
    $title_num = 1;
    $last_type = '';
    $uri_back = '&amp;back_url=' . $url_encode;
    $first = true;

    // Customfields initialize
    require_once (_adm_ . '/lib/lib.customfield.php');
    $fman = new CustomFieldList();
    $fman->setFieldArea ("LO_TEST");

    while (list($id_quest , $type , $title , $sequence , $page) = sql_fetch_row ($re_quest)) {

        // Customfields Get
        $fields_mask = $fman->playFieldsFlat ($id_quest);

        if ($first) {
            $arrHead = array ();
            array_push ($arrHead , $lang->def ('_QUEST') , $lang->def ('_TYPE') , $lang->def ('_QUESTION'));
            // Customfields head
            foreach ($fields_mask as $field) {
                array_push ($arrHead , $field[ 'name' ]);
            }
            array_push ($arrHead , $lang->def ('_TEST_QUEST_ORDER') ,
                '<img src="' . getPathImage () . 'standard/down.png" alt="' . $lang->def ('_DOWN') . '" longdesc="' . $lang->def ('_MOVE_DOWN') . '" />' ,
                '<img src="' . getPathImage () . 'standard/up.png" alt="' . $lang->def ('_UP') . '" longdesc="' . $lang->def ('_MOVE_UP') . '" />' ,
                '<img src="' . getPathImage () . 'standard/edit.png" alt="' . $lang->def ('_MOD') . '" />' ,
                '<img src="' . getPathImage () . 'standard/delete.png" alt="' . $lang->def ('_DEL') . '" />');

            $tab->addHead ($arrHead);
            $first = false;
        }

        $last_type = $type;

        $content = array ();
        array_push ($content , ((($type != 'break_page') && ($type != 'title')) ? '<span class="text_bold">' . ($quest_num++) . '</span>' : '') ,
            $lang->def ('_QUEST_ACRN_' . strtoupper ($type)) ,
            '<div style="width:300px;">' . $title . '</div>');

        // Customfields content
        foreach ($fields_mask as $field) {
            array_push ($content , $field[ 'value' ]);
        }

        array_push ($content , $sequence ,
            (($i != ($num_quest - 1)) ?
                '<a href="index.php?modname=test&amp;op=movedown&amp;idQuest=' . $id_quest . $uri_back . '" title="' . $lang->def ('_MOVE_DOWN') . '">'
                . '<img src="' . getPathImage () . 'standard/down.png" alt="' . $lang->def ('_DOWN') . ' : ' . $lang->def ('_ROW') . ' ' . ($i + 1) . '" /></a>' : '') ,
            (($i != 0) ?
                '<a href="index.php?modname=test&amp;op=moveup&amp;idQuest=' . $id_quest . $uri_back . '" title="' . $lang->def ('_MOVE_UP') . '">'
                . '<img src="' . getPathImage () . 'standard/up.png" alt="' . $lang->def ('_UP') . ' : ' . $lang->def ('_ROW') . ' ' . ($i + 1) . '" /></a>' : '') ,

            ($type != 'break_page' ? '<a href="index.php?modname=test&amp;op=modquest&amp;idQuest=' . $id_quest . $uri_back . '" title="' . $lang->def ('_MOD') . '">'
                . '<img src="' . getPathImage () . 'standard/edit.png" alt="' . $lang->def ('_MOD') . ' : ' . $lang->def ('_ROW') . ' ' . ($i + 1) . '" /></a>' : '') ,
            '<a href="index.php?modname=test&amp;op=delquest&amp;idQuest=' . $id_quest . $uri_back . '" title="' . $lang->def ('_DEL') . '">'
            . '<img src="' . getPathImage () . 'standard/delete.png" alt="' . $lang->def ('_DEL') . ' : ' . $lang->def ('_ROW') . ' ' . ($i + 1) . '" /></a>');

        $tab->addBody ($content);
        if ($sequence != $correct_sequence) $seq_error_detected = true;
        $correct_sequence++;
        ++$i;
    }

    //------------------------------------------------------------------
    $move_quest = "";
    if ($num_quest > 1) {
        $move_quest = '<form class="align_right" method="post" action="index.php?modname=test&amp;op=movequest">'
            . '<div>'
            . '<input type="hidden" id="authentic_request_test" name="authentic_request" value="' . Util::getSignature () . '" />'
            . '<input type="hidden" name="back_url" value="' . $url_encode . '" />'
            . '<input type="hidden" name="idTest" value="' . $object_test->getId () . '" />';
        $move_quest .= '<label class="text_bold" for="source_quest">' . $lang->def ('_MOVE') . '</label>&nbsp;'
            . '<select id="source_quest" name="source_quest">';
        for ($opt = 1 ; $opt <= $i ; $opt++) {
            $move_quest .= '<option value="' . $opt . '"'
                . ($opt == 1 ? ' selected="selected"' : '') . '>' . $lang->def ('_TEST_MOVEQUEST') . ' ' . $opt . '</option>';
        }
        $move_quest .= '</select>';
        $move_quest .= '<label class="text_bold" for="dest_quest"> ' . $lang->def ('_TO') . '</label>&nbsp;'
            . '<select id="dest_quest" name="dest_quest">'
            . '<option value="1" selected="selected">' . $lang->def ('_TEST_FIRST_QUEST') . '</option>';
        for ($opt = 1 ; $opt < $i ; $opt++) {
            $move_quest .= '<option value="' . ($opt + 1) . '">' . $lang->def ('_TEST_AFTER_QUEST') . ' ' . $opt . '</option>';
        }
        $move_quest .= '<option value="' . ($i + 1) . '">' . $lang->def ('_TEST_LAST_QUEST') . '</option>';
        $move_quest .= '</select>';
        $move_quest .= '&nbsp;<input class="button_nowh" type="submit" id="move_quest" name="move_quest" value="' . $lang->def ('_MOVE') . '" />'
            . '</div>'
            . '</form>';
        //$tab->addActionAdd( $move_quest );
    }
    //------------------------------------------------------------------
    /*	$re_type = sql_query("
	SELECT type_quest
	FROM ".$GLOBALS['prefix_lms']."_quest_type
	ORDER BY sequence");
	$add_quest = '<form method="post" action="index.php?modname=test&amp;op=addquest">'
		.'<div>'
		.'<input type="hidden" id="authentic_request_test" name="authentic_request" value="'.Util::getSignature().'" />'
		.'<input type="hidden" name="back_url" value="'.$url_encode.'" />'
		.'<input type="hidden" name="idTest" value="'.$object_test->getId().'" />';
	$add_quest .= '<label class="text_bold" for="add_test_quest">'.$lang->def('_TEST_ADDQUEST').'</label>&nbsp;'
		.'<select id="add_test_quest" name="add_test_quest">';
	while(list($type_quest) = sql_fetch_row($re_type)) {
		$add_quest .= '<option value="'.$type_quest.'"'
		.( $last_type == $type_quest ? ' selected="selected"' : '' ).'>'
		.$lang->def('_QUEST_ACRN_'.strtoupper($type_quest)).' - '.$lang->def('_QUEST_'.strtoupper($type_quest)).'</option>';
	}
	$add_quest .= '</select>';
	$add_quest .= '&nbsp;<input class="button_nowh" type="submit" name="add_quest" value="'.$lang->def('_ADD').'" />'
		.'</div>'
		.'</form>';*/

    $re_type = sql_query ("
	SELECT type_quest 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_quest_type
	ORDER BY sequence");

    $add_quest = '<form method="post" action="index.php?modname=test&amp;op=addquest">'
        . '<input type="hidden" id="authentic_request_test" name="authentic_request" value="' . Util::getSignature () . '" />'
        . '<input type="hidden" name="back_url" value="' . $url_encode . '" />'
        . '<input type="hidden" name="idTest" value="' . $object_test->getId () . '" />'

        . '<input type="submit" id="add_quest" name="add_quest" value="' . $lang->def ('_TEST_ADDQUEST') . '">
		<select id="add_test_quest" name="add_test_quest">';
    while (list($type_quest) = sql_fetch_row ($re_type)) {

        $add_quest .= '<option value="' . $type_quest . '">'
            . $lang->def ('_QUEST_ACRN_' . strtoupper ($type_quest)) . ' - ' . $lang->def ('_QUEST_' . strtoupper ($type_quest))
            . '</option>';
    }
    $add_quest .= '</select>'
        . '</form>';

    //------------------------------------------------------------------
    //$tab->addActionAdd( $add_quest, '' );
    $GLOBALS[ 'page' ]->add (
        $tab->getTable ()
        . '<div class="table-container-below">' . $move_quest . '</div>'
        . $add_quest
        . getBackUi (Util::str_replace_once ('&' , '&amp;' , $object_test->back_url) , $lang->def ('_BACK')) , 'content');


    /*
	$GLOBALS['page']->add(
		Form::openForm('add_question', 'index.php?modname=test&amp;op=importquest', false, false, 'multipart/form-data')

		.Form::openElementSpace()
		.Form::getOpenFieldset($lang->def('_IMPORT_FROM_XML'))
		.Form::getHidden('back_url', 'back_url', $url_encode)
		.Form::getHidden('idTest', 'idTest', $object_test->getId())
		.Form::getFilefield($lang->def('_FILE'), 'xml_file', 'xml_file')
		.Form::getCloseFieldset()
		.Form::closeElementSpace()

		.Form::openButtonSpace()
		.form::getButton('import', 'import', $lang->def('_IMPORT'))
		.Form::closeButtonSpace()

		.Form::closeForm()
	, 'content');
	*/


    if ($seq_error_detected) {

        $GLOBALS[ 'page' ]->add (
            ' <a href="index.php?modname=test&amp;op=fixsequence&amp;idTest=' . $object_test->getId () . $uri_back . '" title="' . $lang->def ('_FIX_SEQUENCE') . '">'
            . $lang->def ('_FIX_SEQUENCE') . '</a>'
            , 'content');
    }

    require_once ($GLOBALS[ 'where_lms' ] . '/lib/lib.quest_bank.php');
    $qb_man = new QuestBankMan();
    $supported_format = $qb_man->supported_format ();

    $GLOBALS[ 'page' ]->add (
        '<form method="post" action="index.php?modname=test&amp;op=modtestgui">'
        . '<input type="hidden" id="authentic_request_test" name="authentic_request" value="' . Util::getSignature () . '" />'
        . '<input type="hidden" name="back_url" value="' . $url_encode . '" />'
        . '<input type="hidden" name="idTest" value="' . $object_test->getId () . '" />'

        . '<div class="align_right">
			<input type="submit" id="export_quest" name="export_quest" value="' . $lang->def ('_EXPORT') . '">
			<select id="export_quest_select" name="export_quest_select">' , 'content');
    foreach ($supported_format as $id_exp => $def) {

        cout ('<option value="' . $id_exp . '">' . $def . '</option>' , 'content');
    }
    cout ('<option value="5">' . Lang::t ('_QUEST_BANK' , 'menu_course') . '</option>' , 'content');
    cout ('</select>'

        //.'<input type="submit" id="import_quest" name="import_quest" value="'.$lang->def('_IMPORT').'">'

        . Form::getButton ('import_quest' , 'import_quest' , $lang->def ('_IMPORT'))
        . '</div>'
        . '</form>'
        , 'content');

    $GLOBALS[ 'page' ]->add ('
	<script type="text/javascript">
	YAHOO.util.Event.addListener(window, "load", function() {
		var oSplitExport = new YAHOO.widget.Button("export_quest", { type: "menu", menu: "export_quest_select" });
		//var oPushImport = new YAHOO.widget.Button("import_quest");
		var oMoveQuest = new YAHOO.widget.Button("move_quest");
		var oSplitAddQuest = new YAHOO.widget.Button("add_quest", { type: "menu", menu: "add_test_quest" });
	});
	</script>' , 'content');

    $GLOBALS[ 'page' ]->add ('</div>' , 'content');
    //fixPageSequence($object_test->getId());
}

// XXX: movequestion
function movequestion ($direction)
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');


    $idQuest = importVar ('idQuest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $back_coded = htmlentities (urlencode ($back_url));

    list($seq , $idTest) = sql_fetch_row (sql_query ("
	SELECT sequence, idTest 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idQuest = '$idQuest'"));

    if ($direction == 'up') {
        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET sequence = '$seq' 
		WHERE idTest = '$idTest' AND sequence = '" . ($seq - 1) . "'");
        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET sequence = sequence - 1 
		WHERE idTest = '$idTest' AND idQuest = '$idQuest'");

    }
    if ($direction == 'down') {
        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET sequence = '$seq' 
		WHERE idTest = '$idTest' AND sequence = '" . ($seq + 1) . "'");
        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET sequence = '" . ($seq + 1) . "' 
		WHERE idTest = '$idTest' AND idQuest = '$idQuest'");
    }
    fixPageSequence ($idTest);
    Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $back_coded);
}

// XXX: movequestion from to
function movequest ()
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');


    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $back_coded = htmlentities (urlencode ($back_url));
    $source_seq = importVar ('source_quest' , true , 0);
    $dest_seq = importVar ('dest_quest' , true , 0);

    list($idQuest) = sql_fetch_row (sql_query ("
	SELECT idQuest 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '$idTest' AND sequence = '$source_seq'"));

    if ($source_seq < $dest_seq) {

        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET sequence = sequence - 1 
		WHERE idTest = '$idTest' AND sequence > '" . ($source_seq) . "'  AND sequence < '" . ($dest_seq) . "'");
        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET sequence = '" . ($dest_seq - 1) . "' 
		WHERE idQuest = '$idQuest'");
    } else {

        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET sequence = sequence + 1 
		WHERE idTest = '$idTest' AND sequence >= '" . ($dest_seq) . "'  AND sequence < '" . ($source_seq) . "'");

        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET sequence = '$dest_seq' 
		WHERE idQuest = '$idQuest'");
    }
    fixPageSequence ($idTest);
    Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $back_coded);
}

function fixQuestSequence ()
{
    checkPerm ('view' , false , 'storage');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $back_coded = htmlentities (urlencode ($back_url));

    $re_quest = sql_query ("
	SELECT idQuest, sequence 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '$idTest' 
	ORDER BY page, sequence");
    $seq = 1;
    while (list($id_quest) = sql_fetch_row ($re_quest)) {

        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET sequence = '$seq' 
		WHERE idQuest = '$id_quest'");
        $seq++;
    }
    fixPageSequence ($idTest);
    Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $back_coded);
}

// XXX: fixPageSequence
function fixPageSequence ($id_test)
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');

    list($tot_quest) = sql_fetch_row (sql_query ("
	SELECT COUNT(*) 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '" . $id_test . "'"));

    $re_break_page = sql_query ("
	SELECT sequence 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '" . $id_test . "' AND type_quest = 'break_page'
	ORDER BY sequence");

    $page_num = 1;
    //first page
    $ini_seq = 0;
    while (list($break_sequence) = sql_fetch_row ($re_break_page)) {

        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET page = '" . (int) $page_num . "'
		WHERE idTest = '" . (int) $id_test . "' AND
			sequence > '" . (int) $ini_seq . "' AND sequence <= '" . (int) $break_sequence . "'");
        $ini_seq = $break_sequence;
        ++$page_num;
    }
    sql_query ("
	UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	SET page = '" . (int) $page_num . "'
	WHERE idTest = '" . (int) $id_test . "' AND
		sequence > '" . (int) $ini_seq . "' AND sequence <= '" . (int) $tot_quest . "'");
}

function &istanceQuest ($type_of_quest , $id)
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');


    $re_quest = sql_query ("
	SELECT type_file, type_class 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_quest_type
	WHERE type_quest = '" . $type_of_quest . "'");
    if (! sql_num_rows ($re_quest)) return;
    list($type_file , $type_class) = sql_fetch_row ($re_quest);

    require_once (Docebo::inc (_folder_lms_ . '/modules/question/' . $type_file));
    $quest_obj = eval("return new $type_class ( $id );");

    return $quest_obj;
}

// XXX: addquest
function addquest ()
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');


    $idTest = importVar ('idTest' , true , 0);

    if ($idTest) {
        $max_score = _getTestMaxScore ($idTest);
        if ($max_score !== false) {
            $query = "UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_test SET score_max=" . (int) $max_score . " WHERE idTest=" . (int) $idTest;
            $res = sql_query ($query);
        }
    }
    if (isset($_POST[ 'add_test_quest' ])) {
        //first enter
        $type_quest = importVar ('add_test_quest');
        $var_to_safe = array (
            'idQuest' => 0 ,
            'type_quest' => $type_quest ,
            'idTest' => $idTest ,
            'back_url' => urldecode (importVar ('back_url'))
        );
        $var_save = saveTestStatus ($var_to_safe);
    } else {
        //other enter
        $var_save = importVar ('test_saved');
        $var_loaded = loadTestStatus ($var_save);

        $idTest = $var_loaded[ 'idTest' ];
        $type_quest = $var_loaded[ 'type_quest' ];
    }

    require_once ($GLOBALS[ 'where_lms' ] . '/modules/question/question.php');

    quest_create ($type_quest , $idTest , 'index.php?modname=test&op=modtestgui&test_saved=' . $var_save);
}

// XXX: modquest
function modquest ()
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');


    $idQuest = importVar ('idQuest' , true , 0);

    list($idTest , $type_quest) = sql_fetch_row (sql_query ("
	SELECT idTest, type_quest 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idQuest = '" . $idQuest . "'"));

    if ($idTest) {
        $max_score = _getTestMaxScore ($idTest);
        if ($max_score !== false) {
            $query = "UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_test SET score_max=" . (int) $max_score . " WHERE idTest=" . (int) $idTest;
            $res = sql_query ($query);
        }
    }

    if (! isset($_POST[ 'back_url' ])) {
        //first enter
        $var_to_safe = array (
            'idQuest' => $idQuest ,
            'type_quest' => $type_quest ,
            'idTest' => $idTest ,
            'back_url' => urldecode (importVar ('back_url'))
        );
        $var_save = saveTestStatus ($var_to_safe);
    } else {
        //other enter
        $var_save = importVar ('test_saved');
        $var_loaded = loadTestStatus ($var_save);

        $idQuest = $var_loaded[ 'idQuest' ];
        $type_quest = $var_loaded[ 'type_quest' ];
    }

    require_once ($GLOBALS[ 'where_lms' ] . '/modules/question/question.php');

    quest_edit ($type_quest , $idQuest , 'index.php?modname=test&op=modtestgui&test_saved=' . $var_save);
}

// XXX: deletequest
function delquest ()
{
    checkPerm ('view' , false , 'storage');

    $lang =& DoceboLanguage::createInstance ('test');

    $idQuest = importVar ('idQuest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));

    list($idTest , $title_quest , $type_quest , $seq) = sql_fetch_row (sql_query ("
	SELECT idTest, title_quest, type_quest, sequence 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idQuest = '" . $idQuest . "'"));

    if (isset($_GET[ 'confirm' ])) {

        $quest_obj = istanceQuest ($type_quest , $idQuest);
        if (! $quest_obj->del ()) {

            errorCommunication ($lang->def ('_OPERATION_FAILURE') . 'index.php?modname=test&amp;op=delquest&amp;idTest=' . $idTest . '&amp;back_url='
                . $url_coded , $lang->def ("_BACK"));
            return;
        }
        sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
		SET sequence = sequence -1 
		WHERE sequence > '$seq'");
        fixPageSequence ($idTest);

        $max_score = _getTestMaxScore ($idTest);
        if ($max_score !== false) {
            $query = "UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_test SET score_max=" . (int) $max_score . " WHERE idTest=" . (int) $idTest;
            $res = sql_query ($query);
        }

        Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $url_coded);
    } else {
        $GLOBALS[ 'page' ]->add (
            '<div class="std_block">'
            . getDeleteUi ($lang->def ('_AREYOUSURE') ,
                '<span class="text_bold">' . $lang->def ('_TYPE') . ' : </span>'
                . $lang->def ('_QUEST_ACRN_' . strtoupper ($type_quest)) . ' - ' . $lang->def ('_QUEST_' . strtoupper ($type_quest)) . '<br />'
                . '<span class="text_bold">' . $lang->def ('_QUESTION') . ' : </span>' . $title_quest ,

                true ,
                'index.php?modname=test&amp;op=delquest&amp;idQuest=' . $idQuest . '&amp;back_url=' . $url_coded . '&amp;confirm=1' ,
                'index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded
            )
            . '</div>' , 'content');
    }
}

// XXX: defmodality
function defmodality ()
{

    checkPerm ('view' , false , 'storage');

    $lang =& DoceboLanguage::createInstance ('test');

    require_once (_base_ . '/lib/lib.form.php');
    require_once (_base_ . '/lib/lib.json.php');

    $idTest = importVar ('idTest' , true , 0);
    $db = DbConn::getInstance ();
    $res = $db->query ("SELECT obj_type FROM %lms_test WHERE idTest = '" . (int) $idTest . "'");
    $test_type = $db->fetch_row ($res);
    $object_test = createLO ($test_type[ 0 ] , $idTest);

    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));

    list($title , $description , $display_type , $order_type , $shuffle_answer , $question_random_number ,
        $save_keep , $mod_doanswer , $can_travel ,
        $show_score , $show_score_cat , $show_doanswer , $show_solution ,
        $max_attempt , $hide_info ,
        $order_info , $cf_info , $use_suspension , $suspension_num_attempts , $suspension_num_hours , $suspension_prerequisites , $mandatory_answer , $retain_answers_history
        ) = sql_fetch_row (sql_query ("
	SELECT title, description, display_type, order_type, shuffle_answer, question_random_number, 
		save_keep, mod_doanswer, can_travel, 
		show_score, show_score_cat, show_doanswer, show_solution, 
		max_attempt, hide_info,
		order_info, cf_info, use_suspension, suspension_num_attempts, suspension_num_hours, suspension_prerequisites, mandatory_answer, retain_answers_history
	FROM %lms_test
	WHERE idTest = '" . $idTest . "'"));

    list($tot_quest) = sql_fetch_row (sql_query ("
	SELECT COUNT(*) 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '" . (int) $idTest . "' AND type_quest <> 'title' AND type_quest <> 'break_page'"));

    $re_quest = sql_query ("
	SELECT idQuest
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '" . (int) $idTest . "' AND type_quest <> 'title' AND type_quest <> 'break_page'");
    while (list($idQuest) = sql_fetch_row ($re_quest)) {
        $arr_id_quest[] = $idQuest;
    }

    $GLOBALS[ 'page' ]->add (
        getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
        . '<div class="std_block">'
        . '<div class="title_big">' . $lang->def ('_TEST_MODALITY') . '</div>'
        . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))

        . Form::openForm ('defmodality' , 'index.php?modname=test&amp;op=updatemodality')


        . Form::getOpenFieldset ($lang->def ('_TEST_MM_ONE'))

        . Form::getHidden ('idTest' , 'idTest' , $idTest)
        . Form::getHidden ('back_url' , 'back_url' , $url_coded)
        . Form::getRadio ($lang->def ('_TEST_MM1_GROUPING') , 'display_type_page' , 'display_type' , 0 , ! $display_type)
        . Form::getRadio ($lang->def ('_TEST_MM1_ONEFORPAGE') , 'display_type_one' , 'display_type' , 1 , $display_type)
        . '<br />' , 'content');
    //-order-----------------------------------------------------

    $cat_info = array ();
    if ($order_info != '') {
        require_once (_base_ . '/lib/lib.json.php');
        $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $arr = $json->decode ($order_info);
        if (is_array ($arr)) {
            foreach ($arr as $value) {
                $cat_info[ $value[ 'id_category' ] ] = $value[ 'selected' ];
            }
        }
    }

    $has_categories = false;
    $categories = array ();
    $query = "SELECT tq.idCategory, qc.name, COUNT(tq.idcategory) FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest AS tq LEFT JOIN " . $GLOBALS[ 'prefix_lms' ] . "_quest_category AS qc "
        . " ON (tq.idCategory = qc.idCategory) WHERE idTest='" . (int) $idTest . "' GROUP BY tq.idCategory";
    $res = sql_query ($query);
    if (sql_num_rows ($res) > 0) {
        $has_categories = true;
        while (list($id_cat , $name_cat , $num_quest) = sql_fetch_row ($res)) {
            if ($id_cat == 0) $name_cat = $lang->def ('_NO_CATEGORY');
            if (isset($cat_info[ $id_cat ])) $selected = $cat_info[ $id_cat ]; else $selected = '0';
            $categories[ $id_cat ] = array ( 'name' => $name_cat , 'total' => $num_quest , 'selected' => (int) $selected );
        }
    }

    $script = "";
    if ($has_categories) {
        $GLOBALS[ 'page' ]->add ('<script type="text/javascript">
				function toggleCategoryList(o) {
					var ul = document.getElementById(\'category_list\'), radio = document.getElementById(\'order_type_random_category\');
					if (ul && radio) {
						if (radio.checked) ul.style.display = "block"; else ul.style.display = "none";
					}
				}
			</script>' , 'page_head');
        $script = 'onclick="toggleCategoryList();"';
    }

    $GLOBALS[ 'page' ]->add (
        '<div class="text_bold">' . $lang->def ('_ORDER_BY') . '</div>'
        . Form::getRadio ($lang->def ('_TEST_MM1_SEQUENCE') , 'order_type_seq' , 'order_type' , 0 , $order_type == 0)
        . Form::getRadio ($lang->def ('_TEST_MM1_RANDOM') , 'order_type_random' , 'order_type' , 1 , $order_type == 1)
        , 'content');
    //-random question
    $input_field = Form::getInputTextfield ('textfield_nowh' , 'question_random_number' , 'question_random_number' , $question_random_number , 4 , '' , '');
    $label = str_replace ('[random_quest]' , '</label>' . $input_field . '<label for="question_random_number">' , $lang->def ('_TEST_MM1_QUESTION_RANDOM_NUMBER'));

    $GLOBALS[ 'page' ]->add (
        Form::openFormLine ()
        . Form::getInputRadio ('order_type_random_quest' , 'order_type' , 2 , $order_type == 2 , '')
        . '<label for="order_type_random_quest">' . $lang->def ('_ORDER_TYPE_RANDOM') . '</label> - '
        . '<label for="question_random_number">'
        . str_replace ('[tot_quest]' , $tot_quest , $label)
        . '</label>'
        . Form::closeFormLine () , 'content');

    //------------------------------------------------------------------------------
    $label = '';

    $category_selector = '<ul id="category_list" style="display:' . ($order_type == 3 ? "block" : "none") . '">';
    foreach ($categories as $key => $value) {

        $input_field = Form::getInputTextfield ('textfield_nowh' , 'question_random_category_' . $key , 'question_random_category[' . $key . ']' , $value[ 'selected' ] , 4 , '' , '');

        $category_selector .= '<li><label for="question_random_category_' . $key . '">' . $value[ 'name' ] . ':</label> '
            . str_replace (array ( '[random_quest]' , '[tot_quest]' ) , array ( $input_field , $value[ 'total' ] ) , $lang->def ('_TEST_MM1_QUESTION_RANDOM_NUMBER'))
            . '</li>';
    }

    $category_selector .= '</ul>';

    $GLOBALS[ 'page' ]->add (
        Form::openFormLine ()
        . Form::getInputRadio ('order_type_random_category' , 'order_type' , 3 , $order_type == 3 , $script)
        . '<label for="order_type_random_category">' . $lang->def ('_ORDER_TYPE_CATEGORY') . '</label>'
        . $category_selector
        . Form::closeFormLine ()
        . '<br />' , 'content');

    //Tabella Categorie
    require_once (_adm_ . '/lib/lib.customfield.php');
    $fman = new CustomFieldList();
    $fman->setFieldArea ("LO_TEST");
    $fields_mask = $fman->playFieldsFlat ();

    $cust_info = array ();
    if ($cf_info != '') {
        require_once (_base_ . '/lib/lib.json.php');
        $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $arr = $json->decode ($cf_info);
        if (is_array ($arr)) {
            foreach ($arr as $value) {
                $cust_info[ $value[ 'id_cf_son' ] ] = $value[ 'selected' ];
            }
        }
    }

    $cust_order_type = 4;

    foreach ($fields_mask as $field) {
        $cust_order_type = $cust_order_type + 1;
        $category_selector = '<ul id="customfield_' . $field[ 'id' ] . '_list" style="display:' . '"block"' . '">';
        foreach ($field[ 'value' ] as $key => $value) {
            $tot_quest = $fman->getNumberOfObjFieldEntryData ($field[ 'id' ] , $key , $arr_id_quest);
            $sel_quest = $cust_info[ $key ];
            $input_field = Form::getInputTextfield ('textfield_nowh' , 'question_random_customfield_' . $key , 'question_random_customfield[' . $key . ']' , $sel_quest , $cust_order_type , '' , '');
            $category_selector .= '<li><label for="question_random_customfield_' . $key . '">' . $value . ':</label> '
                . str_replace (array ( '[random_quest]' , '[tot_quest]' ) , array ( $input_field , $tot_quest ) , $lang->def ('_TEST_MM1_QUESTION_RANDOM_NUMBER'))
                . '</li>';
        }
        $category_selector .= '</ul>';
        $GLOBALS[ 'page' ]->add (
            Form::openFormLine ()
            . Form::getInputRadio ('order_type_random_customfield' . $field[ 'id' ] . '' , 'order_type' , $cust_order_type , $order_type == $cust_order_type , '')
            . '<label for="order_type_random_customfield' . $field[ 'id' ] . '">' . $field[ "name" ] . '</label>'
            . $category_selector
            . Form::closeFormLine ()
            . '<br />' , 'content');
    }

    //------------------------------------------------------------------------------
    /*
	$chart_options_decoded = new stdClass();
	if ($chart_options!="") {
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$decoded = $json->decode($chart_options);
	}
	$chart_options_decoded->use_charts = (isset($decoded['use_charts']) ? (bool)$decoded['use_charts'] : false);
	$chart_options_decoded->selected_chart = (isset($decoded['selected_chart']) ? (string)$decoded['selected_chart'] : 'column');
	$chart_options_decoded->show_mode = (isset($decoded['show_mode']) ? $decoded['show_mode'] : 'teacher');

	$chart_list = array(
			'stacked' => $lang->def('_STACKED_CHART'),
			'bar' => $lang->def('_BAR_CHART'),
			//'radar' => $lang->def('_RADAR_CHART'),
			'column' => $lang->def('_COLUMN_CHART')
		);

	$chart_show = array(
			'teacher' => $lang->def('_SHOWMODE_TEACHER'),
			'course' => $lang->def('_SHOWMODE_COURSE')
		);

	$chart_list = array_flip($chart_list);
	$chart_show = array_flip($chart_show);
	*/
    //-order-answer----------------------------------------------
    $GLOBALS[ 'page' ]->add (
        '<div class="text_bold">' . $lang->def ('_TEST_MM1_ANSWER_ORDER') . '</div>'
        . Form::getRadio ($lang->def ('_TEST_MM1_ANSWER_SEQUENCE') , 'shuffle_answer_seq' , 'shuffle_answer' , 0 , ! $shuffle_answer)
        . Form::getRadio ($lang->def ('_TEST_MM1_ANSWER_RANDOM') , 'shuffle_answer_random' , 'shuffle_answer' , 1 , $shuffle_answer) . Form::getCloseFieldset ()

        . Form::getOpenFieldset ($lang->def ('_TEST_MM_TWO'))
        //visualization of the info
        . Form::getCheckBox ($lang->def ('_MANDATORY_ANSWER') , 'mandatory_answer' , 'mandatory_answer' , 1 , $mandatory_answer)
        . $lang->def ('_TEST_MM2_HIDE_INFO') . '<br />'
        . '<input class="valign_middle" type="radio" id="mod_hide_info_no" name="mod_hide_info" value="0"'
        . (! $hide_info ? '  checked="checked"' : '') . ' /> '
        . '<label for="mod_doanswer_no">' . $lang->def ('_NO') . '</label>&nbsp;&nbsp;'
        . '<input class="valign_middle" type="radio" id="mod_hide_info_yes" name="mod_hide_info" value="1"'
        . ($hide_info ? '  checked="checked"' : '') . ' /> '
        . '<label for="mod_doanswer_yes">' . $lang->def ('_YES') . '</label>'
        . '<br /><br />'
        //can modify answer
        . $lang->def ('_TEST_MM2_MODANSWER') . '<br />'
        . '<input class="valign_middle" type="radio" id="mod_doanswer_no" name="mod_doanswer" value="0"'
        . (! $mod_doanswer ? '  checked="checked"' : '') . ' /> '
        . '<label for="mod_doanswer_no">' . $lang->def ('_NO') . '</label>&nbsp;&nbsp;'
        . '<input class="valign_middle" type="radio" id="mod_doanswer_yes" name="mod_doanswer" value="1"'
        . ($mod_doanswer ? '  checked="checked"' : '') . ' /> '
        . '<label for="mod_doanswer_yes">' . $lang->def ('_YES') . '</label>'
        . '<br /><br />'
        // can travel trought page
        . $lang->def ('_TEST_MM2_CANTRAVEL') . '<br />'
        . '<input class="valign_middle" type="radio" id="can_travel_no" name="can_travel" value="0"'
        . (! $can_travel ? '  checked="checked"' : '') . ' /> '
        . '<label for="can_travel_no">' . $lang->def ('_NO') . '</label>&nbsp;&nbsp;'
        . '<input class="valign_middle" type="radio" id="can_travel_yes" name="can_travel" value="1"'
        . ($can_travel ? '  checked="checked"' : '') . ' /> '
        . '<label for="can_travel_yes">' . $lang->def ('_YES') . '</label>'
        . '<br /><br />'
        //can freeze
        . $lang->def ('_TEST_MM2_SAVEKEEP') . '<br />'
        . '<input class="valign_middle" type="radio" id="save_keep_no" name="save_keep" value="0"'
        . ($save_keep == 0 ? '  checked="checked"' : '') . ' /> '
        . '<label for="save_keep_no">' . $lang->def ('_NO') . '</label>&nbsp;&nbsp;'
        . '<input class="valign_middle" type="radio" id="save_keep_yes" name="save_keep" value="1"'
        . ($save_keep == 1 ? '  checked="checked"' : '') . ' /> '
        . '<label for="save_keep_yes">' . $lang->def ('_YES') . '</label>'
        . '<br /><br />'
        , 'content');

    $event = new \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent($object_test , $lang);

	$event->addFormElementForSection (Form::getTextfield ($lang->def ('_MAX_ATTEMPT') , 'max_attempt' , 'max_attempt' , 3 , $max_attempt) , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection (Form::getCheckBox ($lang->def ('_RETAIN_ANSWERS_HISTORY') , 'retain_answers_history' , 'retain_answers_history' , 1 , $retain_answers_history) , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection (Form::getCheckbox ($lang->def ('_USE_SUSPENSION') , 'use_suspension' , 'use_suspension' , 1 , $use_suspension , 'onclick="setSuspension();"') , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection (Form::getTextfield ($lang->def ('_SUSPENSION_NUM_ATTEMPTS') , 'suspension_num_attempts' , 'suspension_num_attempts' , 5 , $suspension_num_attempts) , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection (Form::getTextfield ($lang->def ('_SUSPENSION_NUM_HOURS') , 'suspension_num_hours' , 'suspension_num_hours' , 5 , $suspension_num_hours) , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection (Form::getCheckBox ($lang->def ('_SUSPENSION_PREREQUISITES') , 'suspension_prerequisites' , 'suspension_prerequisites' , 1 , $suspension_prerequisites) , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<br /><br />' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection (Form::getCloseFieldset () , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);

	$event->addFormElementForSection (Form::getOpenFieldset ($lang->def ('_TEST_MM_FOUR')) , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ($lang->def ('_TEST_MM4_SHOWTOT') . '<br />' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<input class="valign_middle" type="radio" id="show_tot_no" name="show_tot" value="0"' . (! $show_score ? '  checked="checked"' : '') . ' /> ' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<label for="show_tot_no">' . $lang->def ('_NO') . '</label>&nbsp;&nbsp;' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<input class="valign_middle" type="radio" id="show_tot_yes" name="show_tot" value="1"' . ($show_score ? '  checked="checked"' : '') . ' /> ' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<label for="show_tot_yes">' . $lang->def ('_YES') . '</label>' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<br /><br />' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);

	$event->addFormElementForSection ($lang->def ('_TEST_MM4_SHOWCAT') . '<br />' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<input class="valign_middle" type="radio" id="show_cat_no" name="show_cat" value="0"' . (! $show_score_cat ? '  checked="checked"' : '') . ' /> ' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<label for="show_cat_no">' . $lang->def ('_NO') . '</label>&nbsp;&nbsp;' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<input class="valign_middle" type="radio" id="show_cat_yes" name="show_cat" value="1"' . ($show_score_cat ? '  checked="checked"' : '') . ' /> ' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<label for="show_cat_yes">' . $lang->def ('_YES') . '</label>' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<br /><br />' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);

	$event->addFormElementForSection ($lang->def ('_TEST_MM4_SHOWDOANSWER') . '<br />' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<input class="valign_middle" type="radio" id="show_doanswer_no" name="show_doanswer" value="0"' . ($show_doanswer == 0 ? '  checked="checked"' : '') . ' /> ' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<label for="show_doanswer_no">' . $lang->def ('_NO') . '</label>&nbsp;&nbsp;' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<input class="valign_middle" type="radio" id="show_doanswer_yes" name="show_doanswer" value="1"' . ($show_doanswer == 1 ? '  checked="checked"' : '') . ' /> ' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<label for="show_doanswer_yes">' . $lang->def ('_YES') . '</label>&nbsp;&nbsp;' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<input class="valign_middle" type="radio" id="show_doanswer_yes_if_passed" name="show_doanswer" value="2"' . ($show_doanswer == 2 ? '  checked="checked"' : '') . ' /> ' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<label for="show_doanswer_yes_if_passed">' . $lang->def ('_YES_IF_PASSED') . '</label>' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<br /><br />' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);

	$event->addFormElementForSection ($lang->def ('_TEST_MM4_SHOWSOL') . '<br />' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<input class="valign_middle" type="radio" id="show_solution_no" name="show_solution" value="0"' . ($show_solution == 0 ? '  checked="checked"' : '') . ' /> ' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<label for="show_solution_no">' . $lang->def ('_NO') . '</label>&nbsp;&nbsp;' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<input class="valign_middle" type="radio" id="show_solution_yes" name="show_solution" value="1"' . ($show_solution == 1 ? '  checked="checked"' : '') . ' /> ' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<label for="show_solution_yes">' . $lang->def ('_YES') . '</label>&nbsp;&nbsp;' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<input class="valign_middle" type="radio" id="show_solution_yes_if_passed" name="show_solution" value="2"' . ($show_solution == 2 ? '  checked="checked"' : '') . ' /> ' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<label for="show_solution_yes_if_passed">' . $lang->def ('_YES_IF_PASSED') . '</label>' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection ('<br /><br />' , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);
	$event->addFormElementForSection (Form::getCloseFieldset () , \appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_SECTION_BASE);


    \appCore\Events\DispatcherManager::dispatch (\appLms\Events\Lms\TestConfigurationMethodOfUseRenderEvent::EVENT_NAME , $event);

    $GLOBALS[ 'page' ]->add ($event->getElementString () , 'content');
    $GLOBALS[ 'page' ]->add (
            '<br /><br />'
        . Form::getCloseFieldset () , 'content');

    //}

    $GLOBALS[ 'page' ]->add (
        '<div class="align_right">'
        . '<input class="button" type="submit" value="' . $lang->def ('_SAVE') . '" />'
        . '</div>'
        . Form::closeForm ()
        . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))
        . '</div>' , 'content');

    //----------------------------------------------------------------------------
    $script = '<script type="text/javascript">
			function setSuspension() {/*
				if (document.getElementById("use_suspension").checked) {
					document.getElementById("suspension_num_attempts").disabled = false;
					document.getElementById("suspension_num_hours").disabled = false;
					document.getElementById("suspension_prerequisites").disabled = false;
				} else {
					document.getElementById("suspension_num_attempts").disabled = true;
					document.getElementById("suspension_num_hours").disabled = true;
					document.getElementById("suspension_prerequisites").disabled = true;
				}
			*/}
		</script>';
    cout ($script , 'content');
}

// XXX: updatemodality
function updatemodality ()
{
    checkPerm ('view' , false , 'storage');

    require_once (_base_ . '/lib/lib.json.php');
    $json = new Services_JSON();
    $lang =& DoceboLanguage::createInstance ('test');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));

    list($time_dependent) = sql_fetch_row (sql_query ("
	SELECT time_dependent 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_test
	WHERE idTest = '" . $idTest . "'"));

    $order_info = "";
    if ($_POST[ 'order_type' ] == 3) {
        $arr = array ();
        if (isset($_POST[ 'question_random_category' ]) && is_array ($_POST[ 'question_random_category' ])) {
            foreach ($_POST[ 'question_random_category' ] as $key => $value) {
                if ((int) $value > 0) $arr[] = array ( 'id_category' => $key , 'selected' => (int) $value );
            }
        }
        $order_info = $json->encode ($arr);
    }

    $cf_info = "";
    if ($_POST[ 'order_type' ] > 4) {
        $arr = array ();
        if (isset($_POST[ 'question_random_customfield' ]) && is_array ($_POST[ 'question_random_customfield' ])) {
            foreach ($_POST[ 'question_random_customfield' ] as $key => $value) {
                if ((int) $value > 0) $arr[] = array ( 'id_cf_son' => $key , 'selected' => (int) $value );
            }
        }
        $cf_info = $json->encode ($arr);
    }

    $queryString = "
	UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_test
	SET display_type = '" . ($_POST[ 'display_type' ] ? 1 : 0) . "',
		order_type = '" . $_POST[ 'order_type' ] . "',
		shuffle_answer = '" . ($_POST[ 'shuffle_answer' ] ? 1 : 0) . "',
		question_random_number = '" . ($_POST[ 'order_type' ] == 2 ? $_POST[ 'question_random_number' ] : 0) . "',
		save_keep = '" . ($_POST[ 'save_keep' ] ? 1 : 0) . "',
		hide_info = '" . ($_POST[ 'mod_hide_info' ] ? 1 : 0) . "',
		order_info = '" . $order_info . "',
		cf_info = '" . $cf_info . "',
		mod_doanswer = '" . ($_POST[ 'mod_doanswer' ] ? 1 : 0) . "',
		can_travel = '" . ($_POST[ 'can_travel' ] ? 1 : 0) . "',
		show_score = '" . ($_POST[ 'show_tot' ] ? 1 : 0) . "',
		show_score_cat = '" . ($_POST[ 'show_cat' ] ? 1 : 0) . "',
		show_doanswer = '" . $_POST[ 'show_doanswer' ] . "',
		show_solution = '" . $_POST[ 'show_solution' ] . "',
		retain_answers_history = '" . $_POST[ 'retain_answers_history' ] . "',
		max_attempt = '" . (int) $_POST[ 'max_attempt' ] . "'"
        . ($time_dependent == 2 && $_POST[ 'display_type' ] == 0 ? " ,time_dependent = 0 " : "")
        . " ,use_suspension = " . Get::req ('use_suspension' , DOTY_INT , 0) .
        " ,suspension_num_attempts = '" . Get::req ('suspension_num_attempts' , DOTY_INT , 0) . "' " .
        " ,suspension_num_hours = '" . Get::req ('suspension_num_hours' , DOTY_INT , 0) . "' " .
        " ,suspension_prerequisites = " . Get::req ('suspension_prerequisites' , DOTY_INT , 0) . " " .
        " ,mandatory_answer = " . Get::req ('mandatory_answer' , DOTY_INT , 0) .
        " WHERE idTest = '$idTest'";


    $event = new \appLms\Events\Lms\TestUpdateModalityEvent($idTest , $queryString);

    $event->setPostVars ($_POST);

    \appCore\Events\DispatcherManager::dispatch (\appLms\Events\Lms\TestUpdateModalityEvent::EVENT_NAME , $event);

    if (! sql_query ($queryString)
    ) {
        errorCommunication ($lang->def ('_OPERATION_FAILURE')
            . getBackUi ('index.php?modname=test&amp;op=deftime&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK')));
        return;
    }

    Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $url_coded);
}

// XXX: deftime
function deftime ()
{
    checkPerm ('view' , false , 'storage');

    $lang =& DoceboLanguage::createInstance ('test');

    require_once (_base_ . '/lib/lib.form.php');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));

    list($time_dependent , $time_assigned ,
        $penality_test , $penality_time_test , $penality_quest , $penality_time_quest) = sql_fetch_row (sql_query ("
	SELECT time_dependent, time_assigned, 
		penality_test, penality_time_test, penality_quest, penality_time_quest 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_test
	WHERE idTest = '$idTest'"));


    if (isset($_POST[ 'undo' ])) {

        Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $url_coded);
    }
    if (isset($_POST[ 'settime_button' ])) {

        // second step, ask for time
        switch ($_POST[ 'time_limit' ]) {
            case 0 : {

                $update_query = "
				UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_test
				SET time_dependent = 0, 
					time_assigned = '" . $_POST[ 'time_assigned' ] . "'
				WHERE idTest = '$idTest'";
                if (! sql_query ($update_query)) {
                    errorCommunication ($lang->def ('_OPERATION_FAILURE')
                        . getBackUi ('index.php?modname=test&amp;op=deftime&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK')));
                    return;
                }
                Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $url_coded . '&mod_operation=1');
            };
                break;
            case 1 : {

                $GLOBALS[ 'page' ]->add (
                    getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
                    . '<div class="std_block">'
                    . '<div class="title_big">' . $lang->def ('_TEST_TIME_MANAGEMENT') . '</div>'
                    . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))
                    //-------------------------------------------------------------
                    . Form::openForm ('deftime' , 'index.php?modname=test&amp;op=updatetime')

                    . Form::getHidden ('idTest' , 'idTest' , $idTest)
                    . Form::getHidden ('back_url' , 'back_url' , $url_coded)
                    . Form::getHidden ('time_limit' , 'time_limit' , $_POST[ 'time_limit' ])

                    . Form::getOpenFieldset ($lang->def ('_TEST_TM_TWO'))
                    . Form::getLineBox ($lang->def ('_TOTAL_TIME') , $time_assigned . ' ' . $lang->def ('_SECONDS'))
                    . Form::getTextfield ($lang->def ('_TEST_TM2_NEWTIMETOTAL') ,
                        'time_assigned' ,
                        'time_assigned' ,
                        5 ,
                        $time_assigned ,
                        $lang->def ('_TEST_TM2_NEWTIMETOTAL') ,
                        $lang->def ('_SECONDS'))
                    . Form::getCloseFieldset ()

                    . Form::openButtonSpace ()
                    . Form::getButton ('settime_button' , 'settime_button' , $lang->def ('_SAVE'))
                    . Form::getButton ('undo' , 'undo' , $lang->def ('_UNDO'))
                    . Form::closeButtonSpace ()

                    . Form::closeForm ()

                    . '</div>' , 'content');
            };
                break;
            case 2 : {

                list($actual_tot_time) = sql_fetch_row (sql_query ("
				SELECT SUM(time_assigned) 
				FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
				WHERE idTest = '$idTest'"));

                $GLOBALS[ 'page' ]->add (
                    getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
                    . '<div class="std_block">'
                    . '<div class="title_big">' . $lang->def ('_TEST_TIME_MANAGEMENT') . '</div>'
                    . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))
                    //-------------------------------------------------------------
                    . Form::openForm ('deftime' , 'index.php?modname=test&amp;op=updatetime')

                    . Form::getHidden ('idTest' , 'idTest' , $idTest)
                    . Form::getHidden ('back_url' , 'back_url' , $url_coded)
                    . Form::getHidden ('time_limit' , 'time_limit' , $_POST[ 'time_limit' ])

                    . Form::getOpenFieldset ($lang->def ('_TEST_TM_THREE'))
                    . Form::getLineBox ($lang->def ('_TOTAL_TIME') , $actual_tot_time . ' ' . $lang->def ('_SECONDS'))
                    . Form::getTextfield ($lang->def ('_TEST_TM2_NEWTIME') , 'new_time' , 'new_time' , 10 , $actual_tot_time ,
                        $lang->def ('_TEST_TM2_NEWTIME') , $lang->def ('_SECONDS'))
                    . Form::getOpenCombo ($lang->def ('_TEST_TM2_SUBD_BY'))
                    . Form::getRadio ($lang->def ('_TEST_PM_DIFFICULT') , 'point_diffcult' , 'point_assignement' , 0)
                    . Form::getRadio ($lang->def ('_TEST_TM2_EQUALTOALL') , 'point_equaltoall' , 'point_assignement' , 1)
                    . Form::getRadio ($lang->def ('_TEST_TM2_MANUAL') , 'point_manual' , 'point_assignement' , 2 , true)
                    . Form::getCloseCombo ()
                    . Form::getCloseFieldset ()

                    . Form::openButtonSpace ()
                    . Form::getButton ('settime_button' , 'settime_button' , $lang->def ('_TEST_TM2_SETTIME'))
                    . Form::getButton ('undo' , 'undo' , $lang->def ('_UNDO'))
                    . Form::closeButtonSpace ()

                    . Form::closeForm ()

                    . '</div>' , 'content');
            };
                break;
        }
    } else {

        $GLOBALS[ 'page' ]->add (
            getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
            . '<div class="std_block">'
            . '<div class="title_big">' . $lang->def ('_TEST_TIME_MANAGEMENT') . '</div>'
            . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))
            //-------------------------------------------------------------
            . Form::openForm ('deftime' , 'index.php?modname=test&amp;op=deftime')

            . Form::getHidden ('idTest' , 'idTest' , $idTest)
            . Form::getHidden ('back_url' , 'back_url' , $url_coded)

            . Form::getOpenFieldset ($lang->def ('_TEST_TM_ONE'))
            . Form::getRadio ($lang->def ('_TEST_TIME_NO') , 'time_limit_no' , 'time_limit' , 0 , $time_dependent == 0)
            . Form::getRadio ($lang->def ('_TEST_TIME_YES') , 'time_limit_yes' , 'time_limit' , 1 , $time_dependent == 1)
            . Form::getRadio ($lang->def ('_TEST_TIME_YES_QUEST') , 'time_limit_yes_quest' , 'time_limit' , 2 , $time_dependent == 2)
            . Form::getCloseFieldset ()

            . Form::openButtonSpace ()
            . Form::getButton ('settime_button' , 'settime_button' , $lang->def ('_TEST_TM2_SETTIME'))
            . Form::getButton ('undo' , 'undo' , $lang->def ('_UNDO'))
            . Form::closeButtonSpace ()

            . Form::closeForm ()

            . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))
            . '</div>' , 'content');
    }

}

// XXX: updatetime
function updatetime ()
{

    $lang =& DoceboLanguage::createInstance ('test');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));

    if (isset($_POST[ 'undo' ])) Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $url_coded);

    $update_query = "
	UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_test
	SET time_dependent = '" . $_POST[ 'time_limit' ] . "',
		time_assigned = '" . $_POST[ 'time_assigned' ] . "' "
        . ($_POST[ 'time_limit' ] == 2 ? " ,display_type = 1 " : "")
        . " WHERE idTest = '$idTest'";

    if (! sql_query ($update_query)) {
        errorCommunication ($lang->def ('_OPERATION_FAILURE')
            . getBackUi ('index.php?modname=test&amp;op=deftime&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK')));
        return;
    }

    if ($_POST[ 'time_limit' ] == 2) {

        Util::jump_to ('index.php?modname=test&op=modassigntime&idTest=' . $idTest . '&back_url=' . $url_coded
            . '&point_assignement=' . $_POST[ 'point_assignement' ] . '&new_time=' . $_POST[ 'new_time' ]);
    }
    Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $url_coded . '&mod_operation=1');
}

// XXX: modassignedtime
function modassigntime ()
{
    checkPerm ('view' , false , 'storage');

    $lang =& DoceboLanguage::createInstance ('test');

    require_once (_base_ . '/lib/lib.form.php');
    require_once (_base_ . '/lib/lib.table.php');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));

    //save new time -------------------------------------------------
    if (isset($_POST[ 'saveandexit' ])) {

        $re = sql_query ("
		UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_test
		SET display_type = '1'
		WHERE idTest = '$idTest'");
        if ($re) {
            while (list($idQuest , $difficult) = each ($_POST[ 'new_difficult_quest' ])) {
                $re &= sql_query ("
				UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
				SET difficult = '" . $difficult . "', 
					time_assigned = '" . $_POST[ 'new_time_quest' ][ $idQuest ] . "'
				WHERE idTest = '$idTest' AND idQuest = '" . (int) $idQuest . "'");
            }
        }
        Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $url_coded . '&mod_operation=' . ($re ? 1 : 0));
    }

    list($test_title) = sql_fetch_row (sql_query ("
	SELECT title 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_test
	WHERE idTest = '" . $idTest . "'"));

    list($tot_quest , $tot_difficult , $actual_tot_time) = sql_fetch_row (sql_query ("
	SELECT COUNT(*), SUM(difficult), SUM(time_assigned) 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '$idTest' AND type_quest <> 'break_page' AND type_quest <> 'title'"));

    $re_quest = sql_query ("
	SELECT idQuest, type_quest, title_quest, difficult, time_assigned 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '" . $idTest . "' 
	ORDER BY sequence");

    $GLOBALS[ 'page' ]->add (
        getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
        . '<div class="std_block">'
        . getBackUi ('index.php?modname=test&amp;op=deftime&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))

        . '<form method="post" action="index.php?modname=test&amp;op=modassigntime">'
        . '<input type="hidden" id="authentic_request_test" name="authentic_request" value="' . Util::getSignature () . '" />'

        . '<fieldset class="fieldset_std">'
        . '<legend>' . $lang->def ('_TEST_TM2_CAPTIONSETTIME') . '</legend>'
        . '<input type="hidden" name="idTest" value="' . $idTest . '" />'
        . '<input type="hidden" name="back_url" value="' . $url_coded . '" />' , 'content');

    //table header---------------------------------------------------
    $tab_quest = new Table(0 , $lang->def ('_TEST_SUMMARY') , $lang->def ('_TEST_SUMMARY'));
    $tab_quest->setColsStyle (array ( 'image' , 'image' , '' , 'image' , 'image' ));
    $tab_quest->addHead (array (
        $lang->def ('_TEST_QUEST_ORDER') , $lang->def ('_TYPE') , $lang->def ('_QUESTION') , $lang->def ('_DIFFICULTY') ,
        $lang->def ('_TEST_QUEST_TIME_ASSIGNED') . ' (' . $lang->def ('_SECONDS') . ')' ));

    $i = 1;
    $effective_time = $effective_difficult = 0;
    //tabel body--------------------------------------------------------
    while (list($idQuest , $type_quest , $title_quest , $difficult , $time_assigned) = sql_fetch_row ($re_quest)) {

        if (isset($_POST[ 'new_difficult_quest' ][ $idQuest ])) {

            //loading new time form previous page
            $difficult = $_POST[ 'new_difficult_quest' ][ $idQuest ];
            $new_time = $_POST[ 'new_time_quest' ][ $idQuest ];
        } elseif (isset($_GET[ 'point_assignement' ])) {

            //calculate new time from deftime page
            switch ($_GET[ 'point_assignement' ]) {
                case "0" : {
                    $new_time = (int) (($_GET[ 'new_time' ] / $tot_difficult) * $difficult);
                };
                    break;
                case "1" : {
                    $new_time = (int) ($_GET[ 'new_time' ] / $tot_quest);
                };
                    break;
                case "2" : {
                    $new_time = (int) ($time_assigned);
                };
                    break;
            }
        }

        $content = array (
            $i++ ,
            $lang->def ('_QUEST_ACRN_' . strtoupper ($type_quest)) ,
            $title_quest ,
            ($difficult ?
                '<label for="new_difficult_quest_' . $idQuest . '">' . $lang->def ('_QUEST_TM2_SETDIFFICULT') . '</label>'
                . Form::getInputDropdown ('' ,
                    'new_difficult_quest_' . $idQuest ,
                    'new_difficult_quest[' . $idQuest . ']' ,
                    array ( 1 => 1 , 2 , 3 , 4 , 5 ) ,
                    $difficult ,
                    '') :
                '&nbsp;') ,
            ($difficult ?
                '<label for="new_time_quest_' . $idQuest . '">' . $lang->def ('_QUEST_TM2_SETTIME') . '</label>' .
                '<input type="text" id="new_time_quest_' . $idQuest . '" name="new_time_quest[' . $idQuest . ']" value="' . $new_time . '" size="5" maxlength="4" alt="' . $lang->def ('_QUEST_TM2_SETTIME') . '" />' :
                '&nbsp;')
        );
        if ($difficult != 0) {
            $effective_time += $new_time;
            $effective_difficult += $difficult;
        }
        $tab_quest->addBody ($content);
    }
    $tab_quest->addBodyCustom ('<tr class="line-top-bordered">'
        . '<td colspan="3" class="align_right">' . $lang->def ('TOTAL') . '</td>'
        . '<td class="align_center">' . $effective_difficult . '</td>'
        . '<td class="align_center">' . $effective_time . '</td>'
        . '</tr>');

    $GLOBALS[ 'page' ]->add ($tab_quest->getTable () , 'content');
    //command for this page---------------------------------------------
    if (isset($_GET[ 'new_time' ])) $previous_time = $_GET[ 'new_time' ];
    else $previous_time = $previous_time = $_POST[ 'previous_time' ];
    $time_difference = $effective_time - $previous_time;
    echo $effective_time;
    $GLOBALS[ 'page' ]->add ('</fieldset>'
        . '<div class="set_time_row">'
        . '<input type="hidden" name="previous_time" value="' . $effective_time . '">'
        . str_replace ('[time_difference]' , $time_difference , $lang->def ('_QUEST_TM2_DIFFERENCE_FROM_PREVIOUS'))
        . '&nbsp;&nbsp;&nbsp;'
        . '<input class="button_nowh" type="submit" name="settime" value="' . $lang->def ('_TEST_TM2_SETTIME') . '" />'
        . '</div><br />'
        . '<div class="align_right">'
        . '<input class="button" type="submit" name="saveandexit" value="' . $lang->def ('_SAVE') . '" />'
        . '</div>'
        . '</form>'
        . '</div>' , 'content');
}

// XXX: defpoint
function defpoint ()
{
    checkPerm ('view' , false , 'storage');

    $lang =& DoceboLanguage::createInstance ('test');

    require_once (_base_ . '/lib/lib.form.php');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));

    list($title , $description , $point_type , $point_required) = sql_fetch_row (sql_query ("
	SELECT title, description, point_type, point_required 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_test
	WHERE idTest = '" . $idTest . "'"));

    $GLOBALS[ 'page' ]->add (
        getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
        . '<div class="std_block">'
        . '<div class="title_big">' . $lang->def ('_TEST_POINT_MANAGEMENT') . '</div>'
        . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))

        . Form::openForm ('defpoint' , 'index.php?modname=test&amp;op=updatepoint')

        . Form::getOpenFieldset ($lang->def ('_MIN_SCORE'))
        . Form::getHidden ('idTest' , 'idTest' , $idTest)
        . Form::getHidden ('back_url' , 'back_url' , $url_coded)
        . Form::getTextfield ($lang->def ('_TEST_PMM_REQUIREDSCORE_POINT') , 'point_required' , 'point_required' , 30 ,
            ($point_required ? $point_required : '0.0'))
        . Form::getCloseFieldset ()

        . Form::getOpenFieldset ($lang->def ('_TEST_PM_ONE'))
        . Form::getRadio ($lang->def ('_TEST_PM1_POINT') , 'point_type_point' , 'point_type' , 0 , ($point_type == 0))
        . Form::getRadio ($lang->def ('_TEST_PM1_PERC') , 'point_type_perc' , 'point_type' , 1 , ($point_type == 1))
        . Form::getCloseFieldset ()

        . '<div class="align_right">'
        . Form::getButton ('defpoint_submit' , 'defpoint_submit' , $lang->def ('_SAVE'))
        . '</div>'
        . Form::closeForm ()

        . Form::openForm ('assignpoint' , 'index.php?modname=test&amp;op=modassignpoint')
        . Form::getOpenFieldset ($lang->def ('_TEST_PM_TWO'))
        . Form::getHidden ('idTest_assign' , 'idTest' , $idTest)
        . Form::getHidden ('back_url_assign' , 'back_url' , $url_coded) , 'content');

    $query_question = "
	SELECT q.idQuest, q.type_quest, t.type_file, t.type_class, q.title_quest, q.difficult 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest AS q JOIN " . $GLOBALS[ 'prefix_lms' ] . "_quest_type AS t
	WHERE q.idTest = '" . (int) $idTest . "' AND q.type_quest = t.type_quest";
    $query_question .= " ORDER BY q.sequence";
    $re_quest = sql_query ($query_question);

    $max_score = 0;
    while (list($idQuest , $type_quest , $type_file , $type_class , $title_quest , $difficult) = sql_fetch_row ($re_quest)) {

        require_once (Docebo::inc (_folder_lms_ . '/modules/question/' . $type_file));
        $quest_obj = eval("return new $type_class( $idQuest );");

        $max_score += $quest_obj->getMaxScore ();
    }

    $GLOBALS[ 'page' ]->add (
        '<div class="form_line_l">'
        . '<div class="label_effect">' . $lang->def ('_TEST_QUEST_MAXTESTSCORE') . '</div>' . $max_score . ' ' . $lang->def ('_SCORE') . '</div>'
        . Form::getTextfield ($lang->def ('_TEST_QUEST_NEWMAXTESTSCORE') , 'new_assigned_score' , 'new_assigned_score' , 255 ,
            $max_score , $lang->def ('_TEST_QUEST_NEWMAXTESTSCORE') , $lang->def ('_SCORE'))

        . Form::getOpenCombo ($lang->def ('_TEST_PM_SUBD_BY'))

        . Form::getRadio ($lang->def ('_TEST_PM_DIFFICULT') , 'point_diffcult' , 'point_assignement' , 0)
        . Form::getRadio ($lang->def ('_TEST_PM_EQUALTOALL') , 'point_equaltoall' , 'point_assignement' , 1)
        . Form::getRadio ($lang->def ('_TEST_PM_MANUAL') , 'point_manual' , 'point_assignement' , 2 , true)

        . Form::getCloseCombo ()
        . Form::getCloseFieldset ()

        . '<div class="align_right">'
        . Form::getButton ('assignpoint_submit' , 'assignpoint_submit' , $lang->def ('_TEST_PM_SETPOINT'))
        . '</div>'
        . Form::closeForm ()

        . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))
        . '</div>' , 'content');
}

// XXX: updatemodality
function updatepoint ()
{
    checkPerm ('view' , false , 'storage');

    $lang =& DoceboLanguage::createInstance ('test');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));
    $max_score = _getTestMaxScore ($idTest);

    if (! sql_query ("
	UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_test
	SET point_required = '" . $_POST[ 'point_required' ] . "',
		point_type = '" . ($_POST[ 'point_type' ] ? $_POST[ 'point_type' ] : 0) . "' ,
		score_max = " . (int) $max_score . "
	WHERE idTest = '$idTest'")
    ) {
        UIFeedback::error ($lang->def ('_OPERATION_FAILURE'));
        defpoint ();
        return;
    }

    Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $url_coded);
}


// XXX: modassignpoint
function modassignpoint ()
{
    checkPerm ('view' , false , 'storage');

    $lang =& DoceboLanguage::createInstance ('test');

    require_once (_base_ . '/lib/lib.table.php');
    require_once (_base_ . '/lib/lib.form.php');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));

    //jump back
    if (isset($_POST[ 'back_to_home' ])) {
        Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $url_coded);
    }

    //save new score ------------------------------------------------
    if (isset($_POST[ 'saveandexit' ])) {

        $query_question = "
		SELECT q.idQuest, q.type_quest, t.type_file, t.type_class 
		FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest AS q JOIN " . $GLOBALS[ 'prefix_lms' ] . "_quest_type AS t
		WHERE q.idTest = '" . (int) $idTest . "' AND q.type_quest = t.type_quest";
        $query_question .= " ORDER BY q.sequence";
        $re_quest = sql_query ($query_question);

        $score_assign = array ();
        while (list($idQuest , $type_quest , $type_file , $type_class) = sql_fetch_row ($re_quest)) {


            sql_query ("
			UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_testquest
			SET difficult = '" . (int) $_POST[ 'new_difficult_quest' ][ $idQuest ] . "'
			WHERE idTest = '" . $idTest . "' AND idQuest = '" . (int) $idQuest . "'");

            require_once (Docebo::inc (_folder_lms_ . '/modules/question/' . $type_file));
            $quest_obj = eval("return new $type_class( $idQuest );");
            $score_assign[ $idQuest ] = $quest_obj->setMaxScore ($_POST[ 'new_score_quest' ][ $idQuest ]);
        }
    }

    list($test_title) = sql_fetch_row (sql_query ("
	SELECT title 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_test
	WHERE idTest = '" . $idTest . "'"));

    list($tot_quest , $tot_difficult) = sql_fetch_row (sql_query ("
	SELECT COUNT(*), SUM(difficult) 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '$idTest' AND type_quest <> 'break_page' AND type_quest <> 'title'"));

    $query_question = "
	SELECT q.idQuest, q.type_quest, t.type_file, t.type_class, q.title_quest, q.difficult 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest AS q JOIN " . $GLOBALS[ 'prefix_lms' ] . "_quest_type AS t
	WHERE q.idTest = '" . (int) $idTest . "' AND q.type_quest = t.type_quest";
    $query_question .= " ORDER BY q.sequence";
    $re_quest = sql_query ($query_question);

    $GLOBALS[ 'page' ]->add (getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
        . '<div class="std_block">'
        . getBackUi ('index.php?modname=test&amp;op=defpoint&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))

        . '<form method="post" action="index.php?modname=test&amp;op=modassignpoint">'
        . '<input type="hidden" id="authentic_request_test" name="authentic_request" value="' . Util::getSignature () . '" />'

        . '<fieldset class="fieldset_std">'
        . '<legend>' . $lang->def ('_TEST_TM2_CAPTIONSETTIME') . '</legend>'
        . '<input type="hidden" name="idTest" value="' . $idTest . '" />'
        . '<input type="hidden" name="back_url" value="' . $url_coded . '" />' , 'content');

    //table header---------------------------------------------------
    $tab_quest = new Table(0 , $lang->def ('_TEST_SUMMARY') , $lang->def ('_TEST_SUMMARY'));
    $tab_quest->setColsStyle (array ( 'image' , 'image' , '' , 'image' , 'image' ));
    $tab_quest->addHead (array ( $lang->def ('_TEST_QUEST_ORDER') , $lang->def ('_TYPE') , $lang->def ('_QUESTION') ,
        $lang->def ('_DIFFICULTY') , $lang->def ('_SCORE') ));

    $i = 1;
    $effective_tot_score = $effective_difficult = 0;
    //table body--------------------------------------------------------

    while (list($idQuest , $type_quest , $type_file , $type_class , $title_quest , $difficult) = sql_fetch_row ($re_quest)) {

        require_once (Docebo::inc (_folder_lms_ . '/modules/question/' . $type_file));
        $quest_obj = eval("return new $type_class( $idQuest );");

        if (isset($_POST[ 'new_score_quest' ][ $idQuest ])) {

            //loading new time form previous page
            $difficult = $_POST[ 'new_difficult_quest' ][ $idQuest ];
            $quest_score = $quest_obj->getRealMaxScore ($_POST[ 'new_score_quest' ][ $idQuest ] , true);

        } elseif (isset($_POST[ 'point_assignement' ])) {

            //calculate new time from deftime page
            switch ($_POST[ 'point_assignement' ]) {
                case "0" : {

                    $quest_score = $quest_obj->getRealMaxScore (round (round ($_POST[ 'new_assigned_score' ] / $tot_difficult , 2) * $difficult) , 2);
                    //$quest_score = (( $_POST['new_assigned_score'] / $tot_difficult ) * $difficult ), 2;
                };
                    break;
                case "1" : {
                    $quest_score = $quest_obj->getRealMaxScore (round ($_POST[ 'new_assigned_score' ] / $tot_quest , 2));
                    //$quest_score = round(( $_POST['new_assigned_score'] / $tot_quest ), 2);
                };
                    break;
                case "2" : {
                    $quest_score = $quest_obj->getMaxScore ();
                };
                    break;
            }
        }

        $content = array (
            $i++ ,
            $lang->def ('_QUEST_ACRN_' . strtoupper ($type_quest)) ,
            $title_quest );

        if (isset($score_assign)) {
            $content[] = ($difficult ? $difficult : '&nbsp;');

            if ($difficult) {
                $content[] = ($score_assign[ $idQuest ] != $_POST[ 'new_score_quest' ][ $idQuest ] ? $score_assign[ $idQuest ] . '&nbsp;<span class="font_red">*</span>' : $score_assign[ $idQuest ]);
            } else {
                $content[] = '&nbsp;';
            }
        } else {
            $content[] = ($difficult ?
                '<label for="new_difficult_quest_' . $idQuest . '">' . $lang->def ('_QUEST_TM2_SETDIFFICULT') . '</label>'
                . Form::getInputDropdown ('' ,
                    'new_difficult_quest_' . $idQuest ,
                    'new_difficult_quest[' . $idQuest . ']' ,
                    array ( 1 => 1 , 2 , 3 , 4 , 5 ) ,
                    $difficult ,
                    '') :
                '&nbsp;');
            $content[] = ($difficult ?
                '<label for="new_difficult_quest_' . $idQuest . '">' . $lang->def ('_QUEST_TM2_SETSCORE') . '</label>'
                . '<input type="text" id="new_score_quest_' . $idQuest . '" name="new_score_quest[' . $idQuest . ']" value="'
                . $quest_score . '" size="5" maxlength="200" alt="' . $lang->def ('_QUEST_TM2_SETSCORE') . '" />' :
                '&nbsp;');
        }
        if ($difficult != 0) {
            $effective_difficult += $difficult;

            if (isset($score_assign)) $effective_tot_score = round ($effective_tot_score + $score_assign[ $idQuest ] , 2);
            else $effective_tot_score = round ($effective_tot_score + $quest_score , 2);
        }
        $tab_quest->addBody ($content);
    }
    $tab_quest->addBodyCustom ('<tr class="line-top-bordered">'
        . '<td colspan="3" class="align_right">' . $lang->def ('TOTAL') . '</td>'
        . '<td class="align_center">' . $effective_difficult . '</td>'
        . '<td class="align_center">' . $effective_tot_score . '</td>'
        . '</tr>');
    $GLOBALS[ 'page' ]->add ($tab_quest->getTable () , 'content');

    //command for this page---------------------------------------------
    if (isset($_POST[ 'new_assigned_score' ])) $previous_score = $_POST[ 'new_assigned_score' ];
    else $previous_score = $_POST[ 'previous_score' ];
    $score_difference = round ($effective_tot_score - $previous_score , 2);

    if ($score_difference < 0) $score_difference = '<strong class="font_red">' . $score_difference . '<strong>';
    else $score_difference = '<strong>' . $score_difference . '<strong>';

    $GLOBALS[ 'page' ]->add ('</fieldset>' , 'content');
    if (! isset($score_assign)) {

        $GLOBALS[ 'page' ]->add (
            '<div class="set_time_row">'
            . Form::getHidden ('previous_score' , 'previous_score' , $effective_tot_score)
            . str_replace ('[score_difference]' , $score_difference , $lang->def ('_QUEST_TM2_SCORE_DIFFERENCE_FROM_PREVIOUS'))
            . Form::getButton ('setpoint' , 'setpoint' , $lang->def ('_PREVIEW') , 'button_nowh')
            . '</div>'
            . Form::openButtonSpace ()
            . Form::getButton ('saveandexit' , 'saveandexit' , $lang->def ('_SAVE') , 'button')
            . Form::getButton ('back_to_home' , 'back_to_home' , $lang->def ('_UNDO') , 'button')
            . Form::closeButtonSpace ()
            . '</form>' , 'content');
    } else {
        $GLOBALS[ 'page' ]->add (
            '<div class="set_time_row">'
            . Form::getHidden ('previous_score' , 'previous_score' , $effective_tot_score)
            . str_replace ('[score_difference]' , $score_difference , $lang->def ('_QUEST_TM2_SCORE_DIFFERENCE_FROM_PREVIOUS'))
            . '</div>'
            . Form::openButtonSpace ()
            . Form::getHidden ('point_manual' , 'point_assignement' , 2)
            . Form::getButton ('setpoint' , 'setpoint' , $lang->def ('_TEST_BACK_TO_SETTIME') , 'button')
            . Form::getButton ('back_to_home' , 'back_to_home' , $lang->def ('_SAVE') , 'button')
            . Form::closeButtonSpace ()
            , 'content');
    }

    $GLOBALS[ 'page' ]->add ('</div>' , 'content');
}

function importquest ()
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $back_coded = htmlentities (urlencode ($back_url));

    require_once (_base_ . '/lib/lib.form.php');

    $form = new Form();
    require_once ($GLOBALS[ 'where_lms' ] . '/lib/lib.quest_bank.php');
    $qb_man = new QuestBankMan();
    $supported_format = $qb_man->supported_format ();

    unset($supported_format[ -1 ]);

    $title = array ( 'index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $back_coded => $lang->def ('_TEST_SECTION') , $lang->def ('_IMPORT') );
    cout (
        getTitleArea ($title , 'quest_bank')
        . '<div class="std_block">'

        . $form->openForm ('import_form' , 'index.php?modname=test&op=doimportquest' , false , false , 'multipart/form-data')

        . $form->openElementSpace ()
        . '<input type="hidden" name="idTest" value="' . $idTest . '" />'
        . '<input type="hidden" name="back_url" value="' . $back_coded . '" />'
        . $form->getFilefield ($lang->def ('_FILE') , 'import_file' , 'import_file')
        . $form->getRadioSet ($lang->def ('_TYPE') , 'file_format' , 'file_format' , array_flip ($supported_format) , 0)
        . $form->getTextfield (Lang::t ('_LANG_CHARSET' , 'admin_lang') , 'file_encode' , 'file_encode' , 255 , 'utf-8')
        . $form->closeElementSpace ()

        . $form->openButtonSpace ()
        . $form->getButton ('save' , 'save' , $lang->def ('_IMPORT'))
        . $form->getButton ('undo' , 'undo' , $lang->def ('_UNDO'))
        . $form->closeButtonSpace ()
        . $form->closeForm ()

        . '</div>'
        , 'content');
}

function doimportquest ()
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $back_coded = htmlentities (urlencode ($back_url));

    require_once ($GLOBALS[ 'where_lms' ] . '/lib/lib.quest_bank.php');

    $qb_man = new QuestBankMan();

    $file_format = Get::req ('file_format' , DOTY_INT , 0);
    $file_encode = Get::req ('file_encode' , DOTY_ALPHANUM , 'utf-8');
    $file_readed = file ($_FILES[ 'import_file' ][ 'tmp_name' ]);

    YuiLib::load ('table');

    $title = array ( 'index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $back_coded => $lang->def ('_QUEST_BANK') , $lang->def ('_IMPORT') );
    cout (getTitleArea ($title , 'quest_bank')
        . '<div class="std_block yui-skin-docebo">'
        . getBackUi ('index.php?modname=test&amp;op=defpoint&amp;idTest=' . $idTest . '&amp;back_url=' . $back_coded , $lang->def ('_BACK'))
    );

    $import_result = $qb_man->import_quest ($file_readed , $file_format , $idTest);
    fixQuestSequence ($idTest);

    cout ('<table id="import_result"><caption>' . $lang->def ('_IMPORT') . '</caption>');
    cout ('<tr>'
        . '<td>' . $lang->def ('_QUEST_TYPE') . '</td>'
        . '<td>' . $lang->def ('_SUCCESS') . '</td>'
        . '<td>' . $lang->def ('_FAIL') . '</td>'
        . '</tr>');
    foreach ($import_result as $type_quest => $i_result) {

        cout ('<tr>'
            . '<td>' . $lang->def ('_QUEST_' . strtoupper ($type_quest)) . '</td>'
            . '<td>' . $i_result[ 'success' ] . '</td>'
            . '<td>' . $i_result[ 'fail' ] . '</td>'
            . '</tr>');
    }
    cout ('</table>');

    cout ('</div>');
}

function exportquest ()
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $back_coded = htmlentities (urlencode ($back_url));

    require_once ($GLOBALS[ 'where_lms' ] . '/lib/lib.quest_bank.php');
    $qb_man = new QuestBankMan();

    $file_format = Get::req ('export_quest_select' , DOTY_INT , 0);


    $quests = array ();

    $re_quest = sql_query ("
	SELECT idQuest, type_quest 
	FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest
	WHERE idTest = '$idTest' 
	ORDER BY page, sequence");
    while (list($id_quest , $type_quest) = sql_fetch_row ($re_quest)) {

        $quests[ $id_quest ] = $type_quest;
    }

    $quest_export = $qb_man->export_quest ($quests , $file_format);

    require_once (_base_ . '/lib/lib.download.php');
    sendStrAsFile ($quest_export , 'export_' . date ("Y-m-d") . '.txt');

}

function exportquestqb ()
{
    checkPerm ('view' , false , 'storage');
    $lang =& DoceboLanguage::createInstance ('test');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $back_coded = htmlentities (urlencode ($back_url));

    require_once (_base_ . '/lib/lib.form.php');

    $form = new Form();
    require_once ($GLOBALS[ 'where_lms' ] . '/lib/lib.quest_bank.php');
    $qb_man = new QuestBankMan();
    $supported_format = $qb_man->supported_format ();

    unset($supported_format[ -1 ]);

    require_once (_lms_ . '/lib/lib.questcategory.php');
    $quest_categories = array (
        0 => $lang->def ('_NONE')
    );
    $cman = new Questcategory();
    $arr = $cman->getCategory ();
    foreach ($arr as $id_category => $name_category) {
        $quest_categories[ $id_category ] = $name_category;
    }
    unset($arr);


    $title = array ( 'index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $back_coded => $lang->def ('_TEST_SECTION') , $lang->def ('_EXPORT') );
    cout (
        getTitleArea ($title , 'quest_bank')
        . '<div class="std_block">'

        . $form->openForm ('import_form' , 'index.php?modname=test&op=doexportquestqb' , false , false , 'multipart/form-data')

        . $form->openElementSpace ()
        . '<input type="hidden" name="idTest" value="' . $idTest . '" />'
        . '<input type="hidden" name="back_url" value="' . $back_coded . '" />'
        . $lang->def ('_EXPORT_TO_QUESTION_BANK')
        //.$form->getDropdown($lang->def('_QUEST_CATEGORY'), 'quest_category', 'quest_category', $quest_categories)
        . $form->closeElementSpace ()

        . $form->openButtonSpace ()
        . $form->getButton ('save' , 'save' , $lang->def ('_EXPORT'))
        . $form->getButton ('undo' , 'undo' , $lang->def ('_UNDO'))
        . $form->closeButtonSpace ()
        . $form->closeForm ()

        . '</div>'
        , 'content');
}


function doexportquestqb ()
{

    require_once (_lms_ . '/lib/lib.quest_bank.php');

    $lang =& DoceboLanguage::createInstance ('test');
    $back_url = urldecode (importVar ('back_url'));
    $back_coded = htmlentities (urlencode ($back_url));
    $qb_man = new QuestBankMan();

    $quest_category = Get::req ('quest_category' , DOTY_INT , 0);
    $id_test = Get::pReq ('idTest' , DOTY_INT);

    if (isset($_POST[ 'undo' ])) Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $id_test . '&back_url=' . $back_coded);

    // Get quest from id test
    $reQuest = sql_query (" SELECT q.idQuest FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest AS q WHERE q.idTest = " . $id_test);

    while (list($idQuest) = sql_fetch_row ($reQuest)) {
        $quest_selection[] = $idQuest;
    }

    if (is_array ($quest_selection) && ! empty($quest_selection)) {
        //Insert the question for the test
        $reQuest = sql_query ("
                        SELECT q.idQuest, q.type_quest, t.type_file, t.type_class
                        FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest AS q JOIN " . $GLOBALS[ 'prefix_lms' ] . "_quest_type AS t
                        WHERE q.idQuest IN (" . implode (',' , $quest_selection) . ") AND q.type_quest = t.type_quest");

        while (list($idQuest , $type_quest , $type_file , $type_class) = sql_fetch_row ($reQuest)) {
            require_once (Docebo::inc (_folder_lms_ . '/modules/question/' . $type_file));
            $quest_obj = new $type_class($idQuest);
            $new_id = $quest_obj->copy (0);
        }
    }

    Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $id_test . '&back_url=' . $back_coded);
}

function _getTestMaxScore ($idTest)
{
    if ($idTest <= 0) return false;

    $query_question = "SELECT q.idQuest, q.type_quest, t.type_file, t.type_class, q.title_quest, q.difficult "
        . " FROM " . $GLOBALS[ 'prefix_lms' ] . "_testquest AS q JOIN " . $GLOBALS[ 'prefix_lms' ] . "_quest_type AS t "
        . " WHERE q.idTest = '" . (int) $idTest . "' AND q.type_quest = t.type_quest ORDER BY q.sequence";
    $re_quest = sql_query ($query_question);


    $max_score = 0;
    while (list($idQuest , $type_quest , $type_file , $type_class , $title_quest , $difficult) = sql_fetch_row ($re_quest)) {
        require_once (Docebo::inc (_folder_lms_ . '/modules/question/' . $type_file));
        $quest_obj = eval("return new $type_class( $idQuest );");
        $max_score += $quest_obj->getMaxScore ();
    }

    return $max_score;
}

function _adjustAllTestMaxScore ()
{
    $query = "SELECT * FROM " . $GLOBALS[ 'prefix_lms' ] . "_test";
    $res = sql_query ($query);
    if (! $res) return;

    while ($obj = sql_fetch_object ($res)) {
        if ($obj->idTest) {
            $max_score = _getTestMaxScore ($obj->idTest);
            if ($max_score !== false) {
                $query = "UPDATE " . $GLOBALS[ 'prefix_lms' ] . "_test SET score_max=" . (int) $max_score . " WHERE idTest=" . (int) $obj->idTest;
                $res2 = sql_query ($query);
            }

        }
    }

}

function feedbackman ()
{
    checkPerm ('view' , false , 'storage');
    $res = '';

    require_once (_lms_ . '/lib/lib.questcategory.php');
    require_once (_lms_ . '/lib/lib.assessment_rule.php');

    $id_test = Get::gReq ('idTest' , DOTY_INT , 0);
    $back_url = urldecode (Get::gReq ('back_url' , DOTY_STRING));
    $url_encode = htmlentities (urlencode ($back_url));
    $back_link_url = 'index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $id_test . '&amp;back_url=' . $url_encode;
    $url_base = 'index.php?modname=test&idTest=' . $id_test . '&back_url=' . $url_encode . '&op=';

    $categories = Questcategory::getTestQuestionsCategories ($id_test);
    unset($categories[ 0 ]);
    $categories[ 0 ] = Lang::t ('_TEST_TOTAL_SCORE' , 'test');


    $res .= getTitleArea (array (
            $back_link_url => Lang::t ('_TEST_SECTION' , 'test') ,
            Lang::t ('_FEEDBACK_MANAGEMENT' , 'test')
        ) , 'test')
        . '<div class="std_block">'
        . getBackUi ($back_link_url , Lang::t ('_BACK'));


    if (empty($categories)) {
        $res .= Lang::t ('_NO_CATEGORIES_AVAILABLE');
    } else {
        $asrule = new AssessmentRuleManager($id_test);
        $data = $asrule->getRules ();

        $first = TRUE;
        foreach ($categories as $cat_id => $category) {

            if ($first) {
                $first = FALSE;
            } else {
                $res .= '<br /><br />';
            }

            $res .= '<p>' . ($cat_id > 0 ? Lang::t ('_TEST_QUEST_CATEGORY' , 'test') . ': ' : '')
                . '<b>' . $category . '</b></p>';

            if (isset($data[ $cat_id ])) {
                $tb = new Table(0 , false);

                $tb->addHead (array (
                    Lang::t ('_SCORE' , 'test') ,
                    Lang::t ('_FEEDBACK_TEXT' , 'test') ,
                    Get::sprite ('subs_mod' , Lang::t ('_MOD' , 'standard') , Lang::t ('_MOD' , 'standard')) ,
                    Get::sprite ('subs_del' , Lang::t ('_DEL' , 'standard') , Lang::t ('_DEL' , 'standard'))
                    //'<span class="ico-sprite subs_mod"><span>'.Lang::t('_MOD', 'standard').'</span></span>',
                    //'<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>',
                ) ,
                    array ( '' , '' , 'image' , 'image' )
                );

                foreach ($data[ $cat_id ] as $row) {
                    $row_ln = array ();
                    $row_ln[] = $row[ 'from_score' ] . ' - ' . $row[ 'to_score' ];
                    $row_ln[] = $row[ 'feedback_txt' ];
                    $row_ln[] = '<a class="ico-sprite subs_mod" href="' .
                        $url_base . 'editfbkrule&item_id=' . $row[ 'rule_id' ] .
                        '"><span></span></a>';
                    $row_ln[] = '<a id="del_rule_' . $row[ 'rule_id' ] .
                        '" class="ico-sprite subs_del" href="' .
                        $url_base . 'delfbkrule&item_id=' . $row[ 'rule_id' ] .
                        '"><span></span></a>';
                    $tb->addBody ($row_ln);
                }

                //$tb->addActionAdd('<a href="'.$url_base.'addfbkrule&cat_id='.$cat_id.'" class="ico-wt-sprite subs_add"><span>'.Lang::t('_ADD', 'test').'</span></a>');
                $res .= $tb->getTable ();
            }

            $res .= '<div class="table-container-below">'
                . '<a href="' . $url_base . 'addfbkrule&cat_id=' . $cat_id . '" class="ico-wt-sprite subs_add"><span>' . Lang::t ('_ADD' , 'test') . '</span></a>'
                . '</div>';
        }


    }

    $res .= getBackUi ($back_link_url , Lang::t ('_BACK'))
        . '</div>';


    require_once (_base_ . '/lib/lib.dialog.php');
    setupHrefDialogBox ('a[id^=del_rule_]');

    $GLOBALS[ 'page' ]->add ($res , 'content');
}

function coursereportMan ()
{
    checkPerm ('view' , false , 'storage');

    $lang =& DoceboLanguage::createInstance ('test');

    require_once (_base_ . '/lib/lib.form.php');
    require_once (_base_ . '/lib/lib.json.php');

    $idTest = importVar ('idTest' , true , 0);

    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));

    $GLOBALS[ 'page' ]->add (
        getTitleArea ($lang->def ('_TEST_SECTION') , 'test')
        . '<div class="std_block">'
        . '<div class="title_big">' . $lang->def ('_COURSEREPORT_MANAGEMENT') . '</div>'
        . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))

        . Form::openForm ('coursereportman' , 'index.php?modname=test&amp;op=updatecoursereport')

        . Form::getOpenFieldset ($lang->def ('_TEST_MM_FIVE'))
        . Form::getHidden ('idTest' , 'idTest' , $idTest)
        . Form::getHidden ('back_url' , 'back_url' , $url_coded)
        . Form::getCloseFieldset ()

        . '<br />' , 'content');
    
    $GLOBALS[ 'page' ]->add (
        '<div class="align_right">'
        . '<input class="button" type="submit" value="' . $lang->def ('_SAVE') . '" />'
        . '</div>'
        . Form::closeForm ()
        . getBackUi ('index.php?modname=test&amp;op=modtestgui&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK'))
        . '</div>' , 'content');

}

function updatecoursereport ()
{

    $lang =& DoceboLanguage::createInstance ('test');

    $idTest = importVar ('idTest' , true , 0);
    $back_url = urldecode (importVar ('back_url'));
    $url_coded = htmlentities (urlencode ($back_url));


    /*if (! $queryResponse) {
        errorCommunication ($lang->def ('_OPERATION_FAILURE')
            . getBackUi ('index.php?modname=test&amp;op=deftime&amp;idTest=' . $idTest . '&amp;back_url=' . $url_coded , $lang->def ('_BACK')));
        return;
    }*/

    Util::jump_to ('index.php?modname=test&op=modtestgui&idTest=' . $idTest . '&back_url=' . $url_coded);
}


function addfbkrule ()
{
    checkPerm ('view' , false , 'storage');
    $res = '';

    require_once (_lms_ . '/lib/lib.questcategory.php');
    require_once (_lms_ . '/lib/lib.assessment_rule.php');


    $id_test = Get::gReq ('idTest' , DOTY_INT , 0);
    $cat_id = Get::gReq ('cat_id' , DOTY_INT , 0);
    $back_url = urldecode (Get::gReq ('back_url' , DOTY_STRING));
    $url_encode = htmlentities (urlencode ($back_url));
    $url_base = 'index.php?modname=test&idTest=' . $id_test . '&back_url=' . $url_encode . '&op=';
    $back_link_url = $url_base . 'feedbackman';

    $asrule = new AssessmentRuleManager($id_test);

    $save = Get::pReq ('save' , DOTY_INT , 0);
    if ($save) {
        $asrule->save ();
        Util::jump_to ($url_base . 'feedbackman');
        die();
    }


    $res .= getTitleArea (array (
            $back_link_url => Lang::t ('_TEST_SECTION' , 'test') ,
            $url_base . 'feedbackman' => Lang::t ('_FEEDBACK_MANAGEMENT' , 'test') ,
            Lang::t ('_ADD_FEEDBACK_RULE' , 'test')
        ) , 'test')
        . '<div class="std_block">'
        . getBackUi ($back_link_url , Lang::t ('_BACK'));


    $form_url = '';

    $data = array ();
    $data[ 'rule_id' ] = false;
    $data[ 'test_id' ] = $id_test;
    $data[ 'category_id' ] = $cat_id;


    $res .= $asrule->getAddEditForm ($form_url , $data);


    $res .= getBackUi ($back_link_url , Lang::t ('_BACK'))
        . '</div>';

    $GLOBALS[ 'page' ]->add ($res , 'content');
}


function editfbkrule ()
{
    checkPerm ('view' , false , 'storage');
    $res = '';

    require_once (_lms_ . '/lib/lib.questcategory.php');
    require_once (_lms_ . '/lib/lib.assessment_rule.php');


    $rule_id = Get::gReq ('item_id' , DOTY_INT , 0);
    $id_test = Get::gReq ('idTest' , DOTY_INT , 0);
    $cat_id = Get::gReq ('cat_id' , DOTY_INT , 0);
    $back_url = urldecode (Get::gReq ('back_url' , DOTY_STRING));
    $url_encode = htmlentities (urlencode ($back_url));
    $url_base = 'index.php?modname=test&idTest=' . $id_test . '&back_url=' . $url_encode . '&op=';
    $back_link_url = $url_base . 'feedbackman';

    $asrule = new AssessmentRuleManager($id_test);

    $save = Get::pReq ('save' , DOTY_INT , 0);
    if ($save) {
        $asrule->save ();
        Util::jump_to ($url_base . 'feedbackman');
        die();
    }


    $res .= getTitleArea (array (
            $back_link_url => Lang::t ('_TEST_SECTION' , 'test') ,
            $url_base . 'feedbackman' => Lang::t ('_FEEDBACK_MANAGEMENT' , 'test') ,
            Lang::t ('_MOD' , 'test')
        ) , 'test')
        . '<div class="std_block">'
        . getBackUi ($back_link_url , Lang::t ('_BACK'));


    $form_url = '';

    $data = $asrule->getRuleInfo ($rule_id);
    $res .= $asrule->getAddEditForm ($form_url , $data);


    $res .= getBackUi ($back_link_url , Lang::t ('_BACK'))
        . '</div>';

    $GLOBALS[ 'page' ]->add ($res , 'content');
}


function delfbkrule ()
{
    checkPerm ('view' , false , 'storage');
    $res = '';

    require_once (_lms_ . '/lib/lib.questcategory.php');
    require_once (_lms_ . '/lib/lib.assessment_rule.php');


    $rule_id = Get::gReq ('item_id' , DOTY_INT , 0);
    $id_test = Get::gReq ('idTest' , DOTY_INT , 0);
    $back_url = urldecode (Get::gReq ('back_url' , DOTY_STRING));
    $url_encode = htmlentities (urlencode ($back_url));
    $url_base = 'index.php?modname=test&idTest=' . $id_test . '&back_url=' . $url_encode . '&op=';

    $asrule = new AssessmentRuleManager($id_test);

    if (Get::gReq ('confirm' , DOTY_INT , 0)) { //TODO: change me
        $asrule->delete ($rule_id);
        Util::jump_to ($url_base . 'feedbackman');
        die();
    }
}


// XXX: switch

if (isset($_POST[ 'import_quest' ])) $GLOBALS[ 'op' ] = 'importquest';
if (isset($_POST[ 'export_quest' ])) $GLOBALS[ 'op' ] = 'exportquest';
if ($_POST[ 'export_quest_select' ] == 5) $GLOBALS[ 'op' ] = 'exportquestqb';

switch ($GLOBALS[ 'op' ]) {
    case "instest" : {
        instest ();
    };
        break;

    case "modtest" : {
        modtest ();
    };
        break;
    case "uptest" : {
        $idTest = importVar ('idTest' , true , 0);
        $db = DbConn::getInstance ();
        $res = $db->query ("SELECT obj_type FROM %lms_test WHERE idTest = '" . (int) $idTest . "'");
        $test_type = $db->fetch_row ($res);
        $object_test = createLO ($test_type[ 0 ] , $idTest);
        uptest ($object_test ? $object_test : null);
    };
        break;

    case "modtestgui" : {
        Util::get_js (Get::rel_path ('base') . '/lib/lib.elem_selector.js' , true , true);
        if (isset($_GET[ 'test_saved' ]) || isset($_POST[ 'test_saved' ])) {

            //other enter
            $var_save = importVar ('test_saved');
            $var_loaded = loadTestStatus ($var_save);

            $idTest = $var_loaded[ 'idTest' ];
            $back_url = urlencode ($var_loaded[ 'back_url' ]);
            fixPageSequence ($idTest);
        } else {

            $idTest = importVar ('idTest' , true , 0);
            $back_url = importVar ('back_url');
        }
        $test_type = importVar ('test_type' , false , 'test');
        $db = DbConn::getInstance ();
        $res = $db->query ("SELECT obj_type FROM %lms_test WHERE idTest = '" . (int) $idTest . "'");
        $test_type = $db->fetch_row ($res);
        $object_test = createLO ($test_type[ 0 ] , $idTest);

        $object_test->edit ($idTest , urldecode ($back_url));
    };
        break;
    case "coursereportman" : {
        coursereportMan ();
    }
        break;
    case "updatecoursereport" : {
        updatecoursereport ();
    }
        break;
    case "movequest" : {
        movequest ();
    };
        break;

    case "movedown" : {
        movequestion ('down');
    };
        break;
    case "moveup" : {
        movequestion ('up');
    };
        break;
    case "fixsequence" : {
        fixQuestSequence ();
    };
        break;

    case "addquest" : {
        addquest ();
    };
        break;
    case "modquest" : {
        modquest ();
    };
        break;
    case "delquest" : {
        delquest ();
    };
        break;

    //modality setting
    case "defmodality" : {
        defmodality ();
    };
        break;
    case "updatemodality" : {
        updatemodality ();
    };
        break;

    //time setting
    case "deftime" : {
        deftime ();
    };
        break;
    case "updatetime" : {
        updatetime ();
    };
        break;
    case "modassigntime" : {
        modassigntime ();
    };
        break;


    //point setting
    case "defpoint" : {
        defpoint ();
    };
        break;
    case "updatepoint" : {
        updatepoint ();
    };
        break;
    case "modassignpoint" : {
        modassignpoint ();
    };
        break;

    case "importquest" : {
        importquest ();
    };
        break;
    case "doimportquest" : {
        doimportquest ();
    };
        break;

    case "exportquest" : {
        exportquest ();
    };
        break;

    case "exportquestqb" : {
        exportquestqb ();
    };
        break;

    case "doexportquestqb" : {
        doexportquestqb ();
    };
        break;

    case "feedbackman": {
        feedbackman ();
    }
        break;

    case "addfbkrule": {
        addfbkrule ();
    }
        break;

    case "editfbkrule": {
        editfbkrule ();
    }
        break;

    case "delfbkrule": {
        delfbkrule ();
    }
        break;

    case "defrelation" : {
        defrelation ();
    };
        break;

}

?>
