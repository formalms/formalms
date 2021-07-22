-- OWNED BY
-- ------------

-- settings
INSERT INTO core_setting(param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
VALUES ('owned_by', 'Copyright &copy; forma.lms', 'string', 255, '0', 1, 7, 1, 0, '');

-- label
INSERT INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_OWNED_BY', 'configuration', '');

-- translation
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_OWNED_BY'), 'english', 'Owned by');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_OWNED_BY'), 'italian', 'Owned by');
