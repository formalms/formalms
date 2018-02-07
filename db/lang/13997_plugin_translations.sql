-- text_key: '_PLUGIN', text_module: 'admin_lang'
INSERT INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_PLUGIN', 'admin_lang', '');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_PLUGIN' and text_module = 'admin_lang'), 'english', 'Plugin');

-- text_key: '_PLUGIN_NAME', text_module: 'admin_lang'
INSERT INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_PLUGIN_NAME', 'admin_lang', '');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_PLUGIN_NAME' and text_module = 'admin_lang'), 'english', 'Plugin name');