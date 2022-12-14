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

require_once _adm_ . '/lib/lib.code.php';
require_once _base_ . '/lib/lib.dialog.php';

function groupCodeList()
{
    require_once _base_ . '/lib/lib.form.php';
    require_once _base_ . '/lib/lib.table.php';

    $lang = &DoceboLanguage::createInstance('code');

    $code_manager = new CodeManager();

    $tot_group_code = $code_manager->getCodeGroupNumber();

    cout(getTitleArea($lang->def('_CODE'))
        . '<div class="std_block">');

    $result = FormaLms\lib\Get::req('result', DOTY_STRING, '');

    if (isset($_GET['activation'])) {
        $query = 'UPDATE ' . $GLOBALS['prefix_fw'] . '_setting'
                . " SET param_value = 'on'"
                . " WHERE param_name = 'use_code_module'";

        if (sql_query($query)) {
            Util::jump_to('index.php?modname=code&amp;op=list&result=ok');
        } else {
            Util::jump_to('index.php?modname=code&amp;op=list&result=err');
        }
    }

    if (FormaLms\lib\Get::cfg('use_code_module') === 'off') {
        cout(getResultUi('<a href="index.php?modname=code&amp;op=list&amp;activation=true">' . $lang->def('_MODULE_NOT_ACTIVATED') . '</a>'));
    }

    switch ($result) {
        case 'ok':
            UIFeedback::info($lang->def('_OPERATION_SUCCESSFUL'));
            break;
        case 'err':
            UIFeedback::error($lang->def('_OPERATION_FAILURE'));
            break;
    }

    if ($tot_group_code) {
        $tb = new Table('20');
        $tb->initNavBar('ini', 'link');

        $ini = $tb->getSelectedElement();

        $cont_h = [$lang->def('_TITLE'),
            $lang->def('_DESCRIPTION'),
            $lang->def('_CODE_USED_NUMBER'),
            FormaLms\lib\Get::sprite('subs_csv', Lang::t('_CODE', 'course')),
            FormaLms\lib\Get::sprite('subs_add', Lang::t('_GENERATE_CODE', 'course')),
            FormaLms\lib\Get::sprite('subs_import', Lang::t('_IMPORT', 'course')),
            FormaLms\lib\Get::sprite('subs_elem', Lang::t('_COURSES', 'course')),
            FormaLms\lib\Get::sprite('subs_users', Lang::t('_ASSIGN_USERS', 'course')),
            FormaLms\lib\Get::sprite('subs_mod', Lang::t('_MOD', 'course')),
            FormaLms\lib\Get::sprite('subs_del', Lang::t('_DEL', 'course')), ];

        $type_h = ['', '', 'min-cell', 'image', 'image', 'image', 'image', 'image', 'image', 'image'];

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        $array_group_code = $code_manager->getCodeGroupsList($ini);

        foreach ($array_group_code as $group_code_info) {
            $cont = [];

            $cont[] = $group_code_info['title'];

            $cont[] = $group_code_info['description'];

            $cont[] = $group_code_info['code_used'];

            $cont[] = '<a href="index.php?modname=code&amp;op=code_list&amp;id_code_group=' . $group_code_info['id_code_group'] . '">' . FormaLms\lib\Get::sprite('subs_csv', Lang::t('_CODE', 'course')) . '</a>';

            $cont[] = '<a href="index.php?modname=code&amp;op=generate_code&amp;id_code_group=' . $group_code_info['id_code_group'] . '">' . FormaLms\lib\Get::sprite('subs_add', Lang::t('_GENERATE_CODE', 'course')) . '</a>';

            $cont[] = '<a href="index.php?modname=code&amp;op=import_code&amp;id_code_group=' . $group_code_info['id_code_group'] . '">' . FormaLms\lib\Get::sprite('subs_import', Lang::t('_IMPORT', 'course')) . '</a>';

            $cont[] = '<a href="index.php?modname=code&amp;op=assign_course&amp;id_code_group=' . $group_code_info['id_code_group'] . '">' . FormaLms\lib\Get::sprite('subs_elem' . ($group_code_info['course_associated'] ? '' : '_grey'), Lang::t('_COURSES', 'course')) . '</a>';

            $cont[] = '<a href="index.php?modname=code&amp;op=assign_tree&amp;id_code_group=' . $group_code_info['id_code_group'] . '">' . FormaLms\lib\Get::sprite('subs_users' . ($group_code_info['folder_associated'] ? '' : '_grey'), Lang::t('_ASSIGN_USERS', 'course')) . '</a>';

            $cont[] = '<a href="index.php?modname=code&amp;op=mod_group_code&amp;id_code_group=' . $group_code_info['id_code_group'] . '">' . FormaLms\lib\Get::sprite('subs_mod', Lang::t('_MOD', 'course')) . '</a>';

            $cont[] = '<a href="index.php?modname=code&amp;op=del_group_code&amp;id_code_group=' . $group_code_info['id_code_group'] . '">' . FormaLms\lib\Get::sprite('subs_del', Lang::t('_DEL', 'course')) . '</a>';

            $tb->addBody($cont);
        }

        $tb->addActionAdd('<a href="index.php?modname=code&amp;op=add_group_code">'
                . '<img src="' . getPathImage() . 'standard/add.png" alt="' . $lang->def('_ADD') . '" />' . $lang->def('_ADD') . '</a>');

        cout($tb->getTable()
                . $tb->getNavBar($ini, $tot_group_code));

        setupHrefDialogBox('a[href*=del_group_code]');
    } else {
        cout($lang->def('_NO_CONTENT')
                . '<br/>'
                . '<a href="index.php?modname=code&amp;op=add_group_code">'
                . '<img src="' . getPathImage() . 'standard/add.png" alt="' . $lang->def('_ADD') . '" />' . $lang->def('_ADD') . '</a>');
    }

    cout('</div>');
}

