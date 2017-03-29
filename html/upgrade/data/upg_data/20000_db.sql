--
-- Update database formalms
--
--
-- Update db script from formalms 1.3 to formalms 1.4
--

-- ------------------------------------------------------------------

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- ------------------------------------------------------------------


-- #3640 password sha256

ALTER TABLE `core_user` CHANGE `pass` `pass` VARCHAR(60) NOT NULL;


-- #4229 custom fields

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

INSERT ignore INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('COMPETENCE_CATEGORY', 'Competence Category', '%lms_competence_category', 'id_category');
INSERT ignore INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('LO_TEST', 'Learning Object Test', '%lms_testquest', 'idQuest');

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

INSERT INTO `core_customfield_type` (`type_field`, `type_file`, `type_class`, `type_category`) VALUES('textfield', 'class.textfield.php', 'Field_Textfield', 'standard');
INSERT INTO `core_customfield_type` (`type_field`, `type_file`, `type_class`, `type_category`) VALUES('dropdown', 'class.dropdown.php', 'Field_Dropdown', 'standard');


-- ------------------
-- MENU CUSTOM FIELD
-- ------------------


INSERT ignore INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
VALUES(6, 3, 'customfield_manager', '_CUSTOMFIELD_MANAGER', 'field_list', 'view', NULL, 8, 'class.customfield_manager.php', 'Module_Customfield_Manager', '');


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CUSTOMFIELD_MANAGER', 'menu', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_CUSTOMFIELD_MANAGER' and text_module = 'menu'), 'english', 'Custom Field');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_CUSTOMFIELD_MANAGER' and text_module = 'menu'), 'italian', 'Campi Custom');


insert ignore into core_role values(290, '/framework/admin/customfield_manager/view', '');
insert ignore  into core_role values(291, '/framework/admin/customfield_manager/add', '');
insert ignore  into core_role values(292, '/framework/admin/customfield_manager/mod', '');
insert ignore  into core_role values(293, '/framework/admin/customfield_manager/del', '');
insert ignore  into core_role_members values(290, 3);
insert ignore  into core_role_members values(291, 3);
insert ignore  into core_role_members values(292, 3);
insert ignore  into core_role_members values(293, 3);

INSERT ignore  INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('COURSE', 'Course', '%lms_course', 'idCourse');
INSERT ignore  INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('COURSE_EDITION', 'Course Edition', '%lms_course_editions', 'id_edition');

-- ------------------
-- TAB learning_test
-- ------------------

ALTER TABLE  `learning_test` ADD  `cf_info` TEXT NOT NULL AFTER  `order_info`;


-- #4232

UPDATE core_setting
SET
param_value = 'exe,rar,zip,jpg,gif,png,txt,csv,rtf,xml,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,pdf,xps,mp4,mp3,flv,swf,mov,wav,ogg,flac,wma,wmv'
WHERE
param_name = 'file_upload_whitelist';


-- #4299


-- Evento UserNewApi

INSERT  ignore INTO `core_event_class` (`class`, `platform`, `description`) VALUES ('UserNewApi', 'framework', '');
set @v_idst=LAST_INSERT_ID();

INSERT ignore  INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, @v_idst);

INSERT ignore  INTO `core_event_manager` (`idClass`, `permission`, `channel`, `recipients`, `show_level`)
VALUES
(@v_idst, 'mandatory', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user');


-- Evento UserNewApi

INSERT ignore  INTO `core_event_class` (`class`, `platform`, `description`) VALUES ('UserCourseInsertedApi', 'lms-a', '');
set @v_idst=LAST_INSERT_ID();

INSERT ignore  INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, @v_idst);

