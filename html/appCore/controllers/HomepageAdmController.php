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

class HomepageAdmController extends AdmController
{
    /** @var HomepageAdm */
    public $model;

    public function init()
    {

        $this->model = new HomepageAdm();
    }

    public function show()
    {

        if (!Docebo::user()->isAnonymous()) self::redirect();

        $params = array();

        $done = Get::req("done", DOTY_MIXED, null);
        $params['done'] = $this->_translateDone($done);

        $msg = Get::req("msg", DOTY_MIXED, null);
        $params['msg'] = $this->_translateMsg($msg);

        if (Get::req("cancel_social", DOTY_BOOL, false)) unset($_SESSION['social']);

        $block_attempts = $this->model->checkBrute();
        if ($block_attempts) {

            $wait = $block_attempts['wait_for'] >= 1 ? (string)$block_attempts['wait_for'] : " < 1";

            $params['block_attempts'] = Lang::t("_REACH_NUMBERS_OF_ATTEMPT", "user_managment", array(
                '[attempt]' => $block_attempts['max_login_attempt'],
                '[time]' => $wait
            ));
        } else $params['block_attempts'] = false;

        $params['under_maintenence'] = $this->model->isUnderMaintenence();
        $params['isCatalogToShow'] = $this->model->isCatalogToShow();
        $params['isSelfRegistrationActive'] = $this->model->isSelfRegistrationActive();

        foreach ($this->model->getLoginGUI() AS $loginGUI) {
            $params[$loginGUI['type']][] = $loginGUI;
        }


        $external_pages = $this->model->getExternalPages();
        $params['externalPages'] = [];
        if (!empty($external_pages)) {
            foreach ($external_pages AS $id_page => $title) {

                $externalPage = ['id' => $id_page, 'link' => Get::rel_path("base") . "/index.php?r=" . _homewebpage_ . "&page=" . $id_page, 'title' => $title];

                $params['externalPages'][] = $externalPage;
            }
        }

        $params['lostPwdAction'] = Get::rel_path('base') . '/index.php?r=' . _lostpwd_;
        $params['register'] = Get::rel_path('base') . '/index.php?r=' . _register_;

        // translations
        $params['intro_text_body'] = Lang::t("_INTRO_STD_TEXT", "login");
        $params['intro_text_header'] = Lang::t("_INTRO_STD_TEXT_TITLE", "login");
        $params['read_all'] = Lang::t("_READ_ALL", "login");
        $params['close'] = Lang::t("_CLOSE", "standard");

        // force_standard mode
        if(isset($_REQUEST["notuse_plugin"])){
            $params['notuse_plugin'] = true;
        }
        if(isset($_REQUEST["notuse_customscript"])){
            $params['notuse_customscript'] = true;
        }
        if(isset($_REQUEST["notuse_template"])){
            $params['notuse_template'] = true;
        }

        $this->render("show", $params);
    }

    private function _translateMsg($msg)
    {

        switch ($msg) {

            case INVALID_REQUEST:
                $msg_output = Lang::t("_INVALID_REQUEST", "login");
                break;
            case USER_CONCURRENCY:
                $msg_output = Lang::t("_TWO_USERS_LOGGED_WITH_SAME_USERNAME", "login");
                break;
            case SESSION_EXPIRED:
                $msg_output = Lang::t("_SESSION_EXPIRED", "login");
                break;
            case INCORRECT_IP:
                $msg_output = Lang::t("_INCORRECT_IP", "login");
                break;
            case EMPTY_USERID:
                $msg_output = Lang::t("_NOACCESS", "login");
                break;
            case ACCESS_FAILURE:
                $msg_output = Lang::t("_NOACCESS", "login");
                break;
            case INVALID_CODE:
                $msg_output = Lang::t("_INVALID_RANDOM_CODE", "register");
                break;
            case USER_NOT_FOUND:
                $msg_output = Lang::t('_USER_NOT_FOUND','login');
                break;
            default:
                $msg_output = false;
                break;
        }

        return $msg_output;
    }

