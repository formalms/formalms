CREATE TABLE dashboard_permission ( id_dashboard INT NOT NULL , idst_list TEXT NOT NULL ); 

-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

-- #FAY-44 migrate course info to mvc
UPDATE `learning_module`
SET `mvc_path` = 'lms/course/infocourse'
WHERE `learning_module`.`module_name` = "course"
  AND `learning_module`.`default_op` = "infocourse";

-- #19687 Languages - Increase text_key field lenght to 255
ALTER TABLE `core_lang_text`
    MODIFY COLUMN `text_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `id_text`;


CREATE TABLE IF NOT EXISTS `dashboard_layouts`
(
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `caption` varchar(255) NOT NULL,
    `status` varchar(255) NOT NULL,
    `default` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp DEFAULT '0000-00-00 00:00:00',
    `updated_at` timestamp NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `name_idx`(`name`) USING BTREE,
    INDEX `status_idx`(`status`) USING BTREE
) ENGINE = InnoDB
 DEFAULT CHARSET = utf8;    
 
 
 CREATE TABLE IF NOT EXISTS  `dashboard_permission` 
(
  `id_dashboard` int(11) NOT NULL,
  `idst_list` text NOT NULL,
  PRIMARY KEY (`id_dashboard`)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8;    

CREATE TABLE IF NOT EXISTS `dashboard_block_config`
(
    `id`           bigint(20)   NOT NULL AUTO_INCREMENT,
    `block_class`  varchar(255) NOT NULL,
    `block_config` text         NOT NULL,
    `position`     bigint(20)   NOT NULL DEFAULT '999',
    `dashboard_id` bigint(20) NOT NULL,
    `created_at` timestamp DEFAULT '0000-00-00 00:00:00',
    `updated_at` timestamp NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `block_class_idx` (`block_class`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

ALTER TABLE `dashboard_block_config`
    ADD CONSTRAINT `config_layout_fk` FOREIGN KEY (`dashboard_id`) REFERENCES `dashboard_layouts` (`id`) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `dashboard_blocks`
(
    `id`          bigint(20)   NOT NULL AUTO_INCREMENT,
    `block_class` varchar(255) NOT NULL,
    `created_at` timestamp DEFAULT '0000-00-00 00:00:00',
    `updated_at` timestamp NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `block_class_unique` (`block_class`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


INSERT IGNORE INTO `dashboard_blocks` (`id`, `block_class`, `created_at`)
VALUES (7, 'DashboardBlockCalendarLms', CURRENT_TIMESTAMP),
       (3, 'DashboardBlockCertificatesLms',CURRENT_TIMESTAMP),
       (6, 'DashboardBlockAnnouncementsLms',CURRENT_TIMESTAMP),
       (5, 'DashboardBlockCoursesLms',CURRENT_TIMESTAMP),
       (4, 'DashboardBlockMessagesLms',CURRENT_TIMESTAMP),
       (8, 'DashboardBlockBannerLms',CURRENT_TIMESTAMP),
       (9, 'DashboardBlockWelcomeLms',CURRENT_TIMESTAMP);

INSERT IGNORE INTO learning_middlearea (`obj_index`, `disabled`, `idst_list`, `sequence`)
VALUES ('tb_dashboard', '1', 'a:0:{}', '0');


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes)
VALUES ('_DASHBOARD', 'middlearea', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date)
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_DASHBOARD' and text_module = 'middlearea'),
        'english', 'Dashboard', NOW());
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date)
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_DASHBOARD' and text_module = 'middlearea'),
        'italian', 'Dashboard', NOW());

SET @max = (SELECT MAX(idModule) + 1
            FROM `learning_module`);

INSERT IGNORE INTO `learning_module`
VALUES (@max, 'dashboard', 'show', '_DASHBOARD', 'view', '', '', 'all', 'lms/dashboard/show');

SET @max = (SELECT MAX(idMenu) + 1
            FROM `core_menu`);

INSERT IGNORE INTO `core_menu`(`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`)
VALUES (@max, '_DASHBOARD', '', 4, 'false', 'true', NULL, NULL, 'lms');

INSERT IGNORE INTO `core_menu_under`(`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`,
                              `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
