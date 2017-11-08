-- # 14427
insert into core_lang_text (text_key, text_module, text_attributes) VALUES ('_NO_EMAIL_CONFIG', 'standard', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_NO_EMAIL_CONFIG' and text_module = 'standard'), 'italian', 'Invio email non configurato', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_NO_EMAIL_CONFIG' and text_module = 'standard'), 'english', 'Email not configured', now());


insert into core_lang_text (text_key, text_module, text_attributes) VALUES ('_SEND_CC', 'standard', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_SEND_CC' and text_module = 'standard'), 'italian', 'Invio una copia per conoscenza', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_SEND_CC' and text_module = 'standard'), 'english', 'send a copy for knowledge', now());