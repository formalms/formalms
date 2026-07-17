<?php

use FormaLms\lib\Domain\DomainHandler;

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

define('TIME_BEFORE_REACTIVE', 10 * 60); // reactive after 10 minutes

class HomepageAdm extends Model
{
    protected $authentication;
    protected $user_manager;
    protected $options;

    public function __construct()
    {
        $this->authentication = new AuthenticationManager();
        $this->user_manager = new UserManager();
        $this->options = new UserManagerOption();
        parent::__construct();
    }

    public function getLoginGUI()
    {
        $redirect = '';
        $loginRedirect = FormaLms\lib\Get::req('login_redirect', DOTY_MIXED, null);
        if (!is_null($loginRedirect)) {
            $redirect = '&login_redirect=' . $loginRedirect;
        }

        return $this->authentication->getLoginGUI($redirect);
    }

    public function login($plugin)
    {
        return $this->authentication->login($plugin);
    }

    public function saveUser($user)
    {
        return $this->authentication->saveUser($user);
    }

    public function checkBrute()
    {
        $max_login_attempts = $this->options->getOption('max_log_attempt');

        if (!$max_login_attempts) {
            return false;
        }

        $time_before_reactive = TIME_BEFORE_REACTIVE;

        $last_attempt = $this->user_manager->getLastAttemptTime();
        $actual_attempts = $this->user_manager->getAttemptNumber();

        $now = time();

        $wait_for = 0;

        if ($actual_attempts > $max_login_attempts) {
            if (($last_attempt + $time_before_reactive) > $now) {
                $wait_for = (int) ((($last_attempt + $time_before_reactive) - $now) / 60);

                return [
                    'max_login_attempt' => $max_login_attempts,
                    'wait_for' => $wait_for,
                ];
            } else {
                $this->user_manager->resetAttemptNumber();
            }
        }

        return false;
    }

    public function isSelfRegistrationActive()
    {
        $registration_type = $this->options->getOption('register_type');
     
        $active_types = ['self', 'self_optin', 'moderate'];

        return in_array($registration_type, $active_types);
    }

    public function getRegisterForm()
    {
        return $this->user_manager->getRegister(FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _signup_);
    }

    public function getConfirmRegister()
    {
        return $this->user_manager->confirmRegister();
    }

    public function getExternalPages()
    {
        $query = ' SELECT idPages, title'
                . ' FROM %lms_webpages'
                . " WHERE publish = '1'"
                . "     AND in_home='0'"
                . "     AND language = '" . Lang::get() . "'"
                . ' ORDER BY sequence ';
        $r = sql_query($query);

        $external_pages = [];
        while (list($id_page, $title) = sql_fetch_row($r)) {
            $external_pages[$id_page] = $title;
        }

        return $external_pages;
    }

    public function sendLostUserId($email)
    {
        $acl_man = \FormaLms\lib\Forma::getAclManager();
        $user_info = $acl_man->getUserByEmail($email);

        if (!$user_info) {
            return USER_NOT_FOUND;
        }

        $reg_code = null;
        $uma = new UsermanagementAdm();
        $nodes = $uma->getUserFolders($user_info[ACL_INFO_IDST]);
        if ($nodes) {
            $idst_oc = array_keys($nodes)[0];

            $query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idst_oc LIMIT 1");
            if ($query) {
                $reg_code = sql_fetch_object($query)->idOrg;
            }
        }

        // Ottieni la lingua dell'utente con controllo delle lingue abilitate
        $id_user = $user_info[ACL_INFO_IDST];
        $userLanguages = $acl_man->getSettingValueOfUsers('ui.language', [$id_user]);
        $userLanguage = !empty($userLanguages) && in_array($id_user, $userLanguages) && $userLanguages[$id_user] ? $userLanguages[$id_user] : null;

        $enabledLanguages = Lang::getEnabledLanguages();
        if (!$userLanguage || !in_array($userLanguage, $enabledLanguages)) {
            $userLanguage = Lang::getDefault();
        }

        $sender = DomainHandler::getInstance()->getMailerField('sender_mail_system');
        $recipients = $user_info[ACL_INFO_EMAIL];
        $subject = Lang::t('_LOST_USERID_TITLE', 'register', [], $userLanguage);
        $body = Lang::t('_LOST_USERID_MAILTEXT', 'register', [
            '[date_request]' => date('d-m-Y'),
            '[url]' => FormaLms\lib\Get::site_url(),
            '[dynamic_link]' => getCurrentDomain($reg_code) ?: FormaLms\lib\Get::site_url(),
            '[userid]' => $acl_man->relativeId($user_info[ACL_INFO_USERID]),
        ], $userLanguage);
        $params = [MAIL_SENDER_ACLNAME => DomainHandler::getInstance()->getMailerField('sender_name_system')];
        $mailer = FormaLms\lib\Mailer\FormaMailer::getInstance();
        if ($mailer->SendMail([$recipients], $subject, $body, $sender, [], $params)) {
            return SUCCESS_SEND_LOST_PWD;
        } else {
            return FAILURE_SEND_LOST_PWD;
        }
    }

