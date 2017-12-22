drop TABLE if exists `core_menu`;
/*
idMenu	int(11)	NO	PRI		auto_increment
name	varchar(255)	NO			
image	varchar(255)	NO			
sequence	int(3)	NO		0	
collapse	enum('true','false')	NO		false	
*/

CREATE TABLE `core_menu` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `is_active` enum('true','false') NOT NULL DEFAULT 'true',
  `collapse` enum('true','false') NOT NULL DEFAULT 'true', -- false
  `idParent` int(11) NULL,
  `idPlugin` int(11) NULL,
  PRIMARY KEY (`idmenu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


insert into core_menu(idmenu, name, sequence, is_active, image) VALUES
(1, '_USER_MANAGMENT', 1, 'true', '<i class="fa fa-users fa-fw"></i>');
insert into core_menu(idmenu, name, sequence, is_active, image) VALUES
(2, '_FIRST_LINE_lms', 2, 'true', ' <i class="fa fa-graduation-cap" aria-hidden="true"></i>');
insert into core_menu(idmenu, name, sequence, is_active, image) VALUES
(3, '_CONTENTS', 3, 'true', '<i class="fa fa-clipboard fa-fw"></i>');
insert into core_menu(idmenu, name, sequence, is_active, image) VALUES
(4, '_REPORT', 4, 'true', '<i class="fa fa-bar-chart-o fa-fw"></i>');
insert into core_menu(idmenu, name, sequence, is_active, image) VALUES
(5, '_CONFIGURATION', 5, 'true', '<i class="fa fa-cogs fa-fw"></i>');

insert into core_menu(idmenu, idparent, name, sequence) VALUES
(40, 4, '_REPORT', 1), 
(11, 1, '_LISTUSER', 1), 
(12, 1, '_MANAGE_GROUPS', 2), 
(13, 1, '_COMPETENCES', 3), 
(14, 1, '_FUNCTIONAL_ROLE', 4), 
(15, 1, '_ADMINISTRATORS', 5), 
(21, 2, '_COURSES', 1), 
(22, 2, '_LOCATION', 2), 
(23, 2, '_CONTEST', 3), 
(24, 2, '_MAN_CERTIFICATE', 4), 
(25, 2, '_MANAGEMENT_RESERVATION', 5), 
(26, 2, '_CONTENT_LIBRARY', 6), 
(27, 2, '_ENROLLRULES', 7), 
(28, 2, '_TRANSACTION', 8), 
(31, 3, '_WEBPAGES', 1), 
(32, 3, '_NEWS', 2), 
(33, 3, '_NEWS_INTERNAL', 3), 
(34, 3, '_COMMUNICATION_MAN', 4), 
(35, 3, '_NEWSLETTER', 5), 
(50, 5, '_FIELD_MANAGER', 4), 
(51, 5, '_DASHBOARD', 1), 
(52, 5, '_CONFIG_SYS', 2), 
(54, 5, '_PLUGIN_MANAGER', 4), 
(55, 5, '_LANG', 5), 
(59, 5, '_CONFIG_ELEARNING', 3), 
(151, 15, '_ADMIN_RULES', 1), 
(152, 15, '_ADMIN_MANAGER', 2), 
(211, 21, '_COURSES', 1), 
(212, 21, '_COURSEPATH', 2), 
(213, 21, '_CATALOGUE', 3), 
(241, 24, '_CERTIFICATE', 1), 
(242, 24, '_META_CERTIFICATE', 2), 
(251, 25, '_EVENTS', 1), 
(252, 25, '_CATEGORY', 2), 
(253, 25, '_RESERVATION', 3), 
(501, 50, '_FIELD_MANAGER', 1), 
(502, 50, '_CUSTOMFIELD_MANAGER', 2), 
(521, 52, '_CONFIGURATION', 1), 
(523, 52, '_EVENTMANAGER', 3), 
(524, 52, '_IOTASK', 4), 
(526, 52, '_PRIVACYPOLICIES', 6), 
(528, 52, '_CODE', 8), 
(591, 59, '_MAN_MENU', 1), 
(592, 59, '_MIDDLE_AREA', 2), 
(593, 59, '_QUESTCATEGORY', 3), 
(594, 59, '_TIME_PERIODS', 4), 
(595, 59, '_LABEL', 5);

drop TABLE if exists `core_menu_under`;

CREATE TABLE `core_menu_under` (
  `idUnder` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `module_name` varchar(255) NULL,
  `default_name` varchar(255) NOT NULL DEFAULT '',
  `default_op` varchar(255) NULL ,
  `associated_token` varchar(255) NULL ,
  `of_platform` varchar(255) DEFAULT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  `class_file` varchar(255) NULL,
  `class_name` varchar(255) NULL,
  `mvc_path` varchar(255) null,
  PRIMARY KEY (`idUnder`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;


insert into core_menu_under
(idUnder, idMenu, module_name, default_name, default_op, associated_token, of_platform, sequence, class_file, class_name, mvc_path) VALUES
(11, 11, 'usermanagement', '_LISTUSER', '', 'view', NULL, 1, '', '', 'adm/usermanagement/show'), 
(12, 12, 'groupmanagement', '_MANAGE_GROUPS', '', 'view', NULL, 1, '', '', 'adm/groupmanagement/show'), 
(13, 13, 'competences', '_COMPETENCES', '', 'view', NULL, 1, '', '', 'adm/competences/show'), 
(14, 14, 'functionalroles', '_FUNCTIONAL_ROLE', '', 'view', NULL, 4, '', '', 'adm/functionalroles/show'), 
(22, 22, 'location', '_LOCATION', '', 'view', 'lms', 2, '', '', 'alms/location/show'), 
(23, 23, 'games', '_CONTEST', '', 'view', 'lms', 3, '', '', 'alms/games/show'), 
(26, 26, 'kb', '_CONTENT_LIBRARY', '', 'view', 'lms', 6, '', '', 'alms/kb/show'), 
(27, 27, 'enrollrules', '_ENROLLRULES', '', 'view', 'lms', 7, '', '', 'alms/enrollrules/show'), 
(28, 28, 'transaction', '_TRANSACTION', '', 'view', 'lms', 8, '', '', 'alms/transaction/show'), 
(31, 31, 'webpages', '_WEBPAGES', 'webpages', 'view', 'lms', 1, 'class.webpages.php', 'Module_Webpages', ''), 
(32, 32, 'news', '_NEWS', 'news', 'view', 'lms', 2, 'class.news.php', 'Module_News', ''), 
(33, 33, 'internal_news', '_NEWS_INTERNAL', 'news', 'view', 'lms', 3, 'class.internal_news.php', 'Module_Internal_News', ''), 
(34, 34, 'communication', '_COMMUNICATION_MAN', '', 'view', 'lms', 1, '', '', 'alms/communication/show'), 
(35, 35, 'newsletter', '_NEWSLETTER', 'newsletter', 'view', 'framework', 1, 'class.newsletter.php', 'Module_Newsletter', ''), 
(40, 40, 'report', '_REPORT', 'reportlist', 'view', 'lms', 1, 'class.report.php', 'Module_Report', ''), 
(51, 51, 'dashboard', '_DASHBOARD', '', 'view', NULL, 1, '', '', 'adm/dashboard/show'), 
(54, 54, 'pluginmanager', '_PLUGIN_MANAGER', '', 'view', NULL, 4, '', '', 'adm/pluginmanager/show'), 
(55, 55, 'lang', '_LANG', '', 'view', NULL, 5, '', '', 'adm/lang/show'), 
(151, 151, 'adminrules', '_ADMIN_RULES', '', 'view', NULL, 1, '', '', 'adm/adminrules/show'), 
(152, 152, 'adminmanager', '_ADMIN_MANAGER', '', 'view', NULL, 1, '', '', 'adm/adminmanager/show'), 
(211, 211, 'course', '_COURSES', '', 'view', 'lms', 1, '', '', 'alms/course/show'), 
(212, 212, 'coursepath', '_COURSEPATH', 'pathlist', 'view', 'lms', 2, 'class.coursepath.php', 'Module_Coursepath', ''), 
(213, 213, 'catalogue', '_CATALOGUE', 'catlist', 'view', 'lms', 3, 'class.catalogue.php', 'Module_Catalogue', ''), 
(241, 241, 'certificate', '_CERTIFICATE', 'certificate', 'view', 'lms', 1, 'class.certificate.php', 'Module_Certificate', ''), 
(242, 242, 'meta_certificate', '_META_CERTIFICATE', 'meta_certificate', 'view', 'lms', 2, 'class.meta_certificate.php', 'Module_Meta_Certificate', ''), 
(251, 251, 'reservation', '_EVENTS', 'view_event', 'view', 'lms', 1, 'class.reservation.php', 'Module_Reservation', ''), 
(252, 252, 'reservation', '_CATEGORY', 'view_category', 'view', 'lms', 2, 'class.reservation.php', 'Module_Reservation', ''), 
(253, 253, 'reservation', '_RESERVATION', 'view_registration', 'view', 'lms', 3, 'class.reservation.php', 'Module_Reservation', ''), 
(501, 501, 'field_manager', '_FIELD_MANAGER', 'field_list', 'view', 'framework', 1, 'class.field_manager.php', 'Module_Field_Manager', ''), 
(502, 502, 'customfield_manager', '_CUSTOMFIELD_MANAGER', 'field_list', 'view', 'framework', 2, 'class.customfield_manager.php', 'Module_Customfield_Manager', ''), 
(521, 521, 'setting', '_CONFIGURATION', '', 'view', NULL, 1, 'class.configuration.php', 'Module_Configuration', 'adm/setting/show'), 
(523, 523, 'event_manager', '_EVENTMANAGER', 'display', 'view_event_manager', 'framework', 3, 'class.event_manager.php', 'Module_Event_Manager', ''), 
(524, 524, 'iotask', '_IOTASK', 'iotask', 'view', 'framework', 4, 'class.iotask.php', 'Module_IOTask', ''), 
(526, 526, 'privacypolicy', '_PRIVACYPOLICIES', '', 'view', NULL, 6, '', '', 'adm/privacypolicy/show'), 
(528, 528, 'code', '_CODE', 'list', 'view', 'framework', 8, 'class.code.php', 'Module_Code', ''), 
(591, 591, 'amanmenu', '_MAN_MENU', 'mancustom', 'view', 'lms', 1, 'class.amanmenu.php', 'Module_AManmenu', ''), 
(592, 592, 'middlearea', '_MIDDLE_AREA', 'view_area', 'view', 'lms', 2, 'class.middlearea.php', 'Module_MiddleArea', ''), 
(593, 593, 'questcategory', '_QUESTCATEGORY', '', 'view', 'lms', 3, '', '', 'alms/questcategory/show'), 
(594, 594, 'timeperiods', '_TIME_PERIODS', '', 'view', 'lms', 4, '', '', 'alms/timeperiods/show'), 
(595, 595, 'label', '_LABEL', '', 'view', 'lms', 5, '', '', 'alms/label/show');

-- Drop old (2.0.alfa) tables
drop table if exists core_menu_under_config, core_menu_under_content, core_menu_under_elearning, core_menu_under_report, core_menu_under_user;

drop table if exists core_menu_config, core_menu_content, core_menu_elearning, core_menu_report, core_menu_user;

-- Test report
-- delete from core_menu_under where idmenu not in (4, 40);
-- delete from core_menu where idmenu not in (4, 40);
 
-- delete from core_menu_under where idmenu not in (4);
-- delete from core_menu where idmenu not in (4);

-- Test user
-- delete from core_menu_under where idmenu not in (1, 11, 15, 151);
-- delete from core_menu where idmenu not in (1, 11, 15, 151);

