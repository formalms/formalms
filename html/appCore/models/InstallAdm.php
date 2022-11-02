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

    protected $debug;
   
    public function __construct($debug = null)
    {
       
          //soluzione provvisoria 
        require_once(_lib_.'/installer/lang/'.Lang::getSelLang().'.php');

        $this->fillSteps();
        $this->fillLabels();
        parent::__construct();
    
    }

    public function fillSteps() {
        $this->steps = ['1' => _TITLE_STEP1, 
                        '2' => _TITLE_STEP2, 
                        '3' => _TITLE_STEP3,
                        '4' => _TITLE_STEP4,
                        '5' => _TITLE_STEP5
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
        $labels['ftpUsernameLabel'] = _FTP_USERNAME;
        $labels['ftpPasswordLabel'] = _FTP_PASS;
        $labels['ftpPathLabel'] = _FTP_PATH;
        /*************************************** */
        $labels['adminInfoLabel'] = _ADMIN_USER_INFO;
        $labels['adminUserLabel'] = _ADMIN_USERNAME;
        $labels['adminNameLabel'] = _ADMIN_FIRSTNAME;
        $labels['adminSurnameLabel'] = _ADMIN_LASTNAME;
        $labels['adminPasswordLabel'] = _ADMIN_PASS;
        $labels['adminConfirmationPasswordLabel'] = _ADMIN_CONFPASS;
        $labels['adminEmailLabel'] = _ADMIN_EMAIL;
        $labels['langInstallLabel'] = _LANG_TO_INSTALL;

        $this->labels = $labels;

        return $this;
    }

    public function getLabels() {

        return $this->labels;
    }

    public function getData($request) {

        $params = $this->getLabels();
      
        $params = array_merge($params, 
                                $this->checkRequirements(), 
                                ini_get_all(), 
                                $this->getLicense(),
                                $this->getDbConfig(),
                                $this->getSmtpConfig(),
                                ['setValues' => $this->getSetValues()]);

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
        $fn = _base_ . '/install/data/license/license_' . Lang::getSelLang() . '.txt';
    
        $english_fn = _base_ . '/install/data/license/license_english.txt';

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
            'active' => '',
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

        $localCfg['smtpLabels'] = 
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

        if (file_exists(_base_ . '/config.php')) {
            define('IN_FORMA', true);
            include _base_ . '/config.php';
    
            foreach ($localCfg as $key => $value) {
                $localCfg['smtp'][$key] = $cfg[$key];
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
            $result =  $this->saveValue($keyParam, $paramValue);
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


    public function checkDbData($request) {
        $params = $request->request->all();
        extract($params);
        return $this->checkConnection($dbHost, $dbName, $dbUser, $dbPass);
    }

    private function checkConnection( $db_host, $db_name, $db_user, $db_pass)
    {
        $result['success'] = false;
        $result['message'] = 'err_connect';

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

    //TODO NO_Strict_MODE: to be confirmed
    public function checkStrictMode()
    {
        //TODO NO_Strict_MODE: to be done
        return true;
        //TODO NO_Strict_MODE: to be done [remove below]
        $qtxt = 'SELECT @@GLOBAL.sql_mode AS res';
        $q = sql_query($qtxt, $GLOBALS['db_link']);
        list($r1) = sql_fetch_row($q);
        $qtxt = 'SELECT @@SESSION.sql_mode AS res';
        $q = sql_query($qtxt, $GLOBALS['db_link']);
        list($r2) = sql_fetch_row($q);
        $res = ((strpos($r1 . $r2, 'STRICT_') === false) ? true : false);

        return $res;
    }

    private function checkFtp($ftp_host, $ftp_port, $ftp_user, $ftp_pass)
    {
        if (empty($ftp_host) || empty($ftp_port) || empty($ftp_user) || empty($ftp_pass)) {
            return 'err_param';
        }

        if (!function_exists('ftp_connect')) {
            return 'err_not_supp';
        }

        $timeout = 10;

        $ftp = ftp_connect($ftp_host, $ftp_port, $timeout);
        if ($ftp === false) {
            return 'err_connect';
        }

        return ftp_login($ftp, $ftp_user, $ftp_pass) ? 'ok' : 'err_login';
    }
  


}
