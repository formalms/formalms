
DROP TABLE IF EXISTS core_db_upgrades;
CREATE TABLE core_db_upgrades (
 script_id int(11) NOT NULL AUTO_INCREMENT,
 script_name varchar(255) NOT NULL,
 script_description text,
 script_version varchar(255),
 core_version varchar(255),
 creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 execution_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 PRIMARY KEY (script_id)
);


insert into core_db_upgrades (script_name, script_description, script_version, core_version, creation_date, execution_date) values ('add_log_db_upgrades.sql', 'Creazione tabella di log per script update db', '1.0', (SELECT param_value FROM core_setting WHERE param_name LIKE 'core_version'), now(), now())
ON DUPLICATE KEY UPDATE 
execution_date=now();


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_DB_UPGRADES', 'dashboard', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_DB_UPGRADES' AND text_module = 'dashboard'), 'english', 'Log script SQL', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_DB_UPGRADES' AND text_module = 'dashboard'), 'italian', 'Log script SQL', now() );


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_LIST_DB_UPGRADES', 'configuration', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_LIST_DB_UPGRADES' AND text_module = 'configuration'), 'english', 'List DB Upgrades', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_LIST_DB_UPGRADES' AND text_module = 'configuration'), 'italian', 'Log script SQL eseguiti sul DB', now() );


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SCRIPT_ID', 'standard', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SCRIPT_ID' AND text_module = 'standard'), 'english', 'Script Id', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SCRIPT_ID' AND text_module = 'standard'), 'italian', 'Id Script', now() );


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SCRIPT_NAME', 'standard', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SCRIPT_NAME' AND text_module = 'standard'), 'english', 'Script name', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SCRIPT_NAME' AND text_module = 'standard'), 'italian', 'Nome Script', now() );


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SCRIPT_DESCRIPTION', 'standard', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SCRIPT_DESCRIPTION' AND text_module = 'standard'), 'english', 'Script description', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SCRIPT_DESCRIPTION' AND text_module = 'standard'), 'italian', 'Descrizione Script', now() );


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SCRIPT_VERSION', 'standard', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SCRIPT_VERSION' AND text_module = 'standard'), 'english', 'Script Version', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SCRIPT_VERSION' AND text_module = 'standard'), 'italian', 'Versione Script', now() );


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CORE_VERSION', 'standard', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_CORE_VERSION' AND text_module = 'standard'), 'english', 'Core Version', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_CORE_VERSION' AND text_module = 'standard'), 'italian', 'Versione Core', now() );


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CREATION_DATE', 'standard', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_CREATION_DATE' AND text_module = 'standard'), 'english', 'Creation date', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_CREATION_DATE' AND text_module = 'standard'), 'italian', 'Data di creazione', now() );


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_EXECUTION_DATE', 'standard', '');
-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_EXECUTION_DATE'), 'english', 'Last execution date', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_EXECUTION_DATE'), 'italian', 'Data ultima esecuzione', now() );


