<?php

define("_INSTALLER_TITLE", "forma.lms - Installation");
define("_NEXT", "Next step");
define("_BACK", "Back");
define("_LOADING", "Loading");
define("_TRY_AGAIN", "Try again");
//--------------------------------------
define("_INTRODUCTION", "Introduction");
define("_TITLE_STEP1", "Step 1: Select language");
define("_LANGUAGE", "Language");
define("_INSTALLER_INTRO_TEXT", "
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
	</ul>");
// ---------------------------------------
define("_WARNINGS","Warnings");
define("WARNING_PUB_ADMIN_DELETED","All public admins willl be deleted");
// ---------------------------------------
define("_TITLE_STEP2", "Step 2: System Check");
define("_SERVERINFO","Server information");
define("_SERVER_SOFTWARE","Server software : ");
define("_PHPVERSION","PHP Version : ");
define("_MYSQLCLIENT_VERSION","Mysql Client Version : ");
define("_LDAP","Ldap : ");
define("_FILEINFO","Fileinfo support ");
define("_ONLY_IF_YU_WANT_TO_USE_FILEINFO","Consider this warning only if you need to use fileinfo ");
define("_ONLY_IF_YU_WANT_TO_USE_IT","Consider this warning only if you need to use LDAP ");
define("_OPENSSL","Openssl : ");
define("_WARINNG_SOCIAL","Consider this warning only if you use social login");
define("_MBSTRING","Multibyte Support");
define("_PHP_TIMEZONE","Site Timezone");

define("_PHPINFO","PHP Information : ");
define("_MAGIC_QUOTES_GPC","magic_quotes_gpc : ");
define("_SAFEMODE","Safe mode : ");
define("_REGISTER_GLOBALS","register_global : ");
define("_ALLOW_URL_FOPEN","allow_url_fopen : ");
define("_ALLOW_URL_INCLUDE","allow_url_include : ");
define("_UPLOAD_MAX_FILESIZE","upload_max_filsize : ");
define("_POST_MAX_SIZE","post_max_size : ");
define("_MAX_EXECUTION_TIME","max_execution_time : ");
define("_ON","ON ");
define("_OFF","OFF ");

// -----------------------------------------
define("_TITLE_STEP3", "Step 3: License");
define("_AGREE_LICENSE", "I agree with the term of the license");
// -----------------------------------------
define("_TITLE_STEP4", "Step 4: Configuration");
define("_SITE_BASE_URL", "Base url of the website");
define("_DATABASE_INFO", "Database information");
define("_DB_TYPE", "Type");
define("_DB_HOST", "Address");
define("_DB_NAME", "Database name");
define("_DB_USERNAME", "Database user");
define("_DB_PASS", "Password");
define("_UPLOAD_METHOD", "Upload file method (suggested FTP, if you are on windows at home use HTTP");
define("_HTTP_UPLOAD", "Classic method (HTTP)");
define("_FTP_UPLOAD", "Upload files using FTP");
define("_FTP_INFO", "FTP access data");
define("_IF_FTP_SELECTED", "(If you have selected FTP as Upload method)");
define("_FTP_HOST", "Server address");
define("_FTP_PORT", "Port number (generally is correct)");
define("_FTP_USERNAME", "User name");
define("_FTP_PASS", "Password");
define("_FTP_CONFPASS", "Confirm password");
define("_FTP_PATH", "FTP path (is the root where are stored file, ex. /htdocs/ /mainfile_html/");
define("_CANT_CONNECT_WITH_DB", "Can't connect to DB, please check inserted data");
define("_DB_NOT_EMPTY", "The specified database is not empty");
define("_DB_NOT_UTF8", "The specified database is not utf8 charset");
define("_CANT_SELECT_DB", "Can't select DB, please check inserted data");
define("_CANT_CONNECT_WITH_FTP","Can't connect in ftp to the specified server, please check inserted parameters");
define("_SQL_STRICT_MODE_WARN", "You have MySQL <a href=\"http://dev.mysql.com/doc/en/server-sql-mode.html\" target=\"_blank\">strict mode</a> enabled; forma.lms doesn't support it, so please turn it off");
define("_SQL_STRICT_MODE", "MySQL <a href=\"http://dev.mysql.com/doc/en/server-sql-mode.html\" target=\"_blank\">strict mode</a>");
define("_DB_WILL_BE_CREATED", "Db will be created");
// -----------------------------------------
define("_TITLE_STEP5", "Step 5: Configuration");
define("_ADMIN_USER_INFO", "Information regarding the administrator user (first login)");
define("_ADMIN_USERNAME", "Username");
define("_ADMIN_FIRSTNAME", "Firstname");
define("_ADMIN_LASTNAME", "Lastname");
define("_ADMIN_PASS", "Password");
define("_ADMIN_CONFPASS", "Confirm password");
define("_ADMIN_EMAIL", "e-mail");
define("_LANG_TO_INSTALL", "Languages to install");
define("_ADMIN_USERID_REQ", "Username required");
define("_ADMIN_PASS_REQ", "Password required");
define("_ADMIN_PASS_DOESNT_MATCH", "Password does'nt match");
define("_NO_LANG_SELECTED", "No languages selected");

// -----------------------------------------
define("_TITLE_STEP6", "Step 6: Database data setup");
define("_DATABASE", "Database");
define("_DB_IMPORTING", "Importing database");
define("_LANGUAGES", "Languages");
// -----------------------------------------
define("_TITLE_STEP7", "Step 7: Configurazione SMTP");
define("_SMTP_INFO", "Sarà possibile impostare la configurazione dell'SMTP da backoffice o da config.");
define("_USE_SMTP_DATABASE", "Impostazioni SMTP su Database");
define("_USE_SMTP", "Usa SMTP");
define("_SMTP_HOST", "Host SMTP");
define("_SMTP_PORT", "Porta SMTP");
define("_SMTP_SECURE", "Tipo di sicurezza");
define("_SMTP_AUTO_TLS", "Impostazione Auto TLS SMTP");
define("_SMTP_USER", "User SMTP");
define("_SMTP_PWD", "Password SMTP");
define("_SMTP_DEBUG", "Debug SMTP");
define("_CANT_CONNECT_SMTP", "impossibile connettersi al server SMTP selezionato");
// -----------------------------------------
define("_TITLE_STEP8", "Step 8: Installation completed");
define("REMOVE_INSTALL_FOLDER", "It is suggested to remove the install folder, forma is vulnerable until it is reachable.");
define("_INSTALLATION_COMPLETED", "Installation has been completed");
define("_INSTALLATION_DETAILS", "Details");
define("_SITE_HOMEPAGE", "Home");
define("_REVEAL_PASSWORD", "Reveal password");
define("_COMMUNITY", "Community");
define("_COMMERCIAL_SERVICES", "Commercial Services");
define("_CONFIG_FILE_NOT_SAVED", "The installer was unable to save the config.php file, download it and overwrite it online.");
define("_DOWNLOAD_CONFIG", "Download config");
define("_CHECKED_DIRECTORIES","Some directory where files are stored does not exist or does not have correct permission");
define("_CHECKED_FILES","Certain files does not have adequate permission");
// -----------------------------------------
define("_UPGRADER_TITLE", "forma.lms - Upgrade");
define("_UPGRADE_CONFIG","Upgrading config.php file");
define("_UPG_CONFIG_OK","Config.php file updated successfully");
define("_UPG_CONFIG_NOT_CHANGED", "Config.php already updated");
define("_UPG_CONFIG_NOT_SAVED", "The update process for the config.php failed.");
define("_UPGRADING", "Upgrade in progress");
define("_UPGRADING_LANGUAGES", "Upgrade languages");
define("_UPGRADE_COMPLETE", "Upgrade completed");
define("_VERSION","forma.lms version");
define("_START","Start");
define("_END","Final");
define("_INVALID_CONFIG_FILE", "Invalid config.php file; please make sure it is from a release matching the version specified in the \"Start\" field");
define("_UPGRADE_NOT_NEEDED","You already have the latest version of forma.lms, no need to upgrade.");

define("_USEFUL_LINKS", "Useful links");

define("_COMMUNITY", "Community");
define("_COMMERCIAL_SERVICES", "Commercial services");
define("_DATABASE", "Database");
define("_MIME_CONTENT_TYPE", "mime_content_type() support");

define("_ASSESSMENT_FUNCTION_REMOVED", "Assessment function has been removed");
define("_LEARNING_NEWS_REMOVED", "Login News function and its data has been removed");

define("_SMTP_INFO", "Sarà possibile impostare la configurazione dell'SMTP da backoffice o da config.");
define("_USE_SMTP_DATABASE", "Impostazioni SMTP su Database");
define("_USE_SMTP", "Usa SMTP");
define("_SMTP_HOST", "Host SMTP");
define("_SMTP_PORT", "Porta SMTP");
define("_SMTP_SECURE", "Tipo di sicurezza");
define("_SMTP_USER", "User SMTP");
define("_SMTP_PWD", "Password SMTP");
define("_CANT_CONNECT_SMTP", "impossibile connettersi al server SMTP selezionato");