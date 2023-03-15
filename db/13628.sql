-- # 13628
insert into core_lang_text (text_key, text_module, text_attributes) VALUES ('_NO_CATEGORY_TODISPLAY', 'catalogue', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_NO_CATEGORY_TODISPLAY' and text_module = 'catalogue'), 'italian', 'Nessuna categoria da mostrare', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_NO_CATEGORY_TODISPLAY' and text_module = 'catalogue'), 'english', 'No category to display', now());
