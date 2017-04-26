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

define( 'USER_SAVED',               1 );
define( 'PWD_ELAPSED',              2 );
define( 'MANDATORY_FIELDS',         3 );

define( 'INVALID_REQUEST',          101 );
define( 'USER_CONCURRENCY',         102 );
define( 'SESSION_EXPIRED',          103 );
define( 'INCORRECT_IP',             104 );

define( 'EMPTY_USERID',             "empty_userid" );
define( 'ACCESS_FAILURE',           "access_failure" );

define( 'EMPTY_SOCIALID',           "empty_social_id" );
define( 'UNKNOWN_SOCIAL_ERROR',     "unknown_social_error" );

define( 'INVALID_CODE',             "invalide_code" );

define( 'LOGGED_OUT',               "logged_out" );
define( 'LOST_PWD',                 "lost_pwd" );
define( 'NEW_PWD',                  "new_pwd" );

define( 'USER_NOT_FOUND',           11 );
define( 'FAILURE_SEND_LOST_PWD',    12 );
define( 'SUCCESS_SEND_LOST_PWD',    13 );

define( 'PASSWORD_MISMATCHING',     14 );
define( 'PASSWORD_TOO_SHORT',       15 );
define( 'PASSWORD_MUST_BE_ALPHA',   16 );

define( 'CANCEL_SOCIAL_LOGIN',      20 );

class AuthenticationManager {
    
    protected $plugin_manager;
    
    public function __construct() {
        
        $this->plugin_manager = new PluginManager("Authentication"); // TODO: nome categoria plugin come costante da plugin manager
    }
    
    public function getLoginGUI() {
        
        return $this->plugin_manager->run("getLoginGUI");
    }
    
    public function login($plugin) {
        
        $user = $this->plugin_manager->run_plugin($plugin, "getUserFromLogin");
        
        if(!($user instanceof DoceboUser)) return $user;
            
        return $this->saveUser($user);
    }
    
    public static function logout() {
        
        // TODO: controllo isAnonymous prima del richiamo della funzione
        // TODO: lingua
        
        require_once(_lms_ . '/lib/lib.track_user.php');
        TrackUser::logoutSessionCourseTrack();
        
        $_SESSION = array();
        session_destroy();

        // recreate Anonymous user
        $GLOBALS['current_user'] =& DoceboUser::createDoceboUserFromSession('public_area');
    }
    
    public function saveUser($user) {
                
	//DoceboUser::setupUser($user); // TODO: secondo me meglio tenere la funzione qui ma valutare
        //////////////////////////////////
        $user->loadUserSectionST();
        $user->SaveInSession();
        
        resetTemplate();
        
        $GLOBALS['current_user'] = $user;

        $_SESSION['logged_in'] = true;
        $_SESSION['last_enter'] = $user->getLastEnter();
        $_SESSION['user_enter_mark'] = time();

        $user->setLastEnter(date("Y-m-d H:i:s"));
        //////////////////////////////////        
        
        if(isset($_SESSION['social'])) $this->plugin_manager->run_plugin($_SESSION['social']['plugin'], "setSocial", array("id" => $_SESSION['social']['data']['id']));
        
        if(self::_checkPwdElapsed())        return PWD_ELAPSED;
        if(self::_checkMandatoryFields())   return MANDATORY_FIELDS;
        
        return USER_SAVED;
    }
    
    private static function _checkPwdElapsed() {
        
        return Docebo::user()->isPasswordElapsed() > 0;
    }
    
    private static function _checkMandatoryFields() {
            
        $pcm = new PrecompileLms();
        return $pcm->compileRequired();
    }
}