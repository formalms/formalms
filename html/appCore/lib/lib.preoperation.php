<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

$session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
// access granted only if user is logged in
if (\FormaLms\lib\FormaUser::getCurrentUser()->isAnonymous()) {
    // save requested page in session to call it after login
    $loginRedirect = $_SERVER['REQUEST_URI'];

    // redirect to index

    Util::jump_to(FormaLms\lib\Get::rel_path('base') . '/index.php?login_redirect=' . $loginRedirect);
}

// get maintenence setting

$maintenance = \FormaLms\lib\Get::sett('maintenance');

// handling maintenece
if ($maintenance == 'on' && \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
    // only god admins can access maintenence - logout the user
    Util::jump_to(FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _logout_);
}

// setting of platform
if (!empty(FormaLms\lib\Get::req('of_platform', DOTY_ALPHANUM, ''))) {
    $session->set('current_action_platform', FormaLms\lib\Get::req('of_platform', DOTY_ALPHANUM, ''));
    $session->save();
}

// handling required password renewal
if (!$session->has('must_renew_pwd') && $session->get('must_renew_pwd') == 1
        && \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
    // redirect to lms where password renewal is performed
    Util::jump_to(FormaLms\lib\Get::rel_path('lms'));
}

// close over
if (!empty(FormaLms\lib\Get::req('close_over', DOTY_MIXED, ''))) {
    $session->set('menu_over', ['p_sel' => '', 'main_sel' => 0]);
    $session->save();
}
