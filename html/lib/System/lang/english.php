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

define('_INSTALLER_TITLE', 'forma.lms - Installation');
define('_UPGRADER_TITLE', 'forma.lms - Upgrade');
define('_INSTALLED_VERSION', 'Installed Version');
define('_DETECTED_VERSION', 'Detected Version');
define('_TEST_INSTALL', 'Generate Debug Query');
define('_TEST_UPGRADE', 'Test Upgrade');
define('_GENERATE_LOCK', 'Generate lock file');
define('_OK_UPGRADE', 'You can proceed with Upgrade');
define('_NO_UPGRADE', 'No upgrade needed');
define('_NO_DOWNGRADE', 'Downgrade is not supported');
define('_BLOCK_UPGRADE', 'Impossibile to proceed, read the message in the information block below');
define('_NOT_SUPPORTED_VERSION', 'You don\'t have the minimum version required for this upgrade please download the right package from <a href="https://www.formalms.org/download.html" target="_blank">here</a>');
define('_NEXT', 'Next step');
define('_BACK', 'Back');
define('_LOADING', 'Loading');
define('_TRY_AGAIN', 'Try again');
define('_CANCEL', 'Cancel');
define('_CURRENT', 'current step:');
define('_PAGINATION', 'Pagination');
define('_FINISH', 'Install');
define('_UPGRADE_FINISH', 'Upgrade');
//--------------------------------------
define('_INTRODUCTION', 'Introduction');
define('_TITLE_STEP1', 'Getting Started');
define('_LANGUAGE', 'Language');
define('_INSTALLER_INTRO_TEXT', '
forma.lms is a free open source Learning Management System mantained by an italian group of companies, already used by hundreds of companies and universities all over the world.
	<p><b>Key Features</b></p>
	<ul>
		<li>Scorm 1.2 and 2004 support</li>
		<li>Configurable to fit several training models (self-training, blended learning, collaborative learning, social learning)</li>
		<li>Authoring Tool that allows to manage Tests, File download of any format, Web pages, Faq, Glossaries, Links collections</li>
		<li>Collaboration features like <b>Forum</b>, <b>Wiki</b>, <b>Chat</b>, <b>Project Management</b>, <b>Repository</b></li>
		<li>Talent and Competences management, gap analysis and personal development plan</li>
		<li>Pdf certificates generation and printing</li>
		<li>Support for third party interface with human resources management software (<b>SAP</b>, <b>Cezanne</b>, <b>Lotus Notes</b>, ...) and other companies services (<b>LDAP</b>, <b>Active Directory</b>, <b>CRM</b>, <b>Erp</b> and other custom made solution)</li>
		<li>Social features support like <b>Google Apps</b>, <b>Facebook</b>, <b>Twitter</b> e <b>Linkedin</b></li>
		<li>Fully customizable Reporting system and business intelligence</li>
		<li>Dedicated sub-administrators features, area and country manager features</li>
		<li>Multi-language support and LTR(left-to-right) and RTL (right-to-left) support. 25 languages supported</li>
		<li>Mobile devices support</li>
	</ul>');
// ---------------------------------------
define('_WARNINGS', 'Warnings');
define('WARNING_PUB_ADMIN_DELETED', 'All public admins willl be deleted');
// ---------------------------------------
define('_TITLE_STEP2', 'Database and FTP Settings');
define('_SERVERINFO', 'Server information');
define('_SERVER_SOFTWARE', 'Server software : ');
define('_PHPVERSION', 'PHP Version : ');
define('_PHPCLIVERSION', 'PHP Cli Version : ');
define('_PHP_NOT_FOUND', 'Please install cmd php as alias in your system');
define('_LOG_SQL', 'Check .sql file in your files/logs folder');
define('_MYSQLCLIENT_VERSION', 'MySQL/MariaDB Client Version : ');
define('_MYSQLSERVER_VERSION', 'MySQL/MariaDB Server Version : ');
define('_LDAP', 'Ldap : ');
define('_FILEINFO', 'Fileinfo support ');
define('_ONLY_IF_YU_WANT_TO_USE_FILEINFO', 'Consider this warning only if you need to use fileinfo ');
define('_ONLY_IF_YU_WANT_TO_USE_IT', 'Consider this warning only if you need to use LDAP ');
define('_OPENSSL', 'Openssl : ');
define('_DISABLE_FUNCTIONS', 'Disable shell_exec : ');
define('_WARINNG_SOCIAL', 'Consider this warning only if you use social login');
define('_MBSTRING', 'Multibyte Support');
define('_PHP_TIMEZONE', 'Site Timezone');

define('_PHPINFO', 'PHP Information : ');
define('_MAGIC_QUOTES_GPC', 'magic_quotes_gpc : ');
define('_SAFEMODE', 'Safe mode : ');
define('_REGISTER_GLOBALS', 'register_global : ');
define('_ALLOW_URL_FOPEN', 'allow_url_fopen : ');
define('_ALLOW_URL_INCLUDE', 'allow_url_include : ');
define('_UPLOAD_MAX_FILESIZE', 'upload_max_filsize : ');
define('_POST_MAX_SIZE', 'post_max_size : ');
define('_MAX_EXECUTION_TIME', 'max_execution_time : ');
define('_ON', 'ON ');
define('_OFF', 'OFF ');
define('_YES', 'YES');
define('_NO', 'NO');


define('_STATUSCHECK_TITLE', 'Ooops...Something Went wrong!');
define('_STATUSCHECK_DESCRIPTION', 'Some system requirements are missing:');
// -----------------------------------------
define('_TITLE_STEP3', 'Admin and Language Settings');
define('_AGREE_LICENSE', 'I agree with the term of the license');
// -----------------------------------------
define('_TITLE_STEP4', 'SMTP Configuration');
define('_SITE_BASE_URL', 'Base url of the website');
define('_DATABASE_INFO', 'Database information');
define('_DB_TYPE', 'Type');
define('_DB_HOST', 'Address');
define('_DB_NAME', 'Database name');
define('_DB_USERNAME', 'Database user');
define('_MIGRATION_COMPLETED', 'Migrations completed successfully');
define('_DB_PASS', 'Password');
define('_UPLOAD_METHOD', 'Upload file method (suggested FTP, if you are on windows at home use HTTP');
define('_HTTP_UPLOAD', 'Classic method (HTTP)');
define('_FTP_UPLOAD', 'Upload files using FTP');
define('_FTP_INFO', 'FTP access data');
define('_IF_FTP_SELECTED', '(If you have selected FTP as Upload method)');
define('_FTP_HOST', 'Server address');
define('_FTP_PORT', 'Port number (generally is correct)');
define('_FTP_USERNAME', 'User name');
define('_FTP_PASS', 'Password');
define('_FTP_CONFPASS', 'Confirm password');
define('_FTP_PATH', 'FTP path (is the root where are stored file, ex. /htdocs/ /mainfile_html/');
define('_CANT_CONNECT_WITH_DB', "Can't connect to DB, please check inserted data");
define('_DB_NOT_EMPTY', 'The specified database is not empty');
define('_DB_NOT_UTF8', 'The specified database is not utf8 charset');
define('_CANT_SELECT_DB', "Can't select DB, please check inserted data");
define('_UNSUITABLE_SQL_VERSION', "Sql Server Version unsuitable to requirements");
define('_CANT_CONNECT_WITH_FTP', "Can't connect in ftp to the specified server, please check inserted parameters");
define('_SQL_STRICT_MODE_WARN', "You have MySQL <a href=\"http://dev.mysql.com/doc/en/server-sql-mode.html\" target=\"_blank\">strict mode</a> enabled; forma.lms doesn't support it, so please turn it off");
define('_SQL_STRICT_MODE', 'MySQL <a href="http://dev.mysql.com/doc/en/server-sql-mode.html" target="_blank">strict mode</a>');
define('_DB_WILL_BE_CREATED', 'Db will be created');
// -----------------------------------------
define('_TITLE_STEP5', 'Finalization');
define('_ADMIN_USER_INFO', 'Information regarding the administrator user (first login)');
define('_ADMIN_USERNAME', 'Username');
define('_ADMIN_FIRSTNAME', 'Firstname');
define('_ADMIN_LASTNAME', 'Lastname');
define('_ADMIN_PASS', 'Password');
define('_ADMIN_CONFPASS', 'Confirm password');
define('_ADMIN_EMAIL', 'e-mail');
define('_LANG_TO_INSTALL', 'Languages to install');
define('_ADMIN_USERID_REQ', 'Username required');
define('_ADMIN_PASS_REQ', 'Password required');
define('_ADMIN_PASS_DOESNT_MATCH', "Password does'nt match");
define('_NO_LANG_SELECTED', 'No languages selected');

// -----------------------------------------

define('_DATABASE', 'Database');
define('_DB_IMPORTING', 'Importing database');
define('_LANGUAGES', 'Languages');
// -----------------------------------------

define('_SMTP_INFO', "Sarà possibile impostare la configurazione dell'SMTP da backoffice o da config.");
define('_USE_SMTP_DATABASE', 'Impostazioni SMTP su Database');
define('_USE_SMTP', 'Usa SMTP');
define('_SMTP_HOST', 'Host SMTP');
define('_SMTP_PORT', 'Porta SMTP');
define('_SMTP_SECURE', 'Tipo di sicurezza');
define('_SMTP_AUTO_TLS', 'Impostazione Auto TLS SMTP');
define('_SMTP_USER', 'User SMTP');
define('_SMTP_PWD', 'Password SMTP');
define('_SMTP_DEBUG', 'Debug SMTP');
define('_SMTP_SENDERMAIL', 'Mail notification Sender');
define('_SMTP_SENDERNAME', 'Mail notification Name');
define('_SMTP_SENDERMAILSYS', 'System mail Sender');
define('_SMTP_SENDERNAMESYS', 'System mail Name');
define('_SMTP_SENDERCCMAIL', 'CC mails');
define('_SMTP_SENDERCCNMAILS', 'CCn mails');
define('_SMTP_HDESKMAIL', 'Helper Desk mail');
define('_SMTP_HDESKSUBJECT', 'Helper Desk subject');
define('_SMTP_HDESKNAME', 'Helper Desk Name');
define('_SMTP_REPLYTONAME', 'Reply to Name');
define('_SMTP_REPLYTOMAIL', 'Reply to Mail');
define('_SMTP_ACTIVE', 'Active');
define('_CANT_CONNECT_SMTP', 'impossibile connettersi al server SMTP selezionato');
// -----------------------------------------
define('_TITLE_STEP8', 'Installation completed');
define('_FINALIZE_INSTALL', 'Confirm installation data');
define('REMOVE_INSTALL_FOLDER', 'It is suggested to remove the install folder, forma is vulnerable until it is reachable.');
define('_INSTALLATION_COMPLETED', 'Installation has been completed');
define('_INSTALLATION_ERROR', 'Installation has failed');
define('_DOWNLOAD_LOCK', 'Download lock file');
define('_DOWNLOAD_CONFIG', 'Download Config file');
define('_INSTALLATION_DETAILS', 'Details');
define('_SITE_HOMEPAGE', 'Home');
define('_REVEAL_PASSWORD', 'Reveal password');
define('_COMMUNITY', 'Community');
define('_COMMERCIAL_SERVICES', 'Commercial Services');
define('_CONFIG_FILE_NOT_SAVED', 'The installer was unable to save the config.php file, download it and overwrite it online.');
define('_CHECKED_DIRECTORIES', 'Some directory where files are stored does not exist or does not have correct permission');
define('_CHECKED_FILES', 'Certain files does not have adequate permission');
// -----------------------------------------
define('_UPGRADE_CONFIG', 'Upgrading config.php file');
define('_UPG_CONFIG_OK', 'Config.php file updated successfully');
define('_UPG_CONFIG_NOT_CHANGED', 'Config.php already updated');
define('_UPG_CONFIG_NOT_SAVED', 'The update process for the config.php failed.');
define('_UPGRADING', 'Upgrade in progress');
define('_UPGRADING_LANGUAGES', 'Upgrade languages');
define('_UPGRADE_COMPLETE', 'Upgrade completed');
define('_VERSION', 'forma.lms version');
define('_START', 'Start');
define('_END', 'Final');
define('_INVALID_CONFIG_FILE', 'Invalid config.php file; please make sure it is from a release matching the version specified in the "Start" field');
define('_UPGRADE_NOT_NEEDED', 'You already have the latest version of forma.lms, no need to upgrade.');
define('_UPGRADE_NOT_NEEDED_FILE_IS_LATER', 'Your forma.lms version seems to be later than tha one you want to upgrade,so no need to upgrade.');

define('_USEFUL_LINKS', 'Useful links');

define('_MIME_CONTENT_TYPE', 'mime_content_type() support');

define('_ASSESSMENT_FUNCTION_REMOVED', 'Assessment function has been removed');
define('_LEARNING_NEWS_REMOVED', 'Login News function and its data has been removed');



/************************VALIDATION*************/
define('_MISSING_LICENSE_CHECK', 'License terms must be checked');
define('_UNSUITABLE_REQUIREMENTS', 'Minimal requirements are not satisfied');
define('_MISSING_FIELD', 'Missing Field');
define('_FTP_NOT_SUPPORTED', 'FTP Methods are not supported');
define('_FTP_CONNECT_FAIL', 'FTP connection failed');
define('_FTP_LOGIN_FAIL', 'FTP login failed');
define('_NOT_VALID_EMAIL', 'Email not valid');
define('_NOT_MATCHING_PASSWORD', 'Not matching password');
define('_SMTP_FAILED', 'Smtp connection failed!');

/****************************FINALIZATION */
define('_CONFIG_STEP_SUCCESS', 'Config file generated successfully');
define('_CONFIG_STEP_ERROR', 'Config file generated with errors');
define('_ADMIN_STEP_SUCCESS', 'Admin user generated successfully');
define('_ADMIN_STEP_ERROR', 'Admin user generated with errors');
define('_LANG_STEP_SUCCESS', 'Languages imported successfully');
define('_LANG_STEP_ERROR', 'Languages imported with errors');
define('_MAIL_STEP_SUCCESS', 'Mail settings saved successfully');
define('_MAIL_STEP_ERROR', 'Mail settings saved with errors');
define('_NOT_SUPPORTED_OPERATION', 'Operation not supported');
define('_RESET_INSTALL', 'Restart Clean Installation');

define('_VERSION_STEP_OK', 'Version check OK');
define('_VERSION_STEP_ERROR', 'Version check Error');
define('_UPGRADE_STEP_SUCCESS', 'Core upgrade OK');
define('_UPGRADE_STEP_ERROR', 'Core upgrade Error');
define('_TEMPLATE_STEP_SUCCESS', 'Template reset OK');
define('_TEMPLATE_STEP_ERROR', 'Template reset Error');
define('_CLEARTWIG_CACHE_OK', 'Twig Cache Cleared');


define('_DATABASE_OK', 'Database connected correctly');
define('_CONFIG_OK', 'Configuration file found');
define('_PHPVERSION_OK', 'Suitable PHP Version');
define('_SESSION_OK', 'Session correctly set for http and https');

define('_INSTALL_EXPLAIN', 'You are going to install Forma LMS in your system, clicking on the button above a colored bar will show you the progress of the installation, in case of failure you can check the files/logs to find a SQL file which contains all commands to launch after the failure, to debug your environment, or you can click on Generate Debug Query to execute the routine');
define('_UPGRADE_EXPLAIN', 'You are going to upgrade Forma LMS in your system, please it\'s recommended to save a backup of your database before any operation, clicking on the button above a colored bar will show you the progress of the upgrade, in case of failure you can check the files/logs to find a SQL file which contains all commands to launch after the failure, to debug your environment, or you can click on Generate Debug Query to execute the routine');
