INSERT INTO `forma_20_purple`.`core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) 
VALUES ('orgchart_singlenode', 'off', 'enum', '3', '0', '3', '21', '1', '0', '');

-- label _ORGCHART_SINGLENODE
INSERT INTO core_lang_text(text_key, text_module, text_attributes) values ('_ORGCHART_SINGLENODE', 'configuration', '');

-- translation _ORGCHART_SINGLENODE
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ORGCHART_SINGLENODE' and text_module = 'configuration'), 'italian', "Limita l'utente ad un solo nodo dell'organigramma");
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ORGCHART_SINGLENODE' and text_module = 'configuration'), 'english', 'Limit the user to only one node of the organization chart');

-- label _ALREADY_ASSIGNED
INSERT INTO core_lang_text(text_key, text_module, text_attributes) values ('_ALREADY_ASSIGNED', 'admin_directory', '');

-- translation _ALREADY_ASSIGNED
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ALREADY_ASSIGNED' and text_module = 'admin_directory'), 'italian', 'già assegnato');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ALREADY_ASSIGNED' and text_module = 'admin_directory'), 'english', 'already assigned');

-- label _USERS_ALREADY_ASSIGNED
INSERT INTO core_lang_text(text_key, text_module, text_attributes) values ('_USERS_ALREADY_ASSIGNED', 'admin_directory', '');

-- translation _USERS_ALREADY_ASSIGNED
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_USERS_ALREADY_ASSIGNED' and text_module = 'admin_directory'), 'italian', 'utenti già assegnati');
insert into core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_USERS_ALREADY_ASSIGNED' and text_module = 'admin_directory'), 'english', 'already assigned users');