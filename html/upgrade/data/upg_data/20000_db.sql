--
-- Update database formalms
--
--
-- Update db script from formalms 1.4 to formalms 2.0
--

-- ------------------------------------------------------------------

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- ------------------------------------------------------------------

-- 150605.view_all.sql 


-- label
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_VIEW_ALL', 'standard', '');

-- 338.txt 
ALTER TABLE  `core_user_temp` ADD  `avatar` VARCHAR( 255 ) NOT NULL;

-- 346.sql 
INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('orgchart_singlenode', 'off', 'enum', '3', '0', '3', '21', '1', '0', '');


-- 4232.sql 
UPDATE core_setting 
SET 
param_value = 'exe,rar,zip,jpg,gif,png,txt,csv,rtf,xml,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,pdf,xps,mp4,mp3,flv,swf,mov,wav,ogg,flac,wma,wmv'
WHERE
param_name = 'file_upload_whitelist';

-- 4301_forum_private_thread.sql 
ALTER TABLE `learning_forumthread` ADD COLUMN `privateThread` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rilevantForum`;

-- 4375.sql 


-- adding catalogue link onto the top menu
INSERT IGNORE INTO learning_module (module_name, default_name, token_associated,module_info, mvc_path ) values ('course','_CATALOGUE','view','all','lms/catalog/show');
INSERT IGNORE INTO learning_middlearea (obj_index,disabled,idst_list,sequence)  values ('mo_46',0,'a:0:{}',0);
INSERT IGNORE INTO learning_menucourse_under (idCourse, idModule, idMain,sequence) values (0,46,0,3);

-- 4447.sql
ALTER TABLE learning_course ADD id_menucustom INT(11);

-- 4687_forum_as_table.sql
DELETE FROM core_setting WHERE param_name = 'forum_as_table';

-- core_role_view_all.sql


-- nuovi_tipi_di_test.sql
ALTER TABLE `learning_organization_access` ADD COLUMN `params` VARCHAR(255) NULL COMMENT '' AFTER `value`;

ALTER TABLE `learning_test` ADD COLUMN `obj_type` VARCHAR(45) NULL DEFAULT 'test' COMMENT '' AFTER `score_max`;


-- 4303.sql


-- Creazione permesso view_all per modulo repository

INSERT IGNORE INTO core_st(idst) values(null);

set @v_idst=LAST_INSERT_ID();

INSERT IGNORE INTO core_role(idst, roleid, description) VALUES
(@v_idst, concat('/lms/course/private/light_repo/view_all'), NULL);

-- 4299.sql



-- Evento UserNewApi

INSERT IGNORE INTO `core_event_class` (`class`, `platform`, `description`) VALUES ('UserNewApi', 'framework', '');
set @v_idst=LAST_INSERT_ID();

INSERT IGNORE INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, @v_idst);

INSERT IGNORE INTO `core_event_manager` (`idClass`, `permission`, `channel`, `recipients`, `show_level`)
VALUES
(@v_idst, 'mandatory', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user');




-- Evento UserNewApi

INSERT IGNORE INTO `core_event_class` (`class`, `platform`, `description`) VALUES ('UserCourseInsertedApi', 'lms-a', '');
set @v_idst=LAST_INSERT_ID();

INSERT IGNORE INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, @v_idst);

INSERT IGNORE INTO `core_event_manager` (`idClass`, `permission`, `channel`, `recipients`, `show_level`)
VALUES
(@v_idst, 'mandatory', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user');

-- 6364.sql

DROP TABLE IF EXISTS core_db_upgrades;
CREATE TABLE IF NOT EXISTS core_db_upgrades (
 script_id int(11) NOT NULL AUTO_INCREMENT,
 script_name varchar(255) NOT NULL,
 script_description text,
 script_version varchar(255),
 core_version varchar(255),
 creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 execution_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 PRIMARY KEY (script_id)
);


INSERT IGNORE INTO core_db_upgrades (script_name, script_description, script_version, core_version, creation_date, execution_date) values ('add_log_db_upgrades.sql', 'Creazione tabella di log per script update db', '1.0', (SELECT param_value FROM core_setting WHERE param_name LIKE 'core_version'), now(), now())
ON DUPLICATE KEY UPDATE
execution_date=now();




-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_DB_UPGRADES', 'dashboard', '');




-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_LIST_DB_UPGRADES', 'configuration', '');



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SCRIPT_ID', 'standard', '');





-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SCRIPT_NAME', 'standard', '');




-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SCRIPT_DESCRIPTION', 'standard', '');




-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SCRIPT_VERSION', 'standard', '');



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CORE_VERSION', 'standard', '');




-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CREATION_DATE', 'standard', '');






-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_EXECUTION_DATE', 'standard', '');





-- retain_answers_history.sql
ALTER TABLE learning_testtrack_answer ADD COLUMN number_time TINYINT(4) NULL DEFAULT '1' COMMENT '' AFTER user_answer;

ALTER TABLE learning_test ADD COLUMN retain_answers_history TINYINT(1) NOT NULL DEFAULT '0' COMMENT '' AFTER obj_type;

ALTER TABLE learning_testtrack_answer
CHANGE COLUMN number_time number_time TINYINT(4) NOT NULL DEFAULT '1' COMMENT '' ,
DROP PRIMARY KEY,
ADD PRIMARY KEY (idTrack, idQuest, idAnswer, number_time)  COMMENT '';

-- password_algorithms.sql
ALTER TABLE `core_user` CHANGE `pass` `pass` VARCHAR(255) NOT NULL;

ALTER TABLE `core_user_temp` CHANGE `pass` `pass` VARCHAR(255) NOT NULL;

INSERT IGNORE INTO `core_setting` (
  `param_name` ,
  `param_value` ,
  `value_type` ,
  `max_size` ,
  `pack` ,
  `regroup` ,
  `sequence` ,
  `param_load` ,
  `hide_in_modify` ,
  `extra_info`
)
VALUES (
  'pass_algorithm', '1', 'password_algorithms', '255', 'password', '3', '5', '1', '0', ''
);

-- 4229_customfield.sql

DROP TABLE IF EXISTS `core_customfield`;
CREATE TABLE IF NOT EXISTS `core_customfield` (
  `id_field` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL DEFAULT '',
  `type_field` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(5) NOT NULL DEFAULT '0',
  `show_on_platform` varchar(255) NOT NULL DEFAULT 'framework,',
  `use_multilang` tinyint(1) NOT NULL DEFAULT '0',
  `area_code` varchar(255) NOT NULL,
  PRIMARY KEY (`id_field`)
);

DROP TABLE IF EXISTS `core_customfield_area`;
CREATE TABLE IF NOT EXISTS `core_customfield_area` (
  `area_code` varchar(255) NOT NULL DEFAULT '',
  `area_name` varchar(255) NOT NULL DEFAULT '',
  `area_table` varchar(255) NOT NULL DEFAULT '',
  `area_field` varchar(255) NOT NULL DEFAULT ''
);

INSERT IGNORE INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('LO_TEST', 'Learning Object Test', '%lms_testquest', 'idQuest');
INSERT IGNORE INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('COURSE', 'Course', '%lms_course', 'idCourse');
INSERT IGNORE INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('COURSE_EDITION', 'Course Edition', '%lms_course_editions', 'id_edition');


DROP TABLE IF EXISTS `core_customfield_entry`;
CREATE TABLE IF NOT EXISTS `core_customfield_entry` (
  `id_field` varchar(11) NOT NULL DEFAULT '',
  `id_obj` int(11) NOT NULL DEFAULT '0',
  `obj_entry` text NOT NULL,
  PRIMARY KEY (`id_field`,`id_obj`)
);

DROP TABLE IF EXISTS `core_customfield_lang`;
CREATE TABLE IF NOT EXISTS `core_customfield_lang` (
  `id_field` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `translation` varchar(255) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS `core_customfield_son`;
CREATE TABLE IF NOT EXISTS `core_customfield_son` (
  `id_field_son` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL DEFAULT '',
  `id_field` int(11) NOT NULL DEFAULT '0',
  `sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_field_son`)
);

DROP TABLE IF EXISTS `core_customfield_son_lang`;
CREATE TABLE IF NOT EXISTS `core_customfield_son_lang` (
  `id_field_son` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(50) NOT NULL DEFAULT '',
  `translation` varchar(255) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS `core_customfield_type`;
CREATE TABLE IF NOT EXISTS `core_customfield_type` (
  `type_field` varchar(255) NOT NULL DEFAULT '',
  `type_file` varchar(255) NOT NULL DEFAULT '',
  `type_class` varchar(255) NOT NULL DEFAULT '',
  `type_category` varchar(255) NOT NULL DEFAULT 'standard',
  PRIMARY KEY (`type_field`)
);

INSERT IGNORE INTO `core_customfield_type` (`type_field`, `type_file`, `type_class`, `type_category`) VALUES('textfield', 'class.textfield.php', 'Field_Textfield', 'standard');
INSERT IGNORE INTO `core_customfield_type` (`type_field`, `type_file`, `type_class`, `type_category`) VALUES('dropdown', 'class.dropdown.php', 'Field_Dropdown', 'standard');




-- ------------------


-- MENU CUSTOM FIELD


-- ------------------

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CUSTOMFIELD_MANAGER', 'menu', '');


INSERT IGNORE INTO core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
INSERT IGNORE INTO core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/customfield_manager/view'), NULL);
INSERT IGNORE INTO core_role_members values(@v_idst, 3);


INSERT IGNORE INTO core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
INSERT IGNORE INTO core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/customfield_manager/add'), NULL);
INSERT IGNORE INTO core_role_members values(@v_idst, 3);


INSERT IGNORE INTO core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
INSERT IGNORE INTO core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/customfield_manager/mod'), NULL);
INSERT IGNORE INTO core_role_members values(@v_idst, 3);


INSERT IGNORE INTO core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
INSERT IGNORE INTO core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/customfield_manager/del'), NULL);
INSERT IGNORE INTO core_role_members values(@v_idst, 3);




-- ------------------


-- MENU CUSTOM FIELD


-- ------------------

-- 4229_customfield_Lang.sql



-- ------------------


--        LABEL


-- ------------------



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ADD_NEW_CUSTOMFIELD', 'field', '');



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_RU_CAT_TESTSTAT', 'report', '');





-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_EXISTING_TEST', 'test', '');





-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SELECTTEST', 'storage', '');




-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_EXPORT_QUESTIONS', 'storage', '');



-- 4229_customfield_test.sql


-- ------------------


-- TAB learning_test


-- ------------------

ALTER TABLE  `learning_test` ADD  `cf_info` TEXT NOT NULL AFTER  `order_info`;

-- 8967.sql


-- label _ACTION_ON_USERS

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ACTION_ON_USERS', 'user_managment', '');




-- label _CREATE_AND_UPDATE

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CREATE_AND_UPDATE', 'user_managment', '');




-- label _CREATE_ALL

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CREATE_ALL', 'user_managment', '');



-- label _ONLY_CREATE

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ONLY_CREATE', 'user_managment', '');


-- label _ONLY_UPDATE

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ONLY_UPDATE', 'user_managment', '');







-- label _SET_PASSWORD

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SET_PASSWORD', 'user_managment', '');





-- label _FROM_FILE

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_FROM_FILE', 'user_managment', '');




-- label _INSERT_EMPTY

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_INSERT_EMPTY', 'user_managment', '');



-- label _INSERT_ALL

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_INSERT_ALL', 'user_managment', '');




-- label _PASSWORD_TO_INSERT

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_PASSWORD_TO_INSERT', 'user_managment', '');






-- label _MANUAL_PASSWORD

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_MANUAL_PASSWORD', 'user_managment', '');




-- label _AUTOMATIC_PASSWORD

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_AUTOMATIC_PASSWORD', 'user_managment', '');




-- label _SEND_NEW_CREDENTIALS_ALERT

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SEND_NEW_CREDENTIALS_ALERT', 'user_managment', '');







-- label _NEED_TO_ALERT

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_NEED_TO_ALERT', 'user_managment', '');





-- label _NO_FILE

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_NO_FILE', 'user_managment', '');




-- label _USERID_NEEDED

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_USERID_NEEDED', 'user_managment', '');




-- label _FIELD_REPEATED

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_FIELD_REPEATED', 'user_managment', '');






-- label _GENERATE_PASSWORD

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_GENERATE_PASSWORD', 'user_managment', '');







-- label _USER_ALREADY_EXISTS

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_USER_ALREADY_EXISTS', 'standard', '');







-- 8996_add_simpleadmin_orgchart.sql
INSERT IGNORE INTO core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
INSERT IGNORE INTO core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/usermanagement/add_org'), NULL);
INSERT IGNORE INTO core_role_members values(@v_idst, 3);

INSERT IGNORE INTO core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
INSERT IGNORE INTO core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/usermanagement/mod_org'), NULL);
INSERT IGNORE INTO core_role_members values(@v_idst, 3);

INSERT IGNORE INTO core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
INSERT IGNORE INTO core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/usermanagement/del_org'), NULL);
INSERT IGNORE INTO core_role_members values(@v_idst, 3);

-- 9004_chiusura_corsi_edizioni_ora.sql


-- edition.date_end DATETIME
ALTER TABLE  `learning_course_editions` CHANGE  `date_end`  `date_end` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00';




-- 9497_import_ucfirst.sql


-- setting
INSERT IGNORE INTO `core_setting`
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES
('import_ucfirst', 'on', 'enum', 3, '0', 3, 22, 1, 0, '');



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_IMPORT_UCFIRST', 'configuration', '');




-- coursereport_mvc.sql
UPDATE `learning_module` SET `mvc_path` = 'lms/coursereport/coursereport' WHERE `learning_module`.`module_name` = "coursereport" AND `learning_module`.`default_op` = "coursereport";

-- 9593_custom_fields_mandatory_for_admin.sql
INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES ('custom_fields_mandatory_for_admin', 'off', 'enum', '3', 'register', '3', '21', '1', '0', '');

INSERT IGNORE INTO `core_lang_text` (`text_key`, `text_module`, `text_attributes`) VALUES ('_CUSTOM_FIELDS_MANDATORY_FOR_ADMIN', 'configuration', '');


-- 4559_remove.sql

DELETE FROM `learning_module` WHERE `module_name` = "pusermanagement";
DELETE FROM `learning_module` WHERE `module_name` = "pcourse";
DELETE FROM `learning_module` WHERE `module_name` = "public_report_admin";
DELETE FROM `learning_module` WHERE `module_name` = "public_newsletter_admin";
DELETE FROM `learning_module` WHERE `module_name` = "pcertificate";

DELETE FROM `core_role` WHERE `roleId` = "/framework/admin/publicadminmanager/mod";
DELETE FROM `core_role` WHERE `roleId` = "/framework/admin/publicadminmanager/view";
DELETE FROM `core_role` WHERE `roleId` = "/framework/admin/publicadminrules/view";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pusermanagement/view";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pusermanagement/add";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pusermanagement/mod";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pusermanagement/del";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pusermanagement/approve_waiting_user";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/view";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/add";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/mod";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/del";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/moderate";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcourse/subscribe";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_report_admin/view";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_newsletter_admin/view";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_subscribe_admin/approve_waiting_user";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_subscribe_admin/createuser_org_chart";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_subscribe_admin/deluser_org_chart";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_subscribe_admin/edituser_org_chart";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/public_subscribe_admin/view_org_chart";

DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcertificate/view";
DELETE FROM `core_role` WHERE `roleId` = "/lms/course/public/pcertificate/mod";

DELETE FROM `core_group` WHERE `groupid` = "/framework/level/publicadmin";


-- 4560_remove_coursecharts.sql

DELETE from learning_module WHERE module_name = "coursecharts";

DELETE from core_role WHERE roleid like "%coursecharts%";

-- plugin-requests_tempname.sql
CREATE TABLE IF NOT EXISTS `core_requests` (
  `id` int(11) NOT NULL,
  `app` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `controller` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `plugin` varchar(255) NOT NULL
);

ALTER TABLE `core_requests`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `core_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- dev-plugin-system.sql 


