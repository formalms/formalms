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

/**
 * The install model class.
 *
 * This class can be used in order to retrive and manipulate all kind of
 * information about the installation of the platform
 *
 * @since 4.0
 */

use \FormaLms\lib\Helpers\HelperTool;

class InstallAdm extends Model
{
    /** @var array * */
    protected $steps;
    /** @var array * */
    protected $labels;
    /** @var array * */
    protected $errorLabels;
    /** @var bool * */
    protected $debug;
    /** @var array * */
    protected $response;
    /** @var bool * */
    protected $upgrade;
    /** @var bool * */
    protected $installFlag;


    public const CHECK_REQUIREMENTS = '1';
    public const CHECK_DATABASE = '2';
    public const CHECK_ADMIN = '3';
    public const CHECK_SMTP = '4';
    public const CHECK_FINAL = '5';
    public const CHECK_UPGRADE = '2';

    public const SMTP_REQUIRED = [
        'smtpHost', 'smtpPort', 'smtpUser', 'smtpPwd', 'smtpSecure', 'smtpAutoTls'
    ];

    /**
     * Constructor method of the class.
     *
     * @param bool debug Debug not implemented yet
     */
    public function __construct($debug = false)
    {
        //TODO: refactoring using namespace and improvements
        require_once(_lib_ . '/System/lang/' . Lang::getSelLang() . '.php');
        require_once(_lib_ . '/System/lang/' . 'english' . '.php');

        $this->installFlag = static::checkDbInstallation();
        $this->upgrade = $this->installFlag ? \FormaLms\lib\Version\VersionChecker::needsUpgrade() : false;

        $this->debug = $debug;

        if (!$debug) {
            // ini_set("display_errors", false);
        }
        $this->fillSteps();
        $this->fillLabels();
        $this->fillErrorLabels();

        $this->setResponse(false, []);
        parent::__construct();
    }

    /**
     * Method to fill default steps.
     *
     * @return self
     */
    public function fillSteps(): self
    {
        if ($this->installFlag) {
            $this->steps = [self::CHECK_REQUIREMENTS => _TITLE_STEP1,
                self::CHECK_UPGRADE => _TITLE_STEP5
            ];
        } else {
            $this->steps = [self::CHECK_REQUIREMENTS => _TITLE_STEP1,
                self::CHECK_DATABASE => _TITLE_STEP2,
                self::CHECK_ADMIN => _TITLE_STEP3,
                self::CHECK_SMTP => _TITLE_STEP4,
                self::CHECK_FINAL => _TITLE_STEP5
            ];
        }

        return $this;
    }

    /**
     * Method to get steps.
     *
     * @return array
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * Method to fill labels for view definitions.
     *
     * @return self
     */
    public function fillLabels(): self
    {
        $labels['introText'] = _INSTALLER_INTRO_TEXT;
        $labels['languageLabel'] = _LANGUAGE;
        $labels['installerTitle'] = _INSTALLER_TITLE;
        $labels['upgraderTitle'] = _UPGRADER_TITLE;
        $labels['next'] = _NEXT;
        $labels['back'] = _BACK;
        $labels['cancel'] = _CANCEL;
        $labels['current'] = _CURRENT;
        $labels['pagination'] = _PAGINATION;
        $labels['finish'] = !$this->upgrade ? _FINISH : _UPGRADE_FINISH;
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
        $labels['successLabel'] = _INSTALLATION_COMPLETED;
        $labels['installExplain'] = _INSTALL_EXPLAIN;
        $labels['upgradeExplain'] = _UPGRADE_EXPLAIN;
        $labels['errorLabel'] = _INSTALLATION_ERROR;
        $labels['goToLogin'] = _SITE_HOMEPAGE;
        $labels['downloadLock'] = _DOWNLOAD_LOCK;
        $labels['downloadConfig'] = _DOWNLOAD_CONFIG;
        $labels['logSql'] = _LOG_SQL;

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

        /****************************/
        $labels['installedVersion'] = _INSTALLED_VERSION;
        $labels['detectedVersion'] = _DETECTED_VERSION;

        $labels['testButton'] = !$this->upgrade ? _TEST_INSTALL : _TEST_UPGRADE;
        $labels['generateLock'] = _GENERATE_LOCK;

        $this->labels = $labels;

        return $this;
    }

    /**
     * Method to fill error labels for view validation.
     *
     * @return self
     */
    public function fillErrorLabels(): self
    {
        $this->errorLabels['unsuitable_requirements'] = _UNSUITABLE_REQUIREMENTS;
        $this->errorLabels['missing_check'] = _MISSING_LICENSE_CHECK;
        $this->errorLabels['missing_field'] = _MISSING_FIELD;
        $this->errorLabels['db_not_utf8'] = _DB_NOT_UTF8;
        $this->errorLabels['db_not_empty'] = _DB_NOT_EMPTY;
        $this->errorLabels['cant_connect_db'] = _CANT_CONNECT_WITH_DB;
        $this->errorLabels['cant_select_db'] = _CANT_SELECT_DB;
        $this->errorLabels['unsuitable_sql_version'] = _UNSUITABLE_SQL_VERSION;
        $this->errorLabels['not_matching_password'] = _NOT_MATCHING_PASSWORD;
        $this->errorLabels['email_not_valid'] = _NOT_VALID_EMAIL;
        $this->errorLabels['smtp_failed'] = _SMTP_FAILED;
        $this->errorLabels['block_upgrade'] = _BLOCK_UPGRADE;

        return $this;
    }

