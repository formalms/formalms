--
-- Update database formalms
--
--
-- Update db script from docebo ce 4.0.5 to forma 1.0
--

-- maintenance mode settings
INSERT INTO `core_setting`
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`) VALUES
('maintenance', 'off', 'enum', 3, 'security', 8, 25);

INSERT INTO `core_setting`
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`) VALUES
('maintenance_pw', 'manutenzione', 'string', 3, 'security', 8, 25);

-- new field type
INSERT INTO `core_field_type` (`type_field`, `type_file`, `type_class`) VALUES
('textlabel', 'class.label.php', 'Field_Textlabel');

-- update key language
UPDATE `core_lang_text` SET `text_key`= '_IMPORT_NOTHINGTOPROCESS' WHERE `text_key` = '_DOCEBO_IMPORT_NOTHINGTOPROCESS';
