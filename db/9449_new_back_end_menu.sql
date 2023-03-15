start transaction;
-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CONFIG_SYS', 'menu', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_CONFIG_SYS' AND text_module = 'menu'), 'english', 'Configuration System', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_CONFIG_SYS' AND text_module = 'menu'), 'italian', 'Configurazione sistema', now() );

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CONFIG_ELEARNING', 'menu', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_CONFIG_ELEARNING' AND text_module = 'menu'), 'english', 'Configuration elearning', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_CONFIG_ELEARNING' AND text_module = 'menu'), 'italian', 'Configurazione elearning', now() );


-- core_platform
INSERT INTO `core_platform` (`platform`, `class_file`, `class_name`, `class_file_menu`, `class_name_menu`, `class_name_menu_managment`, `file_class_config`, `class_name_config`, `var_default_template`, `class_default_admin`, `sequence`, `is_active`, `mandatory`, `dependencies`, `main`, `hidden_in_config`) VALUES
('menu_user', '', '', 'class.admin_menu_admin_user.php', 'Admin_Framework_user', 'Admin_Managment_Framework_User', 'class.conf_fw.php', 'Config_Framework', 'defaultTemplate', 'Module', 1, 'true', 'true', '', 'false', 'false'),
('menu_elearning', '', '', 'class.admin_menu_admin_elearning.php', 'Admin_Framework_Elearning', 'Admin_Managment_Framework_Elearning', 'class.conf_fw.php', 'Config_Framework', 'defaultTemplate', 'Module', 2, 'true', 'true', '', 'false', 'false'),
('menu_content', '', '', 'class.admin_menu_admin_content.php', 'Admin_Framework_Content', 'Admin_Managment_Framework_Content', 'class.conf_fw.php', 'Config_Framework', 'defaultTemplate', 'Module', 3, 'true', 'true', '', 'false', 'false'),
('menu_report', '', '', 'class.admin_menu_admin_report.php', 'Admin_Framework_Report', 'Admin_Managment_Framework_Report', 'class.conf_fw.php', 'Config_Framework', 'defaultTemplate', 'Module', 4, 'true', 'true', '', 'false', 'false'),
('menu_config', '', '', 'class.admin_menu_admin_config.php', 'Admin_Framework_Config', 'Admin_Managment_Framework_Config', 'class.conf_fw.php', 'Config_Framework', 'defaultTemplate', 'Module', 5, 'true', 'true', '', 'false', 'false');

DELETE FROM `core_platform` WHERE `core_platform`.`platform` = 'framework';
DELETE FROM `core_platform` WHERE `core_platform`.`platform` = 'lms';



--
-- USER MENU
--


DROP TABLE IF EXISTS `core_menu_user`;
CREATE TABLE IF NOT EXISTS `core_menu_user` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;


INSERT INTO `core_menu_user` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(1, '', '', 1, 'true'),
(4, '_ADMINISTRATORS', '', 5, 'false'),
(7, '', '', 2, 'true'),
(8, '', '', 3, 'true'),
(9, '', '', 4, 'true');


DROP TABLE IF EXISTS `core_menu_under_user`;
CREATE TABLE IF NOT EXISTS `core_menu_under_user` (
  `idUnder` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `module_name` varchar(255) NOT NULL DEFAULT '',
  `default_name` varchar(255) NOT NULL DEFAULT '',
  `default_op` varchar(255) NOT NULL DEFAULT '',
  `associated_token` varchar(255) NOT NULL DEFAULT '',
  `of_platform` varchar(255) DEFAULT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  `class_file` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `mvc_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idUnder`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;


INSERT INTO `core_menu_under_user` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(3, 7, 'groupmanagement', '_MANAGE_GROUPS', '', 'view', NULL, 1, '', '', 'adm/groupmanagement/show'),
(16, 1, 'usermanagement', '_LISTUSER', '', 'view', NULL, 1, '', '', 'adm/usermanagement/show'),
(18, 4, 'adminrules', '_ADMIN_RULES', '', 'view', NULL, 1, '', '', 'adm/adminrules/show'),
(20, 4, 'adminmanager', '_ADMIN_MANAGER', '', 'view', NULL, 1, '', '', 'adm/adminmanager/show'),
(22, 9, 'functionalroles', '_FUNCTIONAL_ROLE', '', 'view', NULL, 4, '', '', 'adm/functionalroles/show'),
(23, 8, 'competences', '_COMPETENCES', '', 'view', NULL, 1, '', '', 'adm/competences/show');

--
-- ELEARNING MENU
--

DROP TABLE IF EXISTS `core_menu_elearning`;
CREATE TABLE IF NOT EXISTS `core_menu_elearning` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
)ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;


