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

if (!\FormaLms\lib\FormaUser::getCurrentUser()->isAnonymous()) {
    function group()
    {
        checkPerm('view');
        require_once _base_ . '/lib/lib.form.php';
        require_once _base_ . '/lib/lib.navbar.php';
        require_once _base_ . '/lib/lib.table.php';
        $lang = &FormaLanguage::createInstance('standard', 'lms');

        $field_search = importVar('field_search');
        $search = (isset($_POST['search']) && $_POST['search'] != '' ? $_POST['search'] : false);
        if (isset($_POST['clear'])) {
            $search = false;
        }

        $acl_man = &\FormaLms\lib\Forma::getAclManager();
        $acl = \FormaLms\lib\Forma::getAclManager();;
        $groups = &$acl_man->getAllGroupsId(['free', 'moderate', 'private'],
                                                $search);

        $user_group_wait = &$acl_man->getPendingGroupOfUser(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());

        $hidden_group = [];
        if (!isset($_POST['working'])) {
            $user_group = $acl->getSTGroupsST(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());
            $user_group = array_flip($user_group);
        } else {
            if (isset($_POST['group_sel'])) {
                $user_group = $_POST['group_sel'];
                $hidden_group = array_diff($user_group, $groups);
            } else {
                $user_group = [];
            }
        }
        $GLOBALS['page']->add(
        getTitleArea($lang->def('_MYGROUP'), 'mygroup')
        . '<div class="std_block">'
        . Form::openForm('group_subscription', 'index.php?modname=mygroup&amp;op=group')
        . Form::getHidden('working', 'working', 1)
        . Form::getOpenFieldset($lang->def('_FILTER'))
        . Form::getTextfield($lang->def('_SEARCH_ARG'), 'search', 'search', '255',
            (isset($_POST['search']) && !isset($_POST['clear']) ? $_POST['search'] : ''))
        . Form::openButtonSpace()
        . Form::getButton('search_button', 'search_button', $lang->def('_SEARCH'))
        . ($search ? Form::getButton('clear', 'clear', $lang->def('_CLEAR')) : '')
        . Form::closeButtonSpace()
        . Form::getCloseFieldset()
        . Form::closeForm(), 'content');
        if (is_array($hidden_group)) {
            foreach ($hidden_group as $id) {
                $GLOBALS['page']->add(Form::getHidden('group_sel_' . $id, 'group_sel[' . $id . ']', $id), 'content');
            }
        }
        if (!empty($groups)) {
            $tb = new Table(0, $lang->def('_MYGROUP_CAPTION'), $lang->def('_MYGROUP_SUMMARY'));

            $type_h = ['image', 'nowrap', ''];
            $cont_h = ['<span class="access-only">' . $lang->def('_SUBSCRIBE') . '</span>',
                        $lang->def('_NAME'),
                        $lang->def('_DESCRIPTION'), ];
            $tb->setColsStyle($type_h);
            $tb->addHead($cont_h);
            foreach ($groups as $id => $info) {
                $cont = [];

                if (isset($user_group[$id])) {
                    $cont[] = Form::getInputCheckbox('group_sel_' . $id,
                                            'group_sel[' . $id . ']',
                                            $id,
                                            isset($user_group[$id]),
                                            '');
                    $cont[] = $info['type_ico'] . ' ' . $info['groupid'] . '</label>';
                    $cont[] = $info['description'];
                } elseif (isset($user_group_wait[$id])) {
                    $cont[] = '<img src="' . getPathImage() . 'standard/userwait.gif" alt="' . $lang->def('_WAITING') . '" />';
                    $cont[] = '<label for="group_sel_' . $id . '">' . $info['type_ico'] . ' ' . $info['groupid'] . '</label>';
                    $cont[] = $info['description'];
                } elseif ($info['type'] != 'private' && $info['type'] != 'invisible') {
                    $cont[] = Form::getInputCheckbox('group_sel_' . $id,
                                            'group_sel[' . $id . ']',
                                            $id,
                                            isset($user_group[$id]),
                                            '');
                    $cont[] = '<label for="group_sel_' . $id . '">' . $info['type_ico'] . ' ' . $info['groupid'] . '</label>';
                    $cont[] = $info['description'];
                }
                $tb->addBody($cont);
            }
            $GLOBALS['page']->add(
            Form::openForm('group_subscription_sec', 'index.php?modname=mygroup&amp;op=savesel')
            . Form::getHidden('search_hidden', 'search', '255', $search)
            . $tb->getTable()
            . Form::openButtonSpace()
            . Form::getButton('save', 'save', $lang->def('_SAVE'))
            . Form::getButton('undo', 'undo', $lang->def('_UNDO'))
            . Form::closeButtonSpace()
            . Form::closeForm(), 'content');
        }
        $GLOBALS['page']->add(
            '</div>', 'content');
    }

    function savesel()
    {
        checkPerm('view');

        require_once _adm_ . '/lib/lib.field.php';
        require_once _base_ . '/lib/lib.form.php';
        $lang = &FormaLanguage::createInstance('register', 'lms');

        $mand_sym = '<span class="mandatory">*</span>';
        $extra_field = new FieldList();

        $GLOBALS['page']->add(
        getTitleArea($lang->def('_MYGROUP'), 'mygroup')
        . '<div class="std_block">', 'content');

        $selected = [];
        if (isset($_POST['group_sel'])) {
            $selected = $_POST['group_sel'];
        } elseif (isset($_POST['group_sel_implode'])) {
            $selected = explode(',', $_POST['group_sel_implode']);
        }

        $play_field = $extra_field->playFieldsForUser(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(),
                                            $selected,
                                            false,
                                            false,
                                            ['readonly']);

        if (isset($_POST['save_field']) || $play_field === false || $play_field == '') {
            $re_filled = $extra_field->isFilledFieldsForUser(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(),
                                                            $selected);
            if (!$re_filled) {
                $GLOBALS['page']->add(getErrorUi($lang->def('_SOME_MANDATORY_EMPTY')), 'content');
            } else {
                $acl = \FormaLms\lib\Forma::getAclManager();;
                $acl_man = &\FormaLms\lib\Forma::getAclManager();

                $groups = &$acl_man->getAllGroupsId(['free', 'moderate']);
                $groups_id = array_keys($groups);
                $user_group = $acl->getSTGroupsST(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());

                $add_groups = array_diff($selected, $user_group);
                $del_groups = array_diff($groups_id, $selected);

                $moderate_add = false;
                if (!empty($add_groups)) {
                    foreach ($add_groups as $idst) {
                        if ($groups[$idst]['type'] == 'free') {
                            $acl_man->addToGroup($idst, \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());
                        } elseif ($groups[$idst]['type'] == 'moderate') {
                            $acl_man->addToWaitingGroup($idst, \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());
                            $moderate_add = true;
                        }
                    }
                }
                if ($moderate_add === true) {
                    require_once _base_ . '/lib/lib.eventmanager.php';

                    // message to user that is odified
                    $msg_composer = new EventMessageComposer();

                    $msg_composer->setSubjectLangText('email', '_TO_APPROVE_GROUP_USER_SBJ', false);
                    $msg_composer->setBodyLangText('email', '_TO_APPROVE_GROUP_USER_TEXT', ['[url]' => FormaLms\lib\Get::site_url()]);

                    $msg_composer->setBodyLangText('sms', '_TO_APPROVE_GROUP_USER_TEXT_SMS', ['[url]' => FormaLms\lib\Get::site_url()]);
                    $idst_approve = $acl->getRoleST('/framework/admin/directory/editgroup');
                    $recipients = $acl_man->getAllRoleMembers($idst_approve);

                    createNewAlert('UserGroupModerated', 'directory', 'moderate', '1', 'User group subscription to moderate',
                            $recipients, $msg_composer);
                }
                if (!empty($del_groups)) {
                    foreach ($del_groups as $idst_group) {
                        $extra_field->removeUserEntry(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(), $idst_group);
                        $acl_man->removeFromGroup($idst_group, \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());
                    }
                }
                // Save fields
                $extra_field->storeFieldsForUser(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());
                Util::jump_to('index.php?modname=mygroup&amp;op=group');
            }
        }

        $GLOBALS['page']->add(
        '<div class="reg_note">'
        . $lang->def('_GROUPS_FIELDS')
        . '<ul class="reg_instruction">'
            . '<li>' . str_replace('[mandatory]', $mand_sym, $lang->def('_REG_MANDATORY')) . '</li>'
        . '</ul>'
        . '</div>'
        . Form::openForm('group_subscription', 'index.php?modname=mygroup&amp;op=savesel')
        . Form::openElementSpace()
        . Form::getHidden('group_sel_implode', 'group_sel_implode',
            (isset($_POST['group_sel_implode']) ? $_POST['group_sel_implode'] : implode(',', $selected)))
        . $play_field
        . Form::getBreakRow()
        . Form::closeElementSpace()
        . Form::openButtonSpace()
        . Form::getButton('save_field', 'save_field', $lang->def('_SAVE'))
        . Form::closeButtonSpace()
        . Form::closeForm()
        . '</div>', 'content');
    }

    function mygroupDispatch($op)
    {
        if (isset($_POST['undo'])) {
            $op = 'group';
        }
        if (isset($_POST['save'])) {
            $op = 'savesel';
        }

        switch ($op) {
        case 'group':
            group();
         break;
        case 'savesel':
            savesel();
         break;
    }
    }
}