VALUES (@max, @max, 'course', '_DASHBOARD', NULL, 'view', 'lms', 4, NULL, NULL, 'lms/dashboard/show');

SET @max = (SELECT MAX(idMenu) + 1
            FROM `core_menu`);

INSERT IGNORE INTO `core_menu`(`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`)
VALUES (@max, '_DASHBOARD_CONFIGURATION', '', 4, 'true', 'true', '5', NULL, 'framework');

INSERT IGNORE INTO `core_menu_under`(`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`,
                              `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
VALUES (@max, @max, 'dashboardsettings', '_DASHBOARD_CONFIGURATION', '', 'view', 'framework', 1, '', '',
        'adm/dashboardsettings/show');

--
-- Aggregated certificate refactoring MVC
-- 
UPDATE core_menu_under 
SET  	default_op = '',
		class_file = '',
		class_name = '',
		mvc_path = 'alms/aggregatedcertificate/show'
WHERE module_name = 'meta_certificate';



CREATE TABLE IF NOT EXISTS `learning_aggregated_cert_metadata` (
  `idAssociation` int(11) NOT NULL AUTO_INCREMENT,
  `idCertificate` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
   PRIMARY KEY (`idAssociation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
INSERT IGNORE INTO `learning_aggregated_cert_metadata` (`idCertificate`, `title`, `description` ) 
SELECT `idCertificate`, `title`, `description` from `learning_certificate_meta`;

CREATE TABLE IF NOT EXISTS `learning_aggregated_cert_assign` (
  `idUser` int(11) NOT NULL DEFAULT 0,
  `idCertificate` int(11) NOT NULL DEFAULT 0,
  `idAssociation` int(11) NOT NULL,
  `on_date` datetime DEFAULT NULL,
  `cert_file` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idUser`,`idCertificate`,`idAssociation`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `learning_aggregated_cert_assign` (`idUser`, `idAssociation`, `idCertificate`, `on_date`, `cert_file`  ) 
SELECT learning_certificate_assign.id_user, learning_certificate_meta.idMetaCertificate, learning_certificate_meta.idCertificate,
    learning_certificate_assign.on_date, learning_certificate_assign.cert_file from `learning_certificate_meta`, `learning_certificate_assign` where
    learning_certificate_assign.id_Certificate = learning_certificate_meta.idCertificate;

  
CREATE TABLE IF NOT EXISTS `learning_aggregated_cert_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idAssociation` int(11) NOT NULL DEFAULT 0,
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `idCourseEdition` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idAssociation` (`idAssociation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  
INSERT IGNORE INTO `learning_aggregated_cert_course` (`idAssociation`, `idUser`,  `idCourse`, `idCourseEdition`  ) 
SELECT `idMetaCertificate`, `idUser`, `idCourse`, `idCourseEdition`  from learning_certificate_meta_course;    
  
DELETE FROM learning_aggregated_cert_course WHERE idUser = 0; 
INSERT IGNORE INTO `learning_aggregated_cert_course` (idAssociation, idUser, idCourse, idCourseEdition)  
SELECT idAssociation, 0 as idUser, idCourse, idCourseEdition  FROM `learning_aggregated_cert_course` group by idAssociation,idCourse,idCourseEdition;


CREATE TABLE IF NOT EXISTS `learning_aggregated_cert_coursepath` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idAssociation` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCoursePath` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idAssociation` (`idAssociation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

UPDATE `core_setting` SET `value_type` = 'template_domain_node' WHERE `core_setting`.`param_name` = 'template_domain';


-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


-- delete menu chat
delete from learning_module where module_name like 'chat';
DELETE FROM `learning_quest_type` WHERE `type_quest` = 'hot_text';

-- add property ignorescore in scorm
ALTER TABLE `learning_organization` ADD `ignoreScore` TINYINT( 4 ) NOT NULL DEFAULT '0';

-- setting ignore_score
INSERT INTO `core_setting` 
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) 
VALUES
('ignore_score', 'off', 'enum', 3, '0', 4, 16, 1, 0, '');

-- setting email
UPDATE `core_setting` SET `pack` = 'email_settings', `regroup` = 1, `sequence` = 1  WHERE `param_name` = 'sender_event';

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES
	('use_sender_aclname', '', 'string', 255, 'email_settings', 1, 2, 1, 0, '');

UPDATE `core_setting` SET `pack` = 'email_settings', `regroup` = 1, `sequence` = 3 WHERE `param_name` = 'mail_sender';

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES
	('mail_sender_name_from', 'NameFrom RegPsw', 'string', 255, 'email_settings', 1, 4, 0, 0, '');

-- helpdesk
UPDATE `core_setting` SET `pack` = 'helpdesk', `regroup` = 1, `sequence` = 1 WHERE `param_name` = 'customer_help_name';

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES
	('customer_help_name_from', '', 'string', 255, 'helpdesk', 1, 2, 1, 0, '');

UPDATE `core_setting` SET `pack` = 'helpdesk', `regroup` = 1, `sequence` = 3 WHERE `param_name` = 'customer_help_subj_pfx';

-- email_settings_cc
UPDATE `core_setting` SET `pack` = 'email_settings_cc', `regroup` = 1, `sequence` = 1 WHERE `param_name` = 'send_cc_for_system_emails';

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES
	('send_ccn_for_system_emails', '', 'string', 255, 'email_settings_cc', 1, 2, 1, 0, '');


-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


-- new LO menu
UPDATE `learning_module` SET `mvc_path` = 'lms/lomanager/show' WHERE `learning_module`.`module_name` = 'storage';
UPDATE `learning_module` SET `mvc_path` = 'lms/lo/show' WHERE `learning_module`.`module_name` = 'organization';


-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


SET @max = ((SELECT MAX( idClass )+1 FROM core_event_class));

INSERT INTO core_event_class ( idClass, class, platform, description )
VALUES 
(@max, 'UserCourseInsertOverbooking', 'lms-a', 'A user requests subscription to course that has set overbooking');

INSERT IGNORE INTO core_event_consumer_class (idConsumer, idClass) VALUES (1, @max);

INSERT INTO `core_event_manager` (`idClass`, `permission`, `channel`, `recipients`, `show_level`) VALUES ( @max, 'mandatory', 'email', '_EVENT_RECIPIENTS_TEACHER', 'godadmin,admin,user');


-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


ALTER TABLE `learning_course_date` ADD COLUMN `calendarId` varchar(255) NOT NULL;
ALTER TABLE `learning_course_editions` ADD COLUMN `calendarId` varchar(255) NOT NULL;

-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

ALTER TABLE `learning_course_date_day` ROW_FORMAT=DYNAMIC;
ALTER TABLE `learning_course_date_day`
    ADD COLUMN `id` int(11) NOT NULL AUTO_INCREMENT FIRST,
    ADD COLUMN `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP AFTER `pause_end`,
    ADD COLUMN `updated_at` datetime NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    ADD COLUMN `calendarId` varchar(255) NOT NULL AFTER `pause_end`,
    ADD COLUMN `deleted` tinyint(1) NULL DEFAULT 0 AFTER `calendarId`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`) USING BTREE,
    DROP INDEX `id_date`,
    ADD INDEX `id_day_date`(`id_day`, `id_date`) USING BTREE;

ALTER TABLE `learning_course` ROW_FORMAT=DYNAMIC;
ALTER TABLE `learning_course` ADD COLUMN `sendCalendar` tinyint(1) NULL DEFAULT 0;
ALTER TABLE `learning_course` ADD COLUMN `calendarId` varchar(255) NOT NULL AFTER `sendCalendar`;