-- SETTINGS
  

-- Campo per i plugin
  ALTER TABLE `core_plugin` DROP `code`;
  ALTER TABLE `core_plugin`  ADD `regroup` INT(11) NOT NULL  AFTER `description`;
  ALTER TABLE `core_plugin`  ADD `core` INT(1) NOT NULL;
  ALTER TABLE `core_plugin`  MODIFY `core` INT(1) NOT NULL;
  
  
  INSERT IGNORE INTO `core_plugin` (`name`, `title`, `category`, `version`, `author`, `link`, `priority`, `description`, `active`, `core`) VALUES('FormaAuth', 'Forma Auth', '', '1.0', 'Joint Technologies', '', 0, 'forma auth', 1, 1);

  

-- Campo per i settaggi
 ALTER TABLE `core_setting` CHANGE `regroup` `regroup` INT(11) NOT NULL DEFAULT '0';



-- GENERAL
  

UPDATE `core_lang_text` SET `text_key` = 'course_report' WHERE `core_lang_text`.`text_key` = 'courses_report';

-- 9793_show_in_coursereport.sql 
ALTER TABLE `learning_test` ADD `show_in_coursereport` TINYINT NULL DEFAULT '0' AFTER `retain_answers_history`;

-- reset-twig-cache.sql 
INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
											VALUES ('Clear_Twig_Cache','index.php?r=adm/setting/clearTwigCache','button',2,'Twig Cache',13,0,0,0,'');


