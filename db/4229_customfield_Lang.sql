
-- ------------------
--        LABEL      
-- ------------------

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ADD_NEW_CUSTOMFIELD', 'field', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_ADD_NEW_CUSTOMFIELD' and text_module = 'field'), 'italian', 'Aggiungi un nuovo Campo Custom');


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_RU_CAT_TESTSTAT', 'report', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_RU_CAT_TESTSTAT' and text_module = 'report'), 'italian', 'Relaziona gli utenti ai Test');



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_EXISTING_TEST', 'test', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_EXISTING_TEST' and text_module = 'test'), 'italian', 'In test esistente');



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SELECTTEST', 'storage', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_SELECTTEST' and text_module = 'storage'), 'italian', 'Seleziona il test:');


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_EXPORT_QUESTIONS', 'storage', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) VALUES ((SELECT id_text FROM core_lang_text where text_key = '_EXPORT_QUESTIONS' and text_module = 'storage'), 'italian', 'Esportazione domande');
