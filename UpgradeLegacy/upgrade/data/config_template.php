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
 * Db info
 * -------------------------------------------------------------------------
 * db type, mysqli is supported
 * db server address
 * db user name
 * db user password
 * db name
 * charset to use in the db connection
 */
$cfg['db_type'] = 'mysql';
$cfg['db_host'] = '[%-DB_HOST-%]';
$cfg['db_user'] = '[%-DB_USER-%]';
$cfg['db_pass'] = '[%-DB_PASS-%]';
$cfg['db_name'] = '[%-DB_NAME-%]';
$cfg['db_charset'] = 'utf8';

/*
 * Tables prefix
 * -------------------------------------------------------------------------
 * prefix for the core tables
 * prefix for the lms tables
 * prefix for the cms tables
 * prefix for the scs tables
 * prefix for the ecom tables
 * prefix for the ecom tables
 */
$cfg['prefix_fw'] = 'core';
$cfg['prefix_lms'] = 'learning';
$cfg['prefix_cms'] = 'cms';
$cfg['prefix_scs'] = 'conference';
$cfg['prefix_ecom'] = 'ecom';
$cfg['prefix_crm'] = 'crm';

/*
 * File upload
 * -------------------------------------------------------------------------
 * upload type (fs|ftp)
 * ftphost: the ftp hostname
 * ftpport: the ftp port
 * ftpuser: the ftp username
 * ftppass: the ftp password
 * ftppath: the ftp path from the user main home dir to the docebo root folder
 */
$cfg['uploadType'] = '[%-UPLOAD_METHOD-%]';

$cfg['ftphost'] = '[%-FTP_HOST-%]';
$cfg['ftpport'] = '[%-FTP_PORT-%]';
$cfg['ftpuser'] = '[%-FTP_USER-%]';
$cfg['ftppass'] = '[%-FTP_PASS-%]';
$cfg['ftppath'] = '[%-FTP_PATH-%]';

/*
 * External smtp config
 * -------------------------------------------------------------------------
 */
$cfg['use_smtp_database'] = 'on';
$cfg['use_smtp'] = '';
$cfg['smtp_host'] = '';				// Options: hostname;hostname:port;...
//$cfg['smtp_port'] ='';			// Options: '' (default port) , port number
//$cfg['smtp_secure'] = '';			// Options: "", "ssl", "tls"
$cfg['smtp_user'] = '';
$cfg['smtp_pwd'] = '';

/*
 * Other params
 * -------------------------------------------------------------------------
 * timezone     = default site timezone , if not specified get default from php.ini date.timezone
 *                for valid timezone see http://www.php.net/manual/en/timezones.php
 * set_mysql_tz = set mysql timezone same as php timezone ,  valid value
 *                true = set ,  false = (default) not set
 * keepalive    = set TMO for keepalive scorm tracking. must be < session lifetime, 0 to disable keepalive
 *                default session lifetime - 15 sec
 * enable_customscripts = enable custom scripts processing;  accepted vaule: true , false ; default false
*
 */

//$cfg['timezone'] = 'Europe/Rome';		// define if different from php.ini setting
//$cfg['set_mysql_tz'] = false;			// set mysql timezone same as php timezone , default false

//$cfg['keepalivetmo'] = '';			// timeout for keepalive, must be < session lifetime, 0 to disable keepalive

//$cfg['enable_customscripts'] = false;	// enable custom scripts processing;  accepted value: true , false ; default false

/*
 * Template engine custom param
 * -------------------------------------------------------------------------
 * add all template_engine enabled (if exists)
 * parameter :  array value=file extension
 * template_engine available: twig
 *
 * If not defined no alternate template engine. Twig is enabled on default
 */
$cfg['template_engine']['twig'] = ['ext' => '.html.twig'];
$cfg['twig_debug'] = false;

/*
 * Session custom param
 * -------------------------------------------------------------------------
 * debug is enabled ?
 * session must survive at least X seconds
 * session save_path if specified will be used instead of the defaul one
 */
$cfg['do_debug'] = false;
//$cfg['debug_level'] 			= 'all' // error,warning,notice,deprecated else all
//$cfg['enable_log']            = true;
//$cfg['log_level']             = Monolog\Logger::DEBUG; //
//$cfg['log_path']              = '/app/logger.log';
$cfg['demo_mode'] = false;

//$cfg['session']['handler'] = \FormaLms\lib\Session\SessionManager::FILESYSTEM; //filesystem | memcached | redis | pdo | mongodb
//$cfg['session']['url'] = ''; // dsn pattern url to session server
//$cfg['session']['timeout'] = (float)'2.5';
//$cfg['session']['lifetime'] = (int) 3600; //session lifetime
//$cfg['session']['prefix'] = 'core_sessions'; //session prefix or session table name in case of pdo
//$cfg['session']['name'] = $cfg['db_name']; //db name
//$cfg['session']['port'] = 3306;  // process port session handler
//$cfg['session']['host'] = $cfg['db_host']; //host
//$cfg['session']['authentication'] = true; //true | false
//$cfg['session']['user'] = $cfg['db_user']; // authentication user session handler
//$cfg['session']['pass'] = $cfg['db_pass']; // authentication psw session handler
//$cfg['session']['options'] = []; // other options key value array to pass based on selected handler

/*
 * Technical preferences
 * -------------------------------------------------------------------------
 * filter_tool: the class for input filtering that you want to use
 * mail_br: used in mail composition (no longer needed?)
 */
$cfg['filter_tool'] = 'htmlpurifier';
$cfg['mail_br'] = "\r\n";

/*
 * Certificate Encryption
 * -------------------------------------------------------------------------
 * certificate_encryption: boolean to set whether to enable or not the pdf encryption of certificates (default is TRUE)
 * certificate_password: password to use when encryption is enabled. It must be exactly 32 chars! (default is a random generated one)
 */
//$cfg['certificate_encryption'] = true;
//$cfg['certificate_password']  = "12345678901234567890123456789012";
