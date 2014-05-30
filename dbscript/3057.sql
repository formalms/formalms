-- BBB SETTINGS
-- ------------
-- bbb_server
-- bbb_user
-- bbb_salt
-- bbb_password_moderator
-- bbb_password_viewer
-- bbb_max_mikes
-- bbb_max_participant
-- bbb_max_room

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
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_BBB', 'standard', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_BBB_SERVER', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_BBB_USER', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_BBB_SALT', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_BBB_PASSWORD_MODERATOR', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_BBB_PASSWORD_VIEWER', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_BBB_MAX_MIKES', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_BBB_MAX_PARTICIPANT', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_BBB_MAX_ROOM', 'configuration', '');

-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB'), 'english', 'Big Blue Button');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB'), 'italian', 'Big Blue Button');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_SERVER'), 'english', 'Server address BigBlueButton');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_SERVER'), 'italian', 'Indirizzo server BigBlueButton');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_USER'), 'english', 'Username BigBlueButton');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_USER'), 'italian', 'Nome Utente BigBlueButton');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_SALT'), 'english', 'Salt BigBlueButton');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_SALT'), 'italian', 'Salt BigBlueButton');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_PASSWORD_MODERATOR'), 'english', 'Moderator Password BigBlueButton');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_PASSWORD_MODERATOR'), 'italian', 'Password Moderatore BigBlueButton');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_PASSWORD_VIEWER'), 'english', 'Viewer Password BigBlueButton');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_PASSWORD_VIEWER'), 'italian', 'Password Partecipante BigBlueButton');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_MIKES'), 'english', 'Max Audio users');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_MIKES'), 'italian', 'Massimo utenti Audio');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_PARTICIPANT'), 'english', 'Max users per room');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_PARTICIPANT'), 'italian', 'Massimo utenti per stanza');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_ROOM'), 'english', 'Max rooms');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_BBB_MAX_ROOM'), 'italian', 'Numero massimo di stanze');