    public function sendLostPwd($userid)
    {
        $acl_man = \FormaLms\lib\Forma::getAclManager();
        $user_info = $acl_man->getUser(false, $acl_man->absoluteId($userid));

        if (!$user_info) {
            return USER_NOT_FOUND;
        }

        $code = md5(mt_rand() . mt_rand());

        $exist_code = $this->user_manager->getPwdRandomCode($user_info[ACL_INFO_IDST]);

        if ($exist_code === false) {
            if (!$this->user_manager->insertPwdRandomCode($user_info[ACL_INFO_IDST], $code)) {
                return FAILURE_SEND_LOST_PWD;
            }
        } else {
            if (!$this->user_manager->savePwdRandomCode($user_info[ACL_INFO_IDST], $code)) {
                return FAILURE_SEND_LOST_PWD;
            }
        }

        $reg_code = null;
        $uma = new UsermanagementAdm();
        $nodes = $uma->getUserFolders($user_info[ACL_INFO_IDST]);
        if ($nodes) {
            $idst_oc = array_keys($nodes)[0];

            $query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idst_oc LIMIT 1");
            if ($query) {
                $reg_code = sql_fetch_object($query)->idOrg;
            }
        }

        $url = getCurrentDomain($reg_code) ?: FormaLms\lib\Get::site_url();

        // Ottieni la lingua dell'utente con controllo delle lingue abilitate
        $id_user = $user_info[ACL_INFO_IDST];
        $userLanguages = $acl_man->getSettingValueOfUsers('ui.language', [$id_user]);
        $userLanguage = !empty($userLanguages) && in_array($id_user, $userLanguages) && $userLanguages[$id_user] ? $userLanguages[$id_user] : null;

        $enabledLanguages = Lang::getEnabledLanguages();
        if (!$userLanguage || !in_array($userLanguage, $enabledLanguages)) {
            $userLanguage = Lang::getDefault();
        }

        $sender = DomainHandler::getInstance()->getMailerField('sender_mail_system');
        $sender_name = DomainHandler::getInstance()->getMailerField('sender_name_system');
        $recipients = $user_info[ACL_INFO_EMAIL];
        $subject = Lang::t('_LOST_PWD_TITLE', 'register', [], $userLanguage);
        $body = Lang::t('_LOST_PWD_MAILTEXT', 'register', [
            '[link]' => FormaLms\lib\Get::site_url() . 'index.php?r=' . _newpwd_ . '&code=' . $code,
            '[dynamic_link]' => $url . 'index.php?r=' . _newpwd_ . '&code=' . $code,
            '[userid]' => $acl_man->relativeId($user_info[ACL_INFO_USERID]),
        ], $userLanguage);
        $params = [MAIL_SENDER_ACLNAME => $sender_name];

        $mailer = FormaLms\lib\Mailer\FormaMailer::getInstance();

        if ($mailer->SendMail([$recipients], $subject, $body, $sender, [], $params)) {
            return SUCCESS_SEND_LOST_PWD;
        } else {
            return FAILURE_SEND_LOST_PWD;
        }
    }

    public function checkCode($code)
    {
        if ($user = $this->user_manager->getPwdRandomCode(false, $code)) {
            $acl_man = \FormaLms\lib\Forma::getAclManager();
            $user_info = $acl_man->getUser($user['idst_user'], false);

            return $user_info;
        }

        return false;
    }

    public function getNewPwdOptions()
    {
        $options['pass_max_time_valid'] = $this->options->getOption('pass_max_time_valid');
        $options['pass_min_char'] =  $this->options->getOption('pass_min_char');
        $options['pass_alfanumeric'] = $this->options->getOption('pass_alfanumeric');
        $options['user_pwd_history_length'] = $this->options->getOption('user_pwd_history_length');
        $options['pass_min_uppercase'] = $this->options->getOption('pass_min_uppercase');
        $options['pass_min_lowercase'] = $this->options->getOption('pass_min_lowercase');
        $options['pass_min_digit'] = $this->options->getOption('pass_min_digit');
        $options['pass_special_char'] = $this->options->getOption('pass_special_char');
        return $options;
    }

    public function checkNewPwdValidity($pwd, $retype)
    {
        if ($pwd !== $retype) {
            return [
                'error' => true,
                'msg' => [Lang::t('_ERR_PASSWORD_NO_MATCH','register')]
            ];
        } else {
            require_once _lib_ .'/lib.password.php';
            $chk_pswd = PasswordPolicies::check($pwd);
            if (!$chk_pswd->valid()) {
                return [
                    'error' => true,
                    'msg' => $chk_pswd->messages()
                ];
            } else {
                return ['error' => false];
            }
        }
    }

    public function setNewPwd($pwd, $user, $code)
    {
        $acl_man = \FormaLms\lib\Forma::getAclManager();

        if (!$this->user_manager->deletePwdRandomCode($user, $code)) {
            return false;
        }

        return $acl_man->updateUser($user, false, false, false, $pwd, false, false, false);
    }

    public function isUnderMaintenence()
    {
        return isset($GLOBALS['block_for_maintenance']) && $GLOBALS['block_for_maintenance'];
    }

    public static function staticIsCatalogToShow()
    {
        return FormaLms\lib\Get::sett('course_block', 'on') == 'on';
    }

    public function isCatalogToShow()
    {
        return FormaLms\lib\Get::sett('course_block', 'on') == 'on';
    }

    public function getWebPage($id_page = null)
    {
        $query = ' SELECT title, description'
                . ' FROM %lms_webpages'
                . " WHERE publish = '1'"
                . "     AND language = '" . Lang::get() . "'"
                . '     AND ' . ($id_page ? 'idPages = ' . $id_page : "in_home = '1'");

        return sql_fetch_row(sql_query($query));
    }
}