INSERT ignore  INTO `core_event_manager` (`idClass`, `permission`, `channel`, `recipients`, `show_level`)
VALUES
(@v_idst, 'mandatory', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user');


-- #4301 forum private thread

ALTER TABLE `learning_forumthread` ADD COLUMN `privateThread` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rilevantForum`;


-- #4303 Creazione permesso view_all per modulo repository

insert ignore  into core_st(idst) values(null);

set @v_idst=LAST_INSERT_ID();

insert into core_role(idst, roleid, description) VALUES
(@v_idst, concat('/lms/course/private/light_repo/view_all'), NULL);


-- #4375 adding catalogue link onto the top menu

insert ignore  into  learning_module (module_name, default_name, token_associated,module_info, mvc_path ) values ('course','_CATALOGUE','view','all','lms/catalog/show');
insert ignore    into learning_middlearea (obj_index,disabled,idst_list,sequence)  values ('mo_46',0,'a:0:{}',0);
insert ignore  into learning_menucourse_under (idCourse, idModule, idMain,sequence) values (0,46,0,3);

-- #4447

ALTER TABLE learning_course
 ADD id_menucustom INT(11);
ALTER TABLE learning_course
 ADD CONSTRAINT fk_menucustom FOREIGN KEY (id_menucustom) REFERENCES learning_menucustom (idCustom) ON UPDATE CASCADE ON DELETE NO ACTION;

-- #4559 delete public admins

DELETE FROM `core_menu_under` WHERE `module_name` = "publicadminrules";
DELETE FROM `core_menu_under` WHERE `module_name` = "publicadminmanager";


DELETE FROM `learning_module` WHERE `module_name` = "pusermanagement";
DELETE FROM `learning_module` WHERE `module_name` = "pcourse";
DELETE FROM `learning_module` WHERE `module_name` = "public_report_admin";
DELETE FROM `learning_module` WHERE `module_name` = "public_newsletter_admin";
DELETE FROM `learning_module` WHERE `module_name` = "pcertificate";


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


-- #4560

DELETE from learning_module WHERE module_name = "coursecharts";

DELETE from core_role WHERE roleid like "%coursecharts%";


-- #4687

DELETE FROM core_setting WHERE param_name = 'forum_as_table';


-- #6364

DROP TABLE IF EXISTS core_db_upgrades;
CREATE TABLE core_db_upgrades (
 script_id int(11) NOT NULL AUTO_INCREMENT,
 script_name varchar(255) NOT NULL,
 script_description text,
 script_version varchar(255),
 core_version varchar(255),
 creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 execution_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 PRIMARY KEY (script_id)
);

insert into core_db_upgrades (script_name, script_description, script_version, core_version, creation_date, execution_date) values ('add_log_db_upgrades.sql', 'Creazione tabella di log per script update db', '1.0', (SELECT param_value FROM core_setting WHERE param_name LIKE 'core_version'), now(), now())
ON DUPLICATE KEY UPDATE
execution_date=now();


-- #8996 add simpleadmin orgchart

insert ignore  into core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
insert ignore  into core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/usermanagement/add_org'), NULL);

insert ignore  into core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
insert ignore  into core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/usermanagement/mod_org'), NULL);

insert ignore  into core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
insert  ignore into core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/usermanagement/del_org'), NULL);


-- #9004 course edition closing

ALTER TABLE  `learning_course_editions` CHANGE  `date_end`  `date_end` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00';


-- #9497 import ucfirst

INSERT  ignore INTO `core_setting`
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES
('import_ucfirst', 'on', 'enum', 3, '0', 3, 22, 1, 0, '');


-- #9593

INSERT ignore  INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES ('custom_fields_mandatory_for_admin', 'off', 'enum', '3', 'register', '3', '21', '1', '0', '');


-- nuovi_tipi_di_test.sql

ALTER TABLE `learning_organization_access` ADD COLUMN `params` VARCHAR(255) NULL COMMENT '' AFTER `value`;
ALTER TABLE `learning_test` ADD COLUMN `obj_type` VARCHAR(45) NULL DEFAULT 'test' COMMENT '' AFTER `score_max`;


-- coursereport_mvc.sql

UPDATE `learning_module` SET `mvc_path` = 'lms/coursereport/coursereport' WHERE `learning_module`.`module_name` = "coursereport" AND `learning_module`.`default_op` = "coursereport";


-- retain_answers_history.sql

ALTER TABLE learning_testtrack_answer ADD COLUMN number_time TINYINT(4) NULL DEFAULT '1' COMMENT '' AFTER user_answer;

ALTER TABLE learning_test ADD COLUMN retain_answers_history TINYINT(1) NOT NULL DEFAULT '0' COMMENT '' AFTER obj_type;

ALTER TABLE learning_testtrack_answer
CHANGE COLUMN number_time number_time TINYINT(4) NOT NULL DEFAULT '1' COMMENT '' ,
DROP PRIMARY KEY,
ADD PRIMARY KEY (idTrack, idQuest, idAnswer, number_time)  COMMENT '';


-- plugin system

ALTER TABLE `core_plugin`  ADD `regroup` INT(11) NOT NULL  AFTER `description`;

ALTER TABLE `core_setting` CHANGE `regroup` `regroup` INT(11) NOT NULL DEFAULT '0';


ALTER TABLE `core_plugin` ADD `core` BIT(1) NOT NULL DEFAULT b'0' AFTER `active`;

ALTER TABLE `core_plugin` DROP COLUMN `code`;

TRUNCATE `learning_report`;
ALTER TABLE `learning_report` DROP COLUMN `file_name`;
TRUNCATE `learning_report_filter`;
-- Insert new report
INSERT ignore  INTO `learning_report` (`id_report`, `report_name`, `class_name`, `use_user_selection`, `enabled`) VALUES (NULL, 'user_report', 'report_user', 'true', '1'), (NULL, 'course_report', 'report_course', 'true', '1'), (NULL, 'aggregate_report', 'report_aggregate', 'true', '1');
-- Insert new report_filters
INSERT  ignore INTO `learning_report_filter` (`id_filter`, `id_report`, `author`, `creation_date`, `filter_name`, `filter_data`, `is_public`, `views`) VALUES (NULL, '2', '270', '0000-00-00 00:00:00', 'Courses - Users', 'a:5:{s:9:"id_report";s:1:"4";s:11:"report_name";s:15:"Courses - Users";s:11:"rows_filter";a:2:{s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}}s:23:"columns_filter_category";s:5:"users";s:14:"columns_filter";a:6:{s:9:"time_belt";a:3:{s:10:"time_range";s:1:"0";s:10:"start_date";s:0:"";s:8:"end_date";s:0:"";}s:21:"org_chart_subdivision";i:0;s:11:"showed_cols";a:7:{i:0;s:12:"_CODE_COURSE";i:1;s:12:"_NAME_COURSE";i:2;s:6:"_INSCR";i:3;s:10:"_MUSTBEGIN";i:4;s:18:"_USER_STATUS_BEGIN";i:5;s:15:"_COMPLETECOURSE";i:6;s:14:"_TOTAL_SESSION";}s:12:"show_percent";b:1;s:9:"all_users";b:1;s:5:"users";a:0:{}}}', '1', '10'), (NULL, '1', '270', '0000-00-00 00:00:00', 'Users - Courses', 'a:5:{s:9:"id_report";s:1:"2";s:11:"report_name";s:15:"Users - Courses";s:11:"rows_filter";a:2:{s:5:"users";a:0:{}s:9:"all_users";b:1;}s:23:"columns_filter_category";s:7:"courses";s:14:"columns_filter";a:7:{s:21:"org_chart_subdivision";i:0;s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}s:11:"sub_filters";a:0:{}s:16:"filter_exclusive";s:1:"1";s:14:"showed_columns";a:12:{i:0;s:8:"_TH_CODE";i:1;s:25:"_TH_USER_INSCRIPTION_DATE";i:2;s:19:"_TH_USER_START_DATE";i:3;s:17:"_TH_USER_END_DATE";i:4;s:20:"_TH_LAST_ACCESS_DATE";i:5;s:15:"_TH_USER_STATUS";i:6;s:20:"_TH_USER_START_SCORE";i:7;s:20:"_TH_USER_FINAL_SCORE";i:8;s:21:"_TH_USER_COURSE_SCORE";i:9;s:23:"_TH_USER_NUMBER_SESSION";i:10;s:21:"_TH_USER_ELAPSED_TIME";i:11;s:18:"_TH_ESTIMATED_TIME";}s:13:"custom_fields";a:0:{}}}', '1', '3'), (NULL, '1', '270', '0000-00-00 00:00:00', 'Users - Learning Objects', 'a:5:{s:9:"id_report";s:1:"2";s:11:"report_name";s:24:"Users - Learning Objects";s:11:"rows_filter";a:2:{s:5:"users";a:0:{}s:9:"all_users";b:1;}s:23:"columns_filter_category";s:2:"LO";s:14:"columns_filter";a:6:{s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}s:8:"lo_types";a:8:{s:3:"faq";s:3:"faq";s:8:"glossary";s:8:"glossary";s:8:"htmlpage";s:8:"htmlpage";s:4:"item";s:4:"item";s:4:"link";s:4:"link";s:4:"poll";s:4:"poll";s:8:"scormorg";s:8:"scormorg";s:4:"test";s:4:"test";}s:13:"lo_milestones";a:0:{}s:14:"showed_columns";a:8:{i:0;s:9:"user_name";i:1;s:11:"course_name";i:2;s:13:"course_status";i:3;s:7:"lo_type";i:4;s:7:"lo_name";i:5;s:12:"firstAttempt";i:6;s:11:"lastAttempt";i:7;s:9:"lo_status";}s:13:"custom_fields";a:0:{}}}', '1', '0'), (NULL, '1', '270', '0000-00-00 00:00:00', 'Users - 30 Days Delay', 'a:5:{s:9:"id_report";s:1:"2";s:11:"report_name";s:21:"Users - 30 Days Delay";s:11:"rows_filter";a:2:{s:5:"users";a:0:{}s:9:"all_users";b:1;}s:23:"columns_filter_category";s:5:"delay";s:14:"columns_filter";a:9:{s:21:"report_type_completed";b:1;s:19:"report_type_started";b:1;s:21:"day_from_subscription";s:2:"30";s:20:"day_until_course_end";s:0:"";s:21:"date_until_course_end";s:0:"";s:21:"org_chart_subdivision";i:0;s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}s:14:"showed_columns";a:7:{i:0;s:9:"_LASTNAME";i:1;s:5:"_NAME";i:2;s:7:"_STATUS";i:3;s:6:"_EMAIL";i:4;s:11:"_DATE_INSCR";i:5;s:18:"_DATE_FIRST_ACCESS";i:6;s:22:"_DATE_COURSE_COMPLETED";}}}', '1', '0');

INSERT ignore  INTO `core_plugin` (`name`, `title`, `category`, `version`, `author`, `link`, `priority`, `description`, `regroup`, `active`, `core`) VALUES('FormaAuth', 'Forma Auth', '', '1.0', 'Joint Technologies', '', 0, 'forma auth', 1488290190, 1, b'1');

CREATE TABLE `core_requests` (
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


-- ------------------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ------------------------------------------------------------------