function addGroupCode()
{
    require_once _base_ . '/lib/lib.form.php';

    $lang = &DoceboLanguage::createInstance('code');

    $code_manager = new CodeManager();

    cout(getTitleArea(['index.php?modname=code&amp;op=list' => $lang->def('_CODE'),
                $lang->def('_ADD'), ])
            . '<div class="std_block">');

    if (FormaLms\lib\Get::req('confirm', DOTY_MIXED, '')) {
        $title = addslashes(FormaLms\lib\Get::req('title', DOTY_MIXED, ''));
        $description = addslashes(FormaLms\lib\Get::req('description', DOTY_MIXED, ''));

        if ($code_manager->addCodeGroup($title, $description)) {
            Util::jump_to('index.php?modname=code&amp;op=list&result=ok');
        }
        Util::jump_to('index.php?modname=code&amp;op=list&result=err');
    }

    cout(Form::openForm('add_group_code_form', 'index.php?modname=code&amp;op=add_group_code')
            . Form::openElementSpace()
            . Form::getTextField($lang->def('_TITLE'), 'title', 'title', '255')
            . Form::getSimpleTextarea($lang->def('_DESCRIPTION'), 'description', 'description')
            . Form::closeElementSPace()
            . Form::openButtonSpace()
            . Form::getButton('confirm', 'confirm', $lang->def('_INSERT'))
            . Form::getButton('undo_group', 'undo_group', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
            . '</div>');
}

function modGroupCode()
{
    require_once _base_ . '/lib/lib.form.php';

    $lang = &DoceboLanguage::createInstance('code');

    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, '0');

    $code_manager = new CodeManager();

    cout(getTitleArea(['index.php?modname=code&amp;op=list' => $lang->def('_CODE'),
                $lang->def('_MOD'), ])
            . '<div class="std_block">');

    if (FormaLms\lib\Get::req('confirm', DOTY_MIXED, '')) {
        $title = addslashes(FormaLms\lib\Get::req('title', DOTY_MIXED, ''));
        $description = addslashes(FormaLms\lib\Get::req('description', DOTY_MIXED, ''));

        if ($code_manager->updateCodeGroup($id_code_group, $title, $description)) {
            Util::jump_to('index.php?modname=code&amp;op=list&result=ok');
        }
        Util::jump_to('index.php?modname=code&amp;op=list&result=err');
    }

    $group_code_info = $code_manager->getCodeGroupInfo($id_code_group);

    cout(Form::openForm('mod_group_code_form', 'index.php?modname=code&amp;op=mod_group_code')
            . Form::openElementSpace()
            . Form::getTextField($lang->def('_TITLE'), 'title', 'title', '255', $group_code_info['title'])
            . Form::getSimpleTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $group_code_info['description'])
            . Form::getHidden('id_code_group', 'id_code_group', $id_code_group)
            . Form::closeElementSPace()
            . Form::openButtonSpace()
            . Form::getButton('confirm', 'confirm', $lang->def('_MOD'))
            . Form::getButton('undo_group', 'undo_group', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
            . '</div>');
}

function delGroupCode()
{
    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, '0');

    $code_manager = new CodeManager();

    if (FormaLms\lib\Get::req('confirm', DOTY_MIXED, '')) {
        if ($code_manager->delCodeGroup($id_code_group)) {
            Util::jump_to('index.php?modname=code&amp;op=list&result=ok');
        }
        Util::jump_to('index.php?modname=code&amp;op=list&result=err');
    }
}

