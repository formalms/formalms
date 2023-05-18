<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

if (\FormaLms\lib\FormaUser::getCurrentUser()->isAnonymous()) {
    exit("You can't access");
}

require_once _lms_ . '/lib/lib.manmenu_course.php';
require_once _lms_ . '/lib/lib.levels.php';

function manmenu()
{
    checkPerm('view');

    require_once _base_ . '/lib/lib.table.php';
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $out = &$GLOBALS['page'];
    $out->setWorkingZone('content');
    $lang = &FormaLanguage::createInstance('manmenu', 'framework');
    $mo_lang = &FormaLanguage::createInstance('menu', 'lms');

    $mod_perm = checkPerm('mod', true);

    $query_voice = '
	SELECT idMain, name, image, sequence 
	FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
	WHERE idCourse = '" . (int) $idCourse . "'
	ORDER BY sequence";
    $re_voice = sql_query($query_voice);
    $tot_voice = sql_num_rows($re_voice);

    $tb = new Table(0, $lang->def('_TB_MANMENU_CAPTION'), $lang->def('_TB_MANMENU_SUMMARY'));
    $content_h = [
        $lang->def('_ORDER'),
        //'<img src="'.getPathImage().'manmenu/symbol.gif" title="'.$lang->def('_SYMBOL_TITLE').'" alt="'.$lang->def('_SYMBOL').'" />',
        $lang->def('_TITLE_MENUVOICE'),
        '<img src="' . getPathImage() . 'standard/down.png" title="' . $lang->def('_MOVE_DOWN') . '" alt="' . $lang->def('_DOWN') . '" />',
        '<img src="' . getPathImage() . 'standard/up.png" title="' . $lang->def('_MOVE_UP') . '" alt="' . $lang->def('_UP') . '" />',
        '<img src="' . getPathImage() . 'standard/modelem.png" title="' . $lang->def('_MODMODULE') . '" alt="' . $lang->def('_MOD') . '" />', ];
    $type_h = ['image', /*'image',*/ '', 'image', 'image', 'image'];
    if ($mod_perm) {
        $content_h[] = '<img src="' . getPathImage() . 'standard/edit.png" title="' . $lang->def('_MOD') . '" alt="' . $lang->def('_MOD') . '" />';
        $type_h[] = 'image';
        $content_h[] = '<img src="' . getPathImage() . 'standard/delete.png" title="' . $lang->def('_DEL') . '" alt="' . $lang->def('_DEL') . '" />';
        $type_h[] = 'image';
    }
    $tb->setColsStyle($type_h);
    $tb->addHead($content_h);
    $i = 0;
    while (list($id_m, $name, $image, $sequence) = sql_fetch_row($re_voice)) {
        $strip_name = strip_tags(Lang::t($name, 'menu', false, false, $name));

        $content = [
            $sequence,
        //	'<img class="manmenu_symbol" src="'.getPathImage('lms').'menu/'.$image.'" alt="'.$strip_name.'" />',
            '<a href="index.php?modname=manmenu&amp;op=manmodule&amp;id_main=' . $id_m . '"'
                . ' title="' . $lang->def('_MODMODULE') . '">' . Lang::t($name, 'menu', false, false, $name) . '</a>', ];
        // Up and Down action
        $content[] = ($i != ($tot_voice - 1) ? '<a href="index.php?modname=manmenu&amp;op=mdmenuvoice&amp;id_main=' . $id_m . '"'
                . ' title="' . $lang->def('_MOVE_DOWN') . ' : ' . $strip_name . '">'
                . '<img src="' . getPathImage() . 'standard/down.png" alt="' . $lang->def('_DOWN') . ' : ' . $strip_name . '" /></a>' : '');
        $content[] = ($i != 0 ? '<a href="index.php?modname=manmenu&amp;op=mumenuvoice&amp;id_main=' . $id_m . '"'
                . ' title="' . $lang->def('_MOVE_UP') . ' : ' . $strip_name . '">'
                . '<img src="' . getPathImage() . 'standard/up.png" alt="' . $lang->def('_UP') . ' : ' . $strip_name . '" /></a>' : '');
        // Modify module
        $content[] = '<a href="index.php?modname=manmenu&amp;op=manmodule&amp;id_main=' . $id_m . '"'
                . ' title="' . $lang->def('_MODMODULE') . ' : ' . $strip_name . '">'
                . '<img src="' . getPathImage() . 'standard/modelem.png" alt="' . $lang->def('_MOD') . ' : ' . $strip_name . '" /></a>';
        if ($mod_perm) {
            // Modify voice
            $content[] = '<a href="index.php?modname=manmenu&amp;op=modmenuvoice&amp;id_main=' . $id_m . '"'
                . ' title="' . $lang->def('_MOD') . ' : ' . $strip_name . '">'
                . '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . ' : ' . $strip_name . '" /></a>';

            // Delete voice
            $content[] = '<a href="index.php?modname=manmenu&amp;op=delmenuvoice&amp;id_main=' . $id_m . '"'
                . ' title="' . $lang->def('_DEL') . ' : ' . $strip_name . '">'
                . '<img src="' . getPathImage() . 'standard/delete.png" alt="' . $lang->def('_DEL') . ' : ' . $strip_name . '" /></a>';
        }
        $tb->addBody($content);
        ++$i;
    }
    if ($mod_perm) {
        $tb->addActionAdd('<a class="ico-wt-sprite subs_add" href="index.php?modname=manmenu&amp;op=addmenuvoice"'
            . ' title="' . $lang->def('_NEW') . '">'
            . '<span>' . $lang->def('_NEW') . '</span></a>');

        require_once _base_ . '/lib/lib.dialog.php';
        setupHrefDialogBox('a[href*=delmenuvoice]');
    }

    // print out
    $page_title = [
        $lang->def('_TITLE_MANMENU'),
    ];

    $out->setWorkingZone('content');
    $out->add(
        getTitleArea($page_title, 'manmenu')
        . '<div class="std_block">');
    if (isset($_GET['result'])) {
        switch ($_GET['result']) {
            case 0: $out->add(getResultUi($lang->def('_OPERATION_FAILURE'))); break;
            case 1: $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL'))); break;
        }
    }
    $out->add($tb->getTable()
        . '[ <a href="index.php?modname=manmenu&amp;op=fixmenuvoice" '
            . 'title="' . $lang->def('_FIX_SEQUENCE') . '">'
            . $lang->def('_FIX_SEQUENCE') . '</a> ]'
        . '</div>');
}

