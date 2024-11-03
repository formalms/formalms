--
-- Update database formalms
--
--
-- Update db script from forma 1.1 to forma 1.2
--

-- ------------------------------------------------------------------

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- ------------------------------------------------------------------

-- bug #491
INSERT IGNORE INTO core_event_class (idClass, class, platform, description) VALUES (19, 'UserCourseRemovedModerate', 'lms-a', '');
INSERT IGNORE INTO core_event_consumer_class (idConsumer, idClass) VALUES (1, 19);
INSERT IGNORE INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (19, 19, 'not_used', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin');

-- User waiting to be unsubscribed from a course
INSERT IGNORE INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserCourseRemovedModerate', 'event_manager', '');
-- INSERT IGNORE INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES
-- ( (SELECT lt.id_text from core_lang_text lt where lt.text_key = '_EVENT_CLASS_UserCourseRemovedModerate' AND lt.text_module = 'event_manager'), 'english', 'User waiting to be unsubscribed from a course', now());

-- User is waiting for course unsubscription approval
INSERT IGNORE INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_SUBJECT', 'email', '');
-- INSERT IGNORE INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES
-- ( (SELECT lt.id_text from core_lang_text lt where lt.text_key = '_NEW_USER_UNSUBS_WAITING_SUBJECT' AND lt.text_module = 'email'), 'english','User is waiting for course unsubscription approval', now());

-- New user unsubscribed to the '[course]' course and is waiting to be approved. <a href="[url]">[url]</a>
INSERT IGNORE INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_TEXT', 'email', '');
-- INSERT IGNORE INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES
-- ((SELECT lt.id_text from core_lang_text lt where lt.text_key = '_NEW_USER_UNSUBS_WAITING_TEXT' AND lt.text_module = 'email'), 'english','New user unsubscribed to the \'[course]\' course and is waiting to be approved.
--
-- <a href="[url]">[url]</a>', now());

INSERT IGNORE INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_SUBJECT_SMS', 'email', '');
-- INSERT IGNORE INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES
-- ( (SELECT lt.id_text from core_lang_text lt where lt.text_key = '_NEW_USER_UNSUBS_WAITING_SUBJECT_SMS' AND lt.text_module = 'email'), 'english','', now());

INSERT IGNORE INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_TEXT_SMS', 'email', '');
-- INSERT IGNORE INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES
-- ( (SELECT lt.id_text from core_lang_text lt where lt.text_key = '_NEW_USER_UNSUBS_WAITING_TEXT_SMS' AND lt.text_module = 'email'), 'english','', now() );


-- bug #2555
INSERT IGNORE INTO `learning_middlearea` (`obj_index`, `disabled`, `idst_list`, `sequence`) VALUES ('tb_kb', 0, 'a:0:{}', 0);

-- bug #1075
INSERT IGNORE INTO `core_event_manager` (`idEventMgr`, `idClass`, `permission`, `channel`, `recipients`, `show_level`) VALUES (41, 41, 'mandatory', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin');
INSERT IGNORE INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, 41);
INSERT IGNORE INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (41, 'UserNewWaiting', 'framework', '');

-- bug #1104
INSERT IGNORE INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_PROGRESS_COMPLETED', 'course', '');
-- INSERT IGNORE INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
-- SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
-- FROM core_lang_text lt , core_lang_language l ,
-- ( SELECT lt.id_text, t.lang_code, t.translation_text
--   FROM   core_lang_text lt, core_lang_translation t
--   WHERE t.id_text = lt.id_text AND lt.text_key ='_COMPLETED'  AND lt.text_module = 'standard'  ) t1
-- WHERE lt.text_key = '_PROGRESS_COMPLETED' AND lt.text_module = 'course' AND t1.lang_code = l.lang_code
-- ;

INSERT IGNORE INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_PROGRESS_INCOMPLETE', 'course', '');
-- INSERT IGNORE INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
-- SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
-- FROM core_lang_text lt , core_lang_language l ,
-- ( SELECT lt.id_text, t.lang_code, t.translation_text
--   FROM   core_lang_text lt, core_lang_translation t
--   WHERE t.id_text = lt.id_text AND lt.text_key ='incomplete'  AND lt.text_module = 'standard'  ) t1
-- WHERE lt.text_key = '_PROGRESS_INCOMPLETE' AND lt.text_module = 'course' AND t1.lang_code = l.lang_code
-- ;

INSERT IGNORE INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_PROGRESS_PASSED', 'course', '');
-- INSERT IGNORE INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
-- SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
-- FROM core_lang_text lt , core_lang_language l ,
-- ( SELECT lt.id_text, t.lang_code, t.translation_text
--   FROM   core_lang_text lt, core_lang_translation t
--   WHERE t.id_text = lt.id_text AND lt.text_key ='_PASSED'  AND lt.text_module = 'coursereport'  ) t1
-- WHERE lt.text_key = '_PROGRESS_PASSED' AND lt.text_module = 'course' AND t1.lang_code = l.lang_code
-- ;

-- feature #3057
-- settings
INSERT IGNORE INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('bbb_server', 'http://test-install.blindsidenetworks.com/bigbluebutton/', 'string', 255, 'bbb', 6, 1, 1, 0, '');
INSERT IGNORE INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('bbb_user', '', 'string', 255, 'bbb', 6, 2, 1, 0, '');
INSERT IGNORE INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('bbb_salt', 'to be changed with a complex string', 'string', 255, 'bbb', 6, 3, 1, 0, '');
INSERT IGNORE INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('bbb_password_moderator', 'password.moderator', 'string', 255, 'bbb', 6, 4, 1, 0, '');
INSERT IGNORE INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('bbb_password_viewer', 'password.viewer', 'string', 255, 'bbb', 6, 5, 1, 0, '');
INSERT IGNORE INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('bbb_max_mikes', '2', 'string', 255, 'bbb', 6, 6, 1, 0, '');
INSERT IGNORE INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('bbb_max_participant', '300', 'string', 255, 'bbb', 6, 7, 1, 0, '');
INSERT IGNORE INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('bbb_max_room', '999', 'string', 255, 'bbb', 6, 8, 1, 0, '');

-- label
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_BBB', 'standard', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_BBB_SERVER', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_BBB_USER', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_BBB_SALT', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_BBB_PASSWORD_MODERATOR', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_BBB_PASSWORD_VIEWER', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_BBB_MAX_MIKES', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_BBB_MAX_PARTICIPANT', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_BBB_MAX_ROOM', 'configuration', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB'), 'english', 'Big Blue Button');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_SERVER'), 'english', 'Server address BigBlueButton');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_USER'), 'english', 'Username BigBlueButton');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_SALT'), 'english', 'Salt BigBlueButton');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_PASSWORD_MODERATOR'), 'english', 'Moderator Password BigBlueButton');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_PASSWORD_VIEWER'), 'english', 'Viewer Password BigBlueButton');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_PASSWORD_VIEWER'), 'english', 'Password Partecipante BigBlueButton');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_MIKES'), 'english', 'Max Audio users');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_PARTICIPANT'), 'english', 'Max users per room');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_ROOM'), 'english', 'Max rooms');

-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB'), 'italian', 'Big Blue Button');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_SERVER'), 'italian', 'Indirizzo server BigBlueButton');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_USER'), 'italian', 'Nome Utente BigBlueButton');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_SALT'), 'italian', 'Salt BigBlueButton');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_PASSWORD_MODERATOR'), 'italian', 'Password Moderatore BigBlueButton');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_MIKES'), 'italian', 'Massimo utenti Audio');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_PARTICIPANT'), 'italian', 'Massimo utenti per stanza');
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_ROOM'), 'italian', 'Numero massimo di stanze');


-- ------------------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ------------------------------------------------------------------


