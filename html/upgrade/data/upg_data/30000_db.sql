-- #FAY-44 migrate course info to mvc
UPDATE `learning_module`
SET `mvc_path` = 'lms/course/infocourse'
WHERE `learning_module`.`module_name` = "course"
  AND `learning_module`.`default_op` = "infocourse"

-- #19687 Languages - Increase text_key field lenght to 255
ALTER TABLE `core_lang_text`
    MODIFY COLUMN `text_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `id_text`



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
