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

/**
 * The language model class.
 *
 * This class can be used in order to retrive and manipulate all kind of
 * information about the languages of the platforma nd the string
 * localization maded and uploadaded inside the system.
 *
 * @since 4.0
 */
class InstallAdm extends Model
{

    protected $steps;
    protected $labels;
    protected $errorLabels;

    protected $debug;

    protected $response;

    const CHECK_REQUIREMENTS = '1';
    const CHECK_DATABASE = '2';
    const CHECK_ADMIN = '3';
    const CHECK_SMTP = '4';
    const CHECK_FINAL = '5';
   
    public function __construct($debug = null)
    {
       
          //soluzione provvisoria 
        require_once(_lib_.'/installer/lang/'.Lang::getSelLang().'.php');

        $this->fillSteps();
        $this->fillLabels();
        $this->fillErrorLabels();

        $this->setResponse(false, []);
        parent::__construct();
    
    }

    public function fillSteps() {
        $this->steps = [self::CHECK_REQUIREMENTS => _TITLE_STEP1, 
                        self::CHECK_DATABASE => _TITLE_STEP2, 
                        self::CHECK_ADMIN => _TITLE_STEP3,
                        self::CHECK_SMTP => _TITLE_STEP4,
                        self::CHECK_FINAL => _TITLE_STEP5
                    ];
        return $this;
    }

    public function getSteps() {
        return $this->steps;
    }

    public function fillLabels() {
 
        $labels['introText'] = _INSTALLER_INTRO_TEXT;
        $labels['languageLabel'] = _LANGUAGE;
        $labels['installerTitle'] = _INSTALLER_TITLE;
        $labels['next'] = _NEXT;
        $labels['back'] = _BACK;
        $labels['cancel'] = _CANCEL;
        $labels['current'] = _CURRENT;
        $labels['pagination'] = _PAGINATION;
        $labels['finish'] = _FINISH;
        $labels['loading'] = _LOADING;
        $labels['tryAgain'] = _TRY_AGAIN;
        $labels['serverInfo'] = _SERVERINFO;
        $labels['serverSw'] = _SERVER_SOFTWARE;
        $labels['phpVersion'] = _PHPVERSION;
        $labels['mysqlClientVersion'] = _MYSQLCLIENT_VERSION;
        $labels['mysqlServerVersion'] = _MYSQLSERVER_VERSION;
        $labels['mbstringLabel'] = _MBSTRING;
        $labels['mimeCtLabel'] = _MIME_CONTENT_TYPE;
        $labels['fileInfoLabel'] = _FILEINFO;
        $labels['ldapLabel'] = _LDAP;
        $labels['openSslLabel'] = _OPENSSL;
        $labels['phpTimezone'] = _PHP_TIMEZONE;
        $labels['phpInfo'] = _PHPINFO;
        $labels['allowUrlFopenLabel'] = _ALLOW_URL_FOPEN;
        $labels['magicQuotesGpc'] = _MAGIC_QUOTES_GPC; 
        $labels['safeMode'] = _SAFEMODE;
        $labels['registerGlobals'] = _REGISTER_GLOBALS;
        $labels['allowUrlIncludeLabel'] = _ALLOW_URL_INCLUDE;
        $labels['uploadMaxFilesize'] = _UPLOAD_MAX_FILESIZE;
        $labels['postMaxSize'] = _POST_MAX_SIZE;
        $labels['maxExecutionTime'] = _MAX_EXECUTION_TIME;

        $labels['licenseAcceptance'] = _AGREE_LICENSE;
        /***************************************** */
        $labels['databaseInfoLabel'] = _DATABASE_INFO;
        $labels['dbHostLabel'] = _DB_HOST;
        $labels['dbNameLabel'] = _DB_NAME;
        $labels['dbUsernameLabel'] = _DB_USERNAME;
        $labels['dbPassLabel'] = _DB_PASS;
        $labels['uploadMethodLabel'] = _UPLOAD_METHOD;
        $labels['httpUploadLabel'] = _HTTP_UPLOAD;
        $labels['ftpUploadLabel'] = _FTP_UPLOAD;
        $labels['ftpHostLabel'] = _FTP_HOST;
        $labels['ftpPortLabel'] = _FTP_PORT;
        $labels['ftpUserLabel'] = _FTP_USERNAME;
        $labels['ftpPassLabel'] = _FTP_PASS;
        $labels['ftpPathLabel'] = _FTP_PATH;
        /*************************************** */
        $labels['adminInfoLabel'] = _ADMIN_USER_INFO;
        $labels['adminUserLabel'] = _ADMIN_USERNAME;
        $labels['adminNameLabel'] = _ADMIN_FIRSTNAME;
        $labels['adminLastnameLabel'] = _ADMIN_LASTNAME;
        $labels['adminPasswordLabel'] = _ADMIN_PASS;
        $labels['adminConfpassLabel'] = _ADMIN_CONFPASS;
        $labels['adminEmailLabel'] = _ADMIN_EMAIL;
        $labels['langInstallLabel'] = _LANG_TO_INSTALL;

        $labels['loadingLabel'] = _LOADING . '...';

        $labels['smtpLabels'] = 
        [
            'useSmtpDatabase' => _USE_SMTP_DATABASE,
            'useSmtp' => _USE_SMTP,
            'smtpHost' => _SMTP_HOST,
            'smtpPort' => _SMTP_PORT,
            'smtpSecure' => _SMTP_SECURE,
            'smtpAutoTls' => _SMTP_AUTO_TLS,
            'smtpUser' => _SMTP_USER,
            'smtpPwd' => _SMTP_PWD,
            'active' => _SMTP_ACTIVE,
            'smtpDebug' => _SMTP_DEBUG,
            'senderMailNotification' => _SMTP_SENDERMAIL,
            'senderNameNotification' => _SMTP_SENDERNAME,
            'senderMailSystem' => _SMTP_SENDERMAILSYS,
            'senderNameSystem' => _SMTP_SENDERNAMESYS,
            'senderCcMails' => _SMTP_SENDERCCMAIL,
            'senderCcnMails' => _SMTP_SENDERCCNMAILS,
            'helperDeskMail' => _SMTP_HDESKMAIL,
            'helperDeskSubject' => _SMTP_HDESKSUBJECT,
            'helperDeskName' => _SMTP_HDESKNAME,
            'replytoName' => _SMTP_REPLYTONAME,
            'replytoMail' => _SMTP_REPLYTOMAIL,
        ];

        $this->labels = $labels;

        return $this;
    }

