-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_VIEW_ALL', 'standard', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_VIEW_ALL'), 'english', 'View All');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_VIEW_ALL'), 'italian', 'Visualizza tutti');