-- 9449_new_back_end_menu.sql 


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CONFIG_SYS', 'menu', '');



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CONFIG_ELEARNING', 'menu', '');


-- DROPPING


-- NO LONGER USEFUL TABLES
DROP TABLE IF EXISTS `core_menu`;
DROP TABLE IF EXISTS `core_menu_under`;
DROP TABLE IF EXISTS `learning_menu`;
DROP TABLE IF EXISTS `learning_menu_under`;


-- --------------------------------------------------------

--
-- Struttura della tabella `core_menu`
--

CREATE TABLE IF NOT EXISTS `core_menu` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `is_active` enum('true','false') NOT NULL DEFAULT 'true',
  `collapse` enum('true','false') NOT NULL DEFAULT 'true',
  `idParent` int(11) DEFAULT NULL,
  `idPlugin` int(11) DEFAULT NULL,
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_menu`
--

INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(1, '_USER_MANAGMENT', '<i class="fa fa-users fa-fw"></i>', 1, 'true', 'true', NULL, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(2, '_FIRST_LINE_lms', '<span class="glyphicon glyphicon glyphicon-education" aria-hidden="true"></span>', 2, 'true', 'true', NULL, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(3, '_CONTENTS', '<i class="fa fa-clipboard fa-fw"></i>', 3, 'true', 'true', NULL, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(4, '_REPORT', '<i class="fa fa-bar-chart-o fa-fw"></i>', 4, 'true', 'true', NULL, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(5, '_CONFIGURATION', '<i class="fa fa-cogs fa-fw"></i>', 5, 'true', 'true', NULL, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(11, '_LISTUSER', '', 1, 'true', 'true', 1, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(12, '_MANAGE_GROUPS', '', 2, 'true', 'true', 1, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(13, '_COMPETENCES', '', 3, 'true', 'true', 1, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(14, '_FUNCTIONAL_ROLE', '', 4, 'true', 'true', 1, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(15, '_ADMINISTRATORS', '', 5, 'true', 'true', 1, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(21, '_COURSES', '', 1, 'true', 'true', 2, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(22, '_LOCATION', '', 2, 'true', 'true', 2, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(23, '_CONTEST', '', 3, 'true', 'true', 2, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(24, '_MAN_CERTIFICATE', '', 4, 'true', 'true', 2, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(25, '_MANAGEMENT_RESERVATION', '', 5, 'true', 'true', 2, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(26, '_CONTENT_LIBRARY', '', 6, 'true', 'true', 2, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(27, '_ENROLLRULES', '', 7, 'true', 'true', 2, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(28, '_TRANSACTION', '', 8, 'true', 'true', 2, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(31, '_WEBPAGES', '', 1, 'true', 'true', 3, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(32, '_NEWS', '', 2, 'true', 'true', 3, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(33, '_NEWS_INTERNAL', '', 3, 'true', 'true', 3, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(34, '_COMMUNICATION_MAN', '', 4, 'true', 'true', 3, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(35, '_NEWSLETTER', '', 5, 'true', 'true', 3, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(40, '_REPORT', '', 1, 'true', 'true', 4, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(50, '_FIELD_MANAGER', '', 4, 'true', 'true', 5, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(51, '_DASHBOARD', '', 1, 'true', 'true', 5, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(52, '_CONFIG_SYS', '', 2, 'true', 'true', 5, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(55, '_LANG', '', 5, 'true', 'true', 5, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(59, '_CONFIG_ELEARNING', '', 3, 'true', 'true', 5, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(151, '_ADMIN_RULES', '', 1, 'true', 'true', 15, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(152, '_ADMIN_MANAGER', '', 2, 'true', 'true', 15, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(211, '_COURSES', '', 1, 'true', 'true', 21, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(212, '_COURSEPATH', '', 2, 'true', 'true', 21, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(213, '_CATALOGUE', '', 3, 'true', 'true', 21, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(241, '_CERTIFICATE', '', 1, 'true', 'true', 24, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(242, '_META_CERTIFICATE', '', 2, 'true', 'true', 24, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(251, '_EVENTS', '', 1, 'true', 'true', 25, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(252, '_CATEGORY', '', 2, 'true', 'true', 25, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(253, '_RESERVATION', '', 3, 'true', 'true', 25, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(501, '_FIELD_MANAGER', '', 1, 'true', 'true', 50, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(502, '_CUSTOMFIELD_MANAGER', '', 2, 'true', 'true', 50, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(521, '_CONFIGURATION', '', 1, 'true', 'true', 52, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(523, '_EVENTMANAGER', '', 3, 'true', 'true', 52, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(524, '_IOTASK', '', 4, 'true', 'true', 52, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(526, '_PRIVACYPOLICIES', '', 6, 'true', 'true', 52, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(527, '_PLUGIN_MANAGER', '', 7, 'true', 'true', 52, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(528, '_CODE', '', 8, 'true', 'true', 52, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(591, '_MAN_MENU', '', 1, 'true', 'true', 59, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(592, '_MIDDLE_AREA', '', 2, 'true', 'true', 59, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(593, '_QUESTCATEGORY', '', 3, 'true', 'true', 59, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(594, '_TIME_PERIODS', '', 4, 'true', 'true', 59, NULL);
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`) VALUES(595, '_LABEL', '', 5, 'true', 'true', 59, NULL);