    public function fillErrorLabels() {
        $this->errorLabels['unsuitable_requirements'] = _UNSUITABLE_REQUIREMENTS;
        $this->errorLabels['missing_check'] = _MISSING_LICENSE_CHECK;
        $this->errorLabels['missing_field'] = _MISSING_FIELD;
        $this->errorLabels['ftp_not_supported'] = _FTP_NOT_SUPPORTED;
        $this->errorLabels['ftp_connection_fail'] = _FTP_CONNECT_FAIL;
        $this->errorLabels['ftp_login_fail'] = _FTP_LOGIN_FAIL;
        $this->errorLabels['db_not_utf8'] = _DB_NOT_UTF8;
        $this->errorLabels['db_not_empty'] = _DB_NOT_EMPTY;             
        $this->errorLabels['cant_connect_db'] = _CANT_CONNECT_WITH_DB; 
        $this->errorLabels['cant_select_db'] = _CANT_SELECT_DB;
        $this->errorLabels['not_matching_password'] = _NOT_MATCHING_PASSWORD;
        $this->errorLabels['email_not_valid'] = _NOT_VALID_EMAIL;
        $this->errorLabels['smtp_failed'] = _SMTP_FAILED;

        return $this;
    }

    public function getLabels() {

        return $this->labels;
    }

    public function getSmtpFieldsRequired() {
        $fields = [
            'smtpHost', 'smtpPort', 'smtpUser', 'smtpPwd', 'smtpSecure', 'smtpAutoTls'
        ];
        return $fields;
    }

    public function getData($request) {

        $params = $this->getLabels();
      
        $params = array_merge($params, 
                                $this->checkRequirements(), 
                                ini_get_all(), 
                                $this->getLicense(),
                                $this->getDbConfig(),
                                $this->getSmtpConfig(),
                                ['setValues' => $this->getSetValues()],
                                ['setLangs' => $this->getSetLangs()],
                                ['smtpFieldsRequired' => $this->getSmtpFieldsRequired()]);

        $params['setLang'] = Lang::getSelLang();

        $params['serverSwInfo'] = $request->server->get('SERVER_SOFTWARE');
        $params['phpVersionInfo'] = phpversion();
        preg_match('/([0-9]+\.[\.0-9]+)/', sql_get_client_info(), $sqlClientVersion);
        $params['sqlClientVersion'] = empty($sqlClientVersion[1]) ? 'unknown' : $sqlClientVersion[1]; 
        preg_match('/([0-9]+\.[\.0-9]+)/', sql_get_server_info(), $sqlServerVersion);
        $params['sqlServerVersion'] = empty($sqlServerVersion[1]) ? 'unknown' : $sqlServerVersion[1]; 
        $params['mbstringData'] = extension_loaded('mbstring') ? _ON : _OFF;
        $params['mimeCtData'] = $params['mimeCt'] == 'ok' ? _ON : _OFF;
        $params['fileInfoData'] =  extension_loaded('fileinfo') ? _ON : _OFF . ' ' . _ONLY_IF_YU_WANT_TO_USE_FILEINFO;
        $params['ldapData'] = extension_loaded('ldap') ? _ON : _OFF . ' ' . _ONLY_IF_YU_WANT_TO_USE_IT;
        $params['openSslData'] = extension_loaded('openssl') ? _ON : _OFF . ' ' . _WARINNG_SOCIAL;
        $params['phpTimezoneData'] = @date_default_timezone_get();
        $params['magicQuotesGpcData'] = $params['magic_quotes_gpc']['local_value'] != '' ? _ON : _OFF;
        $params['safeModeData'] = $params['safe_mode']['local_value'] != '' ? _ON : _OFF;
        $params['registerGlobalsData'] = $params['register_globals']['local_value'] != '' ? _ON : _OFF;
        $params['allowUrlFopenData'] = $params['allow_url_fopen']['local_value'] != '' ? _ON : _OFF . ' ' . _WARINNG_SOCIAL;
        $params['allowUrlIncludeData'] = $params['allow_url_include']['local_value'] != '' ? _ON : _OFF; 
        $params['uploadMaxFilesizeData'] = $params['upload_max_filesize']['local_value'];
        $params['postMaxSizeData'] = $params['post_max_size']['local_value'];
        $params['maxExecutionTimeData'] = $params['max_execution_time']['local_value'].'s';
        $params['checkFolderPerms'] = $this->checkFolderPerm();
        $params['uploadMetodFlag'] = $params['safe_mode']['local_value'] == '';
       
        return $params;
    }

