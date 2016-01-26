INSERT INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_NO_AVAILABLE_EDITIONS', 'catalogue', '');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_NO_AVAILABLE_EDITIONS' and text_module = 'catalogue'), 'italian', 'Nessuna edizione disponibile');

