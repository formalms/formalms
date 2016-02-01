INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CERTIFICATES_GENERATION', 'certificate', '');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_CERTIFICATES_GENERATION' and text_module = 'certificate'), 'italian', 'Generazione certificati');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_CERTIFICATES_GENERATION' and text_module = 'certificate'), 'english', 'Certificates generation');

UPDATE core_lang_translation 
SET translation_text = 'Progresso' 
WHERE id_text = 
	(SELECT id_text
        FROM core_lang_text
        WHERE text_key = '_PROGRESS'
        AND text_module = 'standard')
AND lang_code = 'italian';