function codeList()
{
    require_once _base_ . '/lib/lib.form.php';
    require_once _base_ . '/lib/lib.table.php';

    $lang = &DoceboLanguage::createInstance('code');

    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, '0');

    $code_manager = new CodeManager();

    $acl_man = Docebo::user()->getAclManager();

    cout(getTitleArea(['index.php?modname=code&amp;op=list' => $lang->def('_CODE'),
                $lang->def('_CODE_LIST'), ])
            . '<div class="std_block">');

    if (isset($_POST['undo_filter'])) {
        unset($_POST['code_filter']);
    }

    cout('<div class="quick_search_form">'
            . Form::openForm('code_list_filter', 'index.php?modname=code&amp;op=code_list&amp;id_code_group=' . $id_code_group)
            . Form::getInputTextfield('search_t', 'code_filter', 'code_filter', (isset($_POST['code_filter']) ? $_POST['code_filter'] : ''), '', 255, '')
            . Form::getButton('filter', 'filter', Lang::t('_SEARCH', 'standard'), 'search_b')
            . Form::getButton('undo_filter', 'undo_filter', Lang::t('_RESET', 'standard'), 'reset_b')
            . Form::closeForm()
            . '</div>');
    $result = FormaLms\lib\Get::req('result', DOTY_STRING, '');

    switch ($result) {
        case 'ok':
            UIFeedback::info($lang->def('_OPERATION_SUCCESSFUL'));
            break;

        case 'err':
            UIFeedback::error($lang->def('_OPERATION_FAILURE'));
            break;

        case 'err_dup':
            UIFeedback::error($lang->def('_DUPLICATED_CODE'));
            break;
    }

    $tot_code = $code_manager->getCodeNumber($id_code_group, isset($_POST['undo_filter']) ? $_POST['undo_filter'] : false);

    if ($tot_code) {
        $tb = new Table('20');
        $tb->initNavBar('ini', 'link');
        $tb->setLink('index.php?modname=code&amp;op=code_list&amp;id_code_group=' . $id_code_group);

        $ini = $tb->getSelectedElement();

        $cont_h = [$lang->def('_CODE'),
            $lang->def('_USED'),
            $lang->def('_USERNAME'),
            $lang->def('_UNLIMITED_USE'),
            '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" />',
            '<img src="' . getPathImage() . 'standard/delete.png" alt="' . $lang->def('_DEL') . '" />', ];

        $type_h = ['', 'image', '', 'image', 'image', 'image'];

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        $array_code = $code_manager->getCodeList($id_code_group, $ini);

        foreach ($array_code as $code_info) {
            $cont = [];

            $cont[] = $code_info['code'];

            if ($code_info['used']) {
                $cont[] = '<img src="' . getPathImage() . 'standard/status_active.png" alt="' . $lang->def('_USED') . '" />';

                $user_info = $acl_man->getUser($code_info['id_user'], false);
                if ($user_info) {
                    $cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);
                } else {
                    $user_info = $acl_man->getTempUserInfo($code_info['id_user'], false);
                    $cont[] = $acl_man->relativeId($user_info['userid']);
                }

                if ($code_info['unlimited_use'] == '1') {
                    $cont[] = '<img src="' . getPathImage() . 'standard/status_active.png" alt="' . $lang->def('_UNLIMITED_USE') . '" />';
                } else {
                    $cont[] = '-';
                }

                $cont[] = '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" />';
            } else {
                $cont[] = '-';

                $cont[] = $lang->def('_NONE');

                if ($code_info['unlimited_use'] == '1') {
                    $cont[] = '<img src="' . getPathImage() . 'standard/status_active.png" alt="' . $lang->def('_UNLIMITED_USE') . '" />';
                } else {
                    $cont[] = '-';
                }

                $cont[] = '<a href="index.php?modname=code&amp;op=mod_code&amp;id_code_group=' . $id_code_group . '&amp;code=' . $code_info['code'] . '">'
                        . '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" />';
            }

            $cont[] = '<a href="index.php?modname=code&amp;op=del_code&amp;id_code_group=' . $id_code_group . '&amp;code=' . $code_info['code'] . '">'
                    . '<img src="' . getPathImage() . 'standard/delete.png" alt="' . $lang->def('_DEL') . '" />';

            $tb->addBody($cont);
        }

        $tb->addActionAdd('<a href="index.php?modname=code&amp;op=add_code&amp;id_code_group=' . $id_code_group . '">'
                . '<img src="' . getPathImage() . 'standard/add.png" alt="' . $lang->def('_ADD') . '" />' . $lang->def('_ADD') . '</a>');

        $tb->addActionAdd('<a class="ico-wt-sprite subs_xls" title="' . Lang::t('_EXPORT_XLS', 'report') . '" '
                . 'href="index.php?modname=code&amp;op=export&amp;id_code_group=' . $id_code_group . '&amp;format=xls">'
                . '<span>' . Lang::t('_EXPORT_XLS', 'report') . '</span></a>');

        $tb->addActionAdd('<a class="ico-wt-sprite subs_csv" title="' . Lang::t('_EXPORT_CSV', 'report') . '" '
                . 'href="index.php?modname=code&amp;op=export&amp;id_code_group=' . $id_code_group . '&amp;format=csv">'
                . '<span>' . Lang::t('_EXPORT_CSV', 'report') . '</span></a>');

        cout($tb->getTable()
                . $tb->getNavBar($ini, $tot_code));

        setupHrefDialogBox('a[href*=del_code]');
    } else {
        cout($lang->def('_NO_CODE_FOUND')
                . '<br/>'
                . '<a href="index.php?modname=code&amp;op=add_code&amp;id_code_group=' . $id_code_group . '">'
                . '<img src="' . getPathImage() . 'standard/add.png" alt="' . $lang->def('_ADD') . '" />' . $lang->def('_ADD') . '</a>');
    }

    cout('<br/><br/>'
            . getBackUi('index.php?modname=code&amp;op=list', $lang->def('_BACK'))
            . '</div>');
}

