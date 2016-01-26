UPDATE core_lang_translation 
SET translation_text = 'Iscrizione permessa dal' 
WHERE id_text = 
	(SELECT id_text
        FROM core_lang_text
        WHERE text_key = '_SUBSCRIPTION_DATE_BEGIN'
        AND text_module = 'course')
AND lang_code = 'italian';



UPDATE core_lang_translation 
SET translation_text = 'Iscrizione permessa al' 
WHERE id_text = 
	(SELECT id_text
        FROM core_lang_text
        WHERE text_key = '_SUBSCRIPTION_DATE_END'
        AND text_module = 'course')
AND lang_code = 'italian';



INSERT INTO core_lang_text(text_key, text_module, text_attributes) VALUES ('_CALENDAR_CLASSROOM_EDITION', 'course', '');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_CALENDAR_CLASSROOM_EDITION' and text_module = 'course'), 'italian', 'Calendario edizione aula');

