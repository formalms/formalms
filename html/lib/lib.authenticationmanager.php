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

define('USER_SAVED', 1);
define('PWD_ELAPSED', 2);
define('MANDATORY_FIELDS', 3);

define('INVALID_REQUEST', 101);
define('USER_CONCURRENCY', 102);
define('SESSION_EXPIRED', 103);
define('INCORRECT_IP', 104);

define('EMPTY_USERID', 'empty_userid');
define('ACCESS_FAILURE', 'access_failure');

define('EMPTY_SOCIALID', 'empty_social_id');
define('UNKNOWN_SOCIAL_ERROR', 'unknown_social_error');

define('INVALID_CODE', 'invalide_code');

define('LOGGED_OUT', 'logged_out');
define('LOST_PWD', 'lost_pwd');
define('NEW_PWD', 'new_pwd');

define('USER_NOT_FOUND', 11);
define('FAILURE_SEND_LOST_PWD', 12);
define('SUCCESS_SEND_LOST_PWD', 13);

define('PASSWORD_MISMATCHING', 14);
define('PASSWORD_TOO_SHORT', 15);
define('PASSWORD_MUST_BE_ALPHA', 16);

define('CANCEL_SOCIAL_LOGIN', 20);

class AuthenticationManager
{
    protected $plugin_manager;

    public function __construct()
    {
        $this->plugin_manager = new PluginManager('Authentication'); // TODO: nome categoria plugin come costante da plugin manager
    }

    public function getLoginGUI($redirect = '')
    {
        return $this->plugin_manager->run('getLoginGUI', [$redirect]);
    }

    public function login($plugin)
    {
        $user = $this->plugin_manager->run_plugin($plugin, 'getUserFromLogin');

        Events::trigger('core.user.logging_in', ['user' => $user]);

        if (!($user instanceof FormaUser)) {
            return $user;
        }

        $saveUser = $this->saveUser($user);

        Events::trigger('core.user.logged_in', ['user' => $user]);

        return $saveUser;
    }

    public static function logout($session = null)
    {
        // TODO: controllo isAnonymous prima del richiamo della funzione
        // TODO: lingua

        $user = Forma::user();

        Events::trigger('core.user.logging_out', ['user' => $user]);

        require_once _lms_ . '/lib/lib.track_user.php';
        TrackUser::logoutSessionCourseTrack();

        \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->invalidate();

        // recreate Anonymous user
        $GLOBALS['current_user'] = &FormaUser::createFormaUserFromSession('public_area');

        Events::trigger('core.user.logged_out', ['user' => $user]);
    }

    public function saveUser($user)
    {
        //FormaUser::setupUser($user); // TODO: secondo me meglio tenere la funzione qui ma valutare
        //////////////////////////////////
        $user->loadUserSectionST();
        $user->SaveInSession();
        Forma::setUser($user);
        resetTemplate();

        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $session->set('logged_in', true);
        $session->set('last_enter', $user->getLastEnter());
        $session->set('user_enter_mark', time());
        $session->set('user', $user);
        $user->setLastEnter(date('Y-m-d H:i:s'));
        //////////////////////////////////

        // force_standard mode
        if (isset($_REQUEST['notuse_plugin'])) {
            $session->set('notuse_plugin', true);
        }
        if (isset($_REQUEST['notuse_customscript'])) {
            $session->set('notuse_customscript', true);
        }
        if (isset($_REQUEST['notuse_template'])) {
            $session->set('notuse_template', true);
        }

        if ($session->has('social')) {
            $plugin = $session->get('social')['plugin'];
            $id = $session->get('social')['data']['id'];
            $this->plugin_manager->run_plugin($plugin, 'setSocial', ['id' => $id]);
        }
        $session->save();
        if (self::_checkMandatoryFields()) {
            return MANDATORY_FIELDS;
        }
        if (self::_checkPwdElapsed()) {
            return PWD_ELAPSED;
        }

        return USER_SAVED;
    }

    private static function _checkPwdElapsed()
    {
        return Forma::user()->isPasswordElapsed() > 0;
    }

    private static function _checkMandatoryFields()
    {
        $pcm = new PrecompileLms();

        return $pcm->compileRequired();
    }
}