    public function checkRequirements()
    {
        $res = [];

        $checkRequirements = 1;
        //TODO PHP7x: set const for Minimum PHP required version: 7.4
        //TODO PHP7x: set const for Maximum PHP suggested version: 7.4.x
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $res['mandatory']['php'] = 'err';
        } elseif (version_compare(PHP_VERSION, '8.0', '>=')) {
            $res['mandatory']['php'] = 'warn';
        } else {
            $res['mandatory']['php'] = 'ok';
        }

        $driver = [
            'mysqli' => extension_loaded('mysqli'),
        ];
        if (array_filter($driver)) {
            // mysql client version, in php the version number is a string regcut it
            preg_match('/([0-9]+\.[\.0-9]+)/', sql_get_client_info(), $version);

            if (empty($version[1])) {
                $res['requirements']['mysqlClient'] = 'ok';
            } else {
                $res['requirements']['mysqlClient'] = (version_compare($version[1], PHP_VERSION) >= 0 ? 'ok' : 'err');
            }
        } else {
            $res['mysqlClient'] = 'err';
        }
        if (array_filter($driver)) {
            // mysql version, in easyphp the version number is ina string regcut it
            preg_match('/([\.0-9][\.0-9]+\.[\.0-9]+)/', sql_get_server_version(), $mysqlVersion);

            if (empty($mysqlVersion[1])) {
                $res['mandatory']['mysql'] = 'ok';
            } else {
                $checkMysql = version_compare($mysqlVersion[1], '5.6') >= 0 && version_compare($mysqlVersion[1], '8.1') < 0;
                $checkMariaDB = version_compare($mysqlVersion[1], '10.0') >= 0 && version_compare($mysqlVersion[1], '11.0') < 0;

                if ($checkMysql || $checkMariaDB) {
                    $res['mandatory']['mysql'] = 'ok';
                } else {
                    $res['mandatory']['mysql'] = 'err';
                }
            }
        } else {
            $res['requirements']['mysql'] = 'err';
        }
        $res['requirements']['xml'] = (extension_loaded('domxml') ? 'ok' : 'err');
        $res['mandatory']['mbstring'] = (extension_loaded('mbstring') ? 'ok' : 'err');
        $res['requirements']['ldap'] = (extension_loaded('ldap') ? 'ok' : 'err');
        $res['requirements']['openssl'] = (extension_loaded('openssl') ? 'ok' : 'err');
        $res['requirements']['allowUrlFopen'] = ($php_conf['allow_url_fopen']['local_value'] ? 'ok' : 'err');
        $res['requirements']['allowUrlInclude'] = ($php_conf['allow_url_include']['local_value'] ? 'err' : 'ok');
        $res['mandatory']['mimeCt'] = (function_exists('mime_content_type') || (class_exists('file') && method_exists('finfo', 'file')) ? 'ok' : 'err');

      
        if(in_array('err', $res['mandatory'])) {
            $checkRequirements = 0;
        }

