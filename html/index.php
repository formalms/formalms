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

define("IN_FORMA", true);
define("_deeppath_", '');
require(dirname(__FILE__).'/base.php');

// start buffer
ob_start();

// initialize
require(_lib_ . '/lib.bootstrap.php');
Boot::init(BOOT_PAGE_WR);

// connect to the database
$db =& DbConn::getInstance();

// -----------------------------------------------------------------------------

// get maintenence setting
$query  = " SELECT param_value FROM %adm_setting"
	. " WHERE param_name = 'maintenance'"
	. " ORDER BY pack, sequence";

$maintenance = $db->fetch_row($db->query($query))[0];

if($maintenance == "on") {
    
    // maintenence mode
    
    // get maintenence password
    $query  = " SELECT param_value FROM %adm_setting"
            . " WHERE param_name = 'maintenance_pw'"
            . " ORDER BY pack, sequence";

    $maintenance_pw = $db->fetch_row($db->query($query))[0];
    
    if(!isset($_GET["passwd"]) || $maintenance_pw != $_GET["passwd"]){
        
        // access maintenence denied - login will not appear
        $GLOBALS['block_for_maintenance'] = true;
    } else $GLOBALS['block_for_maintenance'] = false;
}

// old SSO-URL backward compatibility
$sso = Get::req("login_user", DOTY_MIXED, false) && Get::req("time", DOTY_MIXED, false) && Get::req("token", DOTY_MIXED, false);

// get required action - default: homepage if not logged in, no action if logged in
$req = Get::req('r', DOTY_MIXED, ($sso ? _sso_ : (Docebo::user()->isAnonymous() ? _homepage_ : false)));

if($req) {
    
    // handle required action
    
    $req = preg_replace('/[^a-zA-Z0-9\-\_\/]+/', '', $req);
        
    // allowed pages
    
    $allowed = array(
        _homepage_base_,
        _homecatalog_base_
    );
    $r = explode("/", $req);
    
    if(!in_array($r[0] . "/" . $r[1], $allowed)) {
        
        // reload
        Util::jump_to(Get::rel_path("base"));
    }
    
    // instance page writer
    onecolPageWriter::createInstance();
    
    // get mvc structure
    $mvc_class = ucfirst(strtolower($r[1])) . ucfirst(strtolower($r[0])) . "Controller";
    $mvc_name = $r[1];
    $task = $r[2];
    
    ob_clean();
    
    // execute requested task
    $controller = new $mvc_class($mvc_name);
    $controller->request($task);
    
    // add content to page
    $GLOBALS['page']->add(ob_get_contents(), "content");
    ob_clean();
} else {
    
    // redirect to requested page (default: lms index)
    
    if(isset($_SESSION["login_redirect"])) {
        
        $url = substr_replace($_SESSION["login_redirect"], "", 0, strlen(trim(dirname($_SERVER['SCRIPT_NAME']), DIRECTORY_SEPARATOR)) + 1);
        unset($_SESSION["login_redirect"]);
        
        Util::jump_to($url);
    }
    else {
        
        Util::jump_to(_folder_lms_);
    }
}

// -----------------------------------------------------------------------------

#// finalize TEST_COMPATIBILITA_PHP54
// Boot::finalize();

// remove all the echo and put them in the debug zone
$GLOBALS['page']->add(ob_get_contents(), 'debug');
ob_clean();

// layout
Layout::render(($r[1] === 'homecatalogue' ? "home_catalogue" : "home"));

#// finalize TEST_COMPATIBILITA_PHP54
Boot::finalize();

// flush buffer
ob_end_flush();
