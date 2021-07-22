ALTER TABLE `learning_coursereport` ADD COLUMN `show_in_detail` tinyint(1) NULL DEFAULT 1;
ALTER TABLE `learning_test` DROP COLUMN `show_in_coursereport`;

INSERT IGNORE INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_SHOW_IN_DETAIL', 'test', '');
INSERT IGNORE INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
  SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
  FROM core_lang_text lt , core_lang_language l ,
    ( SELECT lt.id_text, t.lang_code, t.translation_text
      FROM   core_lang_text lt, core_lang_translation t
      WHERE t.id_text = lt.id_text AND lt.text_key ='_SHOW_IN_DETAIL'  AND lt.text_module = 'test'  ) t1
  WHERE lt.text_key = '_SHOW_IN_DETAIL' AND lt.text_module = 'test' AND t1.lang_code = l.lang_code
;

INSERT IGNORE INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_OVERVIEW', 'coursereport', '');
INSERT IGNORE INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
  SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
  FROM core_lang_text lt , core_lang_language l ,
    ( SELECT lt.id_text, t.lang_code, t.translation_text
      FROM   core_lang_text lt, core_lang_translation t
      WHERE t.id_text = lt.id_text AND lt.text_key ='_OVERVIEW'  AND lt.text_module = 'coursereport'  ) t1
  WHERE lt.text_key = '_OVERVIEW' AND lt.text_module = 'coursereport' AND t1.lang_code = l.lang_code
;

INSERT IGNORE INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_DETAILS', 'coursereport', '');
INSERT IGNORE INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
  SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
  FROM core_lang_text lt , core_lang_language l ,
    ( SELECT lt.id_text, t.lang_code, t.translation_text
      FROM   core_lang_text lt, core_lang_translation t
      WHERE t.id_text = lt.id_text AND lt.text_key ='_DETAILS'  AND lt.text_module = 'coursereport'  ) t1
  WHERE lt.text_key = '_DETAILS' AND lt.text_module = 'coursereport' AND t1.lang_code = l.lang_code
;