    private function _translateDone($done)
    {

        switch ($done) {

            case LOGGED_OUT:
                $msg_output = Lang::t("_UNLOGGED", "login");
                break;
            case LOST_PWD:
                $msg_output = Lang::t("_MAIL_SEND_SUCCESSFUL", "login");
                break;
            case NEW_PWD:
                $msg_output = Lang::t("_OPERATION_SUCCESSFUL", "login");
                break;
            default:
                $msg_output = false;
                break;
        }

        return $msg_output;
    }

    public function register()
    {

        if (!Docebo::user()->isAnonymous()) self::redirect();
        if (!$this->model->isSelfRegistrationActive()) self::redirect();
        $dataView = [];

        $registerResultForm = $this->model->getRegisterForm();


        $registerForm = Form::openForm('register', Get::rel_path('base') . '/index.php?r=' . _register_, ' homepage__form ', null, 'multipart/form-data')
            . $registerResultForm
            . Form::closeForm();

        $dataView['form'] = $registerForm;
        $dataView['loginAction'] = Get::rel_path('base') . '/index.php?r=' . _login_;
        $dataView['lostPwdAction'] = Get::rel_path('base') . '/index.php?r=' . _lostpwd_;

        $external_pages = $this->model->getExternalPages();
        $dataView['externalPages'] = [];
        if (!empty($external_pages)) {
            foreach ($external_pages AS $id_page => $title) {

                $externalPage = ['id' => $id_page, 'link' => Get::rel_path("base") . "/index.php?r=" . _homewebpage_ . "&page=" . $id_page, 'title' => $title];

                $dataView['externalPages'][] = $externalPage;
            }
        }

        if (is_array($registerResultForm) && (isset($registerResultForm['registration']))) {

            $dataView['message'] = $registerResultForm['msg'];
            $dataView['registration'] = $registerResultForm['registration'];
            
            return $this->render('register-typ', $dataView);

        }

        $this->render('register', $dataView);
    }