INSERT INTO `core_menu_elearning` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(1, '_COURSES', '', 1, 'false'),
(2, '', '', 2, 'true'),
(3, '', '', 3, 'true'),
(4, '', '', 4, 'true'),
(7, '_MAN_CERTIFICATE', '', 7, 'false'),
(8, '_MANAGEMENT_RESERVATION', '', 8, 'false'),
(10, '', '', 10, 'true'),
(12, '', '', 12, 'true'),
(13, '', '', 13, 'true'),
(14, '', '', 14, 'true');


DROP TABLE IF EXISTS `core_menu_under_elearning`;
CREATE TABLE IF NOT EXISTS `core_menu_under_elearning` (
  `idUnder` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `module_name` varchar(255) NOT NULL DEFAULT '',
  `default_name` varchar(255) NOT NULL DEFAULT '',
  `default_op` varchar(255) NOT NULL DEFAULT '',
  `associated_token` varchar(255) NOT NULL DEFAULT '',
  `of_platform` varchar(255) DEFAULT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  `class_file` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `mvc_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idUnder`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;


INSERT INTO `core_menu_under_elearning` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(1, 1, 'course', '_COURSES', '', 'view', NULL, 1, '', '', 'alms/course/show'),
(3, 1, 'coursepath', '_COURSEPATH', 'pathlist', 'view', 'lms', 2, 'class.coursepath.php', 'Module_Coursepath', ''),
(4, 1, 'catalogue', '_CATALOGUE', 'catlist', 'view', 'lms', 3, 'class.catalogue.php', 'Module_Catalogue', ''),
(14, 7, 'certificate', '_CERTIFICATE', 'certificate', 'view', 'lms', 1, 'class.certificate.php', 'Module_Certificate', ''),
(17, 8, 'reservation', '_EVENTS', 'view_event', 'view', 'lms', 1, 'class.reservation.php', 'Module_Reservation', ''),
(18, 8, 'reservation', '_CATEGORY', 'view_category', 'view', 'lms', 2, 'class.reservation.php', 'Module_Reservation', ''),
(20, 8, 'reservation', '_RESERVATION', 'view_registration', 'view', 'lms', 3, 'class.reservation.php', 'Module_Reservation', ''),
(23, 7, 'meta_certificate', '_META_CERTIFICATE', 'meta_certificate', 'view', 'lms', 3, 'class.meta_certificate.php', 'Module_Meta_Certificate', ''),
(27, 2, 'location', '_LOCATION', '', 'view', NULL, 1, '', '', 'alms/location/show'),
(28, 4, 'games', '_CONTEST', '', 'view', NULL, 1, '', '', 'alms/games/show'),
(30, 12, 'kb', '_CONTENT_LIBRARY', '', 'view', NULL, 1, '', '', 'alms/kb/show'),
(32, 13, 'enrollrules', '_ENROLLRULES', '', 'view', NULL, 1, '', '', 'alms/enrollrules/show'),
(33, 14, 'transaction', '_TRANSACTION', '', 'view', NULL, 1, '', '', 'alms/transaction/show');



--
-- CONTENT MENU
--

DROP TABLE IF EXISTS `core_menu_content`;
CREATE TABLE IF NOT EXISTS `core_menu_content` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_menu_content`
--

INSERT INTO `core_menu_content` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(4, '', '', 4, 'true'),
(5, '', '', 5, 'true'),
(6, '', '', 6, 'true'),
(10, '', '', 10, 'true'),
(11, '', '', 11, 'true');

DROP TABLE IF EXISTS `core_menu_under_content`;
CREATE TABLE IF NOT EXISTS `core_menu_under_content` (
  `idUnder` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `module_name` varchar(255) NOT NULL DEFAULT '',
  `default_name` varchar(255) NOT NULL DEFAULT '',
  `default_op` varchar(255) NOT NULL DEFAULT '',
  `associated_token` varchar(255) NOT NULL DEFAULT '',
  `of_platform` varchar(255) DEFAULT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  `class_file` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `mvc_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idUnder`)
)ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

INSERT INTO `core_menu_under_content` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(5, 4, 'webpages', '_WEBPAGES', 'webpages', 'view', 'lms', 1, 'class.webpages.php', 'Module_Webpages', ''),
(6, 5, 'news', '_NEWS', 'news', 'view', 'lms', 2, 'class.news.php', 'Module_News', ''),
(13, 11, 'newsletter', '_NEWSLETTER', 'newsletter', 'view', 'lms', 1, 'class.newsletter.php', 'Module_Newsletter', ''),
(22, 6, 'internal_news', '_NEWS_INTERNAL', 'news', 'view', 'lms', 3, 'class.internal_news.php', 'Module_Internal_News', ''),
(29, 10, 'communication', '_COMMUNICATION_MAN', '', 'view', 'lms', 1, '', '', 'alms/communication/show');


--
--  REPORT MENU
--
DROP TABLE IF EXISTS `core_menu_report`;
CREATE TABLE IF NOT EXISTS `core_menu_report` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
)ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


INSERT INTO `core_menu_report` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(2, '', '', 2, 'true');

DROP TABLE IF EXISTS `core_menu_under_report`;
CREATE TABLE IF NOT EXISTS `core_menu_under_report` (
  `idUnder` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `module_name` varchar(255) NOT NULL DEFAULT '',
  `default_name` varchar(255) NOT NULL DEFAULT '',
  `default_op` varchar(255) NOT NULL DEFAULT '',
  `associated_token` varchar(255) NOT NULL DEFAULT '',
  `of_platform` varchar(255) DEFAULT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  `class_file` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `mvc_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idUnder`)
)ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;



INSERT INTO `core_menu_under_report` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(9, 2, 'report', '_REPORT', 'reportlist', 'view', 'lms', 1, 'class.report.php', 'Module_Report', '');


--
--  CONFIG MENU
--

DROP TABLE IF EXISTS `core_menu_config`;
CREATE TABLE IF NOT EXISTS `core_menu_config` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;


INSERT INTO `core_menu_config` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(4, '_CONFIG_SYS', '', 1, 'false'),
(5, '', '', 4, 'true'),
(9, '_CONFIG_ELEARNING', '', 2, 'false'),
(10, '_FIELD_MANAGER', '', 3, 'false');


DROP TABLE IF EXISTS `core_menu_under_config`;
CREATE TABLE IF NOT EXISTS `core_menu_under_config` (
  `idUnder` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `module_name` varchar(255) NOT NULL DEFAULT '',
  `default_name` varchar(255) NOT NULL DEFAULT '',
  `default_op` varchar(255) NOT NULL DEFAULT '',
  `associated_token` varchar(255) NOT NULL DEFAULT '',
  `of_platform` varchar(255) DEFAULT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  `class_file` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `mvc_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idUnder`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;


INSERT INTO `core_menu_under_config` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(1, 9, 'questcategory', '_QUESTCATEGORY', '', 'view', NULL, 4, '', '', 'alms/questcategory/show'),
(2, 9, 'amanmenu', '_MAN_MENU', 'mancustom', 'view', 'lms', 1, 'class.amanmenu.php', 'Module_AManmenu', ''),
(4, 10, 'field_manager', '_FIELD_MANAGER', 'field_list', 'view', NULL, 3, 'class.field_manager.php', 'Module_Field_Manager', ''),
(5, 4, 'setting', '_CONFIGURATION', '', 'view', NULL, 1, 'class.configuration.php', 'Module_Configuration', 'adm/setting/show'),
(6, 10, 'customfield_manager', '_CUSTOMFIELD_MANAGER', 'field_list', 'view', NULL, 8, 'class.customfield_manager.php', 'Module_Customfield_Manager', ''),
(7, 4, 'event_manager', '_EVENTMANAGER', 'display', 'view_event_manager', NULL, 3, 'class.event_manager.php', 'Module_Event_Manager', ''),
(8, 4, 'iotask', '_IOTASK', 'iotask', 'view', NULL, 4, 'class.iotask.php', 'Module_IOTask', ''),
(9, 4, 'pluginmanager', '_PLUGIN_MANAGER', '', 'view', NULL, 7, '', '', 'adm/pluginmanager/show'),
(10, 5, 'lang', '_LANG', '', 'view', NULL, 1, '', '', 'adm/lang/show'),
(21, 9, 'middlearea', '_MIDDLE_AREA', 'view_area', 'view', 'lms', 2, 'class.middlearea.php', 'Module_MiddleArea', ''),
(25, 4, 'privacypolicy', '_PRIVACYPOLICIES', '', 'view', NULL, 6, '', '', 'adm/privacypolicy/show'),
(31, 9, 'timeperiods', '_TIME_PERIODS', '', 'view', NULL, 5, '', '', 'alms/timeperiods/show'),
(33, 9, 'label', '_LABEL', '', 'view', NULL, 5, '', '', 'alms/label/show'),
(34, 4, 'code', '_CODE', 'list', 'view', NULL, 8, 'class.code.php', 'Module_Code', '');

-- DROPPING
-- NO LONGER USEFUL TABLES
DROP TABLE IF EXISTS `core_menu`;
DROP TABLE IF EXISTS `core_menu_under`;
DROP TABLE IF EXISTS `learning_menu`;
DROP TABLE IF EXISTS `learning_menu_under`;

-- ENABLING SECOND LEVEL PERMISSION FOR THE NEW MENU STRUCTURE
UPDATE core_menu_under_elearning set of_platform = 'lms' 
UPDATE core_menu_under_content SET of_platform = 'framework' WHERE module_name = 'newsletter';

-- END
commit;