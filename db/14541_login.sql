insert into core_lang_text (text_key, text_module, text_attributes) VALUES ('_INTRO_STD_TEXT_TITLE', 'login', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_INTRO_STD_TEXT_TITLE' and text_module = 'login'), 'italian', 'forma.lms è un sistema open source per l\'e-learning (LMS e LCMS) utilizzato nelle più grandi organizzazioni e università.', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_INTRO_STD_TEXT_TITLE' and text_module = 'login'), 'english', 'forma.lms is an Open Source e-learning platform (LMS and LCMS) used in corporate and higher education markets', now());


insert into core_lang_text (text_key, text_module, text_attributes) VALUES ('_READ_ALL', 'login', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_READ_ALL' and text_module = 'login'), 'italian', 'Leggi tutto', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_READ_ALL' and text_module = 'login'), 'english', 'Read all', now());
