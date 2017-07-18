SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;


--
-- Struttura della tabella `conference_menu`
--

DROP TABLE IF EXISTS `conference_menu`;
CREATE TABLE `conference_menu` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


--
-- Dump dei dati per la tabella `conference_menu`
--

INSERT INTO `conference_menu` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(1, '_MAIN_CONFERENCE_MANAGMENT', '', 1, 'true');

-- --------------------------------------------------------

--
-- Struttura della tabella `conference_menu_under`
--

DROP TABLE IF EXISTS `conference_menu_under`;
CREATE TABLE `conference_menu_under` (
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
  PRIMARY KEY (`idUnder`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `conference_menu_under`
--

INSERT INTO `conference_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`) VALUES
(2, 1, 'room', '_ROOM', 'room', 'view', NULL, 2, 'class.room.php', 'Module_Room');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_menu`
--

DROP TABLE IF EXISTS `core_menu`;
CREATE TABLE `core_menu` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_menu`
--

INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(1, '', '', 1, 'true'),
(2, '_USER_MANAGMENT', '', 2, 'false'),
(3, '_TRASV_MANAGMENT', '', 3, 'false'),
(4, '_ADMINISTRATORS', '', 4, 'false'),
(5, '_LANGUAGE', '', 5, 'false'),
(6, '', '', 6, 'true');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_menu_under`
--

DROP TABLE IF EXISTS `core_menu_under`;
CREATE TABLE `core_menu_under` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_menu_under`
--

INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(1, 1, 'dashboard', '_DASHBOARD', '', 'view', NULL, 1, '', '', 'adm/dashboard/show'),
(3, 2, 'groupmanagement', '_MANAGE_GROUPS', '', 'view', NULL, 2, '', '', 'adm/groupmanagement/show'),
(4, 3, 'field_manager', '_FIELD_MANAGER', 'field_list', 'view', NULL, 3, 'class.field_manager.php', 'Module_Field_Manager', ''),
(5, 3, 'setting', '_CONFIGURATION', '', 'view', NULL, 1, 'class.configuration.php', 'Module_Configuration', 'adm/setting/show'),
(7, 3, 'event_manager', '_EVENTMANAGER', 'display', 'view_event_manager', NULL, 3, 'class.event_manager.php', 'Module_Event_Manager', ''),
(8, 3, 'iotask', '_IOTASK', 'iotask', 'view', NULL, 4, 'class.iotask.php', 'Module_IOTask', ''),
(10, 5, 'lang', '_LANG', '', 'view', NULL, 1, '', '', 'adm/lang/show'),
(13, 6, 'newsletter', '_NEWSLETTER', 'newsletter', 'view', NULL, 1, 'class.newsletter.php', 'Module_Newsletter', ''),
(16, 2, 'usermanagement', '_LISTUSER', '', 'view', NULL, 1, '', '', 'adm/usermanagement/show'),
(18, 4, 'adminrules', '_ADMIN_RULES', '', 'view', NULL, 1, '', '', 'adm/adminrules/show'),
(19, 4, 'publicadminrules', '_PUBLIC_ADMIN_RULES', '', 'view', NULL, 1, '', '', 'adm/publicadminrules/show'),
(20, 4, 'adminmanager', '_ADMIN_MANAGER', '', 'view', NULL, 1, '', '', 'adm/adminmanager/show'),
(21, 4, 'publicadminmanager', '_PUBLIC_ADMIN_MANAGER', '', 'view', NULL, 1, '', '', 'adm/publicadminmanager/show'),
(22, 2, 'functionalroles', '_FUNCTIONAL_ROLE', '', 'view', NULL, 4, '', '', 'adm/functionalroles/show'),
(23, 2, 'competences', '_COMPETENCES', '', 'view', NULL, 3, '', '', 'adm/competences/show'),
(24, 3, 'code', '_CODE', 'list', 'view', NULL, 5, 'class.code.php', 'Module_Code', ''),
(25, 3, 'privacypolicy', '_PRIVACYPOLICIES', '', 'view', NULL, 6, '', '', 'adm/privacypolicy/show');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_menu`
--

DROP TABLE IF EXISTS `learning_menu`;
CREATE TABLE `learning_menu` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_menu`
--

INSERT INTO `learning_menu` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(1, '_MANAGEMENT_COURSE', '', 1, 'false'),
(2, '', '', 2, 'true'),
(3, '_ASSESSMENT', '', 3, 'false'),
(4, '', '', 4, 'true'),
(5, '', '', 5, 'true'),
(6, '_CONTENTS', '', 6, 'false'),
(7, '_MAN_CERTIFICATE', '', 7, 'false'),
(8, '_MANAGEMENT_RESERVATION', '', 8, 'false'),
(9, '_CONFIGURATION', '', 9, 'false'),
(10, '', '', 10, 'true'),
(11, '', '', 11, 'true'),
(12, '', '', 12, 'true'),
(13, '', '', 13, 'true'),
(14, '', '', 14, 'true');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_menu_under`
--

DROP TABLE IF EXISTS `learning_menu_under`;
CREATE TABLE `learning_menu_under` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_menu_under`
--

INSERT INTO `learning_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(1, 1, 'course', '_COURSE', '', 'view', NULL, 1, '', '', 'alms/course/show'),
(2, 9, 'amanmenu', '_MAN_MENU', 'mancustom', 'view', NULL, 1, 'class.amanmenu.php', 'Module_AManmenu', ''),
(3, 1, 'coursepath', '_COURSEPATH', 'pathlist', 'view', NULL, 2, 'class.coursepath.php', 'Module_Coursepath', ''),
(4, 1, 'catalogue', '_CATALOGUE', 'catlist', 'view', NULL, 3, 'class.catalogue.php', 'Module_Catalogue', ''),
(5, 6, 'webpages', '_WEBPAGES', 'webpages', 'view', NULL, 1, 'class.webpages.php', 'Module_Webpages', ''),
(6, 6, 'news', '_NEWS', 'news', 'view', NULL, 2, 'class.news.php', 'Module_News', ''),
(7, 9, 'questcategory', '_QUESTCATEGORY', '', 'view', NULL, 4, '', '', 'alms/questcategory/show'),
(9, 11, 'report', '_REPORT', 'reportlist', 'view', NULL, 1, 'class.report.php', 'Module_Report', ''),
(10, 3, 'preassessment', '_ASSESSMENT', 'assesmentlist', 'view', NULL, 1, 'class.preassessment.php', 'Module_PreAssessment', ''),
(14, 7, 'certificate', '_CERTIFICATE', 'certificate', 'view', NULL, 1, 'class.certificate.php', 'Module_Certificate', ''),
(17, 8, 'reservation', '_EVENTS', 'view_event', 'view', NULL, 1, 'class.reservation.php', 'Module_Reservation', ''),
(18, 8, 'reservation', '_CATEGORY', 'view_category', 'view', NULL, 2, 'class.reservation.php', 'Module_Reservation', ''),
(20, 8, 'reservation', '_RESERVATION', 'view_registration', 'view', NULL, 3, 'class.reservation.php', 'Module_Reservation', ''),
(21, 9, 'middlearea', '_MIDDLE_AREA', 'view_area', 'view', NULL, 2, 'class.middlearea.php', 'Module_MiddleArea', ''),
(22, 6, 'internal_news', '_NEWS_INTERNAL', 'news', 'view', NULL, 3, 'class.internal_news.php', 'Module_Internal_News', ''),
(23, 7, 'meta_certificate', '_META_CERTIFICATE', 'meta_certificate', 'view', NULL, 3, 'class.meta_certificate.php', 'Module_Meta_Certificate', ''),
(26, 14, 'transaction', '_TRANSACTION', '', 'view', NULL, 1, '', '', 'alms/transaction/show'),
(27, 2, 'location', '_LOCATION', '', 'view', NULL, 1, '', '', 'alms/location/show'),
(28, 4, 'games', '_CONTEST', '', 'view', NULL, 1, '', '', 'alms/games/show'),
(29, 5, 'communication', '_COMMUNICATION_MAN', '', 'view', NULL, 1, '', '', 'alms/communication/show'),
(30, 12, 'kb', '_CONTENT_LIBRARY', '', 'view', NULL, 1, '', '', 'alms/kb/show'),
(31, 9, 'timeperiods', '_TIME_PERIODS', '', 'view', NULL, 5, '', '', 'alms/timeperiods/show'),
(32, 13, 'enrollrules', '_ENROLLRULES', '', 'view', NULL, 1, '', '', 'alms/enrollrules/show'),
(33, 9, 'label', '_LABEL', '', 'view', NULL, 5, '', '', 'alms/label/show');



--
-- Struttura della tabella `core_hteditor`
--

DROP TABLE IF EXISTS `core_hteditor`;
CREATE TABLE `core_hteditor` (
  `hteditor` varchar(255) NOT NULL DEFAULT '',
  `hteditorname` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`hteditor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_hteditor`
--

INSERT INTO `core_hteditor` (`hteditor`, `hteditorname`) VALUES
('textarea', '_TEXTAREA'),
('tinymce', '_TINYMCE'),
('yui', '_YUI');

DELETE FROM `learning_module` WHERE `module_name` = 'myfiles' AND `default_op` = 'myfiles';

DELETE FROM `learning_module` WHERE `module_name` = 'myfriends' AND `default_op` = 'myfriends';

DELETE FROM `learning_module` WHERE `module_name` = 'mygroup' AND `default_op` = 'group';

DELETE FROM `learning_module` WHERE `module_name` = 'eportfolio' AND `default_op` = 'eportfolio';

DELETE FROM `learning_module` WHERE `module_name` = 'userevent' AND `default_op` = 'user_display';

UPDATE `learning_module` SET module_info = REPLACE(module_info, 'type=', '') WHERE module_info <> '';

UPDATE `learning_module` SET module_info = 'all' WHERE `module_name` =  'mycertificate';

UPDATE `learning_module` SET module_info = 'all' WHERE `module_name` =  'mycompetences';

DELETE FROM `learning_module` WHERE `module_name` = 'public_user_admin';

DELETE FROM `learning_module` WHERE `module_name` = 'public_course_admin';

DELETE FROM `learning_module` WHERE `module_name` = 'public_subscribe_admin';

DELETE FROM core_event_consumer_class
WHERE idClass IN (
	SELECT idClass
	FROM core_event_class
	WHERE `class` IN (
		'NewsCreated',
		'MediaCreated',
		'DocumentCreated',
		'ContentCreated',
		'PageCreated',
		'NewsModified',
		'MediaModified',
		'DocumentModified',
		'ContentModified',
		'PageModified',
		'FlowApprovation',
		'NewletterReceived',
		'CmsForumNewCategory',
		'CmsForumNewThread',
		'CmsForumNewResponse',
		'KmsDocumentCreated',
		'KmsDocumentModified',
		'KmsDocumentCommented',
		'KmsFlowOperation'
	)
);

DELETE FROM core_event_class
WHERE `class` IN (
	'NewsCreated',
	'MediaCreated',
	'DocumentCreated',
	'ContentCreated',
	'PageCreated',
	'NewsModified',
	'MediaModified',
	'DocumentModified',
	'ContentModified',
	'PageModified',
	'FlowApprovation',
	'NewletterReceived',
	'CmsForumNewCategory',
	'CmsForumNewThread',
	'CmsForumNewResponse',
	'KmsDocumentCreated',
	'KmsDocumentModified',
	'KmsDocumentCommented',
	'KmsFlowOperation' );


INSERT INTO `learning_middlearea` (`obj_index`, `disabled`, `idst_list`) VALUES
('mo_help', 1, 'a:0:{}'),
('tb_communication', 1, 'a:0:{}'),
('tb_games', 1, 'a:0:{}'),
('tb_label', 1, 'a:0:{}'),
('tb_videoconference', 1, 'a:0:{}');

INSERT INTO `learning_report` (`id_report`, `report_name`, `class_name`, `file_name`, `use_user_selection`, `enabled`) VALUES
(5, 'aggregate_report', 'Report_Aggregate', 'class.report_aggregate.php', 'true', 1);

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


ALTER TABLE `core_task` DROP PRIMARY KEY, ADD PRIMARY KEY( `sequence`);

ALTER TABLE `core_task` CHANGE `sequence` `sequence` INT(3) NOT NULL AUTO_INCREMENT;
