
-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_OPENSSL', 'configuration', '');

-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_OPENSSL'), 'english', 'Php extension php_openssl', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_OPENSSL'), 'italian', 'Estensione php_openssl di php', now() );



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ALLOW_URL_FOPEN', 'configuration', '');

-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_ALLOW_URL_FOPEN'), 'english', 'Configuration of "allow_url_fopen"', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_ALLOW_URL_FOPEN'), 'italian', 'Configurazione di "allow_url_fopen"', now() );



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_WARINNG_SOCIAL', 'configuration', '');

-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WARINNG_SOCIAL'), 'english', 'Attention without these settings the social login will not work', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WARINNG_SOCIAL'), 'italian', 'Attenzione senza questi settaggi la login social non funzioner&agrave;', now() );


INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date)
SELECT clt.id_text, cll.lang_code, 'Attention without these settings the social login will not work', now()
FROM   core_lang_text clt, core_lang_language cll
WHERE  text_key = '_WARINNG_SOCIAL' and lang_code not in ('english','italian');

