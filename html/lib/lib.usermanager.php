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

/*
 * This file contains some class that must be used in all the platofrm to perform the
 * register, lostpassword, lostuser, profile, login operations
 *
 * @package admin-core
 * @subpackage user
 * @version  $Id: lib.usermanager.php 966 2007-02-09 14:11:41Z fabio $
 * @author   Fabio Pirovano <fabio [at] docebo-com>
 */

define('_LOG_OPT_GROUP', 3);

class UserManager
{
    /**
     * @var string
     */
    public $_platform;

    /**
     * @var string
     */
    public $prefix;

    /**
     * @var resource #id
     */
    public $db_conn;

    /**
     * @var UserManagerAction
     */
    public $_action;

    /**
     * @var UserManagerRenderer
     */
    public $_render;

    /**
     * @var UserManagerOption
     */
    public $_option;

    /**
     * @var int (seconds)
     */
    public $_time_before_reactive;

    protected $session;

    /**
     * This is the class constructor, set the default value for the varaible and instance
     * the class that it use.
     *
     * @param string $platform specified a different platform for localization
     * @param string $prefix   specified a prefix
     * @param string $db_conn  specified a db connection with the database
     */
    public function __construct($platform = false, $prefix = false, $db_conn = false)
    {
        $this->_platform = ($platform !== false ? $platform : FormaLms\lib\Get::cur_plat());
        $this->prefix = ($prefix !== false ? $prefix : $GLOBALS['prefix_fw']);
        $this->db_conn = ($db_conn !== false ? $db_conn : null);

        $this->_action = new UserManagerAction($prefix, $db_conn);
        $this->_render = new UserManagerRenderer();
        $this->_option = new UserManagerOption();

        $this->_time_before_reactive = 10 * 60;
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    /**
     * The name of the table in which the information of the failed attempt is stored.
     *
     * @return the name of the table
     */
    public function _getLogAttemptTable()
    {
        return $this->prefix . '_user_log_attempt';
    }

    /**
     * simply execute a query.
     *
     * @param string $query  the query
     * @param string $prefix specified a prefix
     * @param mixed     the result of sql_query
     */
    public function _executeQuery($query)
    {
        if ($this->db_conn === null) {
            $re = sql_query($query);
        } else {
            $re = sql_query($query, $this->db_conn);
        }

        return $re;
    }

    /**
     * return information about the login attempt for the user.
     *
     * @return bool return TRUE if the user is logged in correctly or if the user doesn't do any attempt
     *              return FALSE if the user log attempt is failed
     */
    public function _getLoginResult()
    {
        if (UserManagerRenderer::loginAttempt()) {
            if (Docebo::user()->isAnonymous()) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    /**
     * return information about the login attempt for the user.
     *
     * @return bool return TRUE if the user as try to loggin
     *              return FALSE if the user dont'try to login
     */
    public function loginAttempt()
    {
        return UserManagerRenderer::loginAttempt();
    }

    /**
     * return information about the number of attempts.
     *
     * @return int return the number of attempt for the user
     */
    public function getAttemptNumber()
    {
        if (!$this->session->has('user_attempt_number')) {
            $this->session->set('user_attempt_number', 0);
            $this->session->save();
        }

        return $this->session->get('user_attempt_number');
    }

    /**
     * return information about the last attempt.
     *
     * @return int return the time of last attempt
     */
    public function getLastAttemptTime()
    {
        return $this->session->get('user_attempt_lasttime', 0);
    }

    /**
     * increment and refresh attempt info.
     *
     * @return nothing
     */
    public function _incAttemptNumber()
    {
        $userAttemptNumber = $this->session->get('user_attempt_number', 0);
        if ($userAttemptNumber === 0) {
            $userAttemptNumber = 1;
        } else {
            ++$userAttemptNumber;
        }
        $this->session->set('user_attempt_number', $userAttemptNumber);
        $this->session->save();
    }

    public function _updateLastAttemptTime()
    {
        $this->session->set('user_attempt_lasttime', time());
        $this->session->save();
    }

    /**
     * reset the number of the attempt.
     *
     * @return nothing
     */
    public function resetAttemptNumber()
    {
        $this->session->set('user_attempt_lasttime', 0);
        $this->session->set('user_attempt_number', 0);
        $this->session->save();
    }

    /**
     * save information about the failed login.
     *
     * @return bool true if succes false oterwise
     */
    public function _saveLoginFailure($attempt_number)
    {
        $query = '
		INSERT INTO ' . $this->_getLogAttemptTable() . "
		( userid, attempt_at, attempt_number, user_ip ) VALUES
		( '" . $this->_render->getInserted('userid') . "',
		  '" . date('Y-m-d H:i:s') . "',
		  '" . $attempt_number . "',
		  '" . $_SERVER['REMOTE_ADDR'] . "' )";

        return $this->_executeQuery($query);
    }

    // --------------------------------------------------
    // XXX: BEGIN function for a correct login mask

    public function getLoginInfo()
    {
        return UserManagerRenderer::getLoginInfo();
    }

    /**
     * @param string $what       enum('link', 'button')
     * @param string $info_about set the link ref for the link typre or the button name
     */
    public function setRegisterTo($what, $info_about)
    {
        return $this->_render->setRegisterTo($what, $info_about);
    }

    /**
     * @return bool if register is a button return true if is submit, false otherwise
     */
    public function clickRegister()
    {
        return $this->_render->clickRegister();
    }

    /**
     * @param string $what       enum('link', 'button')
     * @param string $info_about set the link ref for the link typre or the button name
     */
    public function setLostpwdTo($what, $info_about)
    {
        return $this->_render->setLostpwdTo($what, $info_about);
    }

    /**
     * @return bool if lostpwd is a button return true if is submit, false otherwise
     */
    public function clickLostpwd()
    {
        return $this->_render->clickLostpwd();
    }

    /**
     * let the class save some info about the logi nof the user.
     *
     * @return nothing
     */
    public function saveUserLoginData()
    {
        if ($this->_render->getInserted('remember') == true) {
            $this->_render->createRemember();
        }
        $this->_render->setAccessibility();
    }

    /**
     * return information about the login attempt for the user.
     *
     * @param string $jump_url an url for jump
     * @param string $extra    extra information to display in the field
     *
     * @return bool return TRUE if the user as try to loggin
     *              return FALSE if the user dont'try to login
     */
    public function getLoginMask($jump_url, $extra = '')
    {
        $advice = '';
        $disable = false;

        if ($this->_render->clickDeleteRemember()) {
            $this->_render->deleteRemember();
        }

        // Control for max number of attempt for this user
        $max_log_attempt = $this->_option->getOption('max_log_attempt');
        $save_log_attempt = $this->_option->getOption('save_log_attempt');

        if ($max_log_attempt != 0) {
            if ($this->_getLoginResult() == false) {
                $last_attempt = $this->getLastAttemptTime();
                $actual_attempt = $this->getAttemptNumber();
                if ($actual_attempt > $max_log_attempt) {
                    if (($last_attempt + $this->_time_before_reactive) > time()) {
                        $wait_for = (int) ((($last_attempt + $this->_time_before_reactive) - time()) / 60);
                        if ($wait_for < 1) {
                            $wait_for = ' < 1';
                        }

                        $advice = str_replace(['[attempt]', '[time]'], [...$max_log_attempt, $wait_for], Lang::t('_REACH_NUMBERS_OF_ATTEMPT', 'user_managment'));
                        $disable = true;
                        if ($save_log_attempt == 'after_max') {
                            $this->_saveLoginFailure($actual_attempt);
                        }
                    } else {
                        $this->resetAttemptNumber();
                    }
                } else {
                    $this->_updateLastAttemptTime();
                    $this->_incAttemptNumber();
                }
                if ($save_log_attempt == 'all') {
                    $this->_saveLoginFailure($actual_attempt);
                }
            }
        }

        return $this->_render->getLoginMask($this->_platform, $advice, $extra, $disable, $this->_option->getOption('register_type'), $jump_url);
    }

    public function setLoginStyle($path_style)
    {
        $this->_render->setStyleToUse($path_style);
    }

    public function hideLoginLanguageSelection()
    {
        $this->_render->hideLoginLanguageSelection();
    }

    public function hideLoginAccessibilityButton()
    {
        $this->_render->hideLoginAccessibilityButton();
    }

    // XXX: END function for a correct login mask
    // --------------------------------------------------

    /**
     * @return string the html for the registration process in his various part
     */
    public function getRegister($opt_link)
    {
        $options = [
            'lastfirst_mandatory' => $this->_option->getOption('lastfirst_mandatory'),
            'register_type' => $this->_option->getOption('register_type'),
            'use_advanced_form' => $this->_option->getOption('use_advanced_form'),
            'pass_alfanumeric' => $this->_option->getOption('pass_alfanumeric'),
            'pass_min_char' => $this->_option->getOption('pass_min_char'),
            'hour_request_limit' => $this->_option->getOption('hour_request_limit'),
            'privacy_policy' => $this->_option->getOption('privacy_policy'),
            'mail_sender' => $this->_option->getOption('mail_sender'),
            'mail_sender_name_from' => $this->_option->getOption('mail_sender_name_from'),
            'field_tree' => $this->_option->getOption('field_tree'),
        ];

        return $this->_render->getRegister(
            $this->_platform,
            $options,
            $opt_link
        );
    }

    public function confirmRegister()
    {
        $options = [
            'lastfirst_mandatory' => $this->_option->getOption('lastfirst_mandatory'),
            'register_type' => $this->_option->getOption('register_type'),
            'use_advanced_form' => $this->_option->getOption('use_advanced_form'),
            'pass_alfanumeric' => $this->_option->getOption('pass_alfanumeric'),
            'pass_min_char' => $this->_option->getOption('pass_min_char'),
            'hour_request_limit' => $this->_option->getOption('hour_request_limit'),
            'privacy_policy' => $this->_option->getOption('privacy_policy'),
            'field_tree' => $this->_option->getOption('field_tree'),
        ];

        return $this->_render->confirmRegister(
            $this->_platform,
            $options
        );
    }

    public function getElapsedPassword($jump_link)
    {
        $option['pass_max_time_valid'] = $this->_option->getOption('pass_max_time_valid');
        $option['pass_min_char'] = $this->_option->getOption('pass_min_char');
        $option['pass_alfanumeric'] = $this->_option->getOption('pass_alfanumeric');
        $option['user_pwd_history_length'] = $this->_option->getOption('user_pwd_history_length');

        return $this->_render->getElapsedPasswordMask($this->_platform, $option, $jump_link);
    }

    public function clickSaveElapsed()
    {
        return $this->_render->clickSaveElapsed();
    }

    public function saveElapsedPassword()
    {
        $option['pass_max_time_valid'] = $this->_option->getOption('pass_max_time_valid');
        $option['pass_min_char'] = $this->_option->getOption('pass_min_char');
        $option['pass_alfanumeric'] = $this->_option->getOption('pass_alfanumeric');
        $option['user_pwd_history_length'] = $this->_option->getOption('user_pwd_history_length');

        return $this->_render->saveElapsedPassword($this->_platform, $option);
    }

    // --------------------------------------------------
    // XXX: BEGIN function for a correct option managment

    /**
     * @param string $platform specified a different platform for localization
     * @param string $prefix   specified a prefix
     * @param string $db_conn  specified a db connection with the database
     *
     * @return array array(group_id => group_name) with the regroup unit
     */
    public function getRegroupUnit()
    {
        return $this->_option->getRegroupUnit();
    }

    /**
     * @param string    contains the group selected
     *
     * @return string contains the displayable information for a selected group
     */
    public function getPageWithElement($group_selected)
    {
        return $this->_option->getPageWithElement($group_selected);
    }

    /**
     * @param string    contains the group selected
     *
     * @return bool true if the operation was successfull false otherwise
     */
    public function saveElement($group_selected)
    {
        return $this->_option->saveElement($group_selected);
    }

    // XXX: END function for a correct option managment
    // ------------------------------------------------

    // ---------------------------------------------------------
    // XXX: BEGIN function for a correct lost password managment

    /**
     * @return bool true if the action to perform is show the lost password and login
     */
    public function haveToLostpwdMask()
    {
        if (!$this->haveToLostpwdConfirm() && !$this->haveToLostpwdAction()) {
            return true;
        }

        return false;
    }

    /**
     * return html about the lost password and user.
     *
     * @return string html
     */
    public function getLostpwdMask($jump_url)
    {
        return $this->_render->getLostpwd($jump_url, $this->_platform);
    }

    /**
     * @return bool true if the action to perform is  confirm a new password request
     */
    public function haveToLostpwdConfirm()
    {
        if (isset($_GET['pwd']) && $_GET['pwd'] == 'retrpwd') {
            return true;
        }

        return false;
    }

    /**
     * return html about the lost password confirm and send email.
     *
     * @return string html
     */
    public function performLostpwdConfirm()
    {
        require_once _base_ . '/lib/lib.form.php';

        $out = &$GLOBALS['page'];
        $out->setWorkingZone('content');

        $form = new Form();

        $lang = DoceboLanguage::createInstance('register');

        $random_code = FormaLms\lib\Get::req('code', DOTY_MIXED, '');
        $exist_code = $this->getPwdRandomCode(false, $random_code);

        if (!isset($_POST['send'])) {
            if ($exist_code === false) {
                return $lang->def('_INVALID_RANDOM_CODE') . '<br/>';
            }
        }

        $acl_man = &Docebo::user()->getAclManager();
        $user_info = $acl_man->getUser($exist_code['idst_user'], false);
        if (isset($_POST['send'])) {
            if ($_POST['new_password'] === $_POST['retype_new_password']) {
                if (strlen($_POST['new_password']) >= $this->_option->getOption('pass_min_char')) {
                    // remove code from core_pwd_recover:
                    $this->deletePwdRandomCode($user_info[ACL_INFO_IDST], $random_code);

                    // update the password:
                    if (!$acl_man->updateUser($user_info[ACL_INFO_IDST], false, false, false, $_POST['new_password'], false, false, false)) {
                        $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')), 'content');
                    } else {
                        return $lang->def('_OPERATION_SUCCESSFUL')
                            . '<br/><a href="./index.php">' . $lang->def('_LOGIN') . '</a>';
                    }
                } else {
                    $out->add(getErrorUi($lang->def('_PASSWORD_TOO_SHORT')));
                    unset($_POST['send']);
                }
            } else {
                $out->add(getErrorUi($lang->def('_ERR_PASSWORD_NO_MATCH')));
                unset($_POST['send']);
            }
        }

        // form reinser pwd -----------------------------------------------------------------
        if ($user_info !== false && !isset($_POST['send'])) {
            $options['pass_max_time_valid'] = $this->_option->getOption('pass_max_time_valid');
            $options['pass_min_char'] = $this->_option->getOption('pass_min_char');
            $options['pass_alfanumeric'] = $this->_option->getOption('pass_alfanumeric');

            $out->add('<div class="reg_note">'
                . $lang->def('_CHOOSE_NEW_PASSWORD')
                . '</div>'
                . '<ul class="reg_instruction">', 'content');
            if ($options['pass_max_time_valid']) {
                $out->add('<li>' . str_replace('[valid_for_day]', $options['pass_max_time_valid'], $lang->def('_NEWPWDVALID')) . '</li>', 'content');
            }
            if ($options['pass_min_char']) {
                $out->add('<li>' . str_replace('[min_char]', $options['pass_min_char'], $lang->def('_REG_PASS_MIN_CHAR')) . '</li>', 'content');
            }
            if ($options['pass_alfanumeric'] == 'on') {
                $out->add('<li>' . $lang->def('_REG_PASS_MUST_BE_ALPNUM') . '</li>', 'content');
            }
            $out->add(
                '</ul>' . "\n"

                . $form->openForm('new_password', 'index.php?modname=login&amp;op=lostpwd&amp;pwd=retrpwd')
                . $form->openElementSpace()
                . $form->getPassword($lang->def('_PASSWORD'), 'new_password', 'new_password', '255')
                . $form->getPassword($lang->def('_RETYPE_PASSWORD'), 'retype_new_password', 'retype_new_password', '255')
                . $form->getHidden('code', 'code', $random_code)
                . $form->closeElementSpace()
                . $form->openButtonSpace()
                . $form->getButton('send', 'send', $lang->def('_SAVE'))
                . $form->closeButtonSpace()
                . $form->closeForm()
                . '<br/>',
                'content'
            );
        } else {
            return $lang->def('_INVALID_RANDOM_CODE') . '<br/>';
        }
    }

    /**
     * @return bool true if the action to perform is  to send email for recover user or password
     */
    public function haveToLostpwdAction()
    {
        return $this->_render->haveToLostpwdAction() || $this->_render->haveToLostuserAction();
    }

    /**
     * return html about the lost password and user action and send email.
     *
     * @return string html
     */
    public function performLostpwdAction($mail_url)
    {
        $lang = DoceboLanguage::createInstance('register');

        //lost userid
        if ($this->_render->haveToLostuserAction()) {
            $mail = $this->_render->getLostUserParam();
            if (preg_match("\r", $mail) || preg_match("\n", $mail)) {
                exit("This isn't a good email address !");
            }

            $acl_man = &Docebo::user()->getAclManager();
            $user_info = $acl_man->getUserByEmail($mail);

            if ($user_info !== false) {
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

                //compose e-mail --------------------------------------------
                $mail_sender = $this->_option->getOption('mail_sender');
                $mail_sender_name_from = $this->_option->getOption('mail_sender_name_from');

                /*$from = "From: ".$mail_sender.$GLOBALS['mail_br'];
            $intestazione  = "MIME-Version: 1.0".$GLOBALS['mail_br'];
            $intestazione .= "Content-type: text/html; charset=".getUnicode().$GLOBALS['mail_br'];

            $intestazione .= "Return-Path: ".$mail_sender.$GLOBALS['mail_br'];
            $intestazione .= "Reply-To: ".$mail_sender.$GLOBALS['mail_br'];
            $intestazione .= "X-Sender: ".$mail_sender.$GLOBALS['mail_br'];
            $intestazione .= "X-Mailer: PHP/". phpversion().$GLOBALS['mail_br'];*/

                $mail_text = $lang->def('_LOST_USERID_MAILTEXT');
                $mail_text = str_replace(['[date_request]', '[url]', '[dynamic_link]', '[userid]'], [date('d-m-Y'), FormaLms\lib\Get::site_url(), getCurrentDomain($reg_code) ?: FormaLms\lib\Get::site_url(), $acl_man->relativeId($user_info[ACL_INFO_USERID])], $mail_text);

                //if(!@mail($user_info[ACL_INFO_EMAIL], $lang->def('_LOST_USERID_TITLE'), $mail_text, $from.$intestazione)) {

                $mailer = FormaMailer::getInstance();
                $success = $mailer->SendMail(
                    $mail_sender,
                    [$user_info[ACL_INFO_EMAIL]],
                    $lang->def('_LOST_USERID_TITLE'),
                    $mail_text,
                    [],
                    [
                        MAIL_SENDER_ACLNAME => $mail_sender_name_from,
                    ]
                );

                if (!$success) {
                    return $lang->def('_OPERATION_FAILURE') . '<br/>';
                } else {
                    return $lang->def('_MAIL_SEND_SUCCESSFUL') . '<br/>';
                }
            } else {
                return $lang->def('_INEXISTENT_USER') . '<br/>';
            }
        }
        //lost pwd
        if ($this->_render->haveToLostpwdAction()) {
            $userid = $this->_render->getLostPwdParam();

            $acl_man = &Docebo::user()->getAclManager();
            $user_info = $acl_man->getUser(false, $acl_man->absoluteId($userid));

            if ($user_info !== false) {
                //compose e-mail --------------------------------------------
                $mail_sender = $this->_option->getOption('mail_sender');
                $mail_sender_name_from = $this->_option->getOption('mail_sender_name_from');

                /*$from = "From: ".$mail_sender.$GLOBALS['mail_br'];
            $intestazione  = "MIME-Version: 1.0".$GLOBALS['mail_br'];
            $intestazione .= "Content-type: text/html; charset=".getUnicode().$GLOBALS['mail_br'];

            $intestazione .= "Return-Path: ".$mail_sender.$GLOBALS['mail_br'];
            $intestazione .= "Reply-To: ".$mail_sender.$GLOBALS['mail_br'];
            $intestazione .= "X-Sender: ".$mail_sender.$GLOBALS['mail_br'];
            $intestazione .= "X-Mailer: PHP/". phpversion().$GLOBALS['mail_br'];*/

                $code = md5(mt_rand() . mt_rand());

                $exist_code = $this->getPwdRandomCode($user_info[ACL_INFO_IDST]);
                if ($exist_code === false) {
                    if (!$this->insertPwdRandomCode($user_info[ACL_INFO_IDST], $code)) {
                        return $lang->def('_OPERATION_FAILURE');
                    }
                } else {
                    if (!$this->savePwdRandomCode($user_info[ACL_INFO_IDST], $code)) {
                        return $lang->def('_OPERATION_FAILURE');
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

                $dynamicUrl = getCurrentDomain($reg_code) ?: FormaLms\lib\Get::site_url();

                $link = FormaLms\lib\Get::site_url() . $mail_url . '&amp;pwd=retrpwd&amp;code=' . $code;
                $dynamicLink = $dynamicUrl . $mail_url . '&amp;pwd=retrpwd&amp;code=' . $code;
                $mail_text = str_replace(['[link]', '[dynamic_link]'], [$link, $dynamicLink], $lang->def('_LOST_PWD_MAILTEXT'));

                $mailer = FormaMailer::getInstance();
                $success = $mailer->SendMail(
                    $mail_sender,
                    [$user_info[ACL_INFO_EMAIL]],
                    $lang->def('_LOST_PWD_TITLE'),
                    $mail_text,
                    [],
                    [
                        MAIL_SENDER_ACLNAME => $mail_sender_name_from,
                    ]
                );

                if (!$success) {
                    return $lang->def('_OPERATION_FAILURE') . '<br/>';
                } else {
                    return $lang->def('_MAIL_SEND_SUCCESSFUL_PWD') . '<br/>';
                }
            } else {
                return $lang->def('_INEXISTENT_USER') . '<br/>';
            }
        }
    }

    public function getPwdRandomCode($idst_user = false, $code = false)
    {
        $code = sha1($code);

        $query = '
		SELECT idst_user, random_code, request_date
		FROM ' . $this->prefix . '_pwd_recover
		WHERE ';
        if ($idst_user !== false) {
            $query .= " idst_user = '" . $idst_user . "'";
        } elseif ($code !== false) {
            $query .= " random_code = '" . $code . "'";
        } else {
            return false;
        }

        $re = $this->_executeQuery($query);
        if (!$re) {
            return false;
        }
        if (sql_num_rows($re) <= 0) {
            return false;
        }

        return sql_fetch_assoc($re);
    }

    public function insertPwdRandomCode($idst_user, $code)
    {
        $code = sha1($code);

        $query = '
		INSERT INTO ' . $this->prefix . "_pwd_recover
		( idst_user, random_code, request_date ) VALUES ( '" . $idst_user . "', '" . $code . "', '" . date('Y-m-d H:i:s') . "' )";
        $re = $this->_executeQuery($query);

        if (!$re) {
            return false;
        }

        return true;
    }

    public function savePwdRandomCode($idst_user, $code)
    {
        $code = sha1($code);

        $query = '
		UPDATE ' . $this->prefix . "_pwd_recover
		SET random_code = '" . $code . "',
			request_date = '" . date('Y-m-d H:i:s') . "'
		WHERE idst_user = '" . $idst_user . "'";

        $re = $this->_executeQuery($query);
        if (!$re) {
            return false;
        }

        return true;
    }

    public function deletePwdRandomCode($idst_user = false, $code = false)
    {
        $code = sha1($code);

        $query = '
		DELETE FROM ' . $this->prefix . '_pwd_recover
		WHERE ';
        if ($idst_user !== false) {
            $query .= " idst_user = '" . $idst_user . "'";
        } elseif ($code !== false) {
            $query .= " random_code = '" . $code . "'";
        }

        $re = $this->_executeQuery($query);
        if (!$re) {
            return false;
        }

        return true;
    }

    // XXX: END function for a correct lost password managment
    // -------------------------------------------------------

    // -----------------------------------------
    // XXX: BEGIN function for a correct profile

    public function getProfile($id_user = false, $userid = false)
    {
        $acl_man = Docebo::user()->getAclManager();

        $user_info = &$acl_man->getUser($id_user, $userid);
        $user_info[ACL_INFO_USERID] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

        return $this->_render->getRenderedProfile($user_info);
    }
    // XXX: END function for a correct profle
    // ---------------------------------------

    /**
     * Check if a given registration code is valid.
     *
     * @param string $reg_code
     * @param string $registration_code_type
     *
     * @return bool
     */
    public function checkRegistrationCode($reg_code, $registration_code_type)
    {
        $res = true;

        $uma = new UsermanagementAdm();
        $code_is_mandatory = (FormaLms\lib\Get::sett('mandatory_code', 'off') == 'on');

        if ($reg_code != '') {
            switch ($registration_code_type) {
                case '0':
                    //nothin to do
                    ;
                    break;
                case 'tree_course':
                    //a mixed code, let's cut the tree part and go on with the tree_man and resolve here the course part
                    $course_code = substr(str_replace('-', '', $reg_code), 10, 10);
                    $reg_code = substr(str_replace('-', '', $reg_code), 0, 10);

                    //control course registration
                    require_once _lms_ . '/lib/lib.course.php';
                    $man_course_user = new Man_CourseUser();
                    $jolly_code = FormaLms\lib\Get::sett('jolly_course_code', '');
                    if ($jolly_code == '' || $jolly_code != $course_code) {
                        $course_registration_result = $man_course_user->checkCode($course_code);
                        if ($course_registration_result <= 0 && $code_is_mandatory) {
                            $res = false; // invalid code
                        }
                    }
                //procced with tree_man
                // no break
                case 'tree_man':
                    // resolving the tree_man
                    $array_folder = $uma->getFoldersFromCode($reg_code);
                    if (empty($array_folder) && $code_is_mandatory) {
                        $res = false; // invalid code
                    }

                    break;
                case 'code_module':
                    require_once _adm_ . '/lib/lib.code.php';
                    $code_manager = new CodeManager();
                    $valid_code = $code_manager->controlCodeValidity($reg_code);
                    if ($valid_code == 0) {
                        $res = false; // code already used
                    } elseif ($valid_code == -1 && $code_is_mandatory) {
                        $res = false; // invalid code
                    }

                    break;
                case 'tree_drop':
                    // from the dropdown we will recive the id of the folder
                    $array_folder = [$reg_code => $reg_code];
                    $oc_folders = $uma->getOcFolders($array_folder);
                    if (empty($oc_folders)) {
                        $res = false; // invalid code
                    }

                    break;
                case 'custom':
                    //Custom code
                    require_once _adm_ . '/lib/lib.field.php';
                    $field_man = new FieldList();

                    $id_common_filed_1 = $field_man->getFieldIdCommonFromTranslation('Filiale');
                    $id_common_filed_2 = $field_man->getFieldIdCommonFromTranslation('Codice Concessionario');
                    $query = 'SELECT `translation`'
                        . ' FROM core_field_son'
                        . ' WHERE id_common_son = ' . (int) $_POST['field_dropdown'][$id_common_filed_1]
                        . " AND lang_code = '" . getLanguage() . "'";
                    list($filed_1_translation) = sql_fetch_row(sql_query($query));
                    $code_part = substr($filed_1_translation, 1, 1);
                    $reg_code = strtoupper($code_part . '_' . $_POST['field_textfield'][$id_common_filed_2]);

                    // resolving the tree_man
                    $array_folder = $uma->getFoldersFromCode($reg_code);
                    if (empty($array_folder) && $code_is_mandatory) {
                        $res = false; // invalid code
                    }

                    break;
                default:
                    $res = false; // invalid code type

                    break;
            }
        } else {
            $res = false;
        }

        return $res;
    }
}

class UserManagerAction
{
    /**
     * @var string
     */
    public $prefix;

    /**
     * @var resource #id
     */
    public $db_conn;

    public function __construct($prefix = false, $db_conn = false)
    {
        $this->prefix = ($prefix !== false ? $prefix : $GLOBALS['prefix_fw']);
        $this->db_conn = ($db_conn !== false ? $db_conn : null);
    }
}

class UserManagerRenderer
{
    public $_register_type;
    public $_register_info;
    public $_lostpwd_type;
    public $_lostpwd_info;

    public $_style_to_use;
    public $_show_accessibility_button;
    public $_show_language_selection;

    public function __construct()
    {
        $this->_register_type = '';
        $this->_register_info = '';
        $this->_lostpwd_type = '';
        $this->_lostpwd_info = '';

        $this->_style_to_use = FormaLms\lib\Get::rel_path(_base_) . '/template/standard/' . 'style/style_login.css';
        $this->_show_accessibility_button = true;
        $this->_show_language_selection = true;
    }

    public function setStyleToUse($path_style)
    {
        $this->_style_to_use = $path_style;
    }

    public function hideLoginAccessibilityButton()
    {
        $this->_show_accessibility_button = false;
    }

    public function hideLoginLanguageSelection()
    {
        $this->_show_language_selection = false;
    }

    /**
     * @static
     * return if the user as attempt a login or not
     *
     * @return bool return TRUE if the user as try to loggin
     *              return FALSE if the user dont'try to login
     */
    public function loginAttempt()
    {
        return isset($_POST['login_userid']);
    }

    /**
     * @static
     *
     * @param string    the name of the param required
     *
     * @return mixed the value of the param required
     */
    public function getInserted($what)
    {
        switch ($what) {
            case 'userid':
                return isset($_POST['login_userid']) ? $_POST['login_userid'] : '';
                break;
            case 'password':
                return isset($_POST['login_pwd']) ? $_POST['login_pwd'] : '';
                break;
            //case 'high_accessibility' : return ( isset($_POST['login_high_accessibility']) ? true : false );break;
            //case 'remember' : return ( isset($_POST['login_remember']) ? true : false );break;
            case 'language':
                if (!isset($_POST['login_lang'])) {
                    return '';
                }
                if ($_POST['login_lang'] == 'default') {
                    return '';
                }
                $all_languages = Docebo::langManager()->getAllLangCode();

                return $all_languages[$_POST['login_lang']];

                break;
        }

        return '';
    }

    /**
     * @static
     *
     * @return array array(    'userid'=>        the userid inserted,
     *               'password'=>    the password inserted,
     *               'lang=>'        the language selected )
     */
    public static function getLoginInfo()
    {
        $info = [
            'userid' => FormaLms\lib\Get::req('login_userid', DOTY_STRING),
            'password' => FormaLms\lib\Get::req('login_pwd', DOTY_STRING),
        ];

        $all_languages = Docebo::langManager()->getAllLangCode();

        $info['lang'] = FormaLms\lib\Get::req('login_lang', DOTY_STRING, false);
        if ($info['lang'] === 'default') {
            $info['lang'] = false;
        } else {
            $info['lang'] = $all_languages[$info['lang']];
        }

        /*if (!isset($_POST['login_lang'])) $info['lang'] = false;
        elseif ($_POST['login_lang'] == 'default') $info['lang'] = false;
        else $info['lang'] = $all_languages[$_POST['login_lang']];
*/
        return $info;
    }

    /**
     * @static
     *
     * @return true if the action of delete remember is to perform or false
     */
    public function clickDeleteRemember()
    {
        if (isset($_GET['log_action']) && $_GET['log_action'] == 'deleteremember') {
            return true;
        }

        return false;
    }

    public function setAccessibility()
    {
        setAccessibilityStatus(isset($_POST['login_button_access']));
    }

    /**
     * remember the user choice about the accessibility flag.
     *
     * @return nothing
     */
    public function createRemember()
    {
        // set the cookie
        $cookie_path = cleanUrlPath(dirname($_SERVER['REQUEST_URI']) . '/' . $GLOBALS['where_config_relative'] . '/');
        $cookie_value = isset($_POST['login_high_accessibility']) ? 1 : 0;

        setcookie(
            'docebo_cookie_data[high_accessibility]',
            $cookie_value,
            time() + (365 * 24 * 3600),    // for an entire year
            $cookie_path
        );
        $_COOKIE['docebo_cookie_data']['high_accessibility'] = $cookie_value;
    }

    /**
     * delete the user choice about the accessibility flag.
     *
     * @return nothing
     */
    public function deleteRemember()
    {
        // delet the cookie if exists
        if (isset($_COOKIE['docebo_cookie_data']['high_accessibility'])) {
            $cookie_value = $_COOKIE['docebo_cookie_data']['high_accessibility'];
            unset($_COOKIE['docebo_cookie_data']['high_accessibility']);
            $cookie_path = cleanUrlPath(dirname($_SERVER['REQUEST_URI']) . '/'
                . (isset($GLOBALS['where_config_relative']) ? $GLOBALS['where_config_relative'] . '/' : ''));

            setcookie(
                'docebo_cookie_data[high_accessibility]',
                $cookie_value,
                time() - 36000,    // a lot of time ago
                $cookie_path
            );
        }
    }

    /**
     * @param string $what       enum('link', 'button')
     * @param string $info_about set the link ref for the link type or the button name
     */
    public function setRegisterTo($what, $info_about)
    {
        switch ($what) {
            case 'link':
                $this->_register_type = 'link';
                break;
            case 'button':
                $this->_register_type = 'button';
                break;
        }
        $this->_register_info = $info_about;
    }

    /**
     * @return bool if register is a button return true if is submit, false otherwise
     */
    public function clickRegister()
    {
        if ($this->_register_type == 'button') {
            return isset($_POST[$this->_register_info]);
        }

        return false;
    }

    /**
     * @param string $what       enum('link', 'button')
     * @param string $info_about set the link ref for the link type or the button name
     */
    public function setLostpwdTo($what, $info_about)
    {
        switch ($what) {
            case 'link':
                $this->_lostpwd_type = 'link';
                break;
            case 'button':
                $this->_lostpwd_type = 'button';
                break;
        }
        $this->_lostpwd_info = $info_about;
    }

    /**
     * @return bool if register is a button return true if is submit, false otherwise
     */
    public function clickLostpwd()
    {
        if ($this->_lostpwd_type == 'button') {
            return isset($_POST[$this->_lostpwd_info]);
        }

        return false;
    }

    /**
     * return html for the login mask.
     *
     *    |-form_login_ext--------------------------------|
     *    |    |-form_login----------------------------|    |
     *    |    |      ______________                    |    |
     *    |    |     |_login_userid_|    _LOG_USENAME    |    |
     *    |    |      ______________                    |    |
     *    |    |     |_login_pwd____|    _PASSWORD        |    |
     *    |    |      ______________                    |    |
     *    |    |     |_login_lang_|v|    _LOG_LANG        |    |
     *    |    |                                        |    |
     *    |    |        $advice                            |    |
     *    |    |                         ______________    |    |
     *    |    |                        |_login_button_||    |
     *    |    |---------------------------------------|    |
     *    |-----------------------------------------------|
     *
     * @param string $platform the platoform of which you want the login
     * @param string $advice   the text of an advice to dispaly
     * @param string $extra    wathever you want to display
     * @param bool   $disable  disable the field
     * @param bool   $jump_url the url for the link
     *
     * @return string the html code for the login mask
     */
    public function getLoginMask($platform, $advice = false, $extra = false, $disable = false, $register_type = 'no', $jump_url = '')
    {
        require_once _base_ . '/lib/lib.form.php';

        $lang = DoceboLanguage::createInstance('login', $platform);

        if (!isset($GLOBALS['login_tabindex'])) {
            $GLOBALS['login_tabindex'] = 1;
        }

        $all_languages = Docebo::langManager()->getAllLangCode();
        $all_languages = array_merge(['default' => $lang->def('_LANGUAGE')], $all_languages);

        if ($this->_style_to_use != false) {
            $GLOBALS['page']->addStart('<link href="' . $this->_style_to_use . '" rel="stylesheet" type="text/css" />' . "\n", 'page_head');
        }

        $out = '<div class="form_login_ext">'
            . '<div class="form_login">';

        if (!$disable) {
            if (isset($GLOBALS['page'])) {
                $GLOBALS['page']->add('<li><a href="#fieldset_login">' . $lang->def('_JUMP_TO_LOGIN') . '</a></li>', 'blind_navigation');
            }
            $out .= Form::getOpenFieldset($lang->def('_LOGIN_LEGEND'), 'fieldset_login', 'fieldset_login')
                . Form::getTextfield(
                    $lang->def('_USERNAME'),
                    'login_userid',
                    'login_userid',
                    255,
                    ''
                )
                . Form::getPassword(
                    $lang->def('_PASSWORD'),
                    'login_pwd',
                    'login_pwd',
                    255
                );

            if ($this->_show_language_selection === true) {
                $out .= Form::getDropdown(
                    $lang->def('_LANGUAGE'),
                    'login_lang',
                    'login_lang',
                    $all_languages,
                    'default'
                );
            }
            $out .= Form::getCloseFieldset();

            $out .= '<div class="form_elem_button">'
                . Form::getButton('login_button', 'login_button', $lang->def('_LOGIN'), 'yui-button', ' tabindex="' . $GLOBALS['login_tabindex']++ . '"');
            if ($this->_show_accessibility_button === true) {
                if (FormaLms\lib\Get::sett('accessibility') == 'on') {
                    $out .= '<br />'
                        . Form::getButton(
                            'login_button_access',
                            'login_button_access',
                            $lang->def('_LOGIN_ACCESSIBILITY'),
                            'log_button_access',
                            ' tabindex="' . $GLOBALS['login_tabindex']++ . '"'
                        );
                }
            }
            $out .= '</div>' . "\n";
        }
        if ($advice != '') {
            $out .= '<p class="log_advice">'
                . $advice
                . '</p>';
        }

        if ($extra != false) {
            $out .= '<p class="' . $extra['style'] . '">'
                . $extra['content']
                . '</p>';
        }
        $out .= '<p class="log_action">';

        if ($register_type == 'self' || $register_type == 'moderate') {
            switch ($this->_register_type) {
                case 'link':
                    if (FormaLms\lib\Get::sett('register_with_code') == 'on') {
                        $out .= '<a href="' . $this->_register_info . '">' . $lang->def('_LOG_REGISTER_WITH_CODE') . '</a>';
                    } else {
                        $out .= '<a href="' . $this->_register_info . '">' . $lang->def('_REGISTER') . '</a>';
                    }

                    break;
                case 'button':
                    if (FormaLms\lib\Get::sett('register_with_code') == 'on') {
                        $out .= Form::getButton('register_button', $this->_register_info, $lang->def('_LOG_REGISTER_WITH_CODE'), 'button_as_link');
                    } else {
                        $out .= Form::getButton('register_button', $this->_register_info, $lang->def('_REGISTER'), 'button_as_link');
                    }

                    break;
            }
        }
        if ($this->_register_type != '' && $this->_lostpwd_type != '') {
            $out .= '&nbsp;|&nbsp;';
        }
        switch ($this->_lostpwd_type) {
            case 'link':
                $out .= '<a href="' . $this->_lostpwd_info . '">' . $lang->def('_LOG_LOSTPWD', 'login') . '</a>';

                break;
            case 'button':
                $out .= Form::getButton('lostwd_button', $this->_lostpwd_info, $lang->def('_LOG_LOSTPWD'), 'button_as_link');

                break;
        }
        $out .= '</p>';
        $out .= '</div>'
            . '</div>';

        return $out;
    }

    public function getExtLoginMask($platform, $advice = false, $extra = false, $disable = false, $register_type = 'no', $jump_url = false)
    {
        require_once _base_ . '/lib/lib.form.php';

        $lang = DoceboLanguage::createInstance('login', $platform);

        if (!isset($GLOBALS['login_tabindex'])) {
            $GLOBALS['login_tabindex'] = 1;
        }

        $all_languages = Docebo::langManager()->getAllLangCode();
        $all_languages = array_merge(['default' => $lang->def('_LANGUAGE')], $all_languages);

        $out = '';

        if (!$disable) {
            if (isset($GLOBALS['page'])) {
                $GLOBALS['page']->add('<li><a href="#fieldset_login">' . $lang->def('_JUMP_TO_LOGIN') . '</a></li>', 'blind_navigation');
            }

            $out .= '<div class="login-line">'
                . '<p><label for="login_userid">' . $lang->def('_USERNAME') . '</label></p>'
                . '<input class="textfield" type="text" id="login_userid" name="login_userid" value="" maxlength="255" tabindex="' . $GLOBALS['login_tabindex']++ . '" />'
                . '</div>'
                . '<div class="login-line">'
                . '<p><label for="login_pwd">' . $lang->def('_PASSWORD') . '</label></p>'
                //.'<input class="textfield" type="password" id="login_pwd" name="login_pwd" maxlength="255" tabindex="'.$GLOBALS['login_tabindex']++.'" autocomplete="off" />'
                // TOLTO AUTOCOMPLETE (BUG 403)
                . '<input class="textfield" type="password" id="login_pwd" name="login_pwd" maxlength="255" tabindex="' . $GLOBALS['login_tabindex']++ . '" />'
                . '</div>'
                . '<div class="login-line">'
                . '<input class="button" type="submit" id="login" name="log_button" value="' . $lang->def('_LOGIN') . '" tabindex="' . $GLOBALS['login_tabindex']++ . '" />'
                . '</div>';
        }
        if ($extra != false) {
            $out .= '<p class="' . $extra['style'] . '">'
                . $extra['content']
                . '</p>';
        }

        return $out;
    }

    /**
     * This function must be called into a open form and it will execute the entire registration process for a user.
     *
     * @param string $platform the platform
     * @param array  $options  (register_type, use_advanced_form, pass_alfanumeric,
     *                         pass_min_char, hour_request_limit, privacy_policy, mail_sender)
     * @param string $opt_link the link used as the base of the confirmation link in the confirm mail
     *
     * @return string html for the various art of the registration process
     */
    public function getRegister($platform, $options, $opt_link)
    {
        require_once _base_ . '/lib/lib.form.php';
        require_once _base_ . '/lib/lib.table.php';
        require_once _adm_ . '/lib/lib.field.php';

        $lang = &DoceboLanguage::createInstance('register', $platform);

        if ($options['register_type'] != 'self' && $options['register_type'] != 'self_optin' && $options['register_type'] != 'moderate') {
            return '<div class="register_noactive">' . Lang::t('_REG_NOT_ACTIVE', 'register', $platform) . '</div>';
        }

        $do = 'first_of_all';
        if (isset($_POST['next_step'])) {
            switch ($_POST['next_step']) {
                case 'special_field':
                    $do = 'special_field';
                    break;
                case 'opt_in':
                    $do = 'opt_in';
                    break;
            }
        }
        $out = '';
        $this->error = false;

        $postRequest = $_POST;
        if (count($postRequest) > 0) {
            $errors = $this->_checkField($postRequest, $options, $platform, true);
        } else {
            $errors = [];
        }

        switch ($do) {
            case 'opt_in':
                if (is_array($errors) && count($errors) > 0) {
                    $this->error = true;
                }

                if ($this->error) {
                    if ($options['use_advanced_form'] == 'on' || FormaLms\lib\Get::sett('register_with_code') == 'on') {
                        $out = $this->_special_field($options, $platform, $opt_link, $errors);
                    } else {
                        $out = $this->_first_of_all($options, $platform, $errors);
                    }
                } else {
                    $out = $this->_opt_in($options, $platform, $opt_link);
                }

                break;
            case 'special_field':
                if (is_array($errors) && count($errors) > 0) {
                    foreach ($errors as $key => $error) {
                        if (!is_numeric($key)) {
                            $this->error = true;
                        }
                    }

                    if ($this->error === false) {
                        if ($postRequest['next_step'] === $do) {
                            $errors = [];
                        }
                    }
                }

                if ($this->error) {
                    $out = $this->_first_of_all($options, $platform, $errors);
                } else {
                    $out = $this->_special_field($options, $platform, $opt_link, $errors);
                }

                break;
            case 'first_of_all':
                $out = $this->_first_of_all($options, $platform, $errors);

                break;
        }

        return $out;
    }

    // TODO: move this function in UserManager ?

    /**
     * getCodeCourses.
     *
     * @param int  $reg_code
     * @param bool $byName
     *
     * @return array
     */
    private function getCodeCourses($reg_code)
    {
        $query = "SELECT re.course_list
            FROM %adm_rules_entity AS re 
            INNER JOIN %adm_rules r ON r.id_rule = re.id_rule
            LEFT JOIN %adm_org_chart_tree AS oct
            ON (oct.idst_oc = re.id_entity) 
            WHERE r.rule_type = 'orgchart' AND oct.code = '" . $reg_code . "'";
        $result = sql_query($query);
        $entity = sql_fetch_array($result);

        return $entity ? json_decode($entity['course_list']) : [];
    }

    private function getRegCodeFromNode($nodeName)
    {
        $query = "SELECT oct.idOrg
            FROM %adm_org_chart_tree AS oct
            WHERE oct.code = '" . $nodeName . "'";

        $result = sql_query($query);
        $entity = sql_fetch_array($result);

        return $entity['idOrg'];
    }

    // TODO: move this function in UserManager ?

    /**
     * processRegistrationCode.
     *
     * @param AclManager       $acl_man
     * @param UserManagerAdmin $uma
     * @param int              $iduser
     * @param string           $reg_code
     * @param string           $registration_code_type
     *
     * @return array 'success'=>boolean, 'msg'=>string
     */
    public function processRegistrationCode(&$acl_man, &$uma, $iduser, $reg_code, $registration_code_type)
    {
        $res = ['success' => true, 'msg' => ''];

        $lang = &DoceboLanguage::createInstance('register', 'lms');
        $code_is_mandatory = (FormaLms\lib\Get::sett('mandatory_code', 'off') == 'on');

        if ($reg_code != '') {
            switch ($registration_code_type) {
                case '0':
                    //nothin to do
                    ;
                    break;
                case 'tree_course':
                    //a mixed code, let's cut the tree part and go on with the tree_man and resolve here the course part
                    $course_code = substr(str_replace('-', '', $reg_code), 10, 10);
                    $reg_code = substr(str_replace('-', '', $reg_code), 0, 10);

                    //control course registration
                    require_once _lms_ . '/lib/lib.course.php';
                    $man_course_user = new Man_CourseUser();
                    $jolly_code = FormaLms\lib\Get::sett('jolly_course_code', '');
                    if ($jolly_code == '' || $jolly_code != $course_code) {
                        $course_registration_result = $man_course_user->subscribeUserWithCode($course_code, $iduser);
                        if ($course_registration_result <= 0 && $code_is_mandatory) {
                            $res['success'] = false;
                            $res['msg'] = $lang->def('_INVALID_CODE');

                            return $res;
                        }
                    }
                //procced with tree_man
                // no break
                case 'tree_man':
                    // resolving the tree_man
                    $org = sql_fetch_object(sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE code = '" . $reg_code . "'"));
                    $org_id = sql_fetch_object(sql_query("SELECT idOrg, code FROM %adm_org_chart_tree WHERE idOrg = '" . $reg_code . "' AND code IS NOT NULL"));

                    if ($org) {
                        $reg_id = $org->idOrg;
                    } elseif ($org_id) {
                        $reg_id = $org_id->idOrg;
                        $reg_code = $org_id->code;
                    }

                    $array_course = $this->getCodeCourses($reg_code);

                    $array_folder = [$reg_code => $reg_id];

                    if (empty($reg_id) && $code_is_mandatory) {
                        //invalid code
                        $res['success'] = false;
                        $res['msg'] = $lang->def('_INVALID_CODE');

                        return $res;
                    }

                    break;
                case 'code_module':
                    require_once _adm_ . '/lib/lib.code.php';
                    $code_manager = new CodeManager();
                    $valid_code = $code_manager->controlCodeValidity($reg_code);
                    if ($valid_code == 1) {
                        $array_folder = $code_manager->getOrgAssociateWithCode($reg_code);
                        $array_course = $code_manager->getCourseAssociateWithCode($reg_code);

                        $code_manager->setCodeUsed($reg_code, $iduser);
                    } elseif ($valid_code == 0) {
                        //duplicated code entered
                        $res['success'] = false;
                        $res['msg'] = $lang->def('_CODE_ALREDY_USED');

                        return $res;
                    } elseif ($valid_code == -1 && $code_is_mandatory) {
                        //invalid code entered
                        $res['success'] = false;
                        $res['msg'] = $lang->def('_INVALID_CODE');

                        return $res;
                    }

                    break;
                case 'tree_drop':
                    $query = sql_query("SELECT code FROM %adm_org_chart_tree WHERE idOrg = $reg_code LIMIT 1");
                    $reg_code = sql_fetch_array($query)['code'];
                    $reg_code = substr(str_replace('-', '', $reg_code), 0, 10);
                    $array_course = $this->getCodeCourses($reg_code);
                    $array_folder = $uma->getFoldersFromCode($reg_code);

                    break;
                case 'custom':
                    //Custom code
                    require_once _adm_ . '/lib/lib.field.php';
                    $field_man = new FieldList();

                    $id_common_filed_1 = $field_man->getFieldIdCommonFromTranslation('Filiale');
                    $id_common_filed_2 = $field_man->getFieldIdCommonFromTranslation('Codice Concessionario');
                    $query = 'SELECT `translation`'
                        . ' FROM core_field_son'
                        . ' WHERE id_common_son = ' . (int) $_POST['field_dropdown'][$id_common_filed_1]
                        . " AND lang_code = '" . getLanguage() . "'";
                    list($filed_1_translation) = sql_fetch_row(sql_query($query));
                    $code_part = substr($filed_1_translation, 1, 1);
                    $reg_code = strtoupper($code_part . '_' . $_POST['field_textfield'][$id_common_filed_2]);

                    // resolving the tree_man
                    $array_folder = $uma->getFoldersFromCode($reg_code);
                    if (empty($array_folder) && $code_is_mandatory) {
                        //invalid code
                        $res['success'] = false;
                        $res['msg'] = $lang->def('_INVALID_CODE');

                        return $res;
                    }

                    break;
            }
        } elseif ($code_is_mandatory) {
            //invalid code
            $res['success'] = false;
            $res['msg'] = $lang->def('_INVALID_CODE');

            return $res;
        }

        // now in array_folder we have the associated folder for the users
        if (!empty($array_folder)) {
            //let's find the oc and ocd
            $oc_folders = $uma->getOcFolders($array_folder);
            foreach ($oc_folders as $id => $ocs) {
                $acl_man->addToGroup($ocs[0], $iduser);
                $acl_man->addToGroup($ocs[1], $iduser);
            }
            foreach ($array_folder as $id => $folder) {
                $acl_man->addToGroup($folder, $iduser);
            }

            $enrollrules = new EnrollrulesAlms();
            $enrollrules->newRules('_NEW_USER', [$iduser], Lang::get(), current($array_folder));
        }
        // and in array_course the courses
        if (!empty($array_course)) {
            require_once Forma::include(_lms_ . '/lib/', 'lib.subscribe.php');

            foreach ($array_course as $id_course) {
                $subscriber = new CourseSubscribe_Management();
                $subscriber->subscribeUser($iduser, $id_course, '3');
            }
        }

        return $res;
    }

    public function _opt_in($options, $platform, $opt_link)
    {
        $lang = &DoceboLanguage::createInstance('register', $platform);

        // Check for error
        $errors = [];

        // Insert temporary
        $random_code = md5($_POST['register']['userid'] . mt_rand() . mt_rand() . mt_rand());
        // register as temporary user and send mail
        $acl_man = &Docebo::user()->getAclManager();
        $iduser = '';

        $iduser = $acl_man->registerTempUser(
            $_POST['register']['userid'],
            $_POST['register']['firstname'],
            $_POST['register']['lastname'],
            $_POST['register']['pwd'],
            $_POST['register']['email'],
            $random_code
        );

        if ($iduser === false) {
            $this->error = true;

            $errors = ['error' => $this->error, 'msg' => $lang->def('_OPERATION_FAILURE')];

            return $errors;
        }

        // ----

        // add base inscription policy
        $enrollrules = new EnrollrulesAlms();
        $enrollrules->newRules('_NEW_USER', [$iduser], Lang::get());

        // subscribe to groups -----------------------------------------
        if (isset($_POST['group_sel_implode'])) {
            $groups = explode(',', $_POST['group_sel_implode']);
            foreach ($groups as $idst) {
                $acl_man->addToGroup($idst, $iduser);
                // FORMA: added the inscription policy
                $enrollrules = new EnrollrulesAlms();
                $enrollrules->applyRulesMultiLang('_LOG_USERS_TO_GROUP', [(string) $iduser], false, (int) $idst, true);
                // END FORMA
            }
        }

        //if the user had enter a code we must check if there are folder related to it and add the folder's field
        $registration_code_type = FormaLms\lib\Get::sett('registration_code_type', '0');

        $code_is_mandatory = (FormaLms\lib\Get::sett('mandatory_code', 'off') == 'on');
        $reg_code = FormaLms\lib\Get::req('reg_code', DOTY_MIXED, '');
        if ($registration_code_type === 'custom') {
            $reg_code = 'change_by_custom_operation';
        }

        $array_folder = false;
        $uma = new UsermanagementAdm();
        $reg_code_res = $this->processRegistrationCode($acl_man, $uma, $iduser, $reg_code, $registration_code_type);
        if ($reg_code_res['success'] == false) {
            $acl_man->deleteTempUser($iduser);
            $this->error = true;

            $errors = ['registration' => false, 'error' => $this->error, 'msg' => $reg_code_res['msg']];

            return $errors;
        }
        // save language selected
        require_once _base_ . '/lib/lib.preference.php';
        $preference = new UserPreferences($iduser);
        $preference->setPreference('ui.language', Lang::get());

        // Save fields
        $extra_field = new FieldList();
        $extra_field->setFieldEntryTable($GLOBALS['prefix_fw'] . '_field_userentry');
        $extra_field->storeFieldsForUser($iduser);

        // Save Privacy
        $precompileLms = new PrecompileLms();
        $policy_id = $precompileLms->getPrivacyPolicyId();
        $precompileLms->setAcceptingPolicy($iduser, $policy_id, true);

        // Send mail
        $admin_mail = $options['mail_sender'];
        $sender_name = $options['mail_sender_name_from'];

        // FIX BUG 399
        $dynamicUrl = getCurrentDomain($reg_code) ?: FormaLms\lib\Get::site_url();
        $url = FormaLms\lib\Get::site_url();
        $link = $url . 'index.php?r=adm/homepage/signup&random_code=' . $random_code;
        $dynamicLink = $dynamicUrl . 'index.php?r=adm/homepage/signup&random_code=' . $random_code;
        // END FIX BUG 399

        $text = $lang->def('_REG_MAIL_TEXT');
        $text = str_replace(['[userid]', '[firstname]', '[lastname]', '[password]', '[link]', '[dynamic_link]', '[hour]'], [$_POST['register']['userid'], $_POST['register']['firstname'], $_POST['register']['lastname'], $_POST['register']['pwd'], '' . $link . '', '' . $dynamicLink . '', $options['hour_request_limit']], $text);
        $text = stripslashes($text);

        //check register_type != self (include all previous cases except the new one "self without opt-in")
        if (strcmp($options['register_type'], 'self') != 0) {
            $mailer = FormaMailer::getInstance();

            if (!$mailer->SendMail($admin_mail, [$_POST['register']['email']], Lang::t('_MAIL_OBJECT', 'register'), $text, [], [MAIL_REPLYTO => $admin_mail, MAIL_SENDER_ACLNAME => $sender_name])) {
                if ($registration_code_type == 'code_module') {
                    // ok, the registration has failed, let's remove the user association form the code
                    require_once _base_ . '/appCore/lib/lib.code.php';
                    $code_manager = new CodeManager();
                    $code_manager->resetUserAssociation($code, $iduser);
                }
                $acl_man->deleteTempUser($iduser);

                $this->error = true;
                $errors = ['registration' => false, 'error' => $this->error, 'msg' => $lang->def('_OPERATION_FAILURE')];
            } else {
                $this->error = false;
                $errors = ['registration' => true, 'error' => $this->error, 'msg' => $lang->def('_REG_SUCCESS')];
            }
        }
        //end

        $_GET['random_code'] = $random_code;
        $_GET['idst'] = $iduser;
        //check register_type = self
        if (strcmp($options['register_type'], 'self') == 0) {
            $text_self = $lang->def('_REG_MAIL_TEXT_SELF');
            $text_self = str_replace(['[userid]', '[firstname]', '[lastname]', '[password]', '[link]', '[dynamic_link]'], [$_POST['register']['userid'], $_POST['register']['firstname'], $_POST['register']['lastname'], $_POST['register']['pwd'], '' . $link . '', '' . $dynamicLink . ''], $text_self);

            $mailer = FormaMailer::getInstance();
            if (!$mailer->SendMail($admin_mail, [$_POST['register']['email']], Lang::t('_MAIL_OBJECT_SELF', 'register'), $text_self, [], [MAIL_REPLYTO => $admin_mail, MAIL_SENDER_ACLNAME => $sender_name])) {
                $this->error = true;
                $errors = ['registration' => false, 'error' => $this->error, 'msg' => $lang->def('_OPERATION_FAILURE')];
            } else {
                $this->error = false;
                $this->confirmRegister($this->_platform, $options);
                $errors = ['registration' => true, 'error' => $this->error, 'msg' => $lang->def('_REG_SUCCESS')];
            }
        }
        //end
        return $errors;
    }

    public function _special_field($options, $platform, $opt_link, $errors)
    {
        $lang = &DoceboLanguage::createInstance('register', $platform);

        // Check for error
        $out = '';

        // if the user had enter a code we must check if there are folder related to it and
        // add the folder's field
        $registration_code_type = FormaLms\lib\Get::sett('registration_code_type', '0');
        $code_is_mandatory = FormaLms\lib\Get::sett('mandatory_code', 'off');
        $reg_code = FormaLms\lib\Get::req('reg_code', DOTY_MIXED, '');

        if ($registration_code_type === 'custom') {
            $reg_code = 'change_by_custom_operation';
        }

        $array_folder = false;
        $folder_group = false;
        $uma = new UsermanagementAdm();
        if ($reg_code != '') {
            switch ($registration_code_type) {
                case '0':
                    //nothin to do
                    ;
                    break;
                case 'tree_course':
                    //a mixed code, let's cut the tree part and go on with the tree_man
                    $reg_code = substr(str_replace('-', '', $reg_code), 0, 10);
                //procced with tree_man
                // no break
                case 'tree_man':
                    // resolving the tree_man
                    $uma = new UsermanagementAdm();
                    $reg_codeId = $this->getRegCodeFromNode($reg_code);
                    $array_folder = $uma->getFolderGroups($reg_codeId);

                    break;
                case 'code_module':
                    require_once _adm_ . '/lib/lib.code.php';
                    $code_manager = new CodeManager();
                    $valid_code = $code_manager->controlCodeValidity($reg_code);
                    if ($valid_code == 1) {
                        $array_folder = $code_manager->getOrgAssociateWithCode($reg_code);
                    }

                    break;
                case 'tree_drop':
                    // from the dropdown we will recive the id of the folder
                    // then we get the oc and ocd
                    $array_folder = $uma->getFolderGroups($reg_code);

                    break;
                case 'custom':
                    //Custom code
                    require_once _adm_ . '/lib/lib.field.php';
                    $field_man = new FieldList();

                    $id_common_filed_1 = $field_man->getFieldIdCommonFromTranslation('Filiale');
                    $id_common_filed_2 = $field_man->getFieldIdCommonFromTranslation('Codice Concessionario');
                    $query = 'SELECT `translation`'
                        . ' FROM core_field_son'
                        . ' WHERE id_common_son = ' . (int) $_POST['field_dropdown'][$id_common_filed_1]
                        . " AND lang_code = '" . getLanguage() . "'";
                    list($filed_1_translation) = sql_fetch_row(sql_query($query));
                    $code_part = substr($filed_1_translation, 1, 1);
                    $reg_code = strtoupper($code_part . '_' . $_POST['field_textfield'][$id_common_filed_2]);

                    // resolving the tree_man
                    $array_folder = $uma->getFoldersFromCode($reg_code);

                    break;
            }
        }

        if ($array_folder !== false) {
            if ($folder_group === false) {
                $folder_group = [];
            }
            foreach ($array_folder as $id_org_folder) {
                $folder_group[] = Docebo::aclm()->getGroupST('/oc_' . $id_org_folder);
            }
        }

        // find all the related extra field
        $extra_field = new FieldList();

        $play_field = $extra_field->playFieldsForUser(
            0,
            (isset($_POST['group_sel'])
                ? $_POST['group_sel']
                : (isset($_POST['group_sel_implode'])
                    ? explode(',', $_POST['group_sel_implode'])
                    : $array_folder)),
            false,
            true,
            false,
            false,
            false,
            true,
            $errors
        );

        if ($play_field === false) {
            return $this->_opt_in($options, $platform, $opt_link);
        }

        $out .= '<div class="homepage__row homepage__row--gray homepage__back">
	                <a href="javascript:history.back()">
		                <span class="fa fa-chevron-left"></span>' . str_replace('<', '', $lang->def('_BACK', 'standard')) . '
	                </a>
                </div>';

        $out .= '<div class="homepage__row homepage__row--gray">
	                        <p>' . $lang->def('_GROUPS_FIELDS') . '</p>
                </div>';

        $out .= '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">';

        $out .= Form::getHidden('next_step', 'next_step', 'opt_in')

            . Form::getHidden('register_userid', 'register[userid]', $_POST['register']['userid'])
            . Form::getHidden('register_email', 'register[email]', $_POST['register']['email'])
            . Form::getHidden('register_firstname', 'register[firstname]', $_POST['register']['firstname'])
            . Form::getHidden('register_lastname', 'register[lastname]', $_POST['register']['lastname'])
            . Form::getHidden('register_pwd', 'register[pwd]', $_POST['register']['pwd'])
            . Form::getHidden('register_pwd_retype', 'register[pwd_retype]', $_POST['register']['pwd_retype'])
            . Form::getHidden('register_privacy', 'register[privacy]', 'ok');

        if (!empty($_POST['group_sel'])) { //&& !empty($_POST['group_sel_implode'])) {
            $out .= Form::getHidden('group_sel_implode', 'group_sel_implode', (isset($_POST['group_sel'])
                ? implode(',', $_POST['group_sel'])
                : (isset($_POST['group_sel_implode']) ? $_POST['group_sel_implode'] : '')));
        }

        $out .= ($reg_code != ''
                ? Form::getHidden('reg_code', 'reg_code', $reg_code)
                : '')

            // show ohter field
            . $play_field

            . Form::getBreakRow()
            . Form::closeElementSpace();

        $out .= '<div class="homepage__row">'
            . '<div class="col-xs-12 col-sm-6 col-sm-offset-3">'
            //. '<button type="submit" class="forma-button forma-button--black">Registrati</button>'
            . Form::getButton('reg_button', 'reg_button', $lang->def('_REGISTER'), ' forma-button forma-button--black ')
            . '</div>'
            . '</div>';

        return $out;
    }

    private function _first_of_all($options, $platform, $errors = [])
    {
        $precompileLms = new PrecompileLms();
        $lang = &DoceboLanguage::createInstance('register', $platform);

        $out = '';
        if ($options['use_advanced_form'] == 'off') {
            $out .= Form::getHidden('next_step', 'next_step', 'opt_in');
        } else {
            $out .= Form::getHidden('next_step', 'next_step', 'special_field');
        }
        $mand_symbol = '*';
        $mand_span = '<span class="mandatory">' . $mand_symbol . '</span>';

        $out .= '<div class="homepage__row homepage__row--gray homepage__back">
	                <a href="./index.php">
		                <span class="fa fa-chevron-left"></span>' . str_replace('<', '', $lang->def('_BACK', 'standard')) . '
	                </a>
                </div>';

        $out .= '<div class="homepage__row homepage__row--gray">
	                <p>' . $lang->def('_REG_NOTE') . '</p>
                </div>';

        $lang_sel = getLanguage();
        $full_langs = [];
        $langs = Docebo::langManager()->getAllLangCode();
        $full_langs = [];
        foreach ($langs as $v) {
            $full_langs[$v] = ucfirst($v);
        }

        /* FIRST ROW */
        $out .= '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">';

        /** USER ID */
        $error = (isset($errors) && $errors['userid']);
        $errorMessage = $errors['userid']['msg'];
        $out .= '<div class="col-xs-12 col-sm-6">'
            . Form::getInputTextfield(
                'form-control ' . ($error ? 'has-error' : ''),
                'register_userid',
                'register[userid]',
                (isset($_POST['register']['userid']) ? stripslashes($_POST['register']['userid']) : ''),
                '',
                255,
                'placeholder="' . $lang->def('_USERNAME') . ' ' . $mand_symbol . '"'
            );

        if ($error) {
            $out .= '<small class="form-text">* ' . $errorMessage . '</small>';
        }
        $out .= '</div>';

        /** EMAIL */
        $error = (isset($errors) && $errors['email']);
        $errorMessage = $errors['email']['msg'];
        $out .= '<div class="col-xs-12 col-sm-6">'
            . Form::getInputTextfield(
                'form-control ' . ($error ? 'has-error' : ''),
                'register_email',
                'register[email]',
                (isset($_POST['register']['email']) ? stripslashes($_POST['register']['email']) : ''),
                '',
                255,
                'placeholder="' . $lang->def('_EMAIL') . ' ' . $mand_symbol . '"'
            );
        if ($error) {
            $out .= '<small class="form-text">* ' . $errorMessage . '</small>';
        }

        $out .= '</div>';

        $out .= '</div>';
        /* END FIRST ROW */

        /* SECOND ROW */
        $out .= '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">';

        /** FIRST NAME */
        $error = (isset($errors) && $errors['firstname']);
        $errorMessage = $errors['firstname']['msg'];
        $out .= '<div class="col-xs-12 col-sm-4">'
            . Form::getInputTextfield(
                'form-control ' . ($error ? 'has-error' : ''),
                'register_firstname',
                'register[firstname]',
                (isset($_POST['register']['firstname']) ? stripslashes($_POST['register']['firstname']) : ''),
                '',
                255,
                'placeholder="' . $lang->def('_FIRSTNAME') . ($options['lastfirst_mandatory'] == 'on' ? ' ' . $mand_symbol : '') . '"'
            );
        if ($error) {
            $out .= '<small class="form-text">* ' . $errorMessage . '</small>';
        }

        $out .= '</div>';

        $error = (isset($errors) && $errors['lastname']);
        $errorMessage = $errors['lastname']['msg'];
        $out .= '<div class="col-xs-12 col-sm-4">'
            . Form::getInputTextfield(
                'form-control ' . ($error ? 'has-error' : ''),
                'register_lastname',
                'register[lastname]',
                (isset($_POST['register']['lastname']) ? stripslashes($_POST['register']['lastname']) : ''),
                '',
                255,
                'placeholder="' . $lang->def('_LASTNAME') . ($options['lastfirst_mandatory'] == 'on' ? ' ' . $mand_symbol : '') . '"'
            );
        if ($error) {
            $out .= '<small class="form-text">* ' . $errorMessage . '</small>';
        }

        $out .= '</div>';

        $out .= '<div class="col-xs-12 col-sm-4">'
            . Form::getInputDropdown(
                $lang->def('_LANGUAGE'),
                'new_lang',
                'new_lang',
                $full_langs,
                $lang_sel,
                '',
                '',
                ' onchange="submit();"'
            ) .
            '</div>';

        $out .= '</div>';
        /* END SECOND ROW */

        /* THIRD ROW */
        $out .= '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">';

        $error = (isset($errors) && $errors['pwd']);
        $errorMessage = $errors['pwd']['msg'];
        $out .= '<div class="col-xs-12 col-sm-6">'
            . Form::getInputPassword(
                'form-control ' . ($error ? 'has-error' : ''),
                'register_pwd',
                'register[pwd]',
                '',
                255,
                'placeholder="' . $lang->def('_PASSWORD') . ' ' . $mand_symbol . '"'
            );
        if ($error) {
            $out .= '<small class="form-text">* ' . $errorMessage . '</small>';
        }

        $out .= '</div>';

        $out .= '<div class="col-xs-12 col-sm-6">'
            . Form::getInputPassword(
                'form-control ' . ($error ? 'has-error' : ''),
                'register_pwd_retype',
                'register[pwd_retype]',
                '',
                255,
                'placeholder="' . $lang->def('_RETYPE_PASSWORD') . ' ' . $mand_symbol . '"'
            );
        if ($error) {
            $out .= '<small class="form-text">* ' . $errorMessage . '</small>';
        }

        $out .= '</div>';

        $out .= '</div>';
        /* END THIRD ROW */

        $out .= Form::getHidden('sop', 'sop', 'changelang');

        $registration_code_type = FormaLms\lib\Get::sett('registration_code_type', '0');
        $code_is_mandatory = FormaLms\lib\Get::sett('mandatory_code', 'off');
        switch ($registration_code_type) {
            case '0':
                //nothin to do
                ;
                break;
            case 'tree_course':
            case 'code_module':
            case 'tree_man':
                $error = (isset($errors) && $errors['reg_code']);
                $errorMessage = $errors['reg_code']['msg'];
                $cssClass = 'form-control';
                if ($error) {
                    $out .= '<div class="has-error">';
                    $cssClass .= ' has-error';
                }

                // we must ask the user to insert a manual code
                $out .= '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">
                            <div class="col-xs-12 col-sm-6">';
                $out .= Form::getInputTextfield(
                    $cssClass,
                    'reg_code',
                    'reg_code',
                    FormaLms\lib\Get::req('reg_code', DOTY_MIXED, ''),
                    '',
                    24,
                    'placeholder="' . $lang->def('_CODE') . ($code_is_mandatory ? ' ' . $mand_symbol : '') . '"'
                );

                if ($error) {
                    $out .= '<small class="form-text">* ' . $errorMessage . '</small>
                </div>';
                }
                $out .= '</div>';
                $out .= '</div>';
                break;
            case 'tree_drop':
                // we must show to the user a selection of code
                $uma = new UsermanagementAdm();
                $tree_names = ['-' => $lang->def('_CODE') . ($code_is_mandatory ? ' ' . $mand_span : '')];
                $tree_names = $tree_names + $uma->getAllFolderNames(true);
                $error = (isset($errors) && $errors['reg_code']);
                $errorMessage = $errors['reg_code']['msg'];
                $cssClass = 'form-control';
                if ($error) {
                    $out .= '<div class="has-error">';
                    $cssClass .= ' has-error';
                }

                $out .= '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid">
                            <div class="col-xs-12 col-sm-6">'
                    . Form::getInputDropdown(
                        $cssClass,
                        'reg_code',
                        'reg_code',
                        $tree_names,
                        FormaLms\lib\Get::req('reg_code', DOTY_MIXED, ''),
                        '',
                        true
                    );

                if ($error) {
                    $out .= '<small class="form-text">* ' . $errorMessage . '</small>
                </div>';
                }
                $out .= '</div>';
                $out .= '</div>';
                break;
            default:
                break;
        }

        if ($options['use_advanced_form'] == 'off') {
            $extra_field = new FieldList();
            $extraFiledsOut = $extra_field->playFieldsForUser(0, false, false, true, false, false, false, true, $errors);

            $out .= $extraFiledsOut;
        } elseif ($options['use_advanced_form'] == 'on') {
            $acl_man = &Docebo::user()->getAclManager();
            $groups = &$acl_man->getAllGroupsId(['free', 'moderate']);

            if (!empty($groups)) {
                $out .= '<div class="homepage__row homepage__row--gray">'
                    . $lang->def('_SUBSCRIBE')
                    . '</div>'
                    . '<div class="homepage__row homepage__row--form homepage__row--gray row-fluid clearfix">';

                foreach ($groups as $id => $info) {
                    $hasDescription = ('' !== $info['description'] && null !== $info['description']);
                    $out .= '<div class="col-xs-12 col-sm-3 ' . ($hasDescription ? 'has-forma-tooltip' : '') . '">';

                    $out .= Form::getInputCheckbox(
                            'group_sel_' . $id,
                            'group_sel[]',
                            $id,
                            isset($_POST['group_sel'][$id]),
                            ''
                        )
                        . '<label class="checkbox-inline" for="group_sel_' . $id . '"for="group_sel_' . $id . '">' . $info['type_ico'] . ' ' . $info['groupid'] . '</label>';

                    if ($hasDescription) {
                        $out .= '<div class="forma-tooltip">' . $info['description'] . '</div>';
                    }
                    $out .= '</div>';
                }

                $out .= '</div>';
            }
        }

        $out .= '<div class="homepage__row homepage__row--form-disclaimer"> '
            . '<p class="mCustomScrollbar" data-mcs-theme="minimal-dark">' . $precompileLms->getPrivacyPolicyText() . '</p>'
            . '</div>';

        if ($options['privacy_policy'] == 'on') {
            $error = (isset($errors) && $errors['privacy']);
            $errorMessage = $errors['privacy']['msg'];
            $out .= '<div class="homepage__row homepage__row--privacy">';
            if ($error) {
                $out .= '<div class="has-error">';
            }

            $out .= Form::getInputCheckbox(
                    'register_privacy',
                    'register[privacy]',
                    'ok',
                    isset($_POST['register']['privacy']),
                    ''
                )
                . '<label class="checkbox-inline">' . $lang->def('_REG_PRIVACY_ACCEPT') . '</label>'
                . '</div>';

            if ($error) {
                $out .= '<small class="form-text">* ' . $errorMessage . '</small>
                </div>';
            }
        }

        $out .= '<div class="homepage__row">'
            . '<div class="col-xs-12 col-sm-6 col-sm-offset-3">'
            //. '<button type="submit" class="forma-button forma-button--black">Registrati</button>'
            . Form::getButton('reg_button', 'reg_button', $lang->def('_REGISTER'), ' forma-button forma-button--black ')
            . '</div>'
            . '</div>';

        $out .= '<div class="homepage__row homepage__links">'
            . '<a href="' . FormaLms\lib\Get::rel_path('base') . '/index.php"><em>' . $lang->def('_LOGIN') . '</em></a>'
            . '</div>';

        return $out;
    }

    public function confirmRegister($platform, $options)
    {
        $lang = &DoceboLanguage::createInstance('register', $platform);
        $acl_man = &Docebo::user()->getAclManager();
        $acl = &Docebo::user()->getAcl();

        if (!isset($_GET['random_code'])) {
        }
        $random_code = $_GET['random_code'];
        if (strpos($random_code, '?') !== false) {
            $random_code = substr($random_code, 0, strpos($random_code, '?'));
        }

        $request = $acl_man->getTempUserInfo(false, $random_code);

        if (time() > (fromDatetimeToTimestamp($request['request_on']) + (3600 * (int) $options['hour_request_limit']))) {
            $out = '<div class="reg_err_data">' . $lang->def('_REG_ELAPSEDREQUEST', 'register') . '</div>';
            $time_limit = time() - 3600 * ((int) $options['hour_request_limit']);

            if (FormaLms\lib\Get::sett('registration_code_type', '0') == 'code_module') {
                // free the code from the old association
                require_once _adm_ . '/lib/lib.code.php';
                $code_manager = new CodeManager();
                $code_manager->resetUserAssociation($code, $request['idst']);
            }
            $acl_man->deleteTempUser(false, false, $time_limit, true);

            return $out;
        }

        if ($options['register_type'] == 'self' || $options['register_type'] == 'self_optin') {
            if ($acl_man->registerUser(
                addslashes($request['userid']),            // $userid
                addslashes($request['firstname']),        // $firstname
                addslashes($request['lastname']),        // $lastname
                $request['pass'],                        // $pass
                addslashes($request['email']),            // $email
                $request['avatar'],                        // $avatar
                '',                                        // $signature
                true,                                    // $alredy_encripted
                $request['idst'],                        // $idst
                '',                                        // $pwd_expire_at
                '',                                        // $force_change
                $request['facebook_id'],                                // $facebook_id
                $request['twitter_id'],                                    // $twitter_id
                $request['linkedin_id'],                                // $linkedin_id
                $request['google_id']
            )) {                            // $google_id
                // remove temporary enter
                //$acl_man->deleteTempUser( $request['idst'] , false, false, false );
                $acl_man->deleteTempUser($request['idst'], false, false, false, false);

                $acl_man->updateUser(
                    $request['idst'],
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    date('Y-m-d H:i:s')
                );

                // subscribe to base group
                $idst_usergroup = $acl_man->getGroup(false, ADMIN_GROUP_USER);
                $idst_usergroup = $idst_usergroup[ACL_INFO_IDST];

                $idst_oc = $acl_man->getGroup(false, '/oc_0');
                $idst_oc = $idst_oc[ACL_INFO_IDST];

                $idst_ocd = $acl_man->getGroup(false, '/ocd_0');
                $idst_ocd = $idst_ocd[ACL_INFO_IDST];

                $acl_man->addToGroup($idst_usergroup, $request['idst']);
                $acl_man->addToGroup($idst_oc, $request['idst']);
                $acl_man->addToGroup($idst_ocd, $request['idst']);

                //  aggiunta notifica UserNewWaiting
                require_once _base_ . '/lib/lib.eventmanager.php';

                // set as recipients all who can approve a waiting user
                $msg_c_new = new EventMessageComposer();

                $msg_c_new->setSubjectLangText('email', '_TO_NEW_USER_SBJ', false);
                $msg_c_new->setBodyLangText('email', '_TO_NEW_USER_TEXT', ['[url]' => FormaLms\lib\Get::site_url()]);

                $msg_c_new->setBodyLangText('sms', '_TO_NEW_USER_TEXT_SMS', ['[url]' => FormaLms\lib\Get::site_url()]);
                $idst_approve = $acl->getRoleST('/framework/admin/directory/approve_waiting_user');

                $recipients = $acl_man->getAllRoleMembers($idst_approve);

                if (!empty($recipients)) {
                    createNewAlert(
                        'UserNewWaiting',
                        'directory',
                        'edit',
                        '1',
                        'User waiting for approvation',
                        $recipients,
                        $msg_c_new
                    );
                }
                // end

                $out = '<div class="reg_success">' . $lang->def('_REG_YOUR_ABI_TO_ACCESS', 'register') . '</div>';

                return $out;
            } else {
                $out = '<div class="reg_err_data">' . $lang->def('_REG_CONFIRM_FAILED', 'register') . '</div>';

                return $out;
            }
        } elseif ($options['register_type'] == 'moderate') {
            if ($acl_man->confirmTempUser($request['idst'])) {
                if (FormaLms\lib\Get::sett('use_code_module') == 'on') {
                    require_once _adm_ . '/lib/lib.code.php';

                    $code_manager = new CodeManager();

                    $code = $code_manager->getCodeAssociate($request['idst']);

                    if ($code !== false) {
                        $array_course = $code_manager->getCourseAssociateWithCode($code);
                        $array_folder = $code_manager->getOrgAssociateWithCode($code);

                        if (count($array_course)) {
                            foreach ($array_course as $id_course) {
                                require_once Forma::include(_lms_ . '/lib/', 'lib.subscribe.php');

                                $subscribe = new CourseSubscribe_Management();

                                $subscribe->subscribeUser($request['idst'], $id_course, '3');
                            }
                        }

                        if (count($array_folder)) {
                            foreach ($array_folder as $id_folder) {
                                $group = $acl_man->getGroup($id_folder, false);
                                $group_d = $acl_man->getGroup(false, '/ocd_' . str_replace('/oc_', '', $group[ACL_INFO_GROUPID]));

                                if ($group) {
                                    $acl_man->addToGroup($group[ACL_INFO_IDST], $request['idst']);
                                }
                                if ($group_d) {
                                    $acl_man->addToGroup($group_d[ACL_INFO_IDST], $request['idst']);
                                }
                            }
                        }
                    }
                }

                $out = '<div class="reg_success">' . Lang::t('_REG_WAIT_FOR_ADMIN_OK', 'register') . '</div>';
                // send alert to admin that can approve
                require_once _base_ . '/lib/lib.eventmanager.php';

                // set as recipients all who can approve a waiting user
                $msg_c_approve = new EventMessageComposer();

                $msg_c_approve->setSubjectLangText('email', '_TO_APPROVE_USER_SBJ', false);
                $msg_c_approve->setBodyLangText('email', '_TO_APPROVE_USER_TEXT', ['[url]' => FormaLms\lib\Get::site_url()]);

                $msg_c_approve->setBodyLangText('sms', '_TO_APPROVE_USER_TEXT_SMS', ['[url]' => FormaLms\lib\Get::site_url()]);
                $idst_approve = $acl->getRoleST('/framework/admin/directory/approve_waiting_user');

                $recipients = $acl_man->getAllRoleMembers($idst_approve);

                if (!empty($recipients)) {
                    createNewAlert(
                        'UserNewModerated',
                        'directory',
                        'edit',
                        '1',
                        'User waiting for approvation',
                        $recipients,
                        $msg_c_approve
                    );
                }

                return $out;
            } else {
                $out = '<div class="reg_err_data">' . $lang->def('_REG_CONFIRM_FAILED', 'register') . '</div>';

                return $out;
            }
        }
    }

    /**
     * Control the contents of the field.
     *
     * @param array $source  the values to check
     * @param array $options the values needed for control
     *
     * @return array ( [error]  => true o false , [msg] => error message)
     */
    public function _checkField($source, $options, $platform, $control_extra_field = true)
    {
        $lang = &DoceboLanguage::createInstance('register', $platform);

        $errors = [];

        // control if the inserted data is valid
        if ($options['privacy_policy'] === 'on') {
            if (!isset($source['register']['privacy'])) {
                $error = [
                    'error' => true,
                    'msg' => $lang->def('_ERR_POLICY_NOT_CHECKED'),
                ];

                $errors['privacy'] = $error;
            } elseif ($source['register']['privacy'] != 'ok') {
                $error = [
                    'error' => true,
                    'msg' => $lang->def('_ERR_POLICY_NOT_CHECKED'),
                ];

                $errors['privacy'] = $error;
            }
        }

        $codeIsMandatory = FormaLms\lib\Get::sett('mandatory_code', 'off') === 'on';
        $regCode = FormaLms\lib\Get::req('reg_code', DOTY_MIXED, '');
        $registrationCodeType = FormaLms\lib\Get::sett('registration_code_type', '0');

        if ($codeIsMandatory) {
            $codeIsValid = (new UserManager())->checkRegistrationCode($regCode, $registrationCodeType);

            if (!$codeIsValid) {
                $error = [
                    'error' => true,
                    'msg' => $lang->def('_ERR_INVALID_CODE'),
                ];

                $errors['reg_code'] = $error;
            }
        }

        // control mail is correct
        $acl_man = &Docebo::user()->getAclManager();
        $source['register']['email'] = strtolower($source['register']['email']);

        if ($source['register']['email'] === '') {
            $error = [
                'error' => true,
                'msg' => $lang->def('_ERR_INVALID_MAIL'),
            ];

            $errors['email'] = $error;
        } elseif (!preg_match("/^([a-z0-9_\-]|\\.[a-z0-9_])+@(([a-z0-9_\-]|\\.-)+\\.)+[a-z]{2,8}$/", $source['register']['email'])) {
            $error = [
                'error' => true,
                'msg' => $lang->def('_ERR_INVALID_MAIL'),
            ];

            $errors['email'] = $error;
        } elseif (preg_match("[\r\n]+", $source['register']['email'])) {
            $error = [
                'error' => true,
                'msg' => $lang->def('_ERR_INVALID_MAIL'),
            ];

            $errors['email'] = $error;
        } elseif ($acl_man->getUserByEmail($source['register']['email']) !== false) {
            $error = [
                'error' => true,
                'msg' => $lang->def('_ERR_DUPLICATE_MAIL'),
            ];

            $errors['email'] = $error;
        } elseif (($tuser = $acl_man->getTempUserByEmail($source['register']['email'])) !== false) {
            $msg = $lang->def('_ERR_DUPLICATE_RESEND');
            $error = [
                'error' => true,
                'msg' => $msg,
            ];

            $errors['email'] = $error;
        }

        // check if userid has been inserted
        $user = $acl_man->getUserST($source['register']['userid']);
        $temp_user = $acl_man->getTempUserInfo($source['register']['userid']);

        if ($source['register']['userid'] === '' || $source['register']['userid'] === $lang->def('_REG_USERID_DEF')) {
            $error = [
                'error' => true,
                'msg' => $lang->def('_ERR_INVALID_USER'),
            ];

            $errors['userid'] = $error;
        } // control if userid is duplicate
        elseif ($user !== false || $temp_user !== false) {
            $error = [
                'error' => true,
                'msg' => $lang->def('_ERR_DUPLICATE_USER'),
            ];

            $errors['userid'] = $error;
        }

        // control password
        if (strlen($_POST['register']['pwd']) < $options['pass_min_char']) {
            $error = [
                'error' => true,
                'msg' => $lang->def('_PASSWORD_TOO_SHORT'),
            ];

            $errors['pwd'] = $error;
        } elseif ($_POST['register']['pwd'] !== $source['register']['pwd_retype']) {
            $error = [
                'error' => true,
                'msg' => $lang->def('_ERR_PASSWORD_NO_MATCH'),
            ];

            $errors['pwd'] = $error;
        } elseif ($options['pass_alfanumeric'] === 'on') {
            if (!preg_match('/[a-z]/i', $source['register']['pwd']) || !preg_match('/[0-9]/', $source['register']['pwd'])) {
                $error = [
                    'error' => true,
                    'msg' => $lang->def('_ERR_PASSWORD_MUSTBE_ALPHA'),
                ];

                $errors['pwd'] = $error;
            }
        }

        if ($options['lastfirst_mandatory'] == 'on') {
            if (trim($source['register']['firstname']) == '' || trim($source['register']['lastname']) == '') {
                $error = [
                    'error' => true,
                    'msg' => $lang->def('_SOME_MANDATORY_EMPTY'),
                ];

                $errors['firstname'] = $error;

                $errors['lastname'] = $error;
            }
        }

        if ($control_extra_field) {
            $selectedGroups = \FormaLms\lib\Get::pReq('group_sel_implode', DOTY_STRING, '');
            if (empty($selectedGroups)) {
                $selectedGroups = explode(',', $selectedGroups);
            }
            if (empty($selectedGroups)) {
                $selectedGroups = \FormaLms\lib\Get::pReq('group_sel', DOTY_MIXED, []);
            }

            if ($options['use_advanced_form'] == 'on' || FormaLms\lib\Get::sett('register_with_code') == 'on') {
                $reg_code = FormaLms\lib\Get::req('reg_code', DOTY_MIXED, '');
                $uma = new UsermanagementAdm();
                $array_folder = $uma->getFolderGroups($reg_code);

                if ($selectedGroups) {
                    $selectedGroups = array_merge($selectedGroups, $array_folder);
                } else {
                    $selectedGroups = $array_folder;
                }
            }

            $extra_field = new FieldList();
            $re_filled = $extra_field->isFilledFieldsForUserInRegistration(0, $selectedGroups);
            if ($re_filled !== true) {
                foreach ($re_filled as $key => $value) {
                    $errors[$key] = $value;
                }
            }
        }

        return $errors;
    }

    /**
     * @return string the html needed for the lost user / password mask
     */
    public function getLostpwd($jump_url, $platform)
    {
        $lang = &DoceboLanguage::createInstance('register', $platform);

        require_once _base_ . '/lib/lib.form.php';

        $html = '';
        // request form
        $html .=
            '<div class="lostpwd_box">' . "\n"
            //.'<img class="lostpwd_logo" src="'.getPathImage().'login/key_identity.png" alt="'.$lang->def('_USERNAME').'" />'
            . '<span class="text_bold">' . $lang->def('_LOST_TITLE_USER') . ' - </span>'
            . $lang->def('_LOST_INSTRUCTION_USER');

        if (FormaLms\lib\Get::sett('ldap_used') == 'off') {
            $html .= Form::openForm('lost_user', $jump_url)
                . Form::openElementSpace('form_right')
                . Form::getLabel('email', $lang->def('_EMAIL'), 'text_bold')
                //$css_text, $id, $name, $value, $alt_name, $maxlenght, $other_param
                . Form::getInputTextfield('textfield', 'email', 'email', '', strip_tags($lang->def('_EMAIL')), 255, '')
                . Form::getButton('email_ins', 'email_ins', $lang->def('_SEND'), 'button_nowh')
                . Form::closeElementSpace()
                . Form::closeForm();
        } else {
            $html .= '<div class="form_right"><span class="font_red">' . $lang->def('_LDAPACTIVE') . '</span></div>';
        }
        $html .= '</div>';

        $html .=
            '<div class="lostpwd_box">' . "\n"
            //.'<img class="lostpwd_logo" src="'.getPathImage().'login/key_pwd.gif" alt="'.$lang->def('_USERNAME').'" />'
            . '<span class="text_bold">' . $lang->def('_LOST_TITLE_PWD') . ' - </span>'
            . $lang->def('_LOST_INSTRUCTION_PWD')
            . Form::openForm('lost_pwd', $jump_url)
            . Form::openElementSpace('form_right')
            . Form::getLabel('email', $lang->def('_USERNAME'), 'text_bold')
            //$css_text, $id, $name, $value, $alt_name, $maxlenght, $other_param
            . Form::getInputTextfield('textfield', 'userid', 'userid', '', strip_tags($lang->def('_USERNAME')), 255, '')
            . Form::getButton('userid_ins', 'userid_ins', $lang->def('_SEND'), 'button_nowh')
            . Form::closeElementSpace()
            . Form::closeForm()
            . '</div>';

        return $html;
    }

    /**
     * @return bool true if the action to perform is  to send email for recover  password
     */
    public function haveToLostUserAction()
    {
        if (isset($_POST['email_ins'])) {
            return true;
        }

        return false;
    }

    public function getLostUserParam()
    {
        if (isset($_POST['email'])) {
            return $_POST['email'];
        }

        return false;
    }

    /**
     * @return bool true if the action to perform is  to send email for recover user
     */
    public function haveToLostpwdAction()
    {
        if (isset($_POST['userid_ins'])) {
            return true;
        }

        return false;
    }

    public function getLostPwdParam()
    {
        if (isset($_POST['userid'])) {
            return $_POST['userid'];
        }

        return false;
    }

    public function getRenderedProfile($user_info)
    {
        require_once _base_ . '/lib/lib.form.php';
        $lang = &DoceboLanguage::createInstance('profile', 'framework');

        $path = FormaLms\lib\Get::site_url() . $GLOBALS['where_files_relative'] . '/appCore/' . FormaLms\lib\Get::sett('pathphoto');

        $txt = '<div>'
            . '<div class="boxinfo_title">' . $lang->def('_USERPARAM') . '</div>'
            . Form::getLineBox($lang->def('_USERNAME'), $user_info[ACL_INFO_USERID])
            . Form::getLineBox($lang->def('_LASTNAME'), $user_info[ACL_INFO_LASTNAME])
            . Form::getLineBox($lang->def('_NAME'), $user_info[ACL_INFO_FIRSTNAME])
            . Form::getLineBox($lang->def('_EMAIL'), $user_info[ACL_INFO_EMAIL])
            . Form::getBreakRow()
            . '<div class="boxinfo_title">' . $lang->def('_USERFORUMPARAM') . '</div>'
            . '<table class="profile_images">'
            . '<tr><td>';
        // NOTE: avatar
        if ($user_info[ACL_INFO_AVATAR] != '') {
            $txt .= '<img class="profile_image" src="' . $path . $user_info[ACL_INFO_AVATAR] . '" alt="' . $lang->def('_AVATAR') . '" /><br />';
        } else {
            $txt .= '<div class="text_italic">' . $lang->def('_NOAVATAR') . '</div>';
        }
        // NOTE: signature
        $txt .= '</td></tr></table>'
            . '<div class="title">' . $lang->def('_SIGNATURE') . '</div>'
            . '<div class="profile_signature">' . $user_info[ACL_INFO_SIGNATURE] . '</div><br />' . "\n"
            . '</div>';

        return $txt;
    }

    public function getElapsedPasswordMask($platform, $options, $jump_link)
    {
        require_once _base_ . '/lib/lib.form.php';

        $lang = &DoceboLanguage::createInstance('register', $platform);

        $res = Docebo::user()->isPasswordElapsed();

        $html = '<ul class="instruction_list">';

        if ($res == 2) {
            $html .= '<li>' . $lang->def('_FORCE_CHANGE') . '</li>';
        } else {
            $html .= '<li>' . $lang->def('_WHYCHANGEPWD', 'register') . '</li>';
        }

        if ($options['pass_max_time_valid']) {
            $html .= '<li>' . str_replace('[valid_for_day]', $options['pass_max_time_valid'], $lang->def('_NEWPWDVALID')) . '</li>';
        }
        if ($options['pass_min_char']) {
            $html .= '<li>' . str_replace('[min_char]', $options['pass_min_char'], $lang->def('_REG_PASS_MIN_CHAR')) . '</li>';
        }
        if ($options['pass_alfanumeric'] == 'on') {
            $html .= '<li>' . $lang->def('_REG_PASS_MUST_BE_ALPNUM') . '</li>';
        }
        if ($options['user_pwd_history_length'] > 0) {
            $html .= '<li>' . Lang::t('_REG_PASS_MUST_DIFF', 'register', ['[diff_pwd]' => $options['user_pwd_history_length']]) . '</li>';
        }

        $html .= '</ul>' . "\n"
            . Form::openForm('update_password', $jump_link)
            . Form::openElementSpace()
            . Form::getPassword($lang->def('_OLD_PWD'), 'oldpwd', 'oldpwd', '30')
            . Form::getPassword($lang->def('_NEW_PASSWORD'), 'newpwd', 'newpwd', '30')
            . Form::getPassword($lang->def('_RETYPE_PASSWORD'), 'repwd', 'repwd', '30')
            . Form::closeElementSpace()
            . Form::openButtonSpace()
            . Form::getButton('save_pwd', 'save_pwd', $lang->def('_SAVE'))
            . Form::closeButtonSpace()
            . Form::closeForm();

        return $html;
    }

    public function clickSaveElapsed()
    {
        return isset($_POST['save_pwd']);
    }

    public function saveElapsedPassword($platform, $options)
    {
        $lang = &DoceboLanguage::createInstance('register', $platform);

        $html = '';

        $idst = getLogUserId();
        $acl_man = &Docebo::user()->getAclManager();
        $user_info = $acl_man->getUser($idst, false);

        $password = new Password($_POST['oldpwd']);
        if (!$password->verify($user_info[ACL_INFO_PASS])) {
            return [
                'error' => true,
                'msg' => getErrorUi($lang->def('_ERR_PWD_OLD')),
            ];
        }
        // control password
        if (strlen($_POST['newpwd']) < $options['pass_min_char']) {
            return [
                'error' => true,
                'msg' => getErrorUi($lang->def('_PASSWORD_TOO_SHORT')),
            ];
        }
        if ($_POST['newpwd'] != $_POST['repwd']) {
            return [
                'error' => true,
                'msg' => getErrorUi($lang->def('_ERR_PASSWORD_NO_MATCH')),
            ];
        }
        if ($_POST['oldpwd'] == $_POST['newpwd']) {
            return [
                'error' => true,
                'msg' => getErrorUi($lang->def('_ERR_PWD_SAME_OLD')),
            ];
        }
        if ($options['pass_alfanumeric'] == 'on') {
            if (!preg_match('/[a-z]/i', $_POST['newpwd']) || !preg_match('/[0-9]/', $_POST['newpwd'])) {
                return [
                    'error' => true,
                    'msg' => getErrorUi($lang->def('_ERR_PASSWORD_MUSTBE_ALPHA')),
                ];
            }
        }
        //check password history

        if (FormaLms\lib\Get::sett('user_pwd_history_length') != 0) {
            $new_pwd = $acl_man->encrypt($_POST['newpwd']);
            if ($user_info[ACL_INFO_PASS] == $new_pwd) {
                return [
                    'error' => true,
                    'msg' => getErrorUi(str_replace('[diff_pwd]', FormaLms\lib\Get::sett('user_pwd_history_length'), $lang->def('_REG_PASS_MUST_DIFF'))),
                ];
            }
            $re_pwd = sql_query('SELECT passw '
                . ' FROM ' . $GLOBALS['prefix_fw'] . '_password_history'
                . ' WHERE idst_user = ' . (int) $idst . ''
                . ' ORDER BY pwd_date DESC');

            list($pwd_history) = sql_fetch_row($re_pwd);
            for ($i = 0; $pwd_history && $i < FormaLms\lib\Get::sett('user_pwd_history_length'); ++$i) {
                if ($pwd_history == $new_pwd) {
                    return [
                        'error' => true,
                        'msg' => getErrorUi(str_replace('[diff_pwd]', FormaLms\lib\Get::sett('user_pwd_history_length'), $lang->def('_REG_PASS_MUST_DIFF'))),
                    ];
                }
                list($pwd_history) = sql_fetch_row($re_pwd);
            }
        }

        // save the password
        $re = $acl_man->updateUser(
            $idst,
            false,
            false,
            false,
            $_POST['newpwd'],
            false,
            false,
            false,
            date('Y-m-d H:i:s'),
            false,
            0
        );

        return [
            'error' => false,
            'msg' => '',
        ];
    }
}

class UserManagerOption
{
    /**
     * @var string
     */
    public $_table;

    /**
     * @var array
     */
    public $_options;

    /**
     * Class constructor.
     *
     * @param string $table secified a different table from the default one
     */
    public function __construct($table = false)
    {
        if ($table === false) {
            $this->_table = $GLOBALS['prefix_fw'] . '_setting';
        } else {
            $this->_table = $table;
        }

        $this->_options = [];
    }

    /**
     * load option form database.
     *
     * @return nothing
     */
    public function _loadOption()
    {
        $reSetting = sql_query('
		SELECT param_name, param_value, value_type, max_size
		FROM ' . $this->_table . '
		ORDER BY sequence');
        while (list($var_name, $var_value, $value_type) = sql_fetch_row($reSetting)) {
            switch ($value_type) {
                //if is int cast it
                case 'int':
                    $this->_options[$var_name] = (int) $var_value;

                    break;
                //if is enum switch value to on or off
                case 'enum':
                    if ($var_value == 'on') {
                        $this->_options[$var_name] = 'on';
                    } else {
                        $this->_options[$var_name] = 'off';
                    }

                    break;
                //else simple assignament
                default:
                    $this->_options[$var_name] = $var_value;
            }
        }
    }

    /**
     * get all the available option.
     *
     * @return array array(ption_name => option_value)
     */
    public function getAllOption()
    {
        if (empty($this->_options)) {
            $this->_loadOption();
        }

        return $this->_options;
    }

    /**
     * get the value of a aspecific option.
     *
     * @param string $option_name specified a different platform for localization
     *
     * @return array return the value for the option required if exists else return FALSE
     */
    public function getOption($option_name)
    {
        if (empty($this->_options)) {
            $this->_loadOption();
        }

        return isset($this->_options[$option_name]) ? $this->_options[$option_name] : false;
    }

    /**
     * @param string $platform specified a different platform for localization
     * @param string $prefix   specified a prefix
     * @param string $db_conn  specified a db connection with the database
     *
     * @return array array(group_id => group_name) with the regroup unit
     */
    public function getRegroupUnit()
    {
        return [
            'user_manager' => Lang::t('_LOG_OPTION', 'user_managment'),
        ];
    }

    /**
     * @param string    contains the group selected
     *
     * @return string contains the displayable information for a selected group
     */
    public function getPageWithElement($group_selected)
    {
        if ($group_selected != 'user_manager') {
            return '';
        }

        require_once _base_ . '/lib/lib.form.php';

        $lang = &DoceboLanguage::createInstance('user_managment', 'framework');

        $reSetting = sql_query('
		SELECT param_name, param_value, value_type, max_size
		FROM ' . $this->_table . "
		WHERE pack = 'log_option' AND
			hide_in_modify = '0'
		ORDER BY sequence");

        $html = '';
        while (list($var_name, $var_value, $value_type, $max_size) = sql_fetch_row($reSetting)) {
            switch ($value_type) {
                case 'register_type':
                    //on off

                    $html .= Form::getOpenCombo($lang->def('_' . strtoupper($var_name)))
                        . Form::getLineRadio(
                            '',
                            'label_bold',
                            $lang->def('_REGISTER_TYPE_SELF'),
                            $var_name . '_self',
                            'option[' . $var_name . ']',
                            'self',
                            ($var_value == 'self')
                        )
                        . Form::getLineRadio(
                            '',
                            'label_bold',
                            $lang->def('_REGISTER_TYPE_SELF_OPTIN'),
                            $var_name . '_self_optin',
                            'option[' . $var_name . ']',
                            'self_optin',
                            ($var_value == 'self_optin')
                        )
                        . Form::getLineRadio(
                            '',
                            'label_bold',
                            $lang->def('_REGISTER_TYPE_MODERATE'),
                            $var_name . '_moderate',
                            'option[' . $var_name . ']',
                            'moderate',
                            ($var_value == 'moderate')
                        )
                        . Form::getLineRadio(
                            '',
                            'label_bold',
                            $lang->def('_REGISTER_TYPE_ADMIN'),
                            $var_name . '_admin',
                            'option[' . $var_name . ']',
                            'admin',
                            ($var_value == 'admin')
                        )
                        . Form::getCloseCombo();

                    break;

                case 'register_tree':
                    $register_possible_option = [
                        'off' => $lang->def('_DONT_USE_TREE_REGISTRATION'),
                        'manual_insert' => $lang->def('_USE_WITH_MANUALEINSERT'),
                        'selection' => $lang->def('_USE_WITH_SELECTION'),
                    ];

                    $html .= Form::getDropdown(
                        $lang->def('_' . strtoupper($var_name)),
                        $var_name,
                        'option[' . $var_name . ']',
                        $register_possible_option,
                        $var_value
                    );

                    break;
                case 'field_tree':
                    require_once _adm_ . '/lib/lib.field.php';

                    $fl = new FieldList();
                    $all_fields = $fl->getAllFields(false);
                    $fields[0] = $lang->def('_NO_VALUE');
                    foreach ($all_fields as $key => $val) {
                        $fields[$val[FIELD_INFO_ID]] = $val[FIELD_INFO_TRANSLATION];
                    }
                    $html .= Form::getDropdown(
                        $lang->def('_' . strtoupper($var_name)),
                        $var_name,
                        'option[' . $var_name . ']',
                        $fields,
                        $var_value
                    );

                    break;
                case 'save_log_attempt':
                    //on off

                    $html .= Form::getOpenCombo($lang->def('_' . strtoupper($var_name)))
                        . Form::getLineRadio(
                            '',
                            'label_bold',
                            $lang->def('_SAVE_LA_ALL'),
                            $var_name . '_all',
                            'option[' . $var_name . ']',
                            'all',
                            ($var_value == 'all')
                        )
                        . Form::getLineRadio(
                            '',
                            'label_bold',
                            $lang->def('_SAVE_LA_AFTER_MAX'),
                            $var_name . '_after_max',
                            'option[' . $var_name . ']',
                            'after_max',
                            ($var_value == 'after_max')
                        )
                        . Form::getLineRadio(
                            '',
                            'label_bold',
                            $lang->def('_NO'),
                            $var_name . '_no',
                            'option[' . $var_name . ']',
                            'no',
                            ($var_value == 'no')
                        )
                        . Form::getCloseCombo();

                    break;
                case 'enum':
                    //on off
                    $html .= Form::openFormLine()
                        . Form::getInputCheckbox(
                            $var_name . '_on',
                            'option[' . $var_name . ']',
                            'on',
                            ($var_value == 'on'),
                            ''
                        )
                        . ' '
                        . Form::getLabel($var_name . '_on', $lang->def('_' . strtoupper($var_name)), 'label_bold')
                        . Form::closeFormLine();

                    break;
                //uncrypted password
                case 'password':
                    $html .= Form::getPassword(
                        $lang->def('_' . strtoupper($var_name)),
                        $var_name,
                        'option[' . $var_name . ']',
                        $max_size,
                        $var_value
                    );

                    break;
                //string or int
                default:
                    $html .= Form::getTextfield(
                        $lang->def('_' . strtoupper($var_name)),
                        $var_name,
                        'option[' . $var_name . ']',
                        $max_size,
                        $var_value
                    );
            }
        }

        return $html;
    }

    /**
     * @param string    contains the group selected
     *
     * @return bool true if the operation was successfull false otherwise
     */
    public function saveElement($regroup)
    {
        if ($regroup != 'user_manager') {
            return true;
        }

        $reSetting = sql_query('
		SELECT param_name, value_type
		FROM ' . $this->_table . "
		WHERE pack = 'log_option' AND
			hide_in_modify = '0'");

        $re = true;
        while (list($var_name, $value_type) = sql_fetch_row($reSetting)) {
            switch ($value_type) {
                case 'int':
                    $new_value = (int) $_POST['option'][$var_name];

                    break;
                //if is enum switch value to on or off
                case 'enum':
                    if (isset($_POST['option'][$var_name])) {
                        $new_value = 'on';
                    } else {
                        $new_value = 'off';
                    }

                    break;
                //else simple assignament
                default:
                    $new_value = $_POST['option'][$var_name];
            }

            if (!sql_query('
			UPDATE ' . $this->_table . "
			SET param_value = '$new_value'
			WHERE pack = 'log_option' AND param_name = '$var_name'")) {
                $re = false;
            }
        }

        return $re;
    }
}
