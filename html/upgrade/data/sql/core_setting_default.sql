-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Versione MySQL: 5.1.41
-- Versione PHP: 5.3.1


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `formalsm`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `core_setting`
--

CREATE TABLE IF NOT EXISTS `core_setting_default` (
  `param_name` varchar(255) NOT NULL DEFAULT '',
  `param_value` text NOT NULL,
  `value_type` varchar(255) NOT NULL DEFAULT 'string',
  `max_size` int(3) NOT NULL DEFAULT '255',
  `pack` varchar(255) NOT NULL DEFAULT 'main',
  `regroup` int(5) NOT NULL DEFAULT '0',
  `sequence` int(5) NOT NULL DEFAULT '0',
  `param_load` tinyint(1) NOT NULL DEFAULT '1',
  `hide_in_modify` tinyint(1) NOT NULL DEFAULT '0',
  `extra_info` text NOT NULL,
  PRIMARY KEY (`param_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_setting`
--

INSERT INTO `core_setting_default` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
('accessibility', 'off', 'enum', 255, '0', 8, 5, 1, 0, ''),
('code_teleskill', '', 'string', 255, 'teleskill', 6, 3, 1, 0, ''),
('common_admin_session', 'on', 'enum', 3, 'security', 8, 24, 1, 0, ''),
('conference_creation_limit_per_user', '99999999999', 'string', 255, '0', 6, 0, 1, 0, ''),
('core_version', '1.0', 'string', 255, '0', 1, 0, 1, 1, ''),
('course_quota', '0', 'string', 255, '0', 4, 5, 1, 0, ''),
('currency_symbol', '&euro;', 'string', 10, '0', 5, 2, 1, 0, ''),
('customer_help_email', '', 'string', 255, '0', 3, 19, 1, 0, ''),
('customer_help_subj_pfx', '', 'string', 255, '0', 3, 20, 1, 0, ''),
('defaultTemplate', 'standard', 'template', 255, '0', 1, 4, 1, 0, ''),
('default_language', 'english', 'language', 255, '0', 1, 3, 1, 0, ''),
('dimdim_max_mikes', '2', 'string', 255, 'dimdim', 6, 7, 1, 0, ''),
('dimdim_max_participant', '300', 'string', 255, 'dimdim', 6, 6, 1, 0, ''),
('dimdim_max_room', '99999999999', 'string', 255, 'dimdim', 6, 5, 1, 0, ''),
('dimdim_password', '', 'password', 255, 'dimdim', 6, 2, 1, 0, ''),
('dimdim_port', '80', 'string', 255, 'dimdim', 6, 2, 1, 0, ''),
('dimdim_server', 'webmeeting.dimdim.com', 'string', 255, 'dimdim', 6, 1, 1, 0, ''),
('dimdim_user', '', 'string', 255, 'dimdim', 6, 2, 1, 0, ''),
('do_debug', 'off', 'enum', 3, 'debug', 8, 8, 1, 0, ''),
('first_catalogue', 'off', 'enum', 3, '0', 4, 2, 1, 0, ''),
('google_stat_code', '', 'textarea', 65535, '0', 10, 2, 1, 0, ''),
('google_stat_in_lms', '0', 'check', 1, '0', 10, 1, 1, 0, ''),
('hour_request_limit', '48', 'int', 2, 'register', 3, 13, 0, 0, ''),
('hteditor', 'tinymce', 'hteditor', 255, '0', 1, 7, 1, 0, ''),
('htmledit_image_admin', '1', 'check', 255, '0', 8, 1, 1, 0, ''),
('htmledit_image_godadmin', '1', 'check', 255, '0', 8, 0, 1, 0, ''),
('htmledit_image_user', '1', 'check', 255, '0', 8, 2, 1, 0, ''),
('kb_filter_by_user_access', 'on', 'enum', 3, 'main', 4, 10, 1, 0, ''),
('kb_show_uncategorized', 'on', 'enum', 3, 'main', 4, 11, 1, 0, ''),
('lang_check', 'off', 'enum', 3, 'debug', 8, 7, 1, 0, ''),
('lastfirst_mandatory', 'off', 'enum', 3, 'register', 3, 14, 2, 0, ''),
('ldap_port', '389', 'string', 5, '0', 7, 3, 1, 0, ''),
('ldap_server', '192.168.0.1', 'string', 255, '0', 7, 2, 1, 0, ''),
('ldap_used', 'off', 'enum', 3, '0', 7, 1, 1, 0, ''),
('ldap_user_string', '$user@domain2.domain1', 'string', 255, '0', 7, 4, 1, 0, ''),
('mail_sender', 'sample@localhost.localdomain', 'string', 255, 'register', 3, 12, 0, 0, ''),
('mandatory_code', 'off', 'enum', 3, 'register', 3, 18, 1, 0, ''),
('max_log_attempt', '0', 'int', 3, '0', 3, 4, 0, 0, ''),
('nl_sendpause', '20', 'int', 3, 'newsletter', 8, 10, 1, 0, ''),
('nl_sendpercycle', '200', 'int', 4, 'newsletter', 8, 9, 1, 0, ''),
('no_answer_in_poll', 'off', 'enum', 3, '0', 4, 7, 1, 0, ''),
('no_answer_in_test', 'off', 'enum', 3, '0', 4, 6, 1, 0, ''),
('on_catalogue_empty', 'on', 'enum', 3, '0', 4, 3, 1, 0, ''),
('org_name_teleskill', '', 'string', 255, 'teleskill', 6, 4, 1, 0, ''),
('page_title', 'Forma E-learning', 'string', 255, '0', 1, 1, 1, 0, ''),
('pass_alfanumeric', 'off', 'enum', 3, 'password', 3, 6, 0, 0, ''),
('pass_change_first_login', 'off', 'enum', 3, 'password', 3, 8, 1, 0, ''),
('pass_max_time_valid', '0', 'int', 4, 'password', 3, 9, 1, 0, ''),
('pass_min_char', '5', 'int', 2, 'password', 3, 7, 0, 0, ''),
('pathchat', 'chat/', 'string', 255, 'path', 8, 21, 1, 0, ''),
('pathcourse', 'course/', 'string', 255, 'path', 8, 11, 1, 0, ''),
('pathfield', 'field/', 'string', 255, 'path', 8, 12, 1, 0, ''),
('pathforum', 'forum/', 'string', 255, 'path', 8, 14, 1, 0, ''),
('pathlesson', 'item/', 'string', 255, 'path', 8, 15, 1, 0, ''),
('pathmessage', 'message/', 'string', 255, 'path', 8, 16, 1, 0, ''),
('pathphoto', 'photo/', 'string', 255, 'path', 8, 13, 1, 0, ''),
('pathprj', 'project/', 'string', 255, 'path', 8, 20, 1, 1, ''),
('pathscorm', 'scorm/', 'string', 255, 'path', 8, 17, 1, 0, ''),
('pathsponsor', 'sponsor/', 'string', 255, 'path', 8, 18, 1, 0, ''),
('pathtest', 'test/', 'string', 255, 'path', 8, 19, 1, 0, ''),
('paypal_currency', 'EUR', 'string', 255, '0', 5, 1, 1, 0, ''),
('paypal_mail', '', 'string', 255, '0', 5, 0, 1, 0, ''),
('paypal_sandbox', 'on', 'enum', 3, '0', 5, 3, 1, 0, ''),
('privacy_policy', 'on', 'enum', 3, 'register', 3, 15, 0, 0, ''),
('profile_only_pwd', 'on', 'enum', 3, '0', 3, 1, 1, 0, ''),
('register_deleted_user', 'off', 'enum', 3, '0', 3, 3, 1, 0, ''),
('register_type', 'admin', 'register_type', 10, 'register', 3, 11, 0, 0, ''),
('registration_code_type', '0', 'registration_code_type', 3, 'register', 3, 17, 1, 0, ''),
('request_mandatory_fields_compilation', 'off', 'enum', 3, '0', 3, 2, 1, 0, ''),
('rest_auth_code', '', 'string', 255, 'api', 9, 4, 1, 0, ''),
('rest_auth_lifetime', '60', 'int', 3, 'api', 9, 5, 1, 0, ''),
('rest_auth_method', '1', 'rest_auth_sel_method', 3, 'api', 9, 3, 1, 0, ''),
('rest_auth_update', 'off', 'enum', 3, 'api', 9, 6, 1, 0, ''),
('save_log_attempt', 'no', 'save_log_attempt', 255, '0', 3, 5, 0, 0, ''),
('sco_direct_play', 'on', 'enum', 3, '0', 8, 3, 1, 0, ''),
('sender_event', 'sample@localhost.localdomain', 'string', 255, '0', 1, 5, 1, 0, ''),
('send_cc_for_system_emails', '', 'string', 255, '0', 8, 4, 1, 0, ''),
('session_ip_control', 'on', 'enum', 3, 'security', 8, 22, 1, 0, ''),
('sms_cell_num_field', '1', 'field_select', 5, '0', 11, 6, 1, 0, ''),
('sms_credit', '0', 'string', 20, '0', 1, 0, 1, 1, ''),
('sms_gateway', 'smsmarket', 'string', 50, '0', 11, 0, 1, 1, ''),
('sms_gateway_host', '193.254.241.47', 'string', 15, '0', 11, 8, 1, 0, ''),
('sms_gateway_id', '3', 'sel_sms_gateway', 1, '0', 11, 7, 1, 0, ''),
('sms_gateway_pass', '', 'string', 255, '0', 11, 5, 1, 0, ''),
('sms_gateway_port', '26', 'int', 5, '0', 11, 9, 1, 0, ''),
('sms_gateway_user', '', 'string', 50, '0', 11, 4, 1, 0, ''),
('sms_international_prefix', '+39', 'string', 3, '0', 11, 1, 1, 0, ''),
('sms_sent_from', '0', 'string', 25, '0', 11, 2, 1, 0, ''),
('social_fb_active', 'off', 'enum', 3, 'main', 12, 0, 1, 0, ''),
('social_fb_api', '', 'string', 255, 'main', 12, 1, 1, 0, ''),
('social_fb_secret', '', 'string', 255, 'main', 12, 2, 1, 0, ''),
('social_google_active', 'off', 'enum', 3, 'main', 12, 9, 1, 0, ''),
('social_linkedin_access', '', 'string', 255, 'main', 12, 7, 1, 0, ''),
('social_linkedin_active', 'off', 'enum', 3, 'main', 12, 6, 1, 0, ''),
('social_linkedin_secret', '', 'string', 255, 'main', 12, 8, 1, 0, ''),
('social_twitter_active', 'off', 'enum', 3, 'main', 12, 3, 1, 0, ''),
('social_twitter_consumer', '', 'string', 255, 'main', 12, 4, 1, 0, ''),
('social_twitter_secret', '', 'string', 255, 'main', 12, 5, 1, 0, ''),
('sso_secret', '', 'text', 255, '0', 9, 1, 1, 0, ''),
('sso_token', 'off', 'enum', 3, '0', 9, 0, 1, 0, ''),
('stop_concurrent_user', 'on', 'enum', 3, 'security', 8, 23, 1, 0, ''),
('tablist_mycourses', 'name,status', 'tablist_mycourses', 255, '0', 4, 1, 1, 0, ''),
('teleskill_max_participant', '300', 'string', 255, 'teleskill', 6, 6, 1, 0, ''),
('teleskill_max_room', '99999999999', 'string', 255, 'teleskill', 6, 5, 1, 0, ''),
('templ_use_field', '0', 'id_field', 11, '0', 1, 0, 1, 1, ''),
('title_organigram_chart', 'Forma', 'string', 255, '0', 1, 0, 1, 1, ''),
('tracking', 'on', 'enum', 3, '0', 4, 8, 1, 0, ''),
('ttlSession', '4000', 'int', 5, '0', 1, 6, 1, 0, ''),
('url', 'http://localhost/', 'string', 255, '0', 1, 2, 1, 0, ''),
('url_checkin_teleskill', 'http://asp.teleskill.it/tvclive/server-1-1.asp', 'string', 255, 'teleskill', 6, 1, 1, 0, ''),
('url_videoconference_teleskill', '', 'string', 255, 'teleskill', 6, 2, 1, 0, ''),
('user_pwd_history_length', '3', 'int', 3, 'password', 3, 10, 1, 0, ''),
('user_quota', '50', 'string', 255, '0', 8, 6, 1, 0, ''),
('use_advanced_form', 'off', 'enum', 3, 'register', 3, 16, 1, 0, ''),
('use_dimdim_api', 'off', 'enum', 3, 'dimdim', 6, 8, 1, 0, ''),
('use_rest_api', 'off', 'enum', 3, 'api', 9, 2, 1, 0, ''),
('use_tag', 'off', 'enum', 3, '0', 4, 5, 1, 0, ''),
('visuItem', '25', 'int', 3, '0', 2, 1, 1, 1, ''),
('visuNewsHomePage', '3', 'int', 5, '0', 1, 0, 1, 1, ''),
('welcome_use_feed', 'on', 'enum', 3, '0', 1, 0, 1, 1, ''),
('template_domain',  '',  'textarea',  '65535',  '0',  '8',  '8',  '1',  '0',  '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
