-- GOOGLE SETTING
-- ------------
-- social_google_client_id
-- social_google_secret

-- settings
INSERT IGNORE INTO core_setting(param_name,param_value,value_type,max_size,pack,regroup,sequence,param_load,hide_in_modify,extra_info) values ('social_google_client_id','213738518465-ert3v53f8dbt2p8ojrcvm647iqqr591b.apps.googleusercontent.com','string',255,'main',12,10,1,0,'');
INSERT IGNORE INTO core_setting(param_name,param_value,value_type,max_size,pack,regroup,sequence,param_load,hide_in_modify,extra_info) values ('social_google_secret','PFUDEPQuzUF57IVa4C0O8iD2','string',255,'main',12,11,1,0,'');

-- label
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_SOCIAL_GOOGLE_CLIENT_ID', 'configuration', '');
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_SOCIAL_GOOGLE_SECRET', 'configuration', '');

-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_SOCIAL_GOOGLE_CLIENT_ID'), 'english', 'Google Client Id');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_SOCIAL_GOOGLE_CLIENT_ID'), 'italian', 'Google Client Id');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_SOCIAL_GOOGLE_SECRET'), 'english', 'Google Secret');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_SOCIAL_GOOGLE_SECRET'), 'italian', 'Google Chiave Segreta');

-- SOCIAL ERROR
-- ------------
-- cancel auth social request
-- empty social id
-- social login generic unknown error
-- social login generic unknown error

-- social login error codes
insert IGNORE into core_lang_text(text_key,text_module) values ('_CANCELSOCIALLOGIN','login');
insert IGNORE into core_lang_text(text_key,text_module) values ('_EMPTYSOCIALID','login');
insert IGNORE into core_lang_text(text_key,text_module) values ('_UNKNOWNSOCIALERROR','login');
insert IGNORE into core_lang_text(text_key,text_module) values ('_SOCIALCONNECTKO','login');
insert IGNORE into core_lang_text(text_key,text_module) values ('_SOCIALCONNECTOK','login');

-- translation login error codes
insert IGNORE into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_CANCELSOCIALLOGIN' and text_module = 'login'), 'italian', 'Richiesta di accesso con social annullata dall''utente');
insert IGNORE into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_CANCELSOCIALLOGIN' and text_module = 'login'), 'english', 'Request access from social, cancel by user');
insert IGNORE into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_EMPTYSOCIALID' and text_module = 'login'), 'italian', 'Impossibile recuperare l''id di accesso social');
insert IGNORE into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_EMPTYSOCIALID' and text_module = 'login'), 'english', 'Unable to find social id account');
insert IGNORE into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_UNKNOWNSOCIALERROR' and text_module = 'login'), 'italian', 'Errore sconosciuto durante login social');
insert IGNORE into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_UNKNOWNSOCIALERROR' and text_module = 'login'), 'english', 'Unknown error in social login');
insert IGNORE into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_SOCIALCONNECTKO' and text_module = 'login'), 'italian', 'Connessione all''account social non riuscita');
insert IGNORE into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_SOCIALCONNECTKO' and text_module = 'login'), 'english', 'Social id connection failed');
insert IGNORE into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_SOCIALCONNECTOK' and text_module = 'login'), 'italian', 'Connessione all''account social riuscita');
insert IGNORE into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_SOCIALCONNECTOK' and text_module = 'login'), 'english', 'Social id connection successs');

-- global update to insert return uri google
update core_lang_translation set translation_text = concat(translation_text, ' (redirect_uri: http://server/path/index.php?modname=login&op=google_login)') where id_text = (select id_text from core_lang_text where text_key = '_SOCIAL_GOOGLE_ACTIVE' and text_module = 'configuration');
