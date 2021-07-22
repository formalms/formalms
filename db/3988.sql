-- GOOGLE SETTING
-- ------------
-- social_google_client_id
-- social_google_secret

-- settings
INSERT IGNORE INTO core_setting (param_name,param_value,value_type,max_size,pack,regroup,sequence,param_load,hide_in_modify,extra_info) VALUES ('social_google_client_id','','string',255,'main',12,10,1,0,'');
INSERT IGNORE INTO core_setting (param_name,param_value,value_type,max_size,pack,regroup,sequence,param_load,hide_in_modify,extra_info) VALUES ('social_google_secret','','string',255,'main',12,11,1,0,'');

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SOCIAL_GOOGLE_CLIENT_ID', 'configuration', '');
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SOCIAL_GOOGLE_SECRET', 'configuration', '');

-- translation
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SOCIAL_GOOGLE_CLIENT_ID'), 'english', 'Google Client Id', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SOCIAL_GOOGLE_CLIENT_ID'), 'italian', 'Google Client Id', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SOCIAL_GOOGLE_SECRET'), 'english', 'Google secret code', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SOCIAL_GOOGLE_SECRET'), 'italian', 'Codice segreto Google', now() );

-- SOCIAL ERROR
-- ------------
-- cancel auth social request
-- empty social id
-- social login generic unknown error
-- social login generic unknown error

-- social login error codes
INSERT IGNORE INTO core_lang_text(text_key,text_module) VALUES ('_CANCELSOCIALLOGIN','login');
INSERT IGNORE INTO core_lang_text(text_key,text_module) VALUES ('_EMPTYSOCIALID','login');
INSERT IGNORE INTO core_lang_text(text_key,text_module) VALUES ('_UNKNOWNSOCIALERROR','login');
INSERT IGNORE INTO core_lang_text(text_key,text_module) VALUES ('_SOCIALCONNECTKO','login');
INSERT IGNORE INTO core_lang_text(text_key,text_module) VALUES ('_SOCIALCONNECTOK','login');

-- translation login error codes
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_CANCELSOCIALLOGIN' AND text_module = 'login'), 'italian', 'Richiesta di accesso con social annullata dall''utente', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_CANCELSOCIALLOGIN' AND text_module = 'login'), 'english', 'Request access from social, cancel by user', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_EMPTYSOCIALID' AND text_module = 'login'), 'italian', 'Impossibile recuperare l''id di accesso social', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_EMPTYSOCIALID' AND text_module = 'login'), 'english', 'Unable to find social id account', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_UNKNOWNSOCIALERROR' AND text_module = 'login'), 'italian', 'Errore sconosciuto durante login social', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_UNKNOWNSOCIALERROR' AND text_module = 'login'), 'english', 'Unknown error in social login', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_SOCIALCONNECTKO' AND text_module = 'login'), 'italian', 'Connessione all''account social non riuscita', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_SOCIALCONNECTKO' AND text_module = 'login'), 'english', 'Social id connection failed', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_SOCIALCONNECTOK' AND text_module = 'login'), 'italian', 'Connessione all''account social riuscita', now() );
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_SOCIALCONNECTOK' AND text_module = 'login'), 'english', 'Social id connection successs', now() );

-- global update to insert return uri google
UPDATE core_lang_translation
SET    translation_text = concat(translation_text, '<br/>(use redirect uri: http://server/path/index.php?modname=login&op=google_login)'),
       save_date = now()
WHERE  id_text = (SELECT id_text FROM core_lang_text WHERE text_key = '_SOCIAL_GOOGLE_ACTIVE' AND text_module = 'configuration')
       AND translation_text not like '%redirect uri:%';

INSERT IGNORE INTO core_lang_translation(id_text, lang_code, translation_text, save_date)
SELECT id_text , lang_code , 'Google Client ID', now()
FROM   core_lang_text , core_lang_language
WHERE  text_key = '_SOCIAL_GOOGLE_CLIENT_ID' AND text_module = 'configuration';