-- --------------------------------------------------------

--
-- Struttura della tabella `core_menu_under`
--

CREATE TABLE IF NOT EXISTS `core_menu_under` (
  `idUnder` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `module_name` varchar(255) DEFAULT NULL,
  `default_name` varchar(255) NOT NULL DEFAULT '',
  `default_op` varchar(255) DEFAULT NULL,
  `associated_token` varchar(255) DEFAULT NULL,
  `of_platform` varchar(255) DEFAULT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  `class_file` varchar(255) DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `mvc_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idUnder`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_menu_under`
--

INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(11, 11, 'usermanagement', '_LISTUSER', '', 'view', NULL, 1, '', '', 'adm/usermanagement/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(12, 12, 'groupmanagement', '_MANAGE_GROUPS', '', 'view', NULL, 1, '', '', 'adm/groupmanagement/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(13, 13, 'competences', '_COMPETENCES', '', 'view', NULL, 1, '', '', 'adm/competences/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(14, 14, 'functionalroles', '_FUNCTIONAL_ROLE', '', 'view', NULL, 4, '', '', 'adm/functionalroles/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(22, 22, 'location', '_LOCATION', '', 'view', 'lms', 2, '', '', 'alms/location/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(23, 23, 'games', '_CONTEST', '', 'view', 'lms', 3, '', '', 'alms/games/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(26, 26, 'kb', '_CONTENT_LIBRARY', '', 'view', 'lms', 6, '', '', 'alms/kb/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(27, 27, 'enrollrules', '_ENROLLRULES', '', 'view', 'lms', 7, '', '', 'alms/enrollrules/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(28, 28, 'transaction', '_TRANSACTION', '', 'view', 'lms', 8, '', '', 'alms/transaction/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(31, 31, 'webpages', '_WEBPAGES', 'webpages', 'view', 'lms', 1, 'class.webpages.php', 'Module_Webpages', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(32, 32, 'news', '_NEWS', 'news', 'view', 'lms', 2, 'class.news.php', 'Module_News', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(33, 33, 'internal_news', '_NEWS_INTERNAL', 'news', 'view', 'lms', 3, 'class.internal_news.php', 'Module_Internal_News', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(34, 34, 'communication', '_COMMUNICATION_MAN', '', 'view', 'lms', 1, '', '', 'alms/communication/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(35, 35, 'newsletter', '_NEWSLETTER', 'newsletter', 'view', 'framework', 1, 'class.newsletter.php', 'Module_Newsletter', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(40, 40, 'report', '_REPORT', 'reportlist', 'view', 'lms', 1, 'class.report.php', 'Module_Report', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(51, 51, 'dashboard', '_DASHBOARD', '', 'view', NULL, 1, '', '', 'adm/dashboard/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(55, 55, 'lang', '_LANG', '', 'view', NULL, 5, '', '', 'adm/lang/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(151, 151, 'adminrules', '_ADMIN_RULES', '', 'view', NULL, 1, '', '', 'adm/adminrules/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(152, 152, 'adminmanager', '_ADMIN_MANAGER', '', 'view', NULL, 1, '', '', 'adm/adminmanager/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(211, 211, 'course', '_COURSES', '', 'view', 'lms', 1, '', '', 'alms/course/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(212, 212, 'coursepath', '_COURSEPATH', 'pathlist', 'view', 'lms', 2, 'class.coursepath.php', 'Module_Coursepath', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(213, 213, 'catalogue', '_CATALOGUE', 'catlist', 'view', 'lms', 3, 'class.catalogue.php', 'Module_Catalogue', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(241, 241, 'certificate', '_CERTIFICATE', 'certificate', 'view', 'lms', 1, 'class.certificate.php', 'Module_Certificate', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(242, 242, 'meta_certificate', '_META_CERTIFICATE', 'meta_certificate', 'view', 'lms', 2, 'class.meta_certificate.php', 'Module_Meta_Certificate', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(251, 251, 'reservation', '_EVENTS', 'view_event', 'view', 'lms', 1, 'class.reservation.php', 'Module_Reservation', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(252, 252, 'reservation', '_CATEGORY', 'view_category', 'view', 'lms', 2, 'class.reservation.php', 'Module_Reservation', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(253, 253, 'reservation', '_RESERVATION', 'view_registration', 'view', 'lms', 3, 'class.reservation.php', 'Module_Reservation', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(501, 501, 'field_manager', '_FIELD_MANAGER', 'field_list', 'view', 'framework', 1, 'class.field_manager.php', 'Module_Field_Manager', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(502, 502, 'customfield_manager', '_CUSTOMFIELD_MANAGER', 'field_list', 'view', 'framework', 2, 'class.customfield_manager.php', 'Module_Customfield_Manager', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(521, 521, 'setting', '_CONFIGURATION', '', 'view', NULL, 1, 'class.configuration.php', 'Module_Configuration', 'adm/setting/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(523, 523, 'event_manager', '_EVENTMANAGER', 'display', 'view_event_manager', 'framework', 3, 'class.event_manager.php', 'Module_Event_Manager', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(524, 524, 'iotask', '_IOTASK', 'iotask', 'view', 'framework', 4, 'class.iotask.php', 'Module_IOTask', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(526, 526, 'privacypolicy', '_PRIVACYPOLICIES', '', 'view', NULL, 6, '', '', 'adm/privacypolicy/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(527, 527, 'pluginmanager', '_PLUGIN_MANAGER', '', 'view', NULL, 7, '', '', 'adm/pluginmanager/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(528, 528, 'code', '_CODE', 'list', 'view', 'framework', 8, 'class.code.php', 'Module_Code', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(591, 591, 'amanmenu', '_MAN_MENU', 'mancustom', 'view', 'lms', 1, 'class.amanmenu.php', 'Module_AManmenu', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(592, 592, 'middlearea', '_MIDDLE_AREA', 'view_area', 'view', 'lms', 2, 'class.middlearea.php', 'Module_MiddleArea', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(593, 593, 'questcategory', '_QUESTCATEGORY', '', 'view', 'lms', 3, '', '', 'alms/questcategory/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(594, 594, 'timeperiods', '_TIME_PERIODS', '', 'view', 'lms', 4, '', '', 'alms/timeperiods/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(595, 595, 'label', '_LABEL', '', 'view', 'lms', 5, '', '', 'alms/label/show');


-- --------------------------------------------------------



ALTER TABLE `learning_certificate_course` ADD `minutes_required` INT( 11 ) NOT NULL DEFAULT '0';

-- 10116_add_box_course_description
ALTER TABLE `learning_course` ADD `box_description` TEXT NOT NULL AFTER `name`;


DELETE FROM `learning_middlearea` WHERE `obj_index` = "tb_catalog";

DELETE FROM `learning_middlearea` WHERE `obj_index` = "tb_assessment";

-- 7996_custom_fields_mandatory_for_admin
INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES ('custom_fields_mandatory_for_admin', 'off', 'enum', '3', 'register', '3', '21', '1', '0', '');

INSERT IGNORE INTO `core_lang_text` (`text_key`, `text_module`, `text_attributes`) VALUES ('_CUSTOM_FIELDS_MANDATORY_FOR_ADMIN', 'configuration', '');

INSERT IGNORE INTO `core_lang_translation` (`id_text`, `lang_code`, `translation_text`, `save_date`) VALUES ((SELECT `id_text` FROM `core_lang_text` ORDER BY `id_text` desc LIMIT 1), 'italian', 'Campi supplementari obbligatori anche per gli admin', '2017-01-13 13:50:05');

-- 13719: added "tab" home feature
ALTER TABLE `learning_middlearea` ADD `is_home` TINYINT(4) NOT NULL DEFAULT '0' AFTER `sequence`;

UPDATE `learning_middlearea` SET `is_home` = '1' WHERE `learning_middlearea`.`obj_index` = 'tb_elearning';

-- 14537: added max threads and private_thread in forum e public forum
ALTER TABLE `learning_forum` ADD COLUMN `max_threads` int(11) NULL DEFAULT 0 AFTER `emoticons`, ADD COLUMN `threads_are_private` tinyint(1) NULL DEFAULT 0 AFTER `max_threads`;
-- ------------------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ------------------------------------------------------------------