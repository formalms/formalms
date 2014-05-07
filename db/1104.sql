
INSERT INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_PROGRESS_COMPLETED', 'course', '');
INSERT INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
FROM core_lang_text lt , core_lang_language l ,
( SELECT lt.id_text, t.lang_code, t.translation_text
  FROM   core_lang_text lt, core_lang_translation t
  WHERE t.id_text = lt.id_text AND lt.text_key ='_COMPLETED'  AND lt.text_module = 'standard'  ) t1
WHERE lt.text_key = '_PROGRESS_COMPLETED' AND lt.text_module = 'course' AND t1.lang_code = l.lang_code
;

INSERT INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_PROGRESS_INCOMPLETE', 'course', '');
INSERT INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
FROM core_lang_text lt , core_lang_language l ,
( SELECT lt.id_text, t.lang_code, t.translation_text
  FROM   core_lang_text lt, core_lang_translation t
  WHERE t.id_text = lt.id_text AND lt.text_key ='incomplete'  AND lt.text_module = 'standard'  ) t1
WHERE lt.text_key = '_PROGRESS_INCOMPLETE' AND lt.text_module = 'course' AND t1.lang_code = l.lang_code
;

INSERT INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_PROGRESS_PASSED', 'course', '');
INSERT INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
FROM core_lang_text lt , core_lang_language l ,
( SELECT lt.id_text, t.lang_code, t.translation_text
  FROM   core_lang_text lt, core_lang_translation t
  WHERE t.id_text = lt.id_text AND lt.text_key ='_PASSED'  AND lt.text_module = 'coursereport'  ) t1
WHERE lt.text_key = '_PROGRESS_PASSED' AND lt.text_module = 'course' AND t1.lang_code = l.lang_code
;
