INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES ('custom_fields_mandatory_for_admin', 'off', 'enum', '3', 'register', '3', '21', '1', '0', '');

INSERT IGNORE INTO `core_lang_text` (`text_key`, `text_module`, `text_attributes`) VALUES ('_CUSTOM_FIELDS_MANDATORY_FOR_ADMIN', 'configuration', '');

INSERT IGNORE INTO `core_lang_translation` (`id_text`, `lang_code`, `translation_text`, `save_date`) VALUES ((SELECT `id_text` FROM `core_lang_text` ORDER BY `id_text` desc LIMIT 1), 'italian', 'Campi supplementari obbligatori anche per gli admin', '2017-01-13 13:50:05');