function addCode()
{
    require_once _base_ . '/lib/lib.form.php';

    $lang = &DoceboLanguage::createInstance('code');

    $code_manager = new CodeManager();

    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, '0');

    cout(getTitleArea(['index.php?modname=code&amp;op=list' => $lang->def('_CODE'),
                'index.php?modname=code&amp;op=code_list&amp;id_code_group=' . $id_code_group => $lang->def('_CODE_LIST'),
                $lang->def('_ADD'), ])
            . '<div class="std_block">');

    if (FormaLms\lib\Get::req('confirm', DOTY_MIXED, '')) {
        $code = addslashes(FormaLms\lib\Get::req('code', DOTY_MIXED, ''));
        $unlimited_use = FormaLms\lib\Get::req('unlimited_use', DOTY_BOOL, false);

        $result = $code_manager->addCode($code, $id_code_group, $unlimited_use);

        if ($result && $result !== 'dup') {
            Util::jump_to('index.php?modname=code&amp;op=code_list&id_code_group=' . $id_code_group . '&result=ok');
        } elseif ($result === 'dup') {
            Util::jump_to('index.php?modname=code&amp;op=code_list&id_code_group=' . $id_code_group . '&result=err_dup');
        }
        Util::jump_to('index.php?modname=code&amp;op=code_list&id_code_group=' . $id_code_group . '&result=err');
    }

    cout(Form::openForm('add_group_code_form', 'index.php?modname=code&amp;op=add_code')
            . Form::openElementSpace()
            . Form::getTextField($lang->def('_CODE'), 'code', 'code', '255')
            . Form::getCheckbox($lang->def('_UNLIMITED_USE'), 'unlimited_use', 'unlimited_use', true)
            . Form::getHidden('id_code_group', 'id_code_group', $id_code_group)
            . Form::closeElementSPace()
            . Form::openButtonSpace()
            . Form::getButton('confirm', 'confirm', $lang->def('_INSERT'))
            . Form::getButton('undo_code', 'undo_code', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
            . '</div>');
}

function modCode()
{
    require_once _base_ . '/lib/lib.form.php';

    $lang = &DoceboLanguage::createInstance('code');

    $code_manager = new CodeManager();

    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, '0');
    $code = stripslashes(FormaLms\lib\Get::req('code', DOTY_MIXED, ''));
    $is_unlimited = $code_manager->codeIsUnlimited($code);

    cout(getTitleArea(['index.php?modname=code&amp;op=list' => $lang->def('_CODE'),
                'index.php?modname=code&amp;op=code_list&amp;id_code_group=' . $id_code_group => $lang->def('_CODE_LIST'),
                $lang->def('_MOD'), ])
            . '<div class="std_block">');

    if (FormaLms\lib\Get::req('confirm', DOTY_MIXED, '')) {
        $code = addslashes(FormaLms\lib\Get::req('code', DOTY_MIXED, ''));
        $old_code = addslashes(FormaLms\lib\Get::req('old_code', DOTY_MIXED, ''));
        $unlimited_use = FormaLms\lib\Get::req('unlimited_use', DOTY_BOOL, false);

        $result = $code_manager->modCode($code, $old_code, $unlimited_use);

        if ($result && $result !== 'dup') {
            Util::jump_to('index.php?modname=code&amp;op=code_list&id_code_group=' . $id_code_group . '&result=ok');
        } elseif ($result === 'dup') {
            Util::jump_to('index.php?modname=code&amp;op=code_list&id_code_group=' . $id_code_group . '&result=err_dup');
        }
        Util::jump_to('index.php?modname=code&amp;op=code_list&id_code_group=' . $id_code_group . '&result=err');
    }

    cout(Form::openForm('add_group_code_form', 'index.php?modname=code&amp;op=mod_code')
            . Form::openElementSpace()
            . Form::getTextField($lang->def('_CODE'), 'code', 'code', '255', $code)
            . Form::getCheckbox($lang->def('_UNLIMITED_USE'), 'unlimited_use', 'unlimited_use', true, $is_unlimited)
            . Form::getHidden('old_code', 'old_code', $code)
            . Form::getHidden('id_code_group', 'id_code_group', $id_code_group)
            . Form::closeElementSPace()
            . Form::openButtonSpace()
            . Form::getButton('confirm', 'confirm', $lang->def('_MOD'))
            . Form::getButton('undo_code', 'undo_code', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
            . '</div>');
}

