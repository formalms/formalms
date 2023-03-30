--
-- Update database formalms
--
--
-- Update db script from forma 1.0 to forma 1.1
--

-- ------------------------------------------------------------------

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- ------------------------------------------------------------------

-- for missing update from dce 405  to forma 1.0

-- maintenance mode settings
INSERT IGNORE INTO `core_setting`
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`) VALUES
('maintenance', 'off', 'enum', 3, 'security', 8, 25);

INSERT IGNORE INTO `core_setting`
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`) VALUES
('maintenance_pw', 'manutenzione', 'string', 3, 'security', 8, 25);

-- new field type
INSERT IGNORE INTO `core_field_type` (`type_field`, `type_file`, `type_class`) VALUES
('textlabel', 'class.label.php', 'Field_Textlabel');

-- update key language
UPDATE IGNORE `core_lang_text` SET `text_key`= '_IMPORT_NOTHINGTOPROCESS' WHERE `text_key` = '_DOCEBO_IMPORT_NOTHINGTOPROCESS';

-- -----------
-- update from 1.0 to 1.1

CREATE TABLE IF NOT EXISTS `learning_htmlpage_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idpage` int(11) unsigned NOT NULL,
  `file` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
--
-- -----------

-- ALTER TABLE `learning_middlearea` ADD `sequence` INT( 5 ) NOT NULL;
-- executed in 10100_pre.php to check if already applied


INSERT IGNORE INTO `learning_middlearea` (`obj_index`, `disabled`, `idst_list`, `sequence`) VALUES
('tb_elearning', 0, 'a:0:{}', 0);

-- Handle missing values in upgrade from D3.6.x
INSERT IGNORE INTO `learning_middlearea` (`obj_index`, `disabled`, `idst_list`, `sequence`) VALUES
('tb_assessment', 0, 'a:0:{}', 0),
('tb_catalog', 0, 'a:0:{}', 0),
('tb_classroom', 0, 'a:0:{}', 0),
('tb_coursepath', 0, 'a:0:{}', 0);


-- -----------

INSERT IGNORE INTO `learning_menu_under`
(`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(8, 1, 'coursecategory', '_COURSECATEGORY', '', 'view', NULL, 4, '', '', 'alms/coursecategory/show');

INSERT IGNORE INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_COURSECATEGORY', 'standard', '');

-- INSERT IGNORE INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` ) VALUES
-- ((SELECT lt.id_text from core_lang_text lt where lt.text_key = '_COURSECATEGORY' AND lt.text_module = 'standard'), 'english', 'Course categories', now());

INSERT IGNORE INTO `core_role` (`idst`, `roleid`, `description`) VALUES (228, '/lms/admin/coursecategory/add', NULL);
INSERT IGNORE INTO `core_role` (`idst`, `roleid`, `description`) VALUES (229, '/lms/admin/coursecategory/mod', NULL);
INSERT IGNORE INTO `core_role` (`idst`, `roleid`, `description`) VALUES (230, '/lms/admin/coursecategory/del', NULL);

INSERT IGNORE INTO core_role_members (idst, idstMember)
SELECT 228 as idst, idst as idstMember
FROM core_group
WHERE (groupid like "/framework/level/godadmin%"
OR     groupid like "/framework/level/publicadmin%"
OR     groupid like "/framework/adminrules%"
OR     groupid like "/framework/publicadminrules%" );


INSERT IGNORE INTO core_role_members (idst, idstMember)
SELECT 229 as idst, idst as idstMember
FROM core_group
WHERE (groupid like "/framework/level/godadmin%"
OR     groupid like "/framework/level/publicadmin%"
OR     groupid like "/framework/adminrules%"
OR     groupid like "/framework/publicadminrules%" );


INSERT IGNORE INTO core_role_members (idst, idstMember)
SELECT 230 as idst, idst as idstMember
FROM core_group
WHERE (groupid like "/framework/level/godadmin%"
OR     groupid like "/framework/level/publicadmin%"
OR     groupid like "/framework/adminrules%"
OR     groupid like "/framework/publicadminrules%" );



-- ------------------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ------------------------------------------------------------------
