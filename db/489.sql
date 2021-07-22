

INSERT INTO learning_middlearea (`obj_index`, `disabled`, `idst_list`, `sequence`) VALUES ('tb_home', '1', 'a:0:{}', '0');


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_HOME', 'middlearea', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_HOME' and text_module = 'middlearea'), 'english', 'Home');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_HOME' and text_module = 'middlearea'), 'italian', 'Home');