function delCode()
{
    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, '0');
    $code = stripslashes(FormaLms\lib\Get::req('code', DOTY_MIXED, '0'));

    $code_manager = new CodeManager();

    if (FormaLms\lib\Get::req('confirm', DOTY_MIXED, '')) {
        if ($code_manager->delCode($code)) {
            Util::jump_to('index.php?modname=code&amp;op=code_list&id_code_group=' . $id_code_group . '&result=ok');
        }
        Util::jump_to('index.php?modname=code&amp;op=code_list&id_code_group=' . $id_code_group . '&result=err');
    }
}

function assignCourse()
{
    require_once _base_ . '/lib/lib.form.php';
    require_once _lms_ . '/lib/lib.course.php';

    $lang = &DoceboLanguage::createInstance('code');

    $code_manager = new CodeManager();

    $selector = new Selector_Course();

    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, '0');

    if (FormaLms\lib\Get::req('confirm', DOTY_MIXED, '')) {
        $selector->parseForState($_POST);
        $course_selected = $selector->getSelection();

        if ($code_manager->insertCourseAssociation($course_selected, $id_code_group)) {
            Util::jump_to('index.php?modname=code&amp;op=list&result=ok');
        }
        Util::jump_to('index.php?modname=code&amp;op=list&result=err');
    }

    $array_course_associated = $code_manager->getCourseAssociated($id_code_group);

    $selector->resetSelection($array_course_associated);

    cout(getTitleArea(['index.php?modname=code&amp;op=list' => $lang->def('_CODE'),
                $lang->def('_COURSES'), ])
            . '<div class="std_block">');

    $selector->parseForState($_POST);

    cout(Form::openForm('add_group_code_form', 'index.php?modname=code&amp;op=assign_course&amp;id_code_group=' . $id_code_group)
            . $selector->loadCourseSelector(true));

    cout(Form::openButtonSpace()
            . Form::getButton('confirm', 'confirm', $lang->def('_INSERT'))
            . Form::getButton('undo_group', 'undo_group', $lang->def('_UNDO'))
            . Form::closeButtonSpace());

    cout('</div>');
}

function assignTree()
{
    require_once _base_ . '/lib/lib.form.php';
    require_once _base_ . '/lib/lib.userselector.php';

    $lang = &DoceboLanguage::createInstance('code');

    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, '0');

    $code_manager = new CodeManager();

    $selector = new UserSelector();

    $selector->show_user_selector = false;
    $selector->show_group_selector = false;
    $selector->show_orgchart_selector = true;
    $selector->show_orgchart_simple_selector = true;

    $array_user_associated = $code_manager->getOrgAssociated($id_code_group);

    $selector->resetSelection($array_user_associated);

    if (FormaLms\lib\Get::req('okselector', DOTY_MIXED, '')) {
        $folder_selected = $selector->getSelection($_POST);

        if ($code_manager->insertOrgAssociation($folder_selected, $id_code_group)) {
            Util::jump_to('index.php?modname=code&amp;op=list&result=ok');
        }
        Util::jump_to('index.php?modname=code&amp;op=list&result=err');
    }

    cout($selector->loadSelector('index.php?modname=code&amp;op=assign_tree&amp;id_code_group=' . $id_code_group, ['index.php?modname=code&amp;op=list' => $lang->def('_CODE'), $lang->def('_ASSIGN_USERS')], ''));

    cout('</div>');
}

function importCode_step1()
{
    require_once _base_ . '/lib/lib.form.php';

    $lang = &DoceboLanguage::createInstance('code');

    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, '0');

    cout(getTitleArea(['index.php?modname=code&amp;op=list' => $lang->def('_CODE'),
                $lang->def('_IMPORT'), ])
            . '<div class="std_block">');

    $lang = &DoceboLanguage::createInstance('admin_directory', 'framework');

    cout(Form::openForm('directory_importgroupuser',
                    'index.php?modname=code&amp;op=import_code_2',
                    false,
                    false,
                    'multipart/form-data'));

    cout(Form::getFilefield($lang->def('_GROUP_USER_IMPORT_FILE'), 'file_import', 'file_import')
            //.Form::getTextfield($lang->def('_GROUP_USER_IMPORT_SEPARATOR'), 'import_separator', 'import_separator', 1, ',')
            . Form::getCheckbox($lang->def('_GROUP_USER_IMPORT_HEADER'), 'import_first_row_header', 'import_first_row_header', 'true')
            . Form::getTextfield($lang->def('_GROUP_USER_IMPORT_CHARSET'), 'import_charset', 'import_charset', 20, 'UTF-8')
            . Form::getHidden('id_code_group', 'id_code_group', $id_code_group)
            . Form::closeElementSpace()
            . Form::openButtonSpace()
            . Form::getButton('import_code_2', 'import_code_2', $lang->def('_IMPORT'))
            . Form::getButton('undo_group', 'undo_group', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
            . Form::closeForm());

    cout('</div>');
}

