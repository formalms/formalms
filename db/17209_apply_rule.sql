

insert ignore into core_lang_text (text_key, text_module, text_attributes) VALUES ('_APPLY_RULE', 'enrollrules', '');

insert ignore into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_APPLY_RULE' and text_module = 'enrollrules'), 'italian', 'Applica politica di iscrizione', now());

insert ignore into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_APPLY_RULE' and text_module = 'enrollrules'), 'english', 'Apply rule', now());
