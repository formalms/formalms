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

-- GOOGLE ERROR
-- ------------
-- cancel auth google request
-- empty google mail
-- google token expired
-- google login generic unknown error

-- google login error codes
insert into core_lang_text(text_key,text_module) values ('_CANCELGOOGLELOGIN','login');
insert into core_lang_text(text_key,text_module) values ('_EMPTYGOOGLEMAIL','login');
insert into core_lang_text(text_key,text_module) values ('_GOOGLETOKENEXPIRED','login');
insert into core_lang_text(text_key,text_module) values ('_UNKNOWNGOOGLERROR','login');
insert into core_lang_text(text_key,text_module) values ('_GOOGLECONNECTMAILKO','login');
insert into core_lang_text(text_key,text_module) values ('_GOOGLECONNECTMAILOK','login');

-- translation login error codes
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_CANCELGOOGLELOGIN' and text_module = 'login'), 'italian', 'Richiesta di accesso con google annullata dall''utente');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_CANCELGOOGLELOGIN' and text_module = 'login'), 'english', 'Request access from google, cancel by user');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_EMPTYGOOGLEMAIL' and text_module = 'login'), 'italian', 'Impossibile recuperare la mail di accesso di google');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_EMPTYGOOGLEMAIL' and text_module = 'login'), 'english', 'Unable to find google mail');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_GOOGLETOKENEXPIRED' and text_module = 'login'), 'italian', 'Token google scaduto, rieffettuare il login');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_GOOGLETOKENEXPIRED' and text_module = 'login'), 'english', 'Google token expired');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_UNKNOWNGOOGLERROR' and text_module = 'login'), 'italian', 'Errore sconosciuto durante login google');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_UNKNOWNGOOGLERROR' and text_module = 'login'), 'english', 'Unknown error in google login');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_GOOGLECONNECTMAILKO' and text_module = 'login'), 'italian', 'Connessione all''account google non riuscita');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_GOOGLECONNECTMAILKO' and text_module = 'login'), 'english', 'Google id connection failed');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_GOOGLECONNECTMAILOK' and text_module = 'login'), 'italian', 'Connessione all''account google riuscita');
insert into core_lang_translation(id_text, lang_code, translation_text) values ((select id_text from core_lang_text where text_key = '_GOOGLECONNECTMAILOK' and text_module = 'login'), 'english', 'Google id connection successs');

-- global update to insert return uri google
update core_lang_translation set translation_text = concat(translation_text, ' (redirect_uri: http://server/path/index.php?modname=login&op=google_login)') where id_text = (select id_text from core_lang_text where text_key = '_SOCIAL_GOOGLE_ACTIVE' and text_module = 'configuration');