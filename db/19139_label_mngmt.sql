-- deleting from middle area
DELETE FROM `learning_middlearea` WHERE `learning_middlearea`.`obj_index` = 'tb_label';

-- adding to setting 
INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) 
VALUES ('use_course_label', 'off', 'enum', '3', '0', '4', '7', '1', '0', '');


-- TRANSLATION
insert into core_lang_text (text_key, text_module, text_attributes) VALUES ('_USE_COURSE_LABEL', 'configuration', '');
insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_USE_COURSE_LABEL' and text_module = 'configuration'), 'italian', 'Utilizza etichette nei corsi', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_USE_COURSE_LABEL' and text_module = 'configuration'), 'english', 'Use label for courses', now());