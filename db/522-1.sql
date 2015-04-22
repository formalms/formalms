INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
VALUES ('course_block', 'off', 'enum', 3, 0, 4, 5, 1, 0, '');

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_COURSE_BLOCK', 'configuration', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_COURSE_BLOCK'), 'english', 'Show catalogue in home page', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_COURSE_BLOCK'), 'italian', 'Attiva elenco corsi in home page', now() );