function importCode_step2()
{
    require_once _base_ . '/lib/lib.upload.php';
    require_once _adm_ . '/lib/lib.import.php';
    require_once _base_ . '/lib/lib.table.php';

    if ($_FILES['file_import']['name'] == '') {
        Util::jump_to('index.php?modname=code&amp;op=list&result=err');
    } else {
        $path = '/appCore/';

        $savefile = mt_rand(0, 100) . '_' . time() . '_' . $_FILES['file_import']['name'];

        if (!file_exists(_files_ . $path . $savefile)) {
            sl_open_fileoperations();

            if (!sl_upload($_FILES['file_import']['tmp_name'], $path . $savefile)) {
                sl_close_fileoperations();

                Util::jump_to('index.php?modname=code&amp;op=list&result=err');
            }

            sl_close_fileoperations();
        } else {
            Util::jump_to('index.php?modname=directory&amp;op=listgroup&import_result=-1');
        }
    }

    $lang = &DoceboLanguage::createInstance('code');

    $code_manager = new CodeManager();

    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, '0');

    cout(getTitleArea($lang->def('_CODE'))
            . '<div class="std_block">');

    $separator = FormaLms\lib\Get::req('import_separator', DOTY_MIXED, ',');
    $first_row_header = isset($_POST['import_first_row_header']) ? ($_POST['import_first_row_header'] == 'true') : false;
    $import_charset = FormaLms\lib\Get::req('import_charset', DOTY_MIXED, 'UTF-8');
    if (trim($import_charset) === '') {
        $import_charset = 'UTF-8';
    }

    $src = new DeceboImport_SourceCSV(['filename' => _files_ . $path . $savefile,
                'separator' => $separator,
                'first_row_header' => $first_row_header,
                'import_charset' => $import_charset, ]);

    $src->connect();

    $code_added = [];
    $code_error = [];
    $code_present = [];

    if (is_array($row = $src->get_first_row()) && !empty($row)) {
        $code = addslashes($row[0]);

        $result = $code_manager->addCode($code, $id_code_group);

        if ($result === 'dup') {
            $code_present[] = $code;
        } elseif ($result) {
            $code_added[] = $code;
        } else {
            $code_error[] = $code;
        }
    }

    while (is_array($row = $src->get_next_row()) && !empty($row)) {
        $code = addslashes($row[0]);

        $result = $code_manager->addCode($code, $id_code_group);

        if ($result === 'dup') {
            $code_present[] = $code;
        } elseif ($result) {
            $code_added[] = $code;
        } else {
            $code_error[] = $code;
        }
    }

    $src->close();
    unset($row);

    sl_open_fileoperations();

    sl_unlink($path . $savefile);

    sl_close_fileoperations();

    cout(getBackUi('index.php?modname=code&amp;op=list', $lang->def('_BACK'))
            . '<br/>'
            . $lang->def('_CODE_ADDED') . ' : ' . count($code_added)
            . '<br/>'
            . $lang->def('_CODE_PRESENT') . ' : ' . count($code_present)
            . '<br/>'
            . $lang->def('_CODE_ERROR') . ' : ' . count($code_error)
            . '<br/>');

    if (count($code_present)) {
        $tb = new Table(false, $lang->def('_CODE_PRESENT'), $lang->def('_CODE_PRESENT'));

        $type_h = ['align_center'];
        $cont_h = [$lang->def('_CODE')];

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        foreach ($code_present as $code) {
            $cont = [];

            $cont[] = stripslashes($code);

            $tb->addBody($cont);
        }

        cout($tb->getTable());
    }

    if (count($code_error)) {
        $tb = new Table(false, $lang->def('_CODE_ERROR'), $lang->def('_CODE_ERROR'));

        $type_h = ['align_center'];
        $cont_h = [$lang->def('_CODE')];

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        foreach ($code_error as $code) {
            $cont = [];

            $cont[] = stripslashes($code);

            $tb->addBody($cont);
        }

        cout($tb->getTable());
    }

    cout(getBackUi('index.php?modname=code&amp;op=list', $lang->def('_BACK'))
            . '</div>');
}