    public function lostPwd()
    {

        if (!Docebo::user()->isAnonymous()) self::redirect();

        $action = Get::req("action", DOTY_MIXED, null);
        $params = [];
        $res = null;

        $ldapEnabled = Get::sett("ldap_used") == "on";

        $mand_symbol = '*';

        $error = false;
        $errorMessage = '';

        switch ($action) {

            case "lost_user":
                $email = Get::req("email", DOTY_STRING);
                if (preg_match("\r", $email) || preg_match("\n", $email)) {

                    $error = true;
                    $errorMessage = Lang::t("_INVALID_EMAIL", "register");
                    break;
                }
                $res = $this->model->sendLostUserId($email);
                break;
            case "lost_pwd":
                $userid = Get::req("userid", DOTY_STRING);
                $res = $this->model->sendLostPwd($userid);
                break;
        }

        switch ($res) {

            case USER_NOT_FOUND:
                $error = true;
                $errorMessage = Lang::t("_INEXISTENT_USER", "register");
                break;
            case FAILURE_SEND_LOST_PWD:
                $error = true;
                $errorMessage = Lang::t("_OPERATION_FAILURE", "register");
                break;
            case SUCCESS_SEND_LOST_PWD:
                $dataView['loginAction'] = Get::rel_path('base') . '/index.php?r=' . _login_;

                switch ($action) {

                    case "lost_user":

                        $dataView['message'] = Lang::t('_MAIL_SEND_SUCCESSFUL', 'register');
                        break;
                    case "lost_pwd":
                        $dataView['message'] = Lang::t('_MAIL_SEND_SUCCESSFUL_PWD', 'register');
                        break;
                }

                $this->render("lostpwd-typ", $dataView);
                return;
                break;
        }

        $lostUsernameForm = '<div class="homepage__row homepage__row--gray homepage__row--form row-fluid">'
            . Form::openForm("lost_user", Get::rel_path("base") . "/index.php?r=" . _lostpwd_)

            . Form::getHidden("lost_user_action", "action", "lost_user")
            . '<div class="col-xs-12 col-sm-5">'
            . Form::getInputTextfield(
                'form-control ' . (($error && $action === 'lost_user') ? 'has-error' : ''),
                'lost_user_email',
                'email',
                '',
                strip_tags(Lang::t("_EMAIL", "register")),
                255,
                'placeholder="' . Lang::t("_EMAIL", "register") . ' ' . $mand_symbol . '"');
        if (($error && $action === 'lost_user')) {
            $lostUsernameForm .= '<small class="form-text">* ' . $errorMessage . '</small>';
        }
        $lostUsernameForm .= '</div>'
            . '<div class="col-xs-12 col-sm-2">'
            . Form::getButton("lost_user_send", "send", Lang::t("_SEND", "register"), "forma-button forma-button--black thin")
            . '</div>'
            . Form::closeForm();

        $lostPwdForm = '<div class="homepage__row homepage__row--gray homepage__row--form row-fluid">'
            . Form::openForm("lost_pwd", Get::rel_path("base") . "/index.php?r=" . _lostpwd_)
            . Form::getHidden("lost_pwd_action", "action", "lost_pwd")
            . '<div class="col-xs-12 col-sm-5">'
            . Form::getInputTextfield(
                'form-control ' . (($error && $action === 'lost_pwd') ? 'has-error' : ''),
                'lost_pwd_userid',
                'userid',
                '',
                strip_tags(Lang::t("_USERNAME", "register")),
                255,
                'placeholder="' . Lang::t("_USERNAME", "register") . ' ' . $mand_symbol . '"');
        if (($error && $action === 'lost_pwd')) {
            $lostPwdForm .= '<small class="form-text">* ' . $errorMessage . '</small>';
        }
        $lostPwdForm .= '</div>'
            . '<div class="col-xs-12 col-sm-2">'
            . Form::getButton("lost_pwd_send", "send", Lang::t("_SEND", "register"), "forma-button forma-button--black thin")
            . '</div>'
            . Form::closeForm() .
            '</div>';


        $params['back']['title'] = Lang::t("_BACK", "standard");
        $params['back']['link'] = "./index.php";
        $params['titleArea'] = Lang::t("_LOGIN", "login");

        $params['lost_username'] = [
            'title' => Lang::t("_LOST_TITLE_USER", "register"),
            'istruction' => Lang::t("_LOST_INSTRUCTION_USER", "register"),
            'ldap' => $ldapEnabled,
            'ldap_title' => Lang::t("_LDAPACTIVE", "register"),
            'form' => $lostUsernameForm
        ];

        $params['lost_pwd'] = [
            'title' => Lang::t("_LOST_TITLE_PWD", "register"),
            'istruction' => Lang::t("_LOST_INSTRUCTION_PWD", "register"),
            'form' => $lostPwdForm
        ];


        $this->render("lostpwd", $params);
    }

