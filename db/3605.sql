-- AUTH EXTRA SETTINGS
-- ------------
-- rest_auth_api_key
-- rest_auth_api_secret

-- settings
INSERT INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('rest_auth_api_key', '', 'string', 255, 'api', 9, 7, 1, 0, '');
INSERT INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) VALUES ('rest_auth_api_secret', '', 'string', 255, 'api', 9, 8, 1, 0, '');

-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_REST_AUTH_API_KEY', 'configuration', '');
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_REST_AUTH_API_SECRET', 'configuration', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_REST_AUTH_API_KEY'), 'english', 'Api Key');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_REST_AUTH_API_KEY'), 'italian', 'Chiave Api');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_REST_AUTH_API_SECRET'), 'english', 'Api Secret');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_REST_AUTH_API_SECRET'), 'italian', 'Codice Segreto Api');

-- inserimento opzione di scelta chiave segreta in metodo di auth

-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_REST_AUTH_SECRET_KEY', 'configuration', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_REST_AUTH_SECRET_KEY'), 'english', 'Secret Key');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_REST_AUTH_SECRET_KEY'), 'italian', 'Chiave Segreta');
