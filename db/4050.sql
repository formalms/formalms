
-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_OPENSSL', 'configuration', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_OPENSSL'), 'english', 'Php extension php_openssl');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_OPENSSL'), 'italian', 'Estensione php_openssl di php');



-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_ALLOW_URL_FOPEN', 'configuration', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ALLOW_URL_FOPEN'), 'english', 'Configuration of "allow_url_fopen"');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ALLOW_URL_FOPEN'), 'italian', 'Configurazione di "allow_url_fopen"');



-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_WARINNG_SOCIAL', 'configuration', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_WARINNG_SOCIAL'), 'english', 'Attention without these settings the social login will not work');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_WARINNG_SOCIAL'), 'italian', 'Attenzione senza questi settaggi la login social non funzioner&agrave;');
