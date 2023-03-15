-- # 14042
insert into core_lang_text (text_key, text_module, text_attributes) VALUES ('_HIDE_EMPTY_CATEGORY', 'configuration', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_HIDE_EMPTY_CATEGORY' and text_module = 'configuration'), 'italian', 'Nascondi categorie vuote', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_HIDE_EMPTY_CATEGORY' and text_module = 'configuration'), 'english', 'Hide empty category', now());