function editmenuvoice($load = false)
{
    checkPerm('mod');

    require_once _base_ . '/lib/lib.form.php';

    $out = &$GLOBALS['page'];
    $out->setWorkingZone('content');
    $lang = &FormaLanguage::createInstance('manmenu', 'framework');
    $mo_lang = &FormaLanguage::createInstance('menu', 'lms');

    $out->setWorkingZone('content');

    // Find images
    /*$all_images = array();
    $templ = dir(getPathImage('lms').'menu/');
    while($elem = $templ->read()) {

        if(ereg('.gif', $elem)) $all_images[$elem] = $elem;
    }
    closedir($templ->handle);
    */
    if ($load == false) {
        $page_title = [
            'index.php?modname=manmenu&amp;op=manmenu' => $lang->def('_TITLE_MANMENU'),
            $lang->def('_NEW'),
        ];

        $name = '';
        $image = 'blank.png';
    } else {
        $id_main = FormaLms\lib\Get::req('id_main', DOTY_INT, 0);
        $query_custom = '
		SELECT name, image 
		FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_main
		WHERE idMain = '" . $id_main . "'";
        list($name, $image) = sql_fetch_row(sql_query($query_custom));
        $page_title = [
            'index.php?modname=manmenu&amp;op=manmenu' => $lang->def('_TITLE_MANMENU'),
            $lang->def('_MOD') . ' : ' . $name,
        ];
    }
    $out->add(
        getTitleArea($page_title, 'manmenu')
        . '<div class="std_block">'
        . getBackUi('index.php?modname=manmenu&amp;op=manmenu'
            . ($load == false ? '' : '&amp;id_main=' . $id_main), $lang->def('_BACK'))
        . Form::openForm('addmenuvoice_form', 'index.php?modname=manmenu&amp;op=savemenuvoice')
        . Form::openElementSpace()
        . Form::getTextfield($lang->def('_TITLE'), 'name', 'name', 255, $name)
    );
    if ($load !== false) {
        $out->add(Form::getHidden('id_main', 'id_main', $id_main));
    }

    $out->add(
        Form::closeElementSpace()
        . Form::openButtonSpace()
        . Form::getButton('addmenuvoice', 'addmenuvoice', ($load == false ? $lang->def('_INSERT') : $lang->def('_SAVE')))
        . Form::getButton('undomenuvoice', 'undomenuvoice', $lang->def('_UNDO'))
        . Form::closeButtonSpace()
        . Form::closeForm()
        . '</div>');
}

