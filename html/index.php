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
define('IN_FORMA', true);
define('_deeppath_', '');
require __DIR__ . '/base.php';

// start buffer
ob_start();

// initialize
require _lib_ . '/lib.bootstrap.php';

// force_standard mode
if (isset($_REQUEST['notuse_plugin'])) {
    $GLOBALS['notuse_plugin'] = true;
}
if (isset($_REQUEST['notuse_customscript'])) {
    $GLOBALS['notuse_customscript'] = true;
}
if (isset($_REQUEST['notuse_template'])) {
    $GLOBALS['notuse_template'] = true;
}

Boot::init(CHECK_SYSTEM_STATUS);

// connect to the database
$db = DbConn::getInstance();

// -----------------------------------------------------------------------------

// get maintenence setting
$query = ' SELECT param_value FROM %adm_setting'
    . " WHERE param_name = 'maintenance'"
    . ' ORDER BY pack, sequence';

$maintenance = $db->fetch_row($db->query($query))[0];

if ($maintenance === 'on') {
    // get maintenence password
    $query = ' SELECT param_value FROM %adm_setting'
        . " WHERE param_name = 'maintenance_pw'"
        . ' ORDER BY pack, sequence';

    $maintenancePassword = $db->fetch_row($db->query($query))[0];

    $password = FormaLms\lib\Get::req('passwd', DOTY_STRING, '');

    if ($maintenancePassword !== $password) {
        // access maintenence denied - login will not appear
        $GLOBALS['block_for_maintenance'] = true;
    } else {
        $GLOBALS['block_for_maintenance'] = false;
    }
}

// old SSO-URL backward compatibility
$sso = FormaLms\lib\Get::req('login_user', DOTY_MIXED, false) && FormaLms\lib\Get::req('time', DOTY_MIXED, false) && FormaLms\lib\Get::req('token', DOTY_MIXED, false);

// get required action - default: homepage if not logged in, no action if logged in
$req = FormaLms\lib\Get::req('r', DOTY_MIXED, ($sso ? _sso_ : (Forma::user()->isAnonymous() ? _homepage_ : false)));

$req = preg_replace('/[^a-zA-Z0-9\-\_\/]+/', '', $req);

$explodedRequest = (array) explode('/', $req);
if (count($explodedRequest) < 3) {
    if (Forma::user()->isLoggedIn()) {
        Util::jump_to(FormaLms\lib\Get::rel_path('lms'));
    }
    Util::jump_to(FormaLms\lib\Get::rel_path('base'));
}

[$platform, $mvcName, $task] = $explodedRequest;

$requestedRoute = sprintf('%s/%s', $platform, $mvcName);

$allowedControllers = [
    _homepage_base_,
    _homecatalog_base_,
    _system_base_,
];

$templatesToRender = [
    _homepage_base_ => 'home',
    _homecatalog_base_ => 'home_catalogue',
    _system_base_ => 'system',
];

if ($req) {
    $eventData = Events::trigger('lms.index.loading', ['allowedControllers' => $allowedControllers, 'templatesToRender' => $templatesToRender]);

    $allowedControllers = $eventData['allowedControllers'];
    $templatesToRender = $eventData['templatesToRender'];
    if (!in_array($requestedRoute, $allowedControllers, true)) {
        // reload
        Util::jump_to(FormaLms\lib\Get::rel_path('base'));
    }

    // instance page writer
    onecolPageWriter::createInstance();

    // get mvc structure
    $mvcClass = ucfirst(strtolower($mvcName)) . ucfirst(strtolower($platform)) . 'Controller';

    ob_clean();

    // execute requested task
    $controller = new $mvcClass($mvcName);
    $controller->request($task);

    // add content to page
    $GLOBALS['page']->add(ob_get_contents(), 'content');
    ob_clean();
} else {
    // redirect to requested page (default: lms index)

    Util::jump_to(_folder_lms_ . DIRECTORY_SEPARATOR);
}

// -----------------------------------------------------------------------------

//// finalize TEST_COMPATIBILITA_PHP54
// Boot::finalize();

// remove all the echo and put them in the debug zone
$GLOBALS['page']->add(ob_get_contents(), 'debug');
ob_clean();

if (array_key_exists($requestedRoute, $templatesToRender)) {
    $render = $templatesToRender[$requestedRoute];
} else {
    $render = 'home';
}

Layout::render($render);
// layout

//// finalize TEST_COMPATIBILITA_PHP54
Boot::finalize();

// flush buffer
ob_end_flush();