    /**
     * Method to fill error labels for view validation.
     *
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Method to retrieve required fileds for smtp validation
     *
     * @return array
     */
    public function getSmtpFieldsRequired(): array
    {
        return self::SMTP_REQUIRED;
    }

    /**
     * Method to retrieve data for the view
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request derived from controller
     *
     * @return array
     */
    public function getData(\Symfony\Component\HttpFoundation\Request $request): array
    {
        $params = $this->getLabels();
        $params['debug'] = $this->debug;
        $params = array_merge(
            $params,
            $this->checkRequirements(),
            ini_get_all()
        );
        if (!$this->installFlag) {
            $params = array_merge(
                $params,
                $this->getLicense(),
                $this->getDbConfig(),
                $this->getSmtpConfig(),
                ['setValues' => $this->getSetValues()],
                ['setLangs' => $this->getSetLangs()],
                ['smtpFieldsRequired' => $this->getSmtpFieldsRequired()]
            );
        }

        $params = array_merge($params, \FormaLms\lib\Version\VersionChecker::compareVersions());


        $params['setLang'] = Lang::getSelLang();
        $params['upgrade'] = (bool)$this->upgrade;
        $params['install'] = $this->installFlag;
        $params['serverSwInfo'] = $request->server->get('SERVER_SOFTWARE');
        $params['currentVersion'] = $this->installFlag ? \FormaLms\lib\Version\VersionChecker::getInstalledVersion() : false;
        $params['fileVersion'] = $this->installFlag ? \FormaLms\lib\Version\VersionChecker::getCurrentVersion() : false;

        $params['phpVersionInfo'] = \FormaLms\lib\Version\VersionChecker::getPhpVersion();
        $params['sqlClientVersion'] = \FormaLms\lib\Version\VersionChecker::getSqlClientVersion();
        $params['sqlServerVersion'] = \FormaLms\lib\Version\VersionChecker::getSqlVersion();
        $params['mbstringData'] = extension_loaded('mbstring') ? _ON : _OFF;
        $params['mimeCtData'] = $params['mimeCt'] == 'ok' ? _ON : _OFF;
        $params['fileInfoData'] = extension_loaded('fileinfo') ? _ON : _OFF . ' ' . _ONLY_IF_YU_WANT_TO_USE_FILEINFO;
        $params['ldapData'] = extension_loaded('ldap') ? _ON : _OFF . ' ' . _ONLY_IF_YU_WANT_TO_USE_IT;
        $params['openSslData'] = extension_loaded('openssl') ? _ON : _OFF . ' ' . _WARINNG_SOCIAL;
        $params['phpTimezoneData'] = @date_default_timezone_get();
        $params['allowUrlFopenData'] = $params['allowUrlFopen'] != 'err' ? _ON : _OFF . ' ' . _WARINNG_SOCIAL;
        $params['allowUrlIncludeData'] = $params['allowUrlInclude'] != 'err' ? _ON : _OFF;
        $params['uploadMaxFilesizeData'] = ini_get('upload_max_filesize');
        $params['postMaxSizeData'] = ini_get('post_max_size');
        $params['maxExecutionTimeData'] = ini_get('max_execution_time') . 's';
        $params['checkFolderPerms'] = $this->checkFolderPerm();


        return $params;
    }

