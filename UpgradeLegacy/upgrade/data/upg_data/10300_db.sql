--
-- Update database formalms
--
--
-- Update db script from formalms 1.2 to formalms 1.3
--

-- ------------------------------------------------------------------

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- ------------------------------------------------------------------

-- new feature #489
INSERT IGNORE INTO learning_middlearea (`obj_index`, `disabled`, `idst_list`, `sequence`) VALUES ('tb_home', '1', 'a:0:{}', '0');

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_HOME', 'middlearea', '');

-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_HOME' and text_module = 'middlearea'), 'english', 'Home');

-- -----------------

-- bug #3605
-- settings
INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
       VALUES ('rest_auth_api_key', '', 'string', 255, 'api', 9, 7, 1, 0, '');
INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
       VALUES ('rest_auth_api_secret', '', 'string', 255, 'api', 9, 8, 1, 0, '');

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_REST_AUTH_API_KEY', 'configuration', '');
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_REST_AUTH_API_SECRET', 'configuration', '');
-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_REST_AUTH_SECRET_KEY', 'configuration', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_REST_AUTH_API_KEY'), 'english', 'Api Key');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_REST_AUTH_API_SECRET'), 'english', 'Api Secret');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_REST_AUTH_SECRET_KEY'), 'english', 'Secret Key');

-- -----------------

-- new feature #3620
-- settings
INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
       VALUES ('owned_by', 'Copyright (c) forma.lms', 'html', 255, '0', 1, 7, 1, 0, '');

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_OWNED_BY', 'configuration', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_OWNED_BY'), 'english', 'Owned by');

-- ----------------

-- new feature #3632
INSERT IGNORE INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
       VALUES (9, 3, 'pluginmanager', '_PLUGIN_MANAGER', '', 'view', NULL, 7, '', '', 'adm/pluginmanager/show');

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_PLUGIN_MANAGER', 'menu', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_PLUGIN_MANAGER' and text_module = 'menu'), 'english', 'Plugin Manager');

INSERT IGNORE INTO core_role VALUES (280, '/framework/admin/pluginmanager/view', '');
INSERT IGNORE INTO core_role_members VALUES (280, 3);

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


-- -----------------

-- bug #3057
-- settings
INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('bbb_port', '', 'string', 255, 'bbb', 6, 1, 1, 0, '');

-- label
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_BBB_PORT', 'configuration', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_PORT'), 'english', 'Server port BigBlueButton');

--
-- ------------
-- create indexes  lack

ALTER TABLE learning_certificate_assign ADD INDEX `id_course` ( `id_course` );
ALTER TABLE learning_certificate_assign ADD INDEX `id_user` ( `id_user` ) ;

-- ------------------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ------------------------------------------------------------------


