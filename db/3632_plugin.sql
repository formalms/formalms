-- ---------------
-- PLUGIN MANAGER
-- ---------------


INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
VALUES(9, 3, 'pluginmanager', '_PLUGIN_MANAGER', '', 'view', NULL, 7, '', '', 'adm/pluginmanager/show');


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_PLUGIN_MANAGER', 'menu', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_PLUGIN_MANAGER' and text_module = 'menu'), 'english', 'Plugin Manager');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_PLUGIN_MANAGER' and text_module = 'menu'), 'italian', 'Plugin');


insert into core_role values(280, '/framework/admin/pluginmanager/view', '');
insert into core_role_members values(280, 3);

CREATE TABLE IF NOT EXISTS `core_plugin` (
  `plugin_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(64) NOT NULL,
  `code` varchar(32) NOT NULL,
  `category` VARCHAR(255),
  `version` varchar(16) NOT NULL,
  `author` varchar(128) NOT NULL,
  `link` varchar(255) NOT NULL,
  `priority` int(5) NOT NULL,
  `description` text NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`plugin_id`),
  UNIQUE KEY `name` (`name`,`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
