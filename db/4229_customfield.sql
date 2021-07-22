
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

INSERT INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('COMPETENCE_CATEGORY', 'Competence Category', '%lms_competence_category', 'id_category');
INSERT INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('LO_TEST', 'Learning Object Test', '%lms_testquest', 'idQuest');

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


INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
VALUES(6, 3, 'customfield_manager', '_CUSTOMFIELD_MANAGER', 'field_list', 'view', NULL, 8, 'class.customfield_manager.php', 'Module_Customfield_Manager', '');


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CUSTOMFIELD_MANAGER', 'menu', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_CUSTOMFIELD_MANAGER' and text_module = 'menu'), 'english', 'Custom Field');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_CUSTOMFIELD_MANAGER' and text_module = 'menu'), 'italian', 'Campi Custom');


insert into core_role values(290, '/framework/admin/customfield_manager/view', '');
insert into core_role values(291, '/framework/admin/customfield_manager/add', '');
insert into core_role values(292, '/framework/admin/customfield_manager/mod', '');
insert into core_role values(293, '/framework/admin/customfield_manager/del', '');
insert into core_role_members values(290, 3);
insert into core_role_members values(291, 3);
insert into core_role_members values(292, 3);
insert into core_role_members values(293, 3);

-- ------------------
-- MENU CUSTOM FIELD
-- ------------------