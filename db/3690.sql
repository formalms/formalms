-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_MAINTENANCE_TEXT', 'login', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_MAINTENANCE_TEXT'), 'english', 'Mainteinance mode: To change these words please go to Admin/Language management, search for login and edit the key _MAINTENANCE_TEXT');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_MAINTENANCE_TEXT'), 'italian', 'In Manutenzione: Per cambiare questa frase andare su Admin/Gestione lingue, cercare per LMS/login e modificare la chiave _MAINTENANCE_TEXT');