    public function newpwd()
    {

        $code = Get::req("code", DOTY_STRING, "");

        $params = array();
        $params["msg"] = "";

        $redirection = array('req' => _homepage_);

        if (!$user_info = $this->model->checkCode($code)) {

            $redirection['query'] = array(
                'msg' => INVALID_CODE
            );
            self::redirect($redirection);
        }

        if (Get::req("send", DOTY_BOOL, false)) {

            $newpwd = Get::req("new_password", DOTY_STRING, null);
            $retype_newpwd = Get::req("retype_new_password", DOTY_STRING, null);

            switch ($this->model->checkNewPwdValidity($newpwd, $retype_newpwd)) {

                case PASSWORD_MISMATCHING:
                    $params["msg"] = Lang::t("_ERR_PASSWORD_NO_MATCH", "register");
                    break;
                case PASSWORD_TOO_SHORT:
                    $params["msg"] = Lang::t("_PASSWORD_TOO_SHORT", "register");
                    break;
                case PASSWORD_MUST_BE_ALPHA:
                    $params["msg"] = Lang::t("_ERR_PASSWORD_MUSTBE_ALPHA", "register");
                    break;
                default:
                    if ($this->model->setNewPwd($newpwd, $user_info[ACL_INFO_IDST], $code)) {

                        $redirection['query'] = array(
                            'done' => NEW_PWD
                        );
                        self::redirect($redirection);
                    } else {

                        $params["msg"] = Lang::t("_OPERATION_FAILURE", "register");
                    }
                    break;
            }
        }

        $params['code'] = $code;
        $params += $this->model->getNewPwdOptions();

        $this->render("newpwd", $params);
    }

    public function signup()
    {

        if (!Docebo::user()->isAnonymous()) self::redirect();
        if (!$this->model->isSelfRegistrationActive()) self::redirect();

        $this->render("signup");
    }

    public function login()
    {

        if (!Docebo::user()->isAnonymous()) self::redirect();

        $plugin = Get::req("plugin", DOTY_STRING, "");
        $res = $this->model->login($plugin);

        $redirection = array();

        switch ($res) {

            case PWD_ELAPSED:
                $_SESSION['must_renew_pwd'] = 1;
                $redirection['req'] = "lms/profile/renewalpwd";
                break;
            case MANDATORY_FIELDS:
                $_SESSION['request_mandatory_fields_compilation'] = 1;
                $redirection['req'] = "lms/precompile/show";
                break;
            case USER_SAVED:
                $redirection['req'] = _homepage_;
                break;
            default:
                $redirection['req'] = _homepage_;
                $redirection['query'] = array(
                    "msg" => $res
                );
                break;
        }

        self::redirect($redirection);
    }

    public function logout()
    {

        $msg = Get::req("msg", DOTY_MIXED, null);

        if (Docebo::user()->isAnonymous()) self::redirect();

        AuthenticationManager::logout();

        $redirection = array();

        $redirection['req'] = _homepage_;
        if ($msg) {
            $redirection['query'] = array(
                "msg" => $msg
            );
        } else {
            $redirection['query'] = array(
                "done" => LOGGED_OUT
            );
        }
        self::redirect($redirection);
    }

    public function stopconcurrency()
    {

        $redirection = array();
        $redirection['req'] = _logout_;
        $redirection['query'] = array(
            "msg" => USER_CONCURRENCY
        );
        self::redirect($redirection);
    }

    public function webpage()
    {

        $id_page = Get::req("page", DOTY_INT, null);

        $params = array();
        list($params['title'], $params['description']) = $this->model->getWebPage($id_page);

        $external_pages = $this->model->getExternalPages();
        $params['externalPages'] = [];

        if (!empty($external_pages)) {
            foreach ($external_pages AS $id_page => $title) {

                $externalPage = ['id' => $id_page, 'link' => Get::rel_path("base") . "/index.php?r=" . _homewebpage_ . "&page=" . $id_page, 'title' => $title];

                $params['externalPages'][] = $externalPage;
            }
        }


        $this->render("webpage", $params);
    }