function savemenuvoice()
{
    checkPerm('mod');
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $re = true;
    if (isset($_POST['undomenuvoice'])) {
        if (isset($_POST['id_main'])) {
            Util::jump_to('index.php?modname=manmenu&op=manmenu&id_main=' . $_POST['id_main']);
        } else {
            Util::jump_to('index.php?modname=manmenu&op=manmenu');
        }
    }
    if (isset($_POST['id_main'])) {
        $re = sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
		SET name = '" . $_POST['name'] . "' 
		WHERE idMain = '" . $_POST['id_main'] . "'");

        Util::jump_to('index.php?modname=manmenu&op=manmenu&id_main=' . $_POST['id_main'] . '&result=' . ($re ? 1 : 0));
    } else {
        $query_seq = '
		SELECT MAX(sequence)
		FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
		WHERE idCourse = '" . (int) $idCourse . "'";
        list($seq) = sql_fetch_row(sql_query($query_seq));
        ++$seq;

        $re = sql_query('
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
		( idCourse, name, image, sequence ) VALUES 
		( '" . $idCourse . "', '" . $_POST['name'] . "', '', '" . $seq . "' )");

        Util::jump_to('index.php?modname=manmenu&op=manmenu&result=' . ($re ? 1 : 0));
    }
}

function delmenuvoice()
{
    checkPerm('mod');

    require_once _base_ . '/lib/lib.form.php';

    $out = &$GLOBALS['page'];
    $out->setWorkingZone('content');
    $lang = &FormaLanguage::createInstance('manmenu', 'framework');
    $mo_lang = &FormaLanguage::createInstance('menu', 'lms');

    $id_main = FormaLms\lib\Get::req('id_main', DOTY_INT, 0);

    $query_custom = '
	SELECT idCustom, name, image 
	FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
	WHERE idMain = '" . $id_main . "'";
    list($id_custom, $name_db, $image) = sql_fetch_row(sql_query($query_custom));

    if (isset($_POST['undo'])) {
        Util::jump_to('index.php?modname=manmenu&op=manmenu&id_main=' . $id_main);
    } elseif (isset($_POST['confirm']) || isset($_GET['confirm'])) {
        $id_main = FormaLms\lib\Get::req('id_main', DOTY_INT, 0);

        $re = true;
        $re_modules = sql_query('
		SELECT idModule 
		FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
		WHERE idMain = '" . $id_main . "'");
        while (list($id_module) = sql_fetch_row($re_modules)) {
            $re &= removeModule($id_module, $id_main, $id_custom);
        }
        if (!$re) {
            Util::jump_to('index.php?modname=manmenu&op=manmenu&result=0');
        }

        if (!sql_query('
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
		WHERE idMain = '" . $id_main . "'")) {
            Util::jump_to('index.php?modname=manmenu&op=manmenu&result=0');
        }

        $re = sql_query('
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
		WHERE idMain = '" . $id_main . "'");

        \FormaLms\lib\FormaUser::getCurrentUser()->loadUserSectionST();
        \FormaLms\lib\FormaUser::getCurrentUser()->saveInSession();

        Util::jump_to('index.php?modname=manmenu&op=manmenu&result=' . ($re ? 1 : 0));
    } else {
        $name = Lang::t($name_db, 'menu', false, false, $name_db);
        $strip_name = strip_tags($name);
        $out->add(
            getTitleArea($lang->def('_TITLE_MANMENU'), 'manmenu')
            . '<div class="std_block">'
            . Form::openForm('delcustom_form', 'index.php?modname=manmenu&amp;op=delmenuvoice')
            . Form::getHidden('id_main', 'id_main', $id_main)
            . getDeleteUi($lang->def('_AREYOUSURE'),
                        '<img class="manmenu_symbol" src="' . getPathImage('lms') . 'menu/' . $image . '" alt="' . $strip_name . '" />'
                        . '<span class="text_bold">' . $lang->def('_TITLE_MENUVOICE') . ' : </span>' . $name,
                        false,
                        'confirm',
                        'undo')
            . Form::closeForm()
            . '</div>');
    }
}

function movemenuvoice($direction)
{
    checkPerm('mod');

    $id_main = FormaLms\lib\Get::req('id_main', DOTY_INT, 0);
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    list($seq) = sql_fetch_row(sql_query('
	SELECT sequence 
	FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
	WHERE idMain = '$id_main'"));

    if ($direction == 'up') {
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
		SET sequence = '$seq' 
		WHERE idCourse = '" . $idCourse . "' AND sequence = '" . ($seq - 1) . "'");
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
		SET sequence = sequence - 1 
		WHERE idMain = '$id_main'");
    }
    if ($direction == 'down') {
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
		SET sequence = '$seq' 
		WHERE idCourse = '" . $idCourse . "' AND sequence = '" . ($seq + 1) . "'");
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
		SET sequence = '" . ($seq + 1) . "' 
		WHERE idMain = '$id_main'");
    }
    Util::jump_to('index.php?modname=manmenu&op=manmenu&id_main=' . $id_main);
}

function fixmenuvoice()
{
    checkPerm('mod');

    $id_custom = FormaLms\lib\Get::req('id_custom', DOTY_INT, 0);
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $query = '
	SELECT idMain 
	FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
	WHERE idCourse = '" . $idCourse . "' 
	ORDER BY sequence";
    $reField = sql_query($query);

    $i = 1;
    while (list($id) = sql_fetch_row($reField)) {
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
		SET sequence = '" . ($i++) . "' 
		WHERE idMain = '$id'");
    }
    Util::jump_to('index.php?modname=manmenu&op=manmenu');
}

function manmodule()
{
    checkPerm('view');

    require_once _base_ . '/lib/lib.table.php';

    $out = &$GLOBALS['page'];
    $lang = &FormaLanguage::createInstance('manmenu', 'framework');
    $mo_lang = &FormaLanguage::createInstance('menu', 'lms');
    $menu_lang = &FormaLanguage::createInstance('menu_course', 'lms');
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $mod_perm = checkPerm('mod', true);

    // Find main voice info
    $id_main = FormaLms\lib\Get::req('id_main', DOTY_INT, 0);
    $query_custom = '
	SELECT name 
	FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
	WHERE idMain = '" . (int) $id_main . "'";
    list($title_main) = sql_fetch_row(sql_query($query_custom));

    // Find all modules in this voice
    $query_module = '
	SELECT module.idModule, module.default_name, menu.my_name, menu.sequence, module.module_name
	FROM %lms_menucourse_under AS menu JOIN
		' . $GLOBALS['prefix_lms'] . "_module AS module
	WHERE module.idModule = menu.idModule AND menu.idMain = '" . (int) $id_main . "' 
	ORDER BY menu.sequence";
    $re_module = sql_query($query_module);
    $tot_module = sql_num_rows($re_module);

    $used_module = '';
    $query_used_module = '
	SELECT module.idModule 
	FROM %lms_menucourse_under AS menu JOIN 
		' . $GLOBALS['prefix_lms'] . "_module AS module 
	WHERE module.idModule = menu.idModule AND 
		( menu.idCourse = '" . $idCourse . "' OR menu.idCourse = 0 )";
    $re_used_module = sql_query($query_used_module);

    while (list($id_mod_used) = sql_fetch_row($re_used_module)) {
        $used_module .= $id_mod_used . ',';
    }

    $query_free_module = '
	SELECT idModule, default_name 
	FROM ' . $GLOBALS['prefix_lms'] . "_module AS module 
	WHERE module_info = '' AND idModule NOT IN ( " . substr($used_module, 0, -1) . ' )';
    $re_free_module = sql_query($query_free_module);

    $tb = new Table(0, $lang->def('_TB_MANMODULE_CAPTION'), $lang->def('_TB_MANMODULE_SUMMARY'));

    $content_h = [
        $lang->def('_ORDER'),
        $lang->def('_TITLE_MODULE'),
        '<img src="' . getPathImage() . 'standard/down.png" title="' . $lang->def('_MOVE_DOWN') . '" alt="' . $lang->def('_DOWN') . '" />',
        '<img src="' . getPathImage() . 'standard/up.png" title="' . $lang->def('_MOVE_UP') . '" alt="' . $lang->def('_UP') . '" />', ];
    $type_h = ['image', '', 'image', 'image'];
    if ($mod_perm) {
        $content_h[] = '<img src="' . getPathImage() . 'standard/edit.png" title="' . $lang->def('_EDIT_SETTINGS') . '"'
            . ' alt="' . $lang->def('_MOD') . '" />';
        $type_h[] = 'image';

        $content_h[] = $lang->def('_DEL');
        $type_h[] = 'image';
    }
    $tb->setColsStyle($type_h);
    $tb->addHead($content_h);

    $i = 0;
    while (list($id_mod, $name_db, $my_name, $sequence, $module_name) = sql_fetch_row($re_module)) {
        $name = ($my_name != '' ? $my_name : $menu_lang->def($name_db));
        $strip_name = strip_tags($name);
        $content = [$sequence, $name];

        $content[] = ($i != ($tot_module - 1) ? '<a href="index.php?modname=manmenu&amp;op=mdmodule&amp;id_main=' . $id_main . '&amp;id_module=' . $id_mod . '"'
                . ' title="' . $lang->def('_MOVE_DOWN') . ' : ' . $strip_name . '">'
                . '<img src="' . getPathImage() . 'standard/down.png" alt="' . $lang->def('_DOWN') . '" /></a>' : '');
        $content[] = ($i != 0 ? '<a href="index.php?modname=manmenu&amp;op=mumodule&amp;id_main=' . $id_main . '&amp;id_module=' . $id_mod . '"'
                . ' title="' . $lang->def('_MOVE_UP') . ' : ' . $strip_name . '">'
                . '<img src="' . getPathImage() . 'standard/up.png" alt="' . $lang->def('_UP') . '" /></a>' : '');
        if ($mod_perm) {
            $content[] = '<a href="index.php?modname=manmenu&amp;op=modmodule&amp;id_main=' . $id_main . '&amp;id_module=' . $id_mod . '"'
                . ' title="' . $lang->def('_EDIT_SETTINGS') . ' : ' . $strip_name . '">'
                . '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" /></a>';
            if ($module_name != 'manmenu') {
                $content[] = '<a href="index.php?modname=manmenu&amp;op=delmodule&amp;id_main=' . $id_main . '&amp;id_module=' . $id_mod . '"'
                    . ' title="' . $lang->def('_DEL') . ' : ' . $strip_name . '">'
                    . $lang->def('_DEL') . '</a>';
            } else {
                $content[] = '';
            }
        }
        $tb->addBody($content);
        ++$i;
    }
    if ($mod_perm) {
        require_once _base_ . '/lib/lib.dialog.php';
        setupHrefDialogBox('a[href*=delmodule]');
    }

    $tb_free = new Table(0, $lang->def('_TB_FREE_MANMODULE_CAPTION'), $lang->def('_NOT_ASSIGNED'));
    $c_free_h = [$lang->def('_TITLE_MODULE')];
    $t_free_h = [''];
    if ($mod_perm) {
        $c_free_h[] = $lang->def('_ASSIGN');
        $t_free_h[] = 'image';
    }
    $tb_free->setColsStyle($t_free_h);
    $tb_free->addHead($c_free_h);
    while (list($id_import_mod, $name_db) = sql_fetch_row($re_free_module)) {
        $name = $menu_lang->def($name_db);
        $strip_name = strip_tags($name);

        $content = [$name];
        if ($mod_perm) {
            $content[] = '<a href="index.php?modname=manmenu&amp;op=addmodule&amp;id_main=' . $id_main . '&amp;id_module=' . $id_import_mod . '"'
                . ' title="' . $lang->def('_TITLE_GRABMODULE') . ' : ' . $strip_name . '">' . $lang->def('_ASSIGN') . '</a>';
        }
        $tb_free->addBody($content);
    }
    // print out
    $out->setWorkingZone('content');

    $page_title = [
        'index.php?modname=manmenu&amp;op=manmenu' => $lang->def('_TITLE_MANMENU'),
        Lang::t($title_main, 'menu', false, false, $title_main),
    ];

    $out->add(
        getTitleArea($page_title, 'manmenu')
        . '<div class="std_block">'
        . getBackUi('index.php?modname=manmenu&amp;op=manmenu', $lang->def('_BACK')));
    if (isset($_GET['result'])) {
        switch ($_GET['result']) {
            case 0: $out->add(getResultUi($lang->def('_OPERATION_FAILURE'))); break;
            case 1: $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL'))); break;
        }
    }
    $out->add(
        $tb->getTable()
        . '[ <a href="index.php?modname=manmenu&amp;op=fixmodule&amp;id_main=' . $id_main . '" '
            . 'title="' . $lang->def('_FIX_SEQUENCE') . '">'
            . $lang->def('_FIX_SEQUENCE') . '</a> ]'
        . '<br /><br />'
        . (sql_num_rows($re_free_module) != false ? $tb_free->getTable() : '')
        . '</div>');
}

function editmodule($load = false)
{
    checkPerm('mod');

    require_once _base_ . '/lib/lib.form.php';
    Util::get_js(FormaLms\lib\Get::rel_path('base') . '/lib/js_utils.js', true, true);

    $lang = &FormaLanguage::createInstance('manmenu', 'framework');
    $menu_lang = &FormaLanguage::createInstance('menu_course', 'lms');
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $out = &$GLOBALS['page'];
    $out->setWorkingZone('content');
    $id_main = FormaLms\lib\Get::req('id_main', DOTY_INT, 0);
    $id_module = FormaLms\lib\Get::req('id_module', DOTY_INT, 0);
    $acl_man = &\FormaLms\lib\Forma::getAclManager();
    $perm = [];

    // Load module info
    $query_module = '
	SELECT module_name, default_name, file_name, class_name 
	FROM ' . $GLOBALS['prefix_lms'] . "_module 
	WHERE idModule = '" . $id_module . "'";
    list($module_name, $name_db, $file_name, $class_name) = sql_fetch_row(sql_query($query_module));
    $module_obj = &createModule($module_name);

    // Standard name
    $name = Lang::t($name_db, 'menu_course', false, false, $name_db);

    $my_name = '';

    $query_module = '
	SELECT default_op 
	FROM ' . $GLOBALS['prefix_lms'] . "_module 
	WHERE idModule = '" . $id_module . "'";
    list($module_op) = sql_fetch_row(sql_query($query_module));

    // Load info
    if ($load) {
        // Find personalized name
        $query_seq = '
		SELECT u.my_name, m.default_op 
		FROM %lms_menucourse_under AS u JOIN
			' . $GLOBALS['prefix_lms'] . "_module AS m 
		WHERE u.idModule = m.idModule AND u.idMain = '" . $id_main . "' AND u.idModule = '" . $id_module . "'";
        list($my_name, $def_op) = sql_fetch_row(sql_query($query_seq));

        // Load actual module permission

        $levels = CourseLevel::getTranslatedLevels();
        $tokens = $module_obj->getAllToken($module_op);

        $map_level_idst = &getCourseLevelSt($idCourse);
        $map_all_role = &getModuleRoleSt($module_name, $tokens, true);
        $group_idst_roles = &getAllModulesPermissionSt($map_level_idst, $map_all_role);
        $perm = &fromStToToken($group_idst_roles, $map_all_role);
    }

    $query_mains = '
	SELECT idMain, name 
	FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_main 
	WHERE idCourse = '" . $idCourse . "'
	ORDER BY sequence";
    $re_mains = sql_query($query_mains);
    while (list($id_db_main, $main_name) = sql_fetch_row($re_mains)) {
        $mains[$id_db_main] = $main_name;
        if ($id_db_main == $id_main) {
            $title_main = $main_name;
        }
    }

    // Form
    $page_title = [
        'index.php?modname=manmenu&amp;op=manmenu' => $lang->def('_TITLE_MANMENU'),
        'index.php?modname=manmenu&amp;op=manmodule&amp;id_main=' . $id_main => $title_main,
        ($my_name != '' ? $my_name : $name),
    ];
    $out->add(
        getTitleArea($page_title, 'manmenu')
        . '<div class="std_block">'
        . getBackUi('index.php?modname=manmenu&amp;op=manmodule&amp;id_main=' . $id_main, $lang->def('_BACK'))

        . Form::openForm('module_permission',
                        'index.php?modname=manmenu&amp;op=upmodule&amp;id_main=' . $id_main . '&amp;id_module=' . $id_module)
        . Form::getHidden('id_main', 'id_main', $id_main)
        . Form::getHidden('id_module', 'id_module', $id_module)

        . ($load ? Form::getHidden('load', 'load', '1') : '')

        . Form::getTextfield($lang->def('_MY_NAME'), 'my_name', 'my_name', 255,
            ($load ? $my_name : ''))
        . Form::getDropdown($lang->def('_TITLE_MENUVOICE'), 'new_id_main', 'new_id_main', $mains, $id_main)
        . Form::getBreakRow()
        . $module_obj->getPermissionUi('module_permission', $perm, $module_op)
        . Form::getBreakRow()
        . Form::openButtonSpace()
        . Form::getButton('saveperm', 'saveperm', ($load ? $lang->def('_SAVE') : $lang->def('_IMPORT')))
        . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
        . Form::closeButtonSpace()

        . Form::closeForm()
        . '</div>'
    );
}

function upmodule()
{
    checkPerm('mod');

    $out = &$GLOBALS['page'];
    $out->setWorkingZone('content');
    $id_main = FormaLms\lib\Get::req('id_main', DOTY_INT, 0);
    $new_id_main = FormaLms\lib\Get::req('new_id_main', DOTY_INT, 0);
    $id_module = FormaLms\lib\Get::req('id_module', DOTY_INT, 0);
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $lang = &FormaLanguage::createInstance('manmenu', 'framework');
    $acl_man = &\FormaLms\lib\Forma::getAclManager();

    if (isset($_POST['undo'])) {
        Util::jump_to('index.php?modname=manmenu&op=manmodule&id_main=' . $id_main);
    }

    // Load module info
    $query_module = '
	SELECT module_name, default_name, file_name, class_name, default_op 
	FROM ' . $GLOBALS['prefix_lms'] . "_module 
	WHERE idModule = '" . $id_module . "'";
    list($module_name, $name_db, $file_name, $class_name, $def_op) = sql_fetch_row(sql_query($query_module));
    $module_obj = &createModule($module_name);

    //*************************************************************//
    //* Find permission to save or delete *************************//
    //*************************************************************//

    $levels = CourseLevel::getTranslatedLevels();
    $all_token = $module_obj->getAllToken($def_op);
    $new_token = $module_obj->getSelectedPermission($def_op);
    // corresponding of token -> idst role
    $map_idst_token = &getModuleRoleSt($module_name, $all_token);
    // corresponding of level -> idst level
    $map_idst_level = &getCourseLevelSt($idCourse);
    // idst of the selected perm
    $idst_new_perm = &fromTokenToSt($new_token, $map_idst_token);
    // old permission of all module
    $idst_old_perm = &getAllModulesPermissionSt($map_idst_level, array_flip($map_idst_token));

    // What to add what to delete
    foreach ($levels as $lv => $name_level) {
        if (isset($idst_new_perm[$lv])) {
            $perm_to_add_idst[$lv] = array_diff_assoc($idst_new_perm[$lv], $idst_old_perm[$lv]);

            $perm_to_del_idst[$lv] = array_diff_assoc($idst_old_perm[$lv], $idst_new_perm[$lv]);
        } else {
            $perm_to_add_idst[$lv] = [];
            $perm_to_del_idst[$lv] = $idst_old_perm[$lv];
        }
    }

    foreach ($levels as $lv => $name_level) {
        $idlevel = $map_idst_level[$lv];
        foreach ($perm_to_add_idst[$lv] as $idrole => $v) {
            $acl_man->addToRole($idrole, $idlevel);
        }
        foreach ($perm_to_del_idst[$lv] as $idrole => $v) {
            $acl_man->removeFromRole($idrole, $idlevel);
        }
    }
    /*
    echo '<div class="box_evidence" style="float: left;">New<br /><pre>';
    print_r($idst_new_perm);
    echo '</pre></div>'
        .'<div class="box_evidence" style="float: left;">Old<br /><pre>';
    print_r($idst_old_perm);
    echo '</pre></div>';

    echo '<div class="box_evidence" style="float: left;">To add<br /><pre>';
    print_r($perm_to_add_idst);
    echo '</pre></div>'
        .'<div class="box_evidence" style="float: left;">To del<br /><pre>';
    print_r($perm_to_del_idst);
    echo '</pre></div>';
    die();*/
    //*************************************************************//
    //* Saving permission setting *********************************//
    //*************************************************************//
    $re = true;
    if (isset($_POST['load'])) {
        $re = sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_under
		SET my_name = '" . $_POST['my_name'] . "', 
			idMain = '" . $new_id_main . "'
		WHERE  	idMain = '" . $id_main . "' AND  
				idModule = '" . $id_module . "'");
    } else {
        $seq = getModuleNextSeq($_POST['id_main']);

        if ($_POST['my_name'] == $lang->def('_DEFAULT_MY_NAME')) {
            $my_name = '';
        } else {
            $my_name = $_POST['my_name'];
        }

        // Insert module in the list of this menu custom
        $re = sql_query('
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
		( idCourse, idMain, idModule, sequence, my_name ) VALUES 
		( '" . $idCourse . "', '" . $new_id_main . "', '" . $id_module . "', '" . $seq . "', '" . $my_name . "' ) ");
    }
    \FormaLms\lib\FormaUser::getCurrentUser()->loadUserSectionST();
    \FormaLms\lib\FormaUser::getCurrentUser()->saveInSession();

    Util::jump_to('index.php?modname=manmenu&op=manmodule&id_main=' . $new_id_main . '&result=' . ($re ? 1 : 0));
}

function removeModule($id_module, $id_main, $id_course)
{
    $acl_man = &\FormaLms\lib\Forma::getAclManager();

    // Load module info
    $query_module = '
	SELECT module_name, default_name, file_name, class_name, default_op 
	FROM ' . $GLOBALS['prefix_lms'] . "_module 
	WHERE idModule = '" . $id_module . "'";
    list($module_name, $name_db, $file_name, $class_name, $def_op) = sql_fetch_row(sql_query($query_module));
    $module_obj = &createModule($module_name);

    $levels = CourseLevel::getTranslatedLevels();
    $all_token = $module_obj->getAllToken();
    // corresponding of token -> idst role
    $map_idst_token = &getModuleRoleSt($module_name, $all_token);
    // corresponding of level -> idst level
    $map_idst_level = &getCourseLevelSt($id_course);
    // old permission of all module
    $actual_perm = &getAllModulesPermissionSt($map_idst_level, array_flip($map_idst_token));

    $re = true;
    foreach ($levels as $lv => $name_level) {
        $idlevel = $map_idst_level[$lv];
        foreach ($actual_perm[$lv] as $idrole => $v) {
            $acl_man->removeFromRole($idrole, $idlevel);
        }
    }
    if ($re) {
        $re = sql_query('
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
		WHERE idMain = '" . (int) $id_main . "' AND idModule = '" . (int) $id_module . "' AND idCourse = '" . $id_course . "'");
    }

    return $re;
}

function delmodule()
{
    checkPerm('mod');

    require_once _base_ . '/lib/lib.form.php';
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $out = &$GLOBALS['page'];
    $out->setWorkingZone('content');
    $id_main = FormaLms\lib\Get::req('id_main', DOTY_INT, 0);
    $id_module = FormaLms\lib\Get::req('id_module', DOTY_INT, 0);

    $lang = &FormaLanguage::createInstance('manmenu', 'framework');
    $menu_lang = &FormaLanguage::createInstance('menu_course', 'lms');

    if (isset($_POST['undo'])) {
        Util::jump_to('index.php?modname=manmenu&op=manmodule&id_main=' . $id_main);
    }

    if (isset($_POST['confirm']) || isset($_GET['confirm'])) {
        $re = removeModule($id_module, $id_main, $idCourse);

        \FormaLms\lib\FormaUser::getCurrentUser()->loadUserSectionST();
        \FormaLms\lib\FormaUser::getCurrentUser()->saveInSession();

        Util::jump_to('index.php?modname=manmenu&op=manmodule&id_main=' . $id_main . '&result=' . ($re ? 1 : 0));
    } else {
        // Load module info
        $query_module = '
		SELECT default_name 
		FROM ' . $GLOBALS['prefix_lms'] . "_module 
		WHERE idModule = '" . $id_module . "'";
        list($name_db) = sql_fetch_row(sql_query($query_module));

        $query_custom = '
		SELECT name 
		FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_main
		WHERE idMain = '" . $id_main . "'";
        list($main_title) = sql_fetch_row(sql_query($query_custom));

        $name = Lang::t($name_db, 'menu_course', false, false, $name_db);

        $page_title = [
            'index.php?modname=manmenu&amp;op=manmenu' => $lang->def('_TITLE_MANMENU'),
            'index.php?modname=manmenu&amp;op=manmodule&amp;id_main=' . $id_main => $main_title,
            $lang->def('_DEL') . ' : ' . $name,
        ];
        $strip_name = strip_tags($name);
        $out->add(
            getTitleArea($page_title, 'manmenu')
            . '<div class="std_block">'
            . Form::openForm('delcustom_form', 'index.php?modname=manmenu&amp;op=delmodule')
            . Form::getHidden('id_main', 'id_main', $id_main)
            . Form::getHidden('id_module', 'id_module', $id_module)
            . getDeleteUi($lang->def('_AREYOUSURE'),
                        '<span class="text_bold">' . $lang->def('_TITLE_MODULE') . ' : </span>' . $name,
                        false,
                        'confirm',
                        'undo')
            . Form::closeForm()
            . '</div>');
    }
}

function movemodule($direction)
{
    checkPerm('mod');

    $id_main = FormaLms\lib\Get::req('id_main', DOTY_INT, 0);
    $id_module = FormaLms\lib\Get::req('id_module', DOTY_INT, 0);

    list($seq) = sql_fetch_row(sql_query('
	SELECT sequence 
	FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
	WHERE idMain = '" . $id_main . "' AND idModule = '" . $id_module . "'"));

    if ($direction == 'up') {
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
		SET sequence = '$seq' 
		WHERE idMain = '" . $id_main . "' AND sequence = '" . ($seq - 1) . "'");
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
		SET sequence = sequence - 1 
		WHERE idMain = '" . $id_main . "' AND idModule = '" . $id_module . "'");
    }
    if ($direction == 'down') {
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
		SET sequence = '$seq' 
		WHERE idMain = '" . $id_main . "' AND sequence = '" . ($seq + 1) . "'");
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
		SET sequence = '" . ($seq + 1) . "' 
		WHERE idMain = '" . $id_main . "' AND idModule = '" . $id_module . "'");
    }
    Util::jump_to('index.php?modname=manmenu&op=manmodule&id_main=' . $id_main);
}

function fixmodule()
{
    checkPerm('mod');

    $id_main = FormaLms\lib\Get::req('id_main', DOTY_INT, 0);
    $id_custom = FormaLms\lib\Get::req('id_custom', DOTY_INT, 0);
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $query = '
	SELECT idModule 
	FROM ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
	WHERE idMain = '$id_main'
	ORDER BY sequence";
    $reField = sql_query($query);

    $i = 1;
    while (list($id) = sql_fetch_row($reField)) {
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_menucourse_under 
		SET sequence = '" . ($i++) . "' 
		WHERE idModule = '$id' AND idCourse = '" . $idCourse . "' ");
    }
    Util::jump_to('index.php?modname=manmenu&op=manmodule&id_main=' . $id_main . '&id_custom=' . $id_custom);
}

function manmenuDispatch($op)
{
    switch ($op) {
        //main menu
        case 'manmenu':
            manmenu();
         break;
        case 'addmenuvoice':
            editmenuvoice();
         break;
        case 'modmenuvoice':
            editmenuvoice(true);
         break;
        case 'savemenuvoice':
            savemenuvoice();
         break;
        case 'delmenuvoice':
            delmenuvoice();
         break;
        case 'mdmenuvoice':
            movemenuvoice('down');
         break;
        case 'mumenuvoice':
            movemenuvoice('up');
         break;
        case 'fixmenuvoice':
            fixmenuvoice();
         break;

        case 'manmodule':
            manmodule();
         break;
        case 'addmodule':
            editmodule();
         break;
        case 'modmodule':
            editmodule(true);
         break;
        case 'upmodule':
            upmodule();
         break;
        case 'delmodule':
            delmodule();
         break;
        case 'mdmodule':
            movemodule('down');
         break;
        case 'mumodule':
            movemodule('up');
         break;
        case 'fixmodule':
            fixmodule();
         break;
    }
}
