UPDATE core_lang_translation 
SET translation_text = 'Quota parte pagamento anticipato' 
WHERE id_text = 
	(SELECT id_text
        FROM core_lang_text
        WHERE text_key = '_COURSE_ADVANCE'
        AND text_module = 'course')
AND lang_code = 'italian';