--
-- Update database formalms
--
--
-- Update db script from formalms 1.3 to formalms 1.4
--

-- ------------------------------------------------------------------

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- ------------------------------------------------------------------

-- bug #3761

-- event UserCourseEnded , update wrong label Receipients
UPDATE core_event_manager SET  recipients = '_EVENT_RECIPIENTS_TEACHER'  WHERE idEventMgr = 12;

-- -----------------

-- bug #3893
-- settings
INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
       VALUES ('maintenance_pw', 'manutenzione', 'string', 16, 'security', 8, 25, 0, 0, '');

-- set max size of the maintenance password to 16 chars
UPDATE `core_setting` SET `max_size`=16 WHERE `param_name`='maintenance_pw';

-- -----------------

-- new feature #3690

-- label
INSERT IGNORE INTO core_lang_text(text_key, text_module, text_attributes)VALUES ('_MAINTENANCE_TEXT', 'login', '');

-- translation
INSERT IGNORE INTO core_lang_translation( id_text, lang_code, translation_text, save_date )
SELECT clt.id_text, cll.lang_code,
        'System in mainteinance mode.<br/><br/>To change these text please go to Admin/Language management, search for login and edit the key _MAINTENANCE_TEXT'
        , now()
FROM   core_lang_text clt, core_lang_language cll
WHERE  text_key = '_MAINTENANCE_TEXT' and lang_code in ('english');

INSERT IGNORE INTO core_lang_translation( id_text, lang_code, translation_text, save_date )
SELECT clt.id_text, cll.lang_code,
       'Sistema in manutenzione.<br/><br/>Per cambiare questa frase andare su Admin/Gestione lingue, cercare il modulo login e modificare la chiave _MAINTENANCE_TEXT'
       , now()
FROM   core_lang_text clt, core_lang_language cll
WHERE  text_key = '_MAINTENANCE_TEXT' and lang_code in ('italian');

INSERT IGNORE INTO core_lang_translation( id_text, lang_code, translation_text, save_date )
SELECT clt.id_text, cll.lang_code,
       'System in mainteinance mode.<br/><br/>To change these text please go to Admin/Language management, search for login and edit the key _MAINTENANCE_TEXT'
       , now()
FROM   core_lang_text clt, core_lang_language cll
WHERE  text_key = '_MAINTENANCE_TEXT' and lang_code not in ('english','italian');


-- ----------------

-- new feature #522

INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
VALUES ('course_block', 'off', 'enum', 3, 0, 4, 5, 1, 0, '');

INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_COURSE_BLOCK', 'configuration', '');

-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_COURSE_BLOCK'), 'english', 'Show catalogue in home page', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_COURSE_BLOCK'), 'italian', 'Attiva elenco corsi in home page', now() );

-- ----------------

-- bug / new feature #3743

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_WITH_COURSE_ASSOCIATIONS', 'competences', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_COURSE_ASSOCIATIONS'), 'english', 'Competence with courses associated', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_COURSE_ASSOCIATIONS'), 'italian', 'Competenza con corsi associati', now() );

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_WITH_FNCROLE_ASSOCIATIONS', 'competences', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_FNCROLE_ASSOCIATIONS'), 'english', 'Competence with roles associated', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_FNCROLE_ASSOCIATIONS'), 'italian', 'Competenza con ruoli associati', now() );


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_WITH_USER_ASSOCIATIONS', 'competences', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_USER_ASSOCIATIONS'), 'english', 'Competence with users associated', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WITH_USER_ASSOCIATIONS'), 'italian', 'Competenza con utenti associati', now() );


-- ----------------

-- new feature #4050

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_OPENSSL', 'configuration', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_OPENSSL'), 'english', 'Php extension php_openssl', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_OPENSSL'), 'italian', 'Estensione php_openssl di php', now() );

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ALLOW_URL_FOPEN', 'configuration', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_ALLOW_URL_FOPEN'), 'english', 'Configuration of "allow_url_fopen"', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_ALLOW_URL_FOPEN'), 'italian', 'Configurazione di "allow_url_fopen"', now() );

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_WARINNG_SOCIAL', 'configuration', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WARINNG_SOCIAL'), 'english', 'Attention without these settings the social login will not work', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_WARINNG_SOCIAL'), 'italian', 'Attenzione senza questi settaggi la login social non funzioner&agrave;', now() );

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date)
SELECT clt.id_text, cll.lang_code, 'Attention without these settings the social login will not work', now()
FROM   core_lang_text clt, core_lang_language cll
WHERE  text_key = '_WARINNG_SOCIAL' and lang_code not in ('english','italian');


-- ----------------

-- bug / new feature #3988
-- Google oauth authentication

-- settings
INSERT IGNORE INTO core_setting (param_name,param_value,value_type,max_size,pack,regroup,sequence,param_load,hide_in_modify,extra_info) VALUES ('social_google_client_id','','string',255,'main',12,10,1,0,'');
INSERT IGNORE INTO core_setting (param_name,param_value,value_type,max_size,pack,regroup,sequence,param_load,hide_in_modify,extra_info) VALUES ('social_google_secret','','string',255,'main',12,11,1,0,'');

-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SOCIAL_GOOGLE_CLIENT_ID', 'configuration', '');
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SOCIAL_GOOGLE_SECRET', 'configuration', '');

-- translation
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SOCIAL_GOOGLE_CLIENT_ID'), 'english', 'Google Client Id', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SOCIAL_GOOGLE_CLIENT_ID'), 'italian', 'Google Client Id', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SOCIAL_GOOGLE_SECRET'), 'english', 'Google secret code', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((SELECT id_text FROM core_lang_text WHERE text_key = '_SOCIAL_GOOGLE_SECRET'), 'italian', 'Codice segreto Google', now() );

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
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_CANCELSOCIALLOGIN' AND text_module = 'login'), 'italian', 'Richiesta di accesso con social annullata dall''utente', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_CANCELSOCIALLOGIN' AND text_module = 'login'), 'english', 'Request access from social, cancel by user', now() );

-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_EMPTYSOCIALID' AND text_module = 'login'), 'italian', 'Impossibile recuperare l''id di accesso social', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_EMPTYSOCIALID' AND text_module = 'login'), 'english', 'Unable to find social id account', now() );

-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_UNKNOWNSOCIALERROR' AND text_module = 'login'), 'italian', 'Errore sconosciuto durante login social', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_UNKNOWNSOCIALERROR' AND text_module = 'login'), 'english', 'Unknown error in social login', now() );

-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_SOCIALCONNECTKO' AND text_module = 'login'), 'italian', 'Connessione all''account social non riuscita', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_SOCIALCONNECTKO' AND text_module = 'login'), 'english', 'Social id connection failed', now() );

-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_SOCIALCONNECTOK' AND text_module = 'login'), 'italian', 'Connessione all''account social riuscita', now() );
-- INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text, save_date) VALUES ((select id_text from core_lang_text WHERE text_key = '_SOCIALCONNECTOK' AND text_module = 'login'), 'english', 'Social id connection successs', now() );

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





-- ------------------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ------------------------------------------------------------------


