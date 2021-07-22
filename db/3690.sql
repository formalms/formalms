-- label
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_MAINTENANCE_TEXT', 'login', '');

-- translation
INSERT IGNORE INTO core_lang_translation( id_text, lang_code, translation_text, save_date )
SELECT clt.id_text, cll.lang_code,
        'System in mainteinance mode.<br/><br/>To change these text please go to Admin/Language management, search for login and edit the key _MAINTENANCE_TEXT'
        , now()
FROM   core_lang_text clt, core_lang_language cll
WHERE  text_key = '_MAINTENANCE_TEXT' and lang_code in ('english');

INSERT IGNORE INTO core_lang_translation( id_text, lang_code, translation_text, save_date )
SELECT clt.id_text, cll.lang_code,
       'Sistema in manutenzione.<br/><br/>Per cambiare questa frase andare su Admin/Gestione lingue, cercare il modulo login e modificare la chiave _MAINTENANCE_TEXT'
       , now()
FROM   core_lang_text clt, core_lang_language cll
WHERE  text_key = '_MAINTENANCE_TEXT' and lang_code in ('italian');

INSERT IGNORE INTO core_lang_translation( id_text, lang_code, translation_text, save_date )
SELECT clt.id_text, cll.lang_code,
       'System in mainteinance mode.<br/><br/>To change these text please go to Admin/Language management, search for login and edit the key _MAINTENANCE_TEXT'
       , now()
FROM   core_lang_text clt, core_lang_language cll
WHERE  text_key = '_MAINTENANCE_TEXT' and lang_code not in ('english','italian');

