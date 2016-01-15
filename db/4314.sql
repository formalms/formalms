-- label _DATE_COMPLETE
INSERT INTO core_lang_text(text_key, text_module, text_attributes) values ('_DATE_COMPLETE', 'subscribe', '');

-- translation _DATE_COMPLETE
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_DATE_COMPLETE' and text_module = 'subscribe'), 'italian', 'Data completamento');