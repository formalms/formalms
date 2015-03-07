
-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_WITH_COURSE_ASSOCIATIONS', 'competences', '');

-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_COURSE_ASSOCIATIONS'), 'english', 'Competence with courses associated', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_COURSE_ASSOCIATIONS'), 'italian', 'Competenza con corsi associati', now() );



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_WITH_FNCROLE_ASSOCIATIONS', 'competences', '');

-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_FNCROLE_ASSOCIATIONS'), 'english', 'Competence with roles associated', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_FNCROLE_ASSOCIATIONS'), 'italian', 'Competenza con ruoli associati', now() );



-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_WITH_USER_ASSOCIATIONS', 'competences', '');

-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_USER_ASSOCIATIONS'), 'english', 'Competence with users associated', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_USER_ASSOCIATIONS'), 'italian', 'Competenza con utenti associati', now() );