    /**
     * Method to check minimum technical requirements for installation
     *
     * @return array
     */
    public function checkRequirements(): array
    {
        $res = [];

        $checkRequirements = 1;

        $res['mandatory']['php'] = \FormaLms\lib\Version\VersionChecker::matchPhpVersion()['message'];

        $driver = [
            'mysqli' => extension_loaded('mysqli'),
        ];
        if (array_filter($driver)) {
            // mysql client version, in php the version number is a string regcut it
            $sqlClientVersion = \FormaLms\lib\Version\VersionChecker::getSqlClientVersion();

            if ('unknown' === $sqlClientVersion) {
                $res['requirements']['mysqlClient'] = 'ok';
            } else {
                $res['requirements']['mysqlClient'] = (\FormaLms\lib\Version\VersionChecker::compareSqlClientVersion($sqlClientVersion) ? 'ok' : 'err');
            }
        } else {
            $res['mysqlClient'] = 'err';
        }
        if (array_filter($driver)) {
            // mysql version, in easyphp the version number is ina string regcut it

            $mysqlVersion = \FormaLms\lib\Version\VersionChecker::getSqlVersion();

            if ('unknown' === $mysqlVersion) {
                $res['mandatory']['mysql'] = 'ok';
            } else {

                if (\FormaLms\lib\Version\VersionChecker::compareSqlVersion($mysqlVersion)) {
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
        $res['requirements']['allowUrlFopen'] = ((bool)ini_get('allow_url_fopen') ? 'ok' : 'err');
        $res['requirements']['allowUrlInclude'] = ((bool)ini_get('allow_url_include') ? 'err' : 'ok');
        $res['mandatory']['mimeCt'] = (function_exists('mime_content_type') || (class_exists('file') && method_exists('finfo', 'file')) ? 'ok' : 'err');
        //$res['mandatory']['disableFunctions'] = in_array('shell_exec', explode(',', ini_get('disable_functions'))) ? 'err' : 'ok';

        if (in_array('err', $res['mandatory'])) {
            $checkRequirements = 0;
        }

        $resultArray = array_merge($res['mandatory'], $res['requirements']);
        $resultArray['checkRequirements'] = $checkRequirements;

        return $resultArray;
    }

    /**
     * Method to check permissions for involved folders
     *
     * @return string
     */

    public function checkFolderPerm(): string
    {
        $res = '';

        $platform_folders = (array)$this->session->get('platform_arr');
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

    /**
     * Method to retrieve right translated license file
     *
     * @return array
     */
    public function getLicense(): array
    {
        $content = '';
        $fn = _lib_ . '/System/license/license_' . Lang::getSelLang() . '.txt';

        $english_fn = _lib_ . '/System/license/license_english.txt';

        $handle = false;
        if ((!file_exists($fn)) && (file_exists($english_fn))) {
            $fn = $english_fn;
        }

        if (file_exists($fn)) {
            $handle = fopen($fn, 'rb');
            $content = fread($handle, filesize($fn));
            fclose($handle);
        }

        $params['licenseLabel'] = (defined('_SOFTWARE_LICENSE') ? _SOFTWARE_LICENSE : 'License');
        $params['licenseContent'] = $content;

        return $params;
    }

    /**
     * Method to retrieve DB settings fields
     *
     * @return array
     */
    public function getDbConfig(): array
    {
        $localCfg['dbConfig'] = [
            'dbType' => '',
            'dbHost' => '',
            'dbName' => '',
            'dbUser' => '',
            'dbPass' => '',
        ];
        if (file_exists(_base_ . '/config.php')) {
            include _base_ . '/config.php';

            foreach ($localCfg['dbConfig'] as $key => $value) {
                $localCfg['dbConfig'][$key] = $cfg[HelperTool::camelCaseToSnake($key)];
            }
        }

        return $localCfg;
    }


    /**
     * Method to retrieve SMTP settings fields
     *
     * @return array
     */
    public function getSmtpConfig(): array
    {
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
            include _base_ . '/config.php';

            foreach ($localCfg['smtp'] as $key => $value) {
                $localCfg['smtp'][$key] = $cfg[HelperTool::camelCaseToSnake($key)] ?? '';
            }
        }

        $localCfg['selectEnabling'] = [
            'on' => _YES,
            'off' => _NO,
        ];

        $localCfg['secureSelect'] = ['ssl' => 'SSL', 'tls' => 'TLS'];

        return $localCfg;
    }

    /**
     * Method to set value in session
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request derived from controller
     *
     * @return bool
     */
    public function setValue($request): bool
    {
        $params = $request->request->all();
        $result = false;
        foreach ($params as $keyParam => $paramValue) {
            if ($paramValue === 'true') {
                $paramValue = true;
            }

            if ($paramValue === 'false') {
                $paramValue = false;
            }

            if ($keyParam === 'selectLangs' || $keyParam === 'deselectLangs') {
                $result = $this->setLangs($paramValue, $keyParam === 'selectLangs' ? 1 : 0);
            } else {
                //per retrocompatibilità
                if ('sel_lang' === $keyParam) {
                    $this->session->set($keyParam, $paramValue);
                    $this->session->save();
                    $result = true;
                } else {
                    $result = $this->saveValue($keyParam, $paramValue);
                }
            }
        }

        return $result;
    }

    /**
     * Method to save value in session
     *
     * @param string $key identifier for index in session
     * @param string $value value to save actually in session
     *
     * @return bool
     */
    public function saveValue($key, $value): bool
    {
        $values = $this->session->get('setValues');
        $values[$key] = $value;
        $this->session->set('setValues', $values);
        $this->session->save();

        return true;
    }

    /**
     * Method to set languages
     *
     *
     * @param string $value value to save actually in session
     * @param int $add idex to determine if it's a new setting to add or to remove
     *
     * @return bool
     */
    public function setLangs($value, $add = 1): bool
    {
        $values = $this->session->get('setLangs');
        if ($add) {
            $values[$value] = $value;
        } else {
            unset($values[$value]);
        }

        $this->session->set('setLangs', $values);
        $this->session->save();

        return true;
    }

    /**
     * Method to set errors
     *
     *
     * @param string $value value to save actually in session
     *
     * @return bool
     */
    public function setErrors($value): bool
    {
        $values = $this->session->get('installErrors');
        $values[] = $value;

        $this->session->set('installErrors', $values);
        $this->session->save();

        return true;
    }


    /**
     * Method to get compiled fields value from session
     *
     * @return array
     */
    public function getSetValues(): array
    {
        $values = $this->session->get('setValues', []);
        if (!in_array('uploadMethod', array_keys($values))) {
            $values['uploadMethod'] = 'http';
        }

        if (!in_array('step', array_keys($values))) {
            $values['step'] = 0;
        }
        return $values;
    }

    /**
     * Method to get checked languages from session
     *
     * @return array
     */
    public function getSetLangs(): ?array
    {
        return $this->session->get('setLangs');
    }

    /**
     * Method to check admin user compiled value
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request derived from controller
     *
     * @return string
     */
    public function checkAdminData($request): string
    {
        $messages = [];
        $params = $request->request->all();
        $optinalFields = ['adminName', 'adminLastname'];
        foreach ($params as $key => $param) {
            if ('' == $param && !in_array($key, $optinalFields)) {
                $messages[] = $this->errorLabels['missing_field'] . ":" . $this->labels[$key . 'Label'];
            }
        }

        if (count($messages)) {
            return $this->setResponse(false, $messages)->wrapResponse();
        }

        //controllo che le password siano coincidenti e che il campo mail sia una mail
        return $this->validateAdminData($params['adminPassword'], $params['adminConfpass'], $params['adminEmail']);
    }

    /**
     * Method to check smtp compiled value
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request derived from controller
     *
     * @return string
     */
    public function checkSmtpData($request): string
    {
        $messages = [];
        $params = $request->request->all();

        foreach ($params as $key => $param) {
            if ('' == $param) {
                $messages[] = $this->errorLabels['missing_field'] . ":" . $this->labels['smtpLabels'][$key];
            }
        }

        if (count($messages)) {
            return $this->setResponse(false, $messages)->wrapResponse();
        }

        //controllo la connessione
        return $this->validateSmtpData($params);
    }

    /**
     * Method to check db compiled value
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request derived from controller
     *
     * @return string
     */
    public function checkDbData($request): string
    {
        $messages = [];
        $params = $request->request->all();

        foreach ($params as $key => $param) {
            if ('' == $param && $key != 'dbPass') { //in lcolae potresti non avere password
                $messages[] = $this->errorLabels['missing_field'] . ":" . $this->labels[$key . 'Label'];
            }
        }

        if (count($messages)) {
            return $this->setResponse(false, $messages)->wrapResponse();
        }
        $checkConnection = $this->checkConnection($params['dbHost'], $params['dbName'], $params['dbUser'], $params['dbPass']);

        return $this->validateConnection($checkConnection, $params['dbName']);
    }

    /**
     * Method to check DB connection existence
     *
     * @param string $db_host Host for DB
     * @param string $db_name Name of DB
     * @param string $db_user User for DB
     * @param string $db_pass Password for DB
     *
     * @return string
     */
    private function checkConnection($db_host, $db_name, $db_user, $db_pass): string
    {
        $result = 'err_connect';

        \FormaLms\db\DbConn::getInstance(
            null, [
                'db_type' => 'mysqli',
                'db_host' => $db_host,
                'db_user' => $db_user,
                'db_pass' => $db_pass,
                'db_name' => $db_name,
            ]
        );
        if (\FormaLms\db\DbConn::getInstance()::$connected) {
            if ($db_name == '') {
                return 'err_db_sel';
            }
            $res = sql_select_db($db_name);
            if (!$res) {
                return 'create_db';
            } else {
                return 'ok';
            }
        }

        return $result;
    }

    /**
     * Method to validate admin fields
     *
     * @param string $password password inserted for admin
     * @param string $confirmPassword confirm password inserted for admin
     * @param string $email email for admin
     *
     * @return string
     */
    private
    function validateAdminData($password, $confirmPassword, $email): string
    {
        $success = true;
        $messages = [];

        if ($password != $confirmPassword) {
            $success = false;
            $messages[] = $this->errorLabels['not_matching_password'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $success = false;
            $messages[] = $this->errorLabels['email_not_valid'];
        }

        return $this->setResponse($success, $messages)->wrapResponse();
    }

    /**
     * Method to validate smtp fields
     *
     * @param array $params Array picker for parameters
     * @param string $params ['smtpHost'] host for smtp
     * @param string $params ['smtpPort'] port for smtp
     * @param string $params ['smtpSecure'] secure for smtp
     * @param string $params ['smtpAutoTls'] tls for smtp
     * @param string $params ['smtpUser'] user for smtp
     * @param string $params ['smtpPwd'] password for smtp
     *
     * @return string
     */
    private
    function validateSmtpData($params): string
    {
        $success = true;
        $messages = [];

        $success = $this->checkSmtpConnection($params['smtpHost'], $params['smtpPort'], $params['smtpSecure'], $params['smtpAutoTls'], $params['smtpUser'], $params['smtpPwd']);

        if (!$success) {
            $messages[] = $this->errorLabels['smtp_failed'];
        }
        return $this->setResponse($success, $messages)->wrapResponse();
    }

    /**
     * Method to validate smtp connection
     *
     * @param string $smtpHost host for smtp
     * @param string $smtpPort port for smtp
     * @param string $smtpSecure secure for smtp
     * @param string $smtpAutoTls tls for smtp
     * @param string $smtpUser user for smtp
     * @param string $smtpPwd password for smtp
     *
     * @return bool
     */
    public
    function checkSmtpConnection($smtpHost, $smtpPort, $smtpSecure, $smtpAutoTls, $smtpUser, $smtpPwd): bool
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

    /**
     * Method to validate db connection
     *
     * @param string $connectionResult sql connection result
     * @param string $dbName name for DB
     *
     * @return string
     */
    private
    function validateConnection($connectionResult, $dbName): string
    {
        $success = false;
        $messages = [];
        $removeCreateDb = false;
        $sqlVersionCheck = false;

        $checkSqlVersion = \FormaLms\lib\Version\VersionChecker::getSqlVersionArray($this->getSqlVersionByQuery());

        if (!empty($checkSqlVersion[1])) {
            $sqlVersionCheck = \FormaLms\lib\Version\VersionChecker::compareSqlVersion($checkSqlVersion[1]);
        }

        if ($sqlVersionCheck) {
            switch ($connectionResult) {
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
                    $messages[] = $this->errorLabels['cant_connect_db'];

                    break;
                case 'err_db_sel':
                    $removeCreateDb = true;
                    $success = false;
                    $messages[] = $this->errorLabels['cant_select_db'];

                    break;
                default:
            }

            if ($removeCreateDb && $this->session->has('creationDb')) {
                //rimuovo dalla sessione il valore di creazione db
                $this->session->remove('creationDb');
                $this->session->save();
            }
        } else {

            if ($connectionResult == 'err_connect') {
                $messages[] = $this->errorLabels['cant_connect_db'];
            } else {
                $messages[] = $this->errorLabels['unsuitable_sql_version'];
            }

        }

        return $this->setResponse($success, $messages)->wrapResponse();
    }

    /**
     * Method to check if DB is empty
     *
     * @param string $dbName name for DB
     *
     * @return bool
     */
    public
    function checkDBEmpty($dbName): bool
    {
        $row = sql_query("SELECT COUNT(DISTINCT `table_name`) FROM `information_schema`.`columns` WHERE `table_schema` = '" . $dbName . "'");
        [$count] = sql_fetch_row($row);

        return $count == 0 ? true : false;
    }

    /**
     * Method to check if sql version in the host is suitable
     *
     *
     * @return string
     */
    public
    function getSqlVersionByQuery(): ?string
    {
        $row = sql_query("SELECT version()");
        [$version] = sql_fetch_row($row);
        return $version;
    }

    /**
     * Method to check the charset of db
     *
     *
     * @return bool
     */
    public
    function checkDBCharset(): bool
    {
        $row = sql_query("show variables like 'character_set_database'");
        [, $charset] = sql_fetch_row($row);

        return $charset === 'utf8' || $charset === 'utf8mb4' ? true : false;
    }


    /**
     * Method to finalize the installation step by step
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request derived from controller
     *
     * @return string
     */
    public
    function finalize($request): string
    {
        $params = $request->request->all();
        $messages = [];
        $success = false;
        $type = 'standard';


        if (array_key_exists('upgrade', $params) && $params['upgrade']) {
            switch ($params['check']) {
                case 1:
                    //se c'è da installare metto la tabella doctrine migrations
                    if (\FormaLms\lib\Version\VersionChecker::compareUpgradeVersion()) {
                        $success = $this->installMigrationsTable();

                        if ($success) {
                            $messages[] = _VERSION_STEP_OK;
                        } else {
                            $messages[] = _VERSION_STEP_ERROR;
                        }
                    }
                    break;

                case 2:
                    //lancio migrate
                    $migrator = FormaLms\lib\Database\FormaMigrator::getInstance();
                    $responseMigration = $migrator->executeCommand('migrate', ['debug' => $params['debug']]);


                    if (!$responseMigration['success']) {
                        $type = 'database';
                        $this->setErrors($type);
                        $messages[] = $responseMigration['message'];
                    } else {
                        $messages[] = _MIGRATION_COMPLETED;
                    }

                    $overWrittenLangs = $this->importLangs($this->getInstalledLanguages());
                    if (count($overWrittenLangs)) {
                        foreach ($overWrittenLangs as $lang) {
                            $messages[] = $lang;
                        }
                        $messages[] = _LANG_STEP_SUCCESS;
                    } else {
                        $messages[] = _LANG_STEP_ERROR;
                    }


                    $success = $this->saveUpgradeVersion();
                    if ($success) {
                        $messages[] = _UPGRADE_STEP_SUCCESS;
                    } else {
                        $messages[] = _UPGRADE_STEP_ERROR;
                    }

                    //$success = $this->setDefaultTemplate();
                    //if ($success) {
                    //    $messages[] = _TEMPLATE_STEP_SUCCESS;
                    //} else {
                    //    $messages[] = _TEMPLATE_STEP_ERROR;
                    //}

                    $this->generateLock();
                    recursiveRmdir(FormaLms\appCore\Template\TwigManager::getCacheDir());
                    $messages[] = _CLEARTWIG_CACHE_OK;

                    $this->cleanSession();
                    break;
            }


            return $this->setResponse($success, $messages, $type)->wrapResponse();
        }


        switch ($params['check']) {
            case 1:
                //genero il file config
                $this->saveConfig();
                $this->saveTmpConfig();
                //controllo esistenza file config
                $success = file_exists(_base_ . '/config.php');
                if ($success) {
                    $messages[] = _CONFIG_STEP_SUCCESS;
                } else {
                    $type = 'config';
                    $this->setErrors($type);
                    $messages[] = _CONFIG_STEP_ERROR;
                }

                break;

            case 2:
                $migrator = FormaLms\lib\Database\FormaMigrator::getInstance();
                $responseMigration = $migrator->executeCommand('migrate', ['debug' => $params['debug']]);

                //controllo che le tabelle siano effettivamente presenti
                $success = static::checkDbInstallation();

                if (!$success || !$responseMigration['success']) {
                    $type = 'database';
                    $this->setErrors($type);
                    $messages[] = $responseMigration['message'];
                } else {
                    $messages[] = _MIGRATION_COMPLETED;
                }


                break;
            case 3:

                //controllo che le tabelle siano effettivamente presenti
                $success = $this->registerAdminUser() && $this->storeSettings() && $this->addInstallerRoles();

                if ($success) {
                    $messages[] = _ADMIN_STEP_SUCCESS;
                } else {
                    $messages[] = _ADMIN_STEP_ERROR;
                }

                break;

            case 4:
                //inserisco file di lingua
                $installedLanguages = $this->importLangs();

                if (count($installedLanguages)) {
                    $success = true;
                    foreach ($installedLanguages as $lang) {
                        $messages[] = $lang;
                    }
                    $messages[] = _LANG_STEP_SUCCESS;
                } else {
                    $success = false;
                    $messages[] = _LANG_STEP_ERROR;
                }
                break;

            case 5:

                //verifico smtp
                $success = $this->saveSmtpToDatabase();

                if ($success) {
                    $messages[] = _MAIL_STEP_SUCCESS;
                } else {
                    $messages[] = _MAIL_STEP_ERROR;
                }


                $lock = $this->handleErrors();
                //genero il file lock
                if ($lock) {
                    $this->generateLock();
                } else {
                    $type = 'lock';
                }

                $this->cleanSession();

                break;

            default:
                $success = false;
                $messages[] = _NOT_SUPPORTED_OPERATION;

                break;
        }

        //cancello il file tmp del config se tutto è andato in porto e cancello il resto

        return $this->setResponse($success, $messages, $type)->wrapResponse();
    }

    /**
     * Method to install database through Doctrine migration command
     *
     * @return string
     */
    public
    function migrateByCli($debug = false, $testLine = '')
    {
        $migrationFile = dirname(__DIR__, 2) . '/bin/doctrine-migrations';
        $mainPath = dirname(__DIR__, 2);

        $debugString = '';
        if ($debug) {
            $debugString = '2>&1';
        }

        //DO NOT UNCOOMENT - it could give error in environment without shell_exec enabled
        # return shell_exec("php " . $migrationFile . " migrate ". $testLine ." --no-interaction --configuration=" . $mainPath . "/migrations.yaml --db-configuration=" . $mainPath . "/migrations-db.php ".$debugString); //2>&1

        return true;
    }

    /**
     * Method to check the integrity of installation
     *
     *
     * @return bool
     */
    public
    static function checkDbInstallation(): bool
    {
        \FormaLms\db\DbConn::getInstance();

        return (bool)sql_query("SELECT * FROM `core_setting`");
    }

    /**
     * Method to check the integrity of installation
     *
     * @param bool $success flag to detect the output of a method
     * @param array $messages array of translated string messages
     * @param string $type type to identify response
     *
     * @return self
     */
    public
    function setResponse($success = false, $messages = [], $type = 'default'): self
    {
        $this->response['success'] = $success;
        $this->response['messages'] = $messages;
        $this->response['type'] = $type;

        return $this;
    }

    /**
     * Method to get error messages
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request derived from controller
     *
     * @return array
     */
    public
    function getErrorMessages($request): array
    {
        $params = $request->request->all();
        $messages = [];
        foreach ($params as $key => $value) {
            if ($value) {
                $messages[] = $this->errorLabels[$key];
            }
        }

        return $messages;
    }

    /**
     * Method to wrap response for user interface
     *
     *
     * @return string
     */

    public
    function wrapResponse()
    {
        return FormaLms\lib\Serializer\FormaSerializer::getInstance()->serialize($this->response, 'json');
    }


    /**
     * Method to save configuration file
     *
     * @return void
     */

    private
    function saveConfig()
    {
        // ----------- Generating config file -----------------------------
        $config = '';


        $config = $this->generateConfig();

        $save_fn = _base_ . '/config.php';
        $saved = false;
        touch($save_fn);
        if (is_writable($save_fn)) {
            $handle = fopen($save_fn, 'wb');
            if (fwrite($handle, $config)) {
                $saved = true;
            }
            fclose($handle);

            @chmod($save_fn, 0644);
        }


        $this->session->set('config_saved', $saved);
        $this->session->save();
    }

    /**
     * Method to save tmp configuration file
     *
     * @return void
     */

    public
    function saveTmpConfig()
    {
        $config = $this->generateConfig();

        $tempConfig = sys_get_temp_dir() . '/config.php';
        touch($tempConfig);
        if (is_writable($tempConfig)) {
            $handle = fopen($tempConfig, 'wb');
            if (fwrite($handle, $config)) {
            }
            fclose($handle);

            @chmod($tempConfig, 0644);
        }
    }

    /**
     * Method to delete tmp configuration file
     *
     * @return void
     */

    public
    function deleteTmpConfig()
    {
        @unlink(sys_get_temp_dir() . '/config.php');
    }

    /**
     * Method to generate config file from standard file
     *
     *
     * @return string
     */
    private
    function generateConfig()
    {
        $tpl_fn = _base_ . '/config_template.php';
        $values = $this->session->get('setValues');
        $config = '';

        if (file_exists($tpl_fn)) {
            $handle = fopen($tpl_fn, 'r');
            $config = fread($handle, filesize($tpl_fn));
            fclose($handle);
        }

        if ($values) {
            $config = str_replace('[%-DB_TYPE-%]', addslashes('mysqli'), $config);
            $config = str_replace('[%-DB_HOST-%]', addslashes($values['dbHost']), $config);
            $config = str_replace('[%-DB_USER-%]', addslashes($values['dbUser']), $config);
            $config = str_replace('[%-DB_PASS-%]', addslashes($values['dbPass']), $config);
            $config = str_replace('[%-DB_NAME-%]', addslashes($values['dbName']), $config);

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
        }


        return $config;
    }

    /**
     * Method to register seed admin user
     *
     * @return boolean
     */
    private
    function registerAdminUser()
    {
        // ----------- Registering admin user ---------------------------------

        $values = $this->session->get('setValues');

        $qtxt = "SELECT * FROM core_user WHERE userid='/" . $values['adminUser'] . "'";
        $q = sql_query($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) { // Did the user refreshed the page?
            // You never know..
            $qtxt = "UPDATE core_user SET firstname='" . $values['adminName'] . "',
                lastname='" . $values['adminLastname'] . "',
                pass='" . \FormaLms\lib\Forma::getAclManager()->encrypt($values['adminPassword']) . "' ";
            $qtxt .= "WHERE userid='/" . $values['adminUser'] . "'";
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
                '" . \FormaLms\lib\Forma::getAclManager()->encrypt($values['adminPassword']) . "', '" . $values['adminEmail'] . "')";
            $q = sql_query($qtxt);
        }

        return $q;
    }

    /**
     * Method to store settings in db
     *
     *
     * @return boolean
     */
    private
    function storeSettings()
    {

        $values = $this->session->get('setValues');
        \FormaLms\db\DbConn::getInstance(
            false,
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

        $qtxt = 'INSERT IGNORE INTO `core_setting` ';
        $qtxt .= ' (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) ';
        $qtxt .= ' VALUES ';
        $qtxt .= " ('core_version', '" . \FormaLms\lib\Version\VersionChecker::getFileVersion() . "', 'string', 255, '0', 1, 0, 1, 1, '') ";
        $q = sql_query($qtxt);

        return $q;
    }

    /**
     * Method to install default roles for admin
     *
     *
     * @return boolean
     */
    private
    function addInstallerRoles()
    {
        require_once _lib_ . '/System/lib.role.php';

        $godadmin = getGroupIdst('/framework/level/godadmin');
        $oc0 = getGroupIdst('/oc_0');

        $fn = _lib_ . '/System/role/rolelist_godadmin.txt';
        $roles = explode("\n", file_get_contents($fn));
        addRoles($roles, $godadmin);

        $fn = _lib_ . '/System/role/rolelist_oc0.txt';
        $roles = explode("\n", file_get_contents($fn));
        addRoles($roles, $oc0);

        return true;
    }

    /**
     * Method to import languages file
     *
     *
     * @return array
     */
    private
    function importLangs($langs = []): array
    {
        $langAdm = new LangAdm();
        $langsToInstall = count($langs) ? $langs : $this->session->get('setLangs');
        $installedLanguages = [];
        if (!count($langs)) {
            $langsToInstall[] = Lang::getSelLang();
        }

        foreach ($langsToInstall as $lang) {
            $fn = _base_ . '/xml_language/lang[' . $lang . '].xml';

            if (file_exists($fn)) {
                $installedLanguages[] = $lang;
                $langAdm->importTranslation($fn, true, false);
            }
        }

        return $installedLanguages;
    }

    /**
     * Method to import languages file
     *
     *
     * @return array
     */
    private
    function getInstalledLanguages(): array
    {
        $langAdm = new LangAdm();

        return $langAdm->getLangCodeList();
    }


    /**
     * Method to save smtp data in db
     *
     *
     * @return boolean
     */
    private
    function saveSmtpToDatabase()
    {
        $result = true;

        $values = $this->session->get('setValues');
        if ($values['useSmtpDatabase'] == 'on') {
            \FormaLms\db\DbConn::getInstance(
                false,
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

            foreach ($mailConfigs['smtp'] as $type => $value) {
                $realValue = $values[$type] ?? $value;
                $queryInsert = 'INSERT INTO'
                    . ' %adm_mail_configs_fields (mailConfigId, type, value) VALUES ("' . $mailConfigId . '", "' . $type . '", "' . $realValue . '")';
                $result = sql_query($queryInsert);
            }
        }

        return $result;
    }

    /**
     * Method to save fields in session
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request derived from controller
     *
     *
     * @return self
     */
    public
    function saveFields($request)
    {
        $params = $request->request->all();

        foreach ($params as $key => $value) {
            if ('lang_install' === $key) {
                foreach ($value as $lang) {
                    $this->setLangs($lang);
                }
            } else {
                $this->saveValue($key, $value);
            }
        }

        return $this->setResponse(true, [])->wrapResponse();
    }


    /**
     * Method to install migration table if database already ecists
     *
     *
     * @return boolean
     */
    public
    function installMigrationsTable()
    {

        $migrationSettings = FormaLms\lib\Database\FormaMigrator::getInstance()->getMigrationTableSettings();
        $connection = \FormaLms\db\DbConn::getInstance();
        $createQuery = "CREATE TABLE IF NOT EXISTS " . $migrationSettings->getTableName() . "  ("
            . $migrationSettings->getVersionColumnName() . " varchar(" . $migrationSettings->getVersionColumnLength() . ") CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,"
            . $migrationSettings->getExecutedAtColumnName() . " datetime(0) NULL DEFAULT NULL,"
            . $migrationSettings->getExecutionTimeColumnName() . " int(11) NULL DEFAULT NULL,
            PRIMARY KEY (" . $migrationSettings->getVersionColumnName() . ") USING BTREE
          ) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic";

        $creationTable = sql_query($createQuery);

        if ($creationTable) {
            sql_query("INSERT IGNORE INTO `core_migration_versions`(`version`, `executed_at`, `execution_time`) VALUES ('Formalms\\\Migrations\\\Version20220815000001','" . (new Datetime())->format("Y-m-d H:i:s") . "', 1000)");
            sql_query("INSERT IGNORE INTO `core_migration_versions`(`version`, `executed_at`, `execution_time`) VALUES ('Formalms\\\Migrations\\\Version20220815000002','" . (new Datetime())->format("Y-m-d H:i:s") . "', 1000)");
            sql_query("INSERT IGNORE INTO `core_migration_versions`(`version`, `executed_at`, `execution_time`) VALUES ('Formalms\\\Migrations\\\Version20220815000003','" . (new Datetime())->format("Y-m-d H:i:s") . "', 1000)");
        }

        return $creationTable;
    }

    /**
     * Method to save file version in db
     *
     *
     * @return boolean
     */
    private
    function saveUpgradeVersion()
    {
        $qtxt = "UPDATE core_setting SET param_value='" . \FormaLms\lib\Version\VersionChecker::getFileVersion() . "' WHERE param_name='core_version'";
        return sql_query($qtxt);
    }

    /**
     * Method to set standard template after upgrade
     *
     *
     * @return boolean
     */
    private
    function setDefaultTemplate()
    {
        $qtxt = "UPDATE core_setting SET param_value='standard' WHERE param_name='defaultTemplate'";
        return sql_query($qtxt);
    }


    /**
     * Method to celan session after installation
     *
     *
     * @return boolean
     */
    private
    function cleanSession()
    {
        $this->session->set('setValues', []);
        $this->session->set('setLangs', []);
        $this->session->set('installErrors', []);
        $this->session->set('step', 0);
        $this->session->save();

        return true;
    }

    /**
     * Method to generate lock file
     *
     * @return bool
     */

    public
    function generateLock()
    {
        $response = false;
        // ----------- Generating lock file -----------------------------
        if (!file_exists(_base_ . '/forma.lock')) {
            $lockFile = _base_ . '/forma.lock';
            touch($lockFile);
            $response = true;
        }

        return $this->setResponse($response, [])->wrapResponse();
    }


    /**
     * Method to download config file
     *
     * @return void
     */
    public
    function downlodConfigFile()
    {
        $tmpName = sys_get_temp_dir() . '/config.php';


        header('Content-Description: File Transfer');
        header('Content-Type: text/php');
        header('Content-Disposition: attachment; filename=config.php');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($tmpName));

        ob_clean();
        flush();
        readfile($tmpName);
        die();

        //unlink($tmpName);
    }

    /**
     * Method to download lock file
     *
     * @return void
     */
    public
    function downloadLockFile()
    {
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=forma.lock');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($tmpName));

        ob_clean();
        flush();
        readfile($tmpName);
        unlink($tmpName);
        die();
    }

    /**
     * Method to download config file
     *
     * @return bool
     */
    private
    function handleErrors(): bool
    {
        $errors = $this->session->get('installErrors') ?? [];
        $result = true;
        if (count($errors)) {
            $result = false;
        }
        foreach ($errors as $error) {
            if ($error === 'database') {
                $this->testMigrate();
            }
        }

        return $result;
    }

    /**
     * Method to generate sql file in logs folder
     *
     * @param $params array of useful parameters
     *
     * @return self
     */
    public
    function testMigrate($params = [], $save = false)
    {
        if ($save) {
            $this->saveTmpConfig();
        }

        $migrator = FormaLms\lib\Database\FormaMigrator::getInstance();
        if (array_key_exists('upgrade', $params) && (int)$params['upgrade']) {
            $this->installMigrationsTable();
        }

        $resultMigration = $migrator->executeCommand('migrate', ['debug' => (bool)array_key_exists('debug', $params), 'test' => true]);
        $messages[] = 'CHECK: ' . $resultMigration['message'];
        return $this->setResponse(true, $messages)->wrapResponse();
    }

}
