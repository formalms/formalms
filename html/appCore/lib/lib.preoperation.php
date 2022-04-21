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

// access granted only if user is logged in
if (Docebo::user()->isAnonymous()) {
    // save requested page in session to call it after login
    $loginRedirect = $_SERVER[REQUEST_URI];

    // redirect to index

    Util::jump_to(Forma\lib\Get::rel_path('base') . '/index.php?login_redirect=' . $loginRedirect);
}

// get maintenence setting
$query = ' SELECT param_value FROM %adm_setting'
        . " WHERE param_name = 'maintenance'"
        . ' ORDER BY pack, sequence';
$maintenance = $db->fetch_row($db->query($query))[0];

// handling maintenece
if ($maintenance == 'on' && Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
    // only god admins can access maintenence - logout the user
    Util::jump_to(Forma\lib\Get::rel_path('base') . '/index.php?r=' . _logout_);
}

// setting of platform
if (isset($_GET['of_platform']) || isset($_POST['of_platform'])) {
    $_SESSION['current_action_platform'] = Forma\lib\Get::req('of_platform');
}

// handling required password renewal
if (isset($_SESSION['must_renew_pwd']) && $_SESSION['must_renew_pwd'] == 1
        && Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
    // redirect to lms where password renewal is performed
    Util::jump_to(Forma\lib\Get::rel_path('lms'));
}

// close over
if (isset($_GET['close_over'])) {
    $_SESSION['menu_over']['p_sel'] = '';
    $_SESSION['menu_over']['main_sel'] = 0;
}
