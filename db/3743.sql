
-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_WITH_COURSE_ASSOCIATIONS', 'competences', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_WITH_COURSE_ASSOCIATIONS'), 'english', 'Competence with courses associated');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_WITH_COURSE_ASSOCIATIONS'), 'italian', 'Competenza con corsi associati');



-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_WITH_FNCROLE_ASSOCIATIONS', 'competences', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_WITH_FNCROLE_ASSOCIATIONS'), 'english', 'Competence with roles associated');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_WITH_FNCROLE_ASSOCIATIONS'), 'italian', 'Competenza con ruoli associati');



-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_WITH_USER_ASSOCIATIONS', 'competences', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_WITH_USER_ASSOCIATIONS'), 'english', 'Competence with users associated');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_WITH_USER_ASSOCIATIONS'), 'italian', 'Competenza con utenti associati');
