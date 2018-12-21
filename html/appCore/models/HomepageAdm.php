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

define('TIME_BEFORE_REACTIVE', 10 * 60); // reactive after 10 minutes

class HomepageAdm extends Model {
    
    protected $authentication;
    protected $user_manager;
    protected $options;
    
    public function  __construct() {
                
        $this->authentication = new AuthenticationManager();
        $this->user_manager = new UserManager();
        $this->options = new UserManagerOption();
    }
    
    public function getLoginGUI() {
        
        return $this->authentication->getLoginGUI();
    }
    
    public function login($plugin) {        
        
        return $this->authentication->login($plugin);
    }
    
    public function saveUser($user) {
        
        return $this->authentication->saveUser($user);
    }
    
    public function checkBrute() {
        
        $max_login_attempts     = $this->options->getOption('max_log_attempt');
        
        if(!$max_login_attempts) return false;
        
        $time_before_reactive   = TIME_BEFORE_REACTIVE;
        
        $last_attempt           = $this->user_manager->getLastAttemptTime();
        $actual_attempts        = $this->user_manager->getAttemptNumber();
        
        $now = time();
        
        $wait_for = 0;
        
        if($actual_attempts > $max_login_attempts) {

            if(($last_attempt + $time_before_reactive) > $now) {
                
                $wait_for = (int)((($last_attempt + $time_before_reactive) - $now) / 60);
                return array(
                    'max_login_attempt' => $max_login_attempts,
                    'wait_for'          => $wait_for
                );
            } else {

                $this->user_manager->resetAttemptNumber();
            }
        }
        
        return false;
    }
    
    public function isSelfRegistrationActive() {
        
        $registration_type = $this->options->getOption("register_type");
        $active_types = array("self", "self_optin", "moderate");
        
        return in_array($registration_type, $active_types);
    }
    
    public function getRegisterForm() {
        
        return $this->user_manager->getRegister(Get::rel_path("base") . "/index.php?r=" . _signup_);
    }
    
    public function getConfirmRegister() {
        
        return $this->user_manager->confirmRegister();
    }
    
    public function getExternalPages() {
        
        $query  = " SELECT idPages, title"
                . " FROM %lms_webpages"
                . " WHERE publish = '1'"
                . "     AND in_home='0'"
                . "     AND language = '" . getLanguage() . "'"
                . " ORDER BY sequence ";
        $r = sql_query($query);
        
        $external_pages = array();
        while(list($id_page, $title) = sql_fetch_row($r)) {
            
            $external_pages[$id_page] = $title;
        }
        
        return $external_pages;
    }
    
    public function sendLostUserId($email) {
        
        $acl_man =& Docebo::user()->getAclManager();
        $user_info = $acl_man->getUserByEmail($email);
        
        if(!$user_info) return USER_NOT_FOUND;
        
        require_once(_lib_ . "/lib.mailer.php");
        
        $sender         = $this->options->getOption('mail_sender');
        $recipients     = $user_info[ACL_INFO_EMAIL];
        $subject        = Lang::t("_LOST_USERID_TITLE", "register");
        $body           = Lang::t("_LOST_USERID_MAILTEXT", "register", array(
            '[date_request]'    => date("d-m-Y"),
            '[url]'             => Get::site_url(),
            '[userid]'          => $acl_man->relativeId($user_info[ACL_INFO_USERID])
        ));
        $attachments    = false;
        $params         = array(MAIL_SENDER_ACLNAME => false);
        
        $mailer = DoceboMailer::getInstance();
        
        if($mailer->SendMail($sender, $recipients, $subject, $body, $attachments, $params)) return SUCCESS_SEND_LOST_PWD;
        else return FAILURE_SEND_LOST_PWD;
    }
    
    public function sendLostPwd($userid) {
        
        $acl_man =& Docebo::user()->getAclManager();
        $user_info = $acl_man->getUser(false, $acl_man->absoluteId($userid));
        
        if(!$user_info) return USER_NOT_FOUND;

        $code = md5(mt_rand() . mt_rand());

        $exist_code = $this->user_manager->getPwdRandomCode($user_info[ACL_INFO_IDST]);
        
        if($exist_code === false) {

            if(!$this->user_manager->insertPwdRandomCode($user_info[ACL_INFO_IDST], $code)) return FAILURE_SEND_LOST_PWD;
        } else {

            if(!$this->user_manager->savePwdRandomCode($user_info[ACL_INFO_IDST], $code)) return FAILURE_SEND_LOST_PWD;
        }

        require_once(_base_.'/lib/lib.mailer.php');
        
        $sender         = $this->options->getOption('mail_sender');
        $recipients     = $user_info[ACL_INFO_EMAIL];
        $subject        = Lang::t("_LOST_PWD_TITLE", "register");
        $body           = Lang::t("_LOST_PWD_MAILTEXT", "register", array(
            '[link]'    => Get::site_url() . "index.php?r=" . _newpwd_ . "&code=" . $code
        ));
        $attachments    = false;
        $params         = array(MAIL_SENDER_ACLNAME => false);
        
        $mailer = DoceboMailer::getInstance();

        if($mailer->SendMail($sender, $recipients, $subject, $body, $attachments, $params)) return SUCCESS_SEND_LOST_PWD;
        else return FAILURE_SEND_LOST_PWD;
    }
    
    public function checkCode($code) {
        
        if($user = $this->user_manager->getPwdRandomCode(false, $code)) {
            
            $acl_man =& Docebo::user()->getAclManager();
            $user_info = $acl_man->getUser($user['idst_user'], false);
            
            return $user_info;
        }
        
        return false;
    }
    
    public function getNewPwdOptions() {
        
        return array(
            "pass_max_time_valid"   => $this->options->getOption("pass_max_time_valid"),
            "pass_min_char"         => $this->options->getOption("pass_min_char"),
            "pass_alfanumeric"      => $this->options->getOption("pass_alfanumeric")
        );
    }
    
    public function checkNewPwdValidity($pwd, $retype) {
        
        if($pwd !== $retype) return PASSWORD_MISMATCHING;
        if(strlen($pwd) < $this->options->getOption("pass_min_char")) return PASSWORD_TOO_SHORT;
        if($this->options->getOption("pass_alfanumeric") == "on" && 
                (!preg_match('/[a-z]/i', $pwd) || !preg_match('/[0-9]/', $pwd))) return PASSWORD_MUST_BE_ALPHA;
    }
    
    public function setNewPwd($pwd, $user, $code) {        
        
        $acl_man =& Docebo::user()->getAclManager();
        
        if(!$this->user_manager->deletePwdRandomCode($user, $code)) return false;
        return $acl_man->updateUser($user, false, false, false, $pwd, false, false, false);
    }
    
    public function isUnderMaintenence() {
        
        return isset($GLOBALS['block_for_maintenance']) && $GLOBALS['block_for_maintenance'];
    }

    public static function staticIsCatalogToShow() {

        return Get::sett('course_block', "on") == "on";
    }

    public function isCatalogToShow() {
        
        return Get::sett('course_block', "on") == "on";
    }
    
    public function getWebPage($id_page = null) {
        
        $query  = " SELECT title, description"
                . " FROM %lms_webpages"
                . " WHERE publish = '1'"
                . "     AND language = '" . getLanguage() . "'"
                . "     AND " . ($id_page ? "idPages = " . $id_page : "in_home = '1'");
        
	return sql_fetch_row(sql_query($query));
    }
}