        $resultArray = array_merge($res['mandatory'], $res['requirements']);
        $resultArray['checkRequirements'] = $checkRequirements;
        return $resultArray;
    }

    public function checkFolderPerm()
    {
    $res = '';

    $platform_folders = $this->session->get('platform_arr');
    $file_to_check = ['config.php'];
    $dir_to_check = [];
    $empty_dir_to_check = [];

    // common dir to check
    $dir_to_check = [
        'files/tmp',
        'files/common/comment',
        'files/common/iofiles',
        'files/common/users',
    ];

    foreach ($platform_folders as $platform_code => $dir_name) {
        $specific_file_to_check = [];
        $specific_dir_to_check = [];

        if (!is_dir(_base_ . '/' . $dir_name . '/')) {
            $install[$platform_code] = false;
        } else {
            $install[$platform_code] = true;

            $empty_specific_dir_to_check = null;

            switch ($platform_code) {
                case 'lms':
                        $specific_dir_to_check = [
                            'files/appLms/certificate',
                            'files/appLms/forum',
                            'files/appLms/htmlpages',
                            'files/appLms/item',
                            'files/appLms/label',
                            'files/appLms/message',
                            'files/appLms/project',
                            'files/appLms/repo_light',
                            'files/appLms/sponsor',
                            'files/appLms/test',
                        ];
                        $empty_specific_dir_to_check = ['files/appLms/course', 'files/appLms/scorm'];

                    break;

                case 'framework':
                        $specific_dir_to_check = [
                            'files/appCore/field',
                            'files/appCore/photo',
                            'files/appCore/newsletter',
                            'files/common/users',
                        ];

                    break;
            }

            $dir_to_check = array_merge($dir_to_check, $specific_dir_to_check);
            $file_to_check = array_merge($file_to_check, $specific_file_to_check);

            if ((is_array($specific_dir_to_check)) && (count($specific_dir_to_check) > 0) && (is_array($empty_specific_dir_to_check))) {
                $empty_dir_to_check = array_merge($empty_dir_to_check, $empty_specific_dir_to_check);
            }
        }
    }

    // Write permission
    $checked_dir = [];
    foreach ($dir_to_check as $dir_name) {
        if (!is_dir(_base_ . '/' . $dir_name . '/')) {
            $checked_dir[] = $dir_name;
        } elseif (!is_writable(_base_ . '/' . $dir_name . '/')) {
            $checked_dir[] = $dir_name;
        }
    }
    if (!empty($checked_dir)) {
        $res .= '<h3>' . Lang::t('_CHECKED_DIRECTORIES') . '</h3>'
            . '<ul class="info"><li class="err">' . implode('</li><li class="err">', $checked_dir) . '</li></ul>';
    }

    $checked_file = [];
    foreach ($file_to_check as $file_name) {
        if (!is_writable(_base_ . '/' . $file_name)) {
            $checked_file[] = $file_name;
        }
    }
    if (!empty($checked_file)) {
        $res .= '<h3>' . Lang::t('_CHECKED_FILES') . '</h3>'
            . '<ul class="info"><li class="err">' . implode('</li><li class="err">', $checked_file) . '</li></ul>';
    }

    return $res;
    }   


    public function getLicense() {

        $content = '';
        $fn = _lib_.'/installer/license/license_'.Lang::getSelLang().'.txt';
    
        $english_fn = _lib_.'/installer/license/license_english.txt';

        $handle = false;
        if ((!file_exists($fn)) && (file_exists($english_fn))) {
            $fn = $english_fn;
        }

        if (file_exists($fn)) {
            $handle = fopen($fn, 'r');
            $content = fread($handle, filesize($fn));
            fclose($handle);
        }

        $params['licenseLabel'] = (defined('_SOFTWARE_LICENSE') ? _SOFTWARE_LICENSE : 'License');
        $params['licenseContent'] = $content;

        return $params;

    }

    public function getDbConfig() {
        $cfg['setValues'] = [
            'dbType' => '',
            'dbHost' => 'localhost',
            'dbName' => '',
            'dbUser' => '',
            'dbPass' => '',
        ];
        if (file_exists(_base_ . '/config.php')) {
            $cfg['setValues']['dbPass'] = '_fromconfig';
        }

        return $cfg;
    }

    public function getSmtpConfig() {
        $localCfg['smtp'] = 
        [
            'useSmtpDatabase' => '',
            'useSmtp' => '',
            'smtpHost' => 'localhost',
            'smtpPort' => '',
            'smtpSecure' => '',
            'smtpAutoTls' => '',
            'smtpUser' => '',
            'smtpPwd' => '',
            'smtpDebug' => '',
            'senderMailNotification' => '',
            'senderNameNotification' => '',
            'senderMailSystem' => '',
            'senderNameSystem' => '',
            'senderCcMails' => '',
            'senderCcnMails' => '',
            'helperDeskMail' => '',
            'helperDeskSubject' => '',
            'helperDeskName' => '',
            'replytoName' => '',
            'replytoMail' => ''
        ];

     

        if (file_exists(_base_ . '/config.php')) {
            define('IN_FORMA', true);
            include _base_ . '/config.php';
    
            foreach ($localCfg['smtp'] as $key => $value) {
                $localCfg['smtp'][$key] = $cfg[lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))))];
            }
        }

        $localCfg['smtp']['useSmtpDatabase'] = $cfg['useSmtpDatabase'];

        $localCfg['selectEnabling'] = [
            'on' => _YES,
            'off' => _NO,
        ];
    
        $localCfg['secureSelect'] = ['ssl' => 'SSL', 'tls' => 'TLS'];

        return $localCfg;
    }


    public function setValue($request) {
        $params = $request->request->all();
        $result = false;
        foreach($params as $keyParam => $paramValue) {
            if($paramValue === 'true') {
                $paramValue = true;
            }

            if($paramValue === 'false') {
                $paramValue = false;
            }

            if($keyParam === 'selectLangs' || $keyParam === 'deselectLangs') {
                $result =  $this->setLangs($paramValue , $keyParam === 'selectLangs' ? 1 : 0);
            } else {
                //per retrocompatibilità
                if('sel_lang' === $keyParam) {
                    $this->session->set($keyParam, $paramValue);
                    $this->session->save();
                    $result = true;
                } else {
                    $result =  $this->saveValue($keyParam, $paramValue);
                }
                
            }
            
        }

        return $result;

    }

    public function saveValue($key, $value) {

        $values = $this->session->get('setValues');
        $values[$key] = $value;
        $this->session->set('setValues', $values);
        $this->session->save();

        return true;
     }

     public function setLangs($value, $add = 1) {

        $values = $this->session->get('setLangs');
        if($add) {
            $values[$value] = $value;
        } else {
            unset($values[$value]);
        }
        
        $this->session->set('setLangs', $values);
        $this->session->save();

        return true;
     }


    public function getSetValues()
    {

        $values = $this->session->get('setValues');
        if(!in_array('uploadMethod', array_keys($values))) {
            $values['uploadMethod'] = 'http';
        }

        if(!in_array('step', array_keys($values))) {
            $values['step'] = 0;
        }
        return  $values;
    }

    public function getSetLangs()
    {

        return $this->session->get('setLangs');

    }

    public function checkAdminData($request) {
        $messages = [];
        $params = $request->request->all();

        foreach($params as $key => $param) {
            if('' == $param) {
                $messages[] = $this->errorLabels['missing_field'] .":".$this->labels[$key.'Label'];
            }
        }

        if(count($messages)) {
            return $this->setResponse(false, $messages)->wrapResponse();
        }

        //controllo che le password siano coincidenti e che il campo mail sia una mail
        return $this->validateAdminData($params['adminPassword'], $params['adminConfpass'], $params['adminEmail']);
      
    }

    public function checkSmtpData($request) {
        $messages = [];
        $params = $request->request->all();

        foreach($params as $key => $param) {
            if('' == $param) {
                $messages[] = $this->errorLabels['missing_field'] .":".$this->labels['smtpLabels'][$key];
            }
        }

        if(count($messages)) {
            return $this->setResponse(false, $messages)->wrapResponse();
        }

        //controllo la connessione
        return $this->validateSmtpData($params);
      
    }


    public function checkDbData($request) {
        $messages = [];
        $params = $request->request->all();

        foreach($params as $key => $param) {
            if('' == $param) {
                $messages[] = $this->errorLabels['missing_field'] .":".$this->labels[$key.'Label'];
            }
        }

        if(count($messages)) {
            return $this->setResponse(false, $messages)->wrapResponse();
        }
        $checkConnection = $this->checkConnection($params['dbHost'], $params['dbName'], $params['dbUser'], $params['dbPass']);

        return $this->validateConnection($checkConnection, $params['dbName']);
    }

    private function checkConnection( $db_host, $db_name, $db_user, $db_pass)
    {
        $result = 'err_connect';

        $GLOBALS['db_link'] = DbConn::getInstance(false, [
            'db_type' => 'mysql',
            'db_host' => $db_host,
            'db_user' => $db_user,
            'db_pass' => $db_pass,
        ]);
        if ($GLOBALS['db_link']::$connected) {
            if ($db_name == '') {
                return 'err_db_sel';
            }
            $res = sql_select_db($db_name, $GLOBALS['db_link']);
            if (!$res) {
                return 'create_db';
            } else {
                return 'ok';
            }
        }

        return $result;
    }

    private function validateAdminData($password, $confirmPassword, $email)
    {
        $success = true;
        $messages = [];

        if($password != $confirmPassword) {
            $success = false;
            $messages[] = $this->errorLabels['not_matching_password'];
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $success = false;
            $messages[] = $this->errorLabels['email_not_valid'];
        }
    
        return $this->setResponse($success, $messages)->wrapResponse();

    }

    private function validateSmtpData($params)
    {
        $success = true;
        $messages = [];

        $success = $this->checkSmtpConnection($params['smtpHost'], $params['smtpPort'], $params['smtpSecure'], $params['smtpAutoTls'], $params['smtpUser'], $params['smtpPwd']);

        if(!$success) {
            $messages[] = $this->errorLabels['smtp_failed'];
        }
        return $this->setResponse($success, $messages)->wrapResponse();

    }

    public function checkSmtpConnection($smtpHost, $smtpPort, $smtpSecure, $smtpAutoTls, $smtpUser, $smtpPwd)
    {
        $mail = new PHPMailer\PHPMailer\PHPMailer();

        $mail->Host = $smtpHost;
        $mail->Port = $smtpPort;

        if (!empty($smtpUser)) {
            $mail->SMTPAuth = true;     // turn on SMTP authentication
            $mail->Username = $smtpUser;  // SMTP username
            $mail->Password = $smtpPwd; // SMTP password
        } else {
            $mail->SMTPAuth = false;     // turn on SMTP authentication
        }

        $mail->SMTPSecure = $smtpSecure;
        $mail->SMTPAutoTLS = $smtpAutoTls === 'on';

        if ($mail->smtpConnect()) {
            $mail->smtpClose();

            return true;
        } else {
            return false;
        }
    }

    private function validateConnection($connection, $dbName)
    {
        $success = false;
        $messages = [];
        $removeCreateDb = false;
        switch ($connection) {
            case 'create_db':
                    //mi salvo in sessione che devo creare il db
                    $this->session->set('creationDb', $dbName);
                    $this->session->save();
                    $success = true;

                break;
            case 'ok':
                $removeCreateDb = true;
                    if ($this->checkDBEmpty($dbName)) {
                        if ($this->checkDBCharset()) {
                            $success = true;
                        } else {
                            $success = false;
                            $messages[] = $this->errorLabels['db_not_utf8'];
                     
                        }
                    } else {
                        $success = false;
                        $messages[] = $this->errorLabels['db_not_empty'];
                    }

                break;
            case 'err_connect':
                $removeCreateDb = true;
                    $success = false;
                    $messages[] =  $this->errorLabels['cant_connect_db'];

                break;
            case 'err_db_sel':
                $removeCreateDb = true;
                    $success = false;
                    $messages[] = $this->errorLabels['cant_select_db'];
          
                break;
        }

        if($removeCreateDb && $this->session->has('creationDb')) {
            //rimuovo dalla sessione il valore di creazione db
            $this->session->remove('creationDb');
            $this->session->save();
        }

        return $this->setResponse($success, $messages)->wrapResponse();

    }

    public function checkDBEmpty($db_name)
    {
        $row = sql_query("SELECT COUNT(DISTINCT `table_name`) FROM `information_schema`.`columns` WHERE `table_schema` = '" . $db_name . "'", $GLOBALS['db_link']);
        list($count) = sql_fetch_row($row);

        return $count == 0 ? true : false;
    }

    public function checkDBCharset()
    {
        $row = sql_query("show variables like 'character_set_database'", $GLOBALS['db_link']);
        list(, $charset) = sql_fetch_row($row);

        return $charset === 'utf8' || $charset === 'utf8mb4' ? true : false;
    }



    public function checkFtp($request)
    {
        $messages = [];
        $timeout = 10;
        $params = $request->request->all();

        foreach($params as $key => $param) {
            if('' == $param) {
                $messages[] = $this->errorLabels['missing_field'] .":".$this->labels[$key.'Label'];
            }
        }

        if (!function_exists('ftp_connect')) {
            $messages[] = $this->errorLabels['ftp_not_supported'];
        }

        if(count($messages)) {
            return $this->setResponse(false, $messages)->wrapResponse();
        }

       
        $ftpConnection = ftp_connect($params['ftpHost'], $params['ftpPort'], $timeout);
        if ($ftpConnection === false) {
            return $this->setResponse(false, [$this->errorLabels['ftp_connection_fail']])->wrapResponse();
        }

        $ftpLogin = ftp_login($ftpConnection, $params['ftpUser'], $params['ftpPass']);

        if(!$ftpLogin) {
            $messages[] = $this->errorLabels['ftp_login_fail'];
        }

        return $this->setResponse($ftpLogin, $messages)->wrapResponse();
    }

    public function finalize($request) {

        $params = $request->request->all();

        switch($params['check']) {
            case 1:
                 //genero il file config
                 $this->saveConfig();

                 //controllo esistenza file config
                $success = file_exists(_base_ . '/config.php');
                if($success) {
                    $messages[] = _CONFIG_STEP_SUCCESS;
                } else {
                    $messages[] = _CONFIG_STEP_ERROR;
                }
                
                break;

            case 2:
               
                $messages[] = $this->installDatabase();

                //controllo che le tabelle siano effettivamente presenti
                $success = static::checkDbInstallation($this->session->get('setValues'));
                //sleep(3);
                //$success = true;
                //if($success) {
                //    $messages[] = 'OK DB';
                //} else {
                //    $messages[] = 'Error DB';
                //}
            
                break;
            case 3:

                //controllo che le tabelle siano effettivamente presenti
                $success = $this->registerAdminUser() && $this->storeSettings() && $this->addInstallerRoles();
                //sleep(3);
                //$success = true;
                if($success) {
                    $messages[] = _ADMIN_STEP_SUCCESS;
                } else {
                    $messages[] = _ADMIN_STEP_ERROR;
                }
            
                break;

            case 4:
                  
               //inserisco file di lingua
               $success = $this->importLangs();
                //sleep(3);
                //$success = true;
                if($success) {
                    $messages[] = _LANG_STEP_SUCCESS;
                } else {
                    $messages[] = _LANG_STEP_ERROR;
                }
                break;

            case 5:
            
            //verifico smtp 
            $success = $this->saveSmtpToDatabase();
            //sleep(3);
            //    $success = true;
            if($success) {
                $messages[] = _MAIL_STEP_SUCCESS;
            } else {
                $messages[] = _MAIL_STEP_ERROR;
            }

                break;

            default:
                $success = false;
                $messages[] = _NOT_SUPPORTED_OPERATION;
               
                break;
        }
     
        return $this->setResponse($success, $messages)->wrapResponse();
    }


    private function installDatabase() {
        return shell_exec("php /app/bin/doctrine-migrations migrate --configuration=/app/migrations.yaml --db-configuration=/app/migrations-db.php 2>&1");
      
    }


    public static function checkDbInstallation($values) {

       
        DbConn::getInstance(false,
            [
                'db_type' => 'mysqli',
                'db_host' => $values['dbHost'],
                'db_user' => $values['dbUser'],
                'db_pass' => $values['dbPass'],
                'db_name' => $values['dbName'],
            ]
        );

        return (bool) sql_query("SELECT * FROM `core_setting`");
    }

    public function setResponse($success = false, $messages = []) {
        
        $this->response['success'] = $success;
        $this->response['messages'] = $messages;

        return $this;
    }

    public function getErrorMessages($request) :array {
        $params = $request->request->all();
        $messages = [];
        foreach($params as $key=>$value) {
            if($value) {
                $messages[] = $this->errorLabels[$key];
            }
        }

        return $messages;
    }

    public function wrapResponse($success = false, $messages = []) {
        
        return json_encode($this->response);
    }

    private function saveConfig()
    {
        // ----------- Generating config file -----------------------------
        $config = '';
        $fn = _base_ .'/config_template.php';

        $config = $this->generateConfig($fn);

        $save_fn = _base_ . '/config.php';
        $saved = false;
        if (is_writeable($save_fn)) {
            $handle = fopen($save_fn, 'w');
            if (fwrite($handle, $config)) {
                $saved = true;
            }
            fclose($handle);

            @chmod($save_fn, 0644);
        }

        $this->session->set('config_saved', $saved);
        $this->session->save();
    }

        private function generateConfig($tpl_fn)
        {

        $values = $this->session->get('setValues');
        $config = '';

        if (file_exists($tpl_fn)) {
            $handle = fopen($tpl_fn, 'r');
            $config = fread($handle, filesize($tpl_fn));
            fclose($handle);
        }
        $config = str_replace('[%-DB_TYPE-%]', addslashes('mysqli'), $config);
        $config = str_replace('[%-DB_HOST-%]', addslashes($values['dbHost']), $config);
        $config = str_replace('[%-DB_USER-%]', addslashes($values['dbUser']), $config);
        $config = str_replace('[%-DB_PASS-%]', addslashes($values['dbPass']), $config);
        $config = str_replace('[%-DB_NAME-%]', addslashes($values['dbName']), $config);

        switch ($values['uploadMethod']) {
            case 'http':
                $upload_method = 'fs';

                $config = str_replace('[%-FTP_HOST-%]', 'localhost', $config);
                $config = str_replace('[%-FTP_PORT-%]', '21', $config);
                $config = str_replace('[%-FTP_USER-%]', '', $config);
                $config = str_replace('[%-FTP_PASS-%]', '', $config);
                $config = str_replace('[%-FTP_PATH-%]', '/', $config);
                break;
            case 'ftp':
                $upload_method = 'ftp';

                $config = str_replace('[%-FTP_HOST-%]', addslashes($values['ftpHost']), $config);
                $config = str_replace('[%-FTP_PORT-%]', addslashes($values['ftpPort']), $config);
                $config = str_replace('[%-FTP_USER-%]', addslashes($values['ftpUser']), $config);
                $config = str_replace('[%-FTP_PASS-%]', addslashes($values['ftpPass']), $config);
                $config = str_replace('[%-FTP_PATH-%]', addslashes($values['ftpPath']), $config);
                break;
            default:
                break;
        }

        if ($values['useSmtpDatabase'] == 'on') {
            $config = str_replace('[%-SMTP_USE_DATABASE-%]', addslashes($values['useSmtpDatabase']), $config);
            $config = str_replace('[%-SMTP_USE_SMTP-%]', addslashes($values['useSmtp']), $config);
            $config = str_replace('[%-SMTP_HOST-%]', addslashes($values['smtpHost']), $config);
            $config = str_replace('[%-SMTP_PORT-%]', addslashes($values['smtpPort']), $config);
            $config = str_replace('[%-SMTP_SECURE-%]', addslashes($values['smtpSecure']), $config);
            $config = str_replace('[%-SMTP_AUTO_TLS-%]', addslashes($values['smtpAutoTls']), $config);
            $config = str_replace('[%-SMTP_USER-%]', addslashes($values['smtpUser']), $config);
            $config = str_replace('[%-SMTP_PWD-%]', addslashes($values['smtpPwd']), $config);
            $config = str_replace('[%-SMTP_DEBUG-%]', addslashes('0'), $config);
        } else {
            $config = str_replace('[%-SMTP_USE_DATABASE-%]', addslashes('off'), $config);
            $config = str_replace('[%-SMTP_USE_SMTP-%]', addslashes('off'), $config);
            $config = str_replace('[%-SMTP_HOST-%]', addslashes(''), $config);
            $config = str_replace('[%-SMTP_PORT-%]', addslashes(''), $config);
            $config = str_replace('[%-SMTP_SECURE-%]', addslashes(''), $config);
            $config = str_replace('[%-SMTP_AUTO_TLS-%]', addslashes(''), $config);
            $config = str_replace('[%-SMTP_USER-%]', addslashes(''), $config);
            $config = str_replace('[%-SMTP_PWD-%]', addslashes(''), $config);
            $config = str_replace('[%-SMTP_DEBUG-%]', addslashes('0'), $config);
        }

        $config = str_replace('[%-UPLOAD_METHOD-%]', $upload_method, $config);

        return $config;
    }

    private function registerAdminUser()
    {
        // ----------- Registering admin user ---------------------------------
    

        $values = $this->session->get('setValues');

        $qtxt = "SELECT * FROM core_user WHERE userid='/" . $values['adminUser'] . "'";
        $q = sql_query($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) { // Did the user refreshed the page?
            // You never know..
            $qtxt = "UPDATE core_user SET firstname='" . $values['adminName'] . "',
                lastname='" . $values['adminLastname'] . "',
                pass='" . md5($admInfo['adminPassword']) . "' ";
            $qtxt .= "WHERE userid='/" . $admInfo['adminUser'] . "'";
            $q = sql_query($qtxt);
        } else { // Let's create the admin user..
            $qtxt = 'INSERT INTO core_st (idst) VALUES(NULL)';
            $q = sql_query($qtxt);
            $user_idst = sql_insert_id();

            $qtxt = "SELECT groupid, idst FROM core_group WHERE groupid='/framework/level/godadmin' ";
            $qtxt .= "OR groupid='/oc_0'";
            $q = sql_query($qtxt);

            $godadmin = 0;
            $oc_0 = 0;
            $res = [];
            if (($q) && (sql_num_rows($q) > 0)) {
                while ($row = sql_fetch_array($q)) {
                    $res[$row['groupid']] = $row['idst'];
                }
                $godadmin = $res['/framework/level/godadmin'];
                $oc_0 = $res['/oc_0'];
            }

            $qtxt = "INSERT INTO core_group_members (idst, idstMember) VALUES('" . $oc_0 . "', '" . $user_idst . "')";
            $q = sql_query($qtxt);
            $qtxt = "INSERT INTO core_group_members (idst, idstMember) VALUES('" . $godadmin . "', '" . $user_idst . "')";
            $q = sql_query($qtxt);

            $qtxt = 'INSERT INTO core_user (idst, userid, firstname, lastname, pass, email) ';
            $qtxt .= "VALUES ('" . $user_idst . "', '/" . $values['adminUser'] . "',
                '" . $values['adminName'] . "', '" . $values['adminLastname'] . "',
                '" . md5($values['adminPassword']) . "', '" . $values['adminEmail'] . "')";
            $q = sql_query($qtxt);
        }

        return $q;
    }

    private function storeSettings()
    {
        require_once _adm_ . '/versions.php';
        $values = $this->session->get('setValues');
        DbConn::getInstance(false,
            [
                'db_type' => 'mysqli',
                'db_host' => $values['dbHost'],
                'db_user' => $values['dbUser'],
                'db_pass' => $values['dbPass'],
                'db_name' => $values['dbName'],
            ]
        );

        $qtxt = "UPDATE core_setting SET param_value='" . Lang::getSelLang() . "' ";
        $qtxt .= "WHERE param_name='default_language'";
        $q = sql_query($qtxt);

        $qtxt = 'INSERT INTO `core_setting` ';
        $qtxt .= ' (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) ';
        $qtxt .= ' VALUES ';
        $qtxt .= " ('core_version', '" . _file_version_ . "', 'string', 255, '0', 1, 0, 1, 1, '') ";
        $q = sql_query($qtxt);

        return $q;
    }

    private function addInstallerRoles()
    {
        require_once _lib_ . '/installer/lib.role.php';

        $godadmin = getGroupIdst('/framework/level/godadmin');
        $oc0 = getGroupIdst('/oc_0');

        $fn = _lib_ . '/installer/role/rolelist_godadmin.txt';
        $roles = explode("\n", file_get_contents($fn));
        addRoles($roles, $godadmin);

        $fn = _lib_ . '/installer/role/rolelist_oc0.txt';
        $roles = explode("\n", file_get_contents($fn));
        addRoles($roles, $oc0);

        return true;
    }

    private function importLangs()
    {
        $LangAdm = new LangAdm();
        $langsToInstall = $this->session->get('setLangs');
        foreach($langsToInstall as $lang) {
            $fn = _base_ . '/xml_language/lang[' . $lang . '].xml';

            if (file_exists($fn)) {
              
                $LangAdm->importTranslation($fn, true, false);
            }
        }
        
        return true;
    }
    private function saveSmtpToDatabase()
    {
        $result = true;
       
        $values = $this->session->get('setValues');
        if($values['useSmtpDatabase'] == 'on') {
            DbConn::getInstance(false,
            [
                'db_type' => 'mysqli',
                'db_host' => $values['dbHost'],
                'db_user' => $values['dbUser'],
                'db_pass' => $values['dbPass'],
                'db_name' => $values['dbName'],
            ]
            );

            $mailConfigs = $this->getSmtpConfig();

            $queryInsert = 'INSERT INTO'
            . ' %adm_mail_configs (title, system) VALUES ("DEFAULT", "1")';

            $result = sql_query($queryInsert);

            $mailConfigId = sql_insert_id();

            foreach($mailConfigs['smtp'] as $type => $value) {

                $realValue = $values[$type] ?? $value; 
                $queryInsert = 'INSERT INTO'
                . ' %adm_mail_configs_fields (mailConfigId, type, value) VALUES ("'. $mailConfigId .'", "'. $type .'", "'. $realValue .'")';
                $result = sql_query($queryInsert);
            }
        }
        
        return $result;

    }


    public function saveFields($request) {

        $params = $request->request->all();
        
        foreach($params as $key => $value) {
            if('lang_install' === $key) {
                foreach($values as $lang) {
                    $this->setLangs($lang);
                }
            } else {
                $this->saveValue($key, $value);
            }
            
        }

        return $this->setResponse(true, [])->wrapResponse();
    }
  


}
