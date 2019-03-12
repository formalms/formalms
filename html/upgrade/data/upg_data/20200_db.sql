--
-- Update database formalms
--
--
-- Update db script from formalms 2.1 to formalms 2.2
--

-- ------------------------------------------------------------------

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- ------------------------------------------------------------------

-- Task #19104: refactoring menu frontend

ALTER TABLE `core_menu` ADD COLUMN `of_platform` VARCHAR(255) NOT NULL DEFAULT 'framework';

UPDATE `core_menu_under` SET `of_platform` = 'framework' WHERE `of_platform` IS NULL;
SET @alms_exists = (SELECT COUNT(*) > 0 FROM `core_menu_under` WHERE `of_platform` = 'alms');
UPDATE `core_menu_under` SET `of_platform` = 'alms' WHERE `of_platform` = 'lms' AND NOT @alms_exists;

INSERT IGNORE INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(596, '_MYCOURSES', '', 1, 'true', 'true', NULL, NULL, 'lms');
INSERT IGNORE INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(597, '_CATALOGUE', '', 2, 'false', 'true', NULL, NULL, 'lms');
INSERT IGNORE INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(598, '_PUBLIC_FORUM', '', 3, 'true', 'true', NULL, NULL, 'lms');
INSERT IGNORE INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(599, '_HELPDESK', '<span class="glyphicon glyphicon-question-sign top-menu__label"></span>', 1000, 'false', 'true', NULL, NULL, 'lms');

INSERT IGNORE INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(596, 596, 'course', '_MYCOURSES', NULL, 'view', 'lms', 1, NULL, NULL, 'lms/mycourses/show');
INSERT IGNORE INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(597, 597, 'coursecatalogue', '_CATALOGUE', NULL, 'view', 'lms', 2, NULL, NULL, 'lms/catalog/show');
INSERT IGNORE INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(598, 598, 'public_forum', '_PUBLIC_FORUM', 'forum', 'view', 'lms', 3, NULL, NULL, NULL);
INSERT IGNORE INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(599, 599, 'helpdesk', '_HELPDESK', 'popup', 'view', 'lms', 1000, NULL, NULL, NULL);

INSERT IGNORE INTO `core_role` (`idst`, `roleid`, `description`) VALUES(300, '/lms/course/public/helpdesk/view', NULL);
INSERT IGNORE INTO `core_role_members` (`idst`, `idstMember`) VALUES(300, 1);

-- Task #19101: custom field in Learning Object

INSERT IGNORE INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('LO_OBJECT', 'Learning Object', 'learning_organization', 'idOrg');

-- ------------------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Task #19101: custom field in Learning Object

DELETE FROM `learning_middlearea` WHERE `learning_middlearea`.`obj_index` = 'tb_label';


INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) 
VALUES ('use_course_label', 'off', 'enum', '3', 'main', '4', '7', '1', '0', '');



-- ------------------------------------------------------------------

-- Task #10896 - #14000: role plug-in reference

DELETE rm
FROM core_role_members rm
LEFT JOIN core_role r ON rm.idst = r.idst
WHERE r.idst IS NULL;

ALTER TABLE core_role
ADD COLUMN idPlugin INT(10) NULL,
ADD CONSTRAINT FOREIGN KEY (idPlugin) REFERENCES core_plugin(plugin_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE core_role_members
ADD CONSTRAINT FOREIGN KEY (idst) REFERENCES core_role(idst) ON DELETE CASCADE ON UPDATE CASCADE;

-- ------------------------------------------------------------------
