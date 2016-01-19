-- label _ENTITY
INSERT INTO core_lang_text(text_key, text_module, text_attributes) values ('_ENTITY', 'enrollrules', '');

-- translation _ENTITY
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ENTITY' and text_module = 'enrollrules'), 'italian', 'Entità');