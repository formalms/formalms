#15346
INSERT INTO `core_lang_text` (`id_text`, `text_key`, `text_module`, `text_attributes`) VALUES (NULL, '_ALL_COURSE_TYPE', 'course', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_ALL_COURSE_TYPE' and text_module = 'course'), 'italian', 'Tutti i tipi di corso', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_ALL_COURSE_TYPE' and text_module = 'course'), 'english', 'All courses type', now());



INSERT INTO `core_lang_text` (`id_text`, `text_key`, `text_module`, `text_attributes`) VALUES (NULL, '_ALL_YEARS', 'course', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_ALL_YEARS' and text_module = 'course'), 'italian', 'Tutti gli anni', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_ALL_YEARS' and text_module = 'course'), 'english', 'All years', now());




INSERT INTO `core_lang_text` (`id_text`, `text_key`, `text_module`, `text_attributes`) VALUES (NULL, '_NO_COURSE_DATA', 'course', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_NO_COURSE_DATA' and text_module = 'course'), 'italian', 'Senza data di inizio', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_NO_COURSE_DATA' and text_module = 'course'), 'english', 'No course data', now());