function generateCode()
{
    require_once _base_ . '/lib/lib.form.php';

    $step = FormaLms\lib\Get::req('step', DOTY_INT, 1);
    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, 0);

    cout(getTitleArea(Lang::t('_CODE', 'code'))
            . '<div class="std_block">');

    switch ($step) {
        case 1:
            cout(Form::openForm('code_generation_step_1', 'index.php?modname=code&amp;op=generate_code&amp;step=2')
                    . Form::getHidden('id_code_group', 'id_code_group', $id_code_group)
                    . Form::openElementSpace()
                    . Form::getTextfield(Lang::t('_HOW_MANY', 'code'), 'code_number', 'code_number', 255, '')
                    . Form::getCheckbox(Lang::t('_UNLIMITED_USE', 'code'), 'unlimited_use', 'unlimited_use', '1')
                    . '<br />'
                    . Form::getCheckbox('0-9', 'use_number', 'use_number', '1', true)
                    . Form::getCheckbox('a-z', 'use_low_letter', 'use_low_letter', '1', true)
                    . Form::getCheckbox('A-Z', 'use_high_letter', 'use_high_letter', '1', true)
                    . Form::closeElementSpace()
                    . Form::openButtonSpace()
                    . Form::getButton('generate', 'generate', Lang::t('_GENERATE', 'code'))
                    . Form::getButton('undo_group', 'undo_group', Lang::t('_UNDO', 'code'))
                    . Form::closeButtonSpace()
                    . Form::closeForm());
            break;

        case 2:
            require_once _adm_ . '/lib/lib.code.php';

            $code_man = new CodeManager();

            $code_number = FormaLms\lib\Get::req('code_number', DOTY_INT, 0);
            $unlimited_use = FormaLms\lib\Get::req('unlimited_use', DOTY_INT, 0);
            $use_number = FormaLms\lib\Get::req('use_number', DOTY_INT, 0);
            $use_low_letter = FormaLms\lib\Get::req('use_low_letter', DOTY_INT, 0);
            $use_high_letter = FormaLms\lib\Get::req('use_high_letter', DOTY_INT, 0);

            if ($unlimited_use == 0) {
                $unlimited_use = false;
            } else {
                $unlimited_use = true;
            }

            if ($use_number == 0) {
                $use_number = false;
            } else {
                $use_number = true;
            }

            if ($use_low_letter == 0) {
                $use_low_letter = false;
            } else {
                $use_low_letter = true;
            }

            if ($use_high_letter == 0) {
                $use_high_letter = false;
            } else {
                $use_high_letter = true;
            }

            if (!$use_number && !$use_low_letter && !$use_high_letter) {
                jumpTo('index.php?modname=code&op=generate_code&id_code_group=' . $id_code_group . '&err=no_char');
            }

            $all_code = $code_man->getAllCode();

            for ($i = 1; $i <= $code_number; ++$i) {
                $control = true;

                while ($control) {
                    $new_code = '';

                    if ($use_number && $use_low_letter && $use_high_letter) {
                        for ($a = 0; $a < 10; ++$a) {
                            $seed = mt_rand(0, 15);

                            if ($seed > 10) {
                                $new_code .= mt_rand(0, 9);
                            } elseif ($seed > 5) {
                                $new_code .= chr(mt_rand(65, 90));
                            } else {
                                $new_code .= chr(mt_rand(97, 122));
                            }
                        }
                    }

                    // BUG LRZ
                    //elseif ($use_number && $use_low_letter) {
                    elseif ($use_number && $use_high_letter) {
                        for ($a = 0; $a < 10; ++$a) {
                            $seed = mt_rand(0, 10);

                            if ($seed > 5) {
                                $new_code .= mt_rand(0, 9);
                            } else {
                                $new_code .= chr(mt_rand(65, 90));
                            }
                        }
                    }

                    //LRZ
                    //elseif ($use_number && $use_high_letter) {
                    elseif ($use_number && $use_low_letter) {
                        for ($a = 0; $a < 10; ++$a) {
                            $seed = mt_rand(0, 10);

                            if ($seed > 5) {
                                $new_code .= mt_rand(0, 9);
                            } else {
                                $new_code .= chr(mt_rand(97, 122));
                            }
                        }
                    } elseif ($use_low_letter && $use_high_letter) {
                        for ($a = 0; $a < 10; ++$a) {
                            $seed = mt_rand(0, 10);

                            if ($seed > 5) {
                                $new_code .= chr(mt_rand(65, 90));
                            } else {
                                $new_code .= chr(mt_rand(97, 122));
                            }
                        }
                    } else {
                        if ($use_number) {
                            for ($a = 0; $a < 10; ++$a) {
                                $new_code .= mt_rand(0, 9);
                            }
                        }

                        // LRZ
                        //if ($use_low_letter)
                        if ($use_high_letter) {
                            for ($a = 0; $a < 10; ++$a) {
                                $new_code .= chr(mt_rand(65, 90));
                            }
                        }

                        // LRZ
                        //if ($use_high_letter)
                        if ($use_low_letter) {
                            for ($a = 0; $a < 10; ++$a) {
                                $new_code .= chr(mt_rand(97, 122));
                            }
                        }
                    }

                    if (array_search($new_code, $all_code) === false) {
                        $all_code[] = $new_code;
                        $code_man->addCode($new_code, $id_code_group, $unlimited_use);
                        $control = false;
                    }
                }
            }

            Util::jump_to('index.php?modname=code&amp;op=list');
            break;
    }

    cout('</div>');
}

