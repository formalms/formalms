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

namespace Plugin\FormaAuth;

defined('IN_FORMA') or exit('Direct access is forbidden.');

use Form;
use Lang;
use UserManager;
use UserManagerOption;

class Authentication extends \PluginAuthentication implements \PluginAuthenticationWithRedirectInterface
{
    public static function getLoginGUI($redirect = '')
    {
        return [
            'name' => 'FormaAuth',
            'type' => self::AUTH_TYPE_BASE,
            'form' => Form::openForm('login_confirm', \FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _login_ . '&plugin=' . Plugin::getName() . $redirect)
                //  . Form::getHidden("plugin", "plugin", "FormaAuth")
                //  . Form::getTextfield(Lang::t("_USERNAME", "login"), "login_userid", "login_userid", 255)
                . Form::getInputTextfield('', 'login_userid', 'login_userid', '', '', 255, 'placeholder="' . Lang::t('_USERNAME', 'login') . '"')
                //  . Form::getPassword(Lang::t("_PASSWORD", "login"), "login_pwd", "login_pwd", 255)
                . Form::getInputPassword('', 'login_pwd', 'login_pwd', '', 255, 'placeholder="' . Lang::t('_PASSWORD', 'login') . '"')
                . (isset($_REQUEST['notuse_plugin']) ? Form::getHidden('notuse_plugin', 'notuse_plugin', 'true') : '')
                . (isset($_REQUEST['notuse_customscript']) ? Form::getHidden('notuse_customscript', 'notuse_customscript', 'true') : '')
                . (isset($_REQUEST['notuse_template']) ? Form::getHidden('notuse_template', 'notuse_template', 'true') : '')
                . Form::getButton('login', 'login', Lang::t('_LOGIN', 'login'), 'forma-button forma-button--black')
                . Form::closeForm(),
        ];
    }

    public static function getUserFromLogin()
    {
        $login_data = self::_getLoginData();

        if (!$login_data['userid']) {
            return EMPTY_USERID;
        }

        if (!$user = &\DoceboUser::createDoceboUserFromLogin(
                $login_data['userid'],
                $login_data['password'],
                'public_area', // XXX: ???
                $login_data['lang']
        )) {
            self::_loginFailure($login_data['userid']);

            return ACCESS_FAILURE;
        }

        return $user;
    }

    private static function _loginFailure($username)
    {
        new UserManager(); // TODO: rimuovere workaround
        $options = new UserManagerOption();

        $max_log_attempts = $options->getOption('max_log_attempt');

        if ($max_log_attempts) {
            self::_incrementSessionLoginFailuresNumber();
            self::_logLoginFailure($username);
        }
    }

    private static function _incrementSessionLoginFailuresNumber()
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $session->set('user_attempt_lasttime', time());

        if (!$session->get('user_attempt_number')) {
            $session->set('user_attempt_lasttime', 1);
        } else {
            $session->set('user_attempt_lasttime', $session->get('user_attempt_number') + 1);
        }

        $session->save();
    }

    private static function _logLoginFailure($username)
    {
        new UserManager(); // TODO: rimuovere workaround
        $options = new UserManagerOption();
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $save_log_attempts = $options->getOption('save_log_attempt');

        switch ($save_log_attempts) {
            case 'after_max':
        if ($session->get('user_attempt_number') > $options->getOption('max_log_attempt')) {
            $save = true;
        } else {
            $save = false;
        }
                break;
            case 'all':
                $save = true;
                break;
            default:
                $save = false;
                break;
        }

        if ($save) {
            $query = ' INSERT INTO %adm_user_log_attempt'
                    . ' ( userid, attempt_at, attempt_number, user_ip )'
                    . ' VALUES'
                    . ' ('
                    . "     '" . $username . "',"
                    . "     '" . date() . "',"
                    . "     '" . $session->get('user_attempt_number') . "',"
                    . "     '" . $_SERVER['REMOTE_ADDR'] . "' )";

            sql_query($query);
        }
    }

    private static function _getLoginData()
    {
        require_once \Forma::inc(_base_ . '/lib/lib.usermanager.php');

        $user_manager = new UserManager();
        $login_data = $user_manager->getLoginInfo();
        $user_manager->saveUserLoginData();

        return $login_data;
    }
}