    public function sso()
    { // index.php?login_user=staff&time=200812101752&token=5D93BCEDF500E9759E4870492AF32E7A

        $login_user = stripslashes(Get::req('login_user', DOTY_MIXED, false));
        $login_idst = Get::req('use_user_idst', DOTY_MIXED, false);

        $redirection = array();

        if (Get::sett('sso_token', "off") != "on" || !$login_user) {

            $redirection['req'] = _homepage_;
            $redirection['query'] = array(
                "msg" => ACCESS_FAILURE // XXX: o SSO_FAILURE?
            );
            self::redirect($redirection);
        }

        if (Docebo::user()->isLoggedIn() && $login_user != Docebo::user()->getACLManager()->relativeId(Docebo::user()->userid)) {
            AuthenticationManager::logout();
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }

        $time = Get::req('time', DOTY_MIXED, '');
        $secret = Get::sett('sso_secret', "8ca0f69afeacc7022d1e589221072d6bcf87e39c"); // XXX: <- orribile questo default
        $token = strtoupper(Get::req('token', DOTY_MIXED, ''));
        $recalc_token = strtoupper(md5($login_user . ',' . $time . ',' . $secret));

        $lifetime = Get::sett('rest_auth_lifetime', 1);

        if ($recalc_token != $token || $time + $lifetime < time()) {

            $redirection['req'] = _homepage_;
            $redirection['query'] = array(
                "msg" => ACCESS_FAILURE // XXX: o SSO_FAILURE?
            );
            self::redirect($redirection);
        }

        $user_manager =& $GLOBALS['current_user']->getAclManager();

        if (!$login_idst) {

            $username = '/' . $login_user;
            $user_info = $user_manager->getUser(false, $username);
        } else {

            $user_info = $user_manager->getUser($login_user);
            if (!empty($user_info)) {

                $username = $user_info[ACL_INFO_USERID];
            }
        }

        if (!$user_info) {

            $redirection['req'] = _homepage_;
            $redirection['query'] = array(
                "msg" => ACCESS_FAILURE // XXX: o SSO_FAILURE?
            );
            self::redirect($redirection);
        }

        $user = new DoceboUser($username, 'public_area');
        Lang::set($user->preference->getLanguage());

        $redirection = array();
        switch ($this->model->saveUser($user)) {

            case PWD_ELAPSED:
                $_SESSION['must_renew_pwd'] = 1;
                $redirection['req'] = "lms/profile/renewalpwd";
                break;
            case MANDATORY_FIELDS:
                $_SESSION['request_mandatory_fields_compilation'] = 1;
                $redirection['req'] = "lms/precompile/show";
                break;
            case USER_SAVED:
                break;
        }

        $id_course = Get::req('id_course', DOTY_INT, 0);
        $next_action = Get::req('act', DOTY_STRING, 'none');
        $id_item = Get::req('id_item', DOTY_INT, '');
        $chapter = Get::req('chapter', DOTY_MIXED, false);

        if ($id_course) {

            define("LMS", true);

            require_once(_lms_ . "/lib/lib.course.php");
            logIntoCourse($id_course, ($next_action == false || $next_action == "none" ? true : false));

            switch ($next_action) {
                case "organization":
                    $_SESSION["login_redirect"] = trim(dirname($_SERVER['SCRIPT_NAME']), DIRECTORY_SEPARATOR) . "/" . _folder_lms_ . "/index.php?modname=organization&op=custom_playitem&id_item=" . $id_item;
                    break;
                case "playsco":
                    $_SESSION["login_redirect"] = trim(dirname($_SERVER['SCRIPT_NAME']), DIRECTORY_SEPARATOR) . "/" . _folder_lms_ . "/index.php?modname=organization&op=custom_playitem&id_course=" . $id_course
                        . "&courseid=" . $id_course . "&id_item=" . $id_item . "&start_from_chapter=" . $chapter . "&collapse_menu=1";
                    break;
            }
        }

        self::redirect($redirection);
    }

    public static function redirect($redirection = array())
    {

        $query = array();
        if (isset($redirection['modname'])) $query['modname'] = $redirection['modname'];
        if (isset($redirection['op'])) $query['op'] = $redirection['op'];
        if (isset($redirection['req'])) $query['r'] = $redirection['req'];

        if (isset($redirection['query'])) $query = $query + $redirection['query'];

        if (!empty($query)) {
            $query = "?" . urldecode(http_build_query($query));
        } else $query = "";

        Util::jump_to("index.php" . $query);
    }
}
