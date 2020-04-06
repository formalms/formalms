-- #FAY-44 migrate course info to mvc
UPDATE `learning_module`
SET `mvc_path` = 'lms/course/infocourse'
WHERE `learning_module`.`module_name` = "course"
  AND `learning_module`.`default_op` = "infocourse";

-- #19687 Languages - Increase text_key field lenght to 255
ALTER TABLE `core_lang_text`
    MODIFY COLUMN `text_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `id_text`;



CREATE TABLE IF NOT EXISTS `dashboard_block_config`
(
    `id`           bigint(20)   NOT NULL AUTO_INCREMENT,
    `block_class`  varchar(255) NOT NULL,
    `block_config` text         NOT NULL,
    `position`     bigint(20)   NOT NULL DEFAULT '999',
    PRIMARY KEY (`id`),
    KEY `block_class_idx` (`block_class`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


CREATE TABLE IF NOT EXISTS `dashboard_blocks`
(
    `id`          bigint(20)   NOT NULL AUTO_INCREMENT,
    `block_class` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `block_class_unique` (`block_class`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


INSERT INTO `dashboard_blocks` (`id`, `block_class`)
VALUES (7, 'DashboardBlockCalendarLms'),
       (3, 'DashboardBlockCertificatesLms'),
       (6, 'DashboardBlockCourseAdviceLms'),
       (5, 'DashboardBlockCoursesLms'),
       (4, 'DashboardBlockMessagesLms'),
       (2, 'DashboardBlockProfileLms'),
       (8, 'DashboardBlockVideoLms'),
       (1, 'DashboardBlockWelcomeLms');


INSERT INTO learning_middlearea (`obj_index`, `disabled`, `idst_list`, `sequence`)
VALUES ('tb_dashboard', '1', 'a:0:{}', '0');


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes)
VALUES ('_DASHBOARD', 'middlearea', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text)
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_DASHBOARD ' and text_module = ' middlearea '),
        'english', 'Dashboard');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text)
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_DASHBOARD ' and text_module = ' middlearea '),
        'italian', 'Dashboard');

INSERT INTO `learning_module`
VALUES (47, 'dashboard', 'show', '_DASHBOARD', 'view', '', '', 'all', 'lms/dashboard/show');

SET @max = (SELECT MAX(idMenu) + 1
            FROM `core_menu`);

INSERT INTO `core_menu`(`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`)
VALUES (@max, '_DASHBOARD', '', 4, 'true', 'true', NULL, NULL, 'lms');

INSERT INTO `core_menu_under`(`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`,
                              `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
VALUES (@max, @max, 'course', '_DASHBOARD', NULL, 'view', 'lms', 4, NULL, NULL, 'lms/dashboard/show');

SET @max = (SELECT MAX(idMenu) + 1
            FROM `core_menu`);

INSERT INTO `core_menu`(`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`)
VALUES (@max, '_DASHBOARD_CONFIGURATION', '', 4, 'true', 'true', '5', NULL, 'framework');

INSERT INTO `core_menu_under`(`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`,
                              `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
VALUES (@max, @max, 'dashboardsettings', '_DASHBOARD_CONFIGURATION', '', 'view', 'framework', 1, '', '',
        'adm/dashboardsettings/show');