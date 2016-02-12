INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ASSIGN_FOR_AT_LEAST_MINUTES', 'course', '');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ASSIGN_FOR_AT_LEAST_MINUTES' and text_module = 'course'), 'italian', 'Tempo di fruizione minimo per l''assegnazione (min.)');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ASSIGN_FOR_AT_LEAST_MINUTES' and text_module = 'course'), 'english', 'Minimum time of fruition for the assignment (min.)');