function export()
{
    $id_code_group = FormaLms\lib\Get::req('id_code_group', DOTY_INT, 0);

    if ($id_code_group <= 0) {
        return Lang::t('_INVALID_ID_CODE_GROUP', 'code');
    }

    //retrieve data to export
    $code_manager = new CodeManager();
    $acl_man = Docebo::user()->getAclManager();
    $codeGroupInfo = $code_manager->getCodeGroupInfo($id_code_group);
    $array_code = $code_manager->getCodeList($id_code_group, 0, false);

    //prepare csv file
    require_once _base_ . '/lib/lib.download.php';
    $format = FormaLms\lib\Get::req('format', DOTY_STRING, 'csv');

    $buffer = '';
    $filename = preg_replace('/[\W]/i', '_', $codeGroupInfo['title']) . '_' . date('Y_m_d') . '.' . $format;

    $_CSV_SEPARATOR = ',';
    $_CSV_ENDLINE = "\r\n";
    $_XLS_STARTLINE = '<tr><td>';
    $_XLS_SEPARATOR = '</td><td>';
    $_XLS_ENDLINE = '</td></tr>';

    //prepare the data for exporting
    if (is_array($array_code) && count($array_code) > 0) {
        if ($format == 'xls') {
            $buffer .= '<head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><style>td, th { border:solid 1px black; } </style><body><table>';
            $buffer .= '<thead>' . $_XLS_STARTLINE . Lang::t('_CODE', 'code') . $_XLS_SEPARATOR . Lang::t('_USED', 'code') . $_XLS_SEPARATOR . Lang::t('_USERNAME', 'code') . $_XLS_SEPARATOR . Lang::t('_UNLIMITED_USE', 'code') . $_XLS_ENDLINE . '</thead>';
        } else {
            $buffer .= Lang::t('_CODE', 'code') . $_CSV_SEPARATOR . Lang::t('_USED', 'code') . $_CSV_SEPARATOR . Lang::t('_USERNAME', 'code') . $_CSV_SEPARATOR . Lang::t('_UNLIMITED_USE', 'code') . $_CSV_ENDLINE;
        }

        foreach ($array_code as $code_info) {
            $line = [];

            $line[] = $code_info['code'];

            if ($code_info['used']) {
                $line[] = '1';

                $user_info = $acl_man->getUser($code_info['id_user'], false);
                if ($user_info) {
                    $line[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);
                } else {
                    $user_info = $acl_man->getTempUserInfo($code_info['id_user'], false);
                    $line[] = $acl_man->relativeId($user_info['userid']);
                }

                if ($code_info['unlimited_use'] == '1') {
                    $line[] = '1';
                } else {
                    $line[] = '0';
                }
            } else {
                $line[] = '0';

                $line[] = Lang::t('_NONE', 'code');

                if ($code_info['unlimited_use'] == '1') {
                    $line[] = '1';
                } else {
                    $line[] = '0';
                }
            }

            if ($format == 'xls') {
                $buffer .= $_XLS_STARTLINE;
                $buffer .= str_replace('"', '', implode($_XLS_SEPARATOR, $line)) . $_CSV_ENDLINE;
            } else {
                $buffer .= implode($_CSV_SEPARATOR, $line) . $_CSV_ENDLINE;
            }
        }
        if ($format == 'xls') {
            $buffer .= '</table></body>';
        }
    }

    sendStrAsFile($buffer, $filename);
}

function codeDispatch($op)
{
    checkPerm('view');

    if (FormaLms\lib\Get::req('undo_group', DOTY_MIXED, '') || FormaLms\lib\Get::req('cancelselector', DOTY_MIXED, '')) {
        $op = 'list';
    }

    if (FormaLms\lib\Get::req('undo_code', DOTY_MIXED, '')) {
        $op = 'code_list';
    }

    switch ($op) {
        case 'add_group_code':
            addGroupCode();
            break;
        case 'mod_group_code':
            modGroupCode();
            break;
        case 'del_group_code':
            delGroupCode();
            break;
        case 'code_list':
            codeList();
            break;
        case 'add_code':
            addCode();
            break;
        case 'mod_code':
            modCode();
            break;
        case 'del_code':
            delCode();
            break;
        case 'assign_course':
            assignCourse();
            break;
        case 'assign_tree':
            assignTree();
            break;
        case 'generate_code':
            generateCode();
            break;
        case 'import_code':
            importCode_step1();
            break;
        case 'import_code_2':
            importCode_step2();
            break;
        case 'export':
            export();
            break;
        default:
        case 'list':
            groupCodeList();
            break;
    }
}
