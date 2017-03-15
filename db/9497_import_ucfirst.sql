-- setting
INSERT INTO `core_setting` 
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) 
VALUES
('import_ucfirst', 'on', 'enum', 3, '0', 3, 22, 1, 0, '');

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_IMPORT_UCFIRST', 'configuration', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_IMPORT_UCFIRST' AND text_module = 'configuration'), 'english', 'Import user width function ucfirst()', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_IMPORT_UCFIRST' AND text_module = 'configuration'), 'italian', 'Importa utenti con funzione ucfirst()', now() );
