-- label _ACTION_ON_USERS
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ACTION_ON_USERS', 'user_managment', '');

-- translation _ACTION_ON_USERS

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ACTION_ON_USERS' and text_module = 'user_managment'), 'english', 'Action on users');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ACTION_ON_USERS' and text_module = 'user_managment'), 'italian', 'Azione sugli utenti');

-- label _CREATE_AND_UPDATE
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CREATE_AND_UPDATE', 'user_managment', '');

-- translation _CREATE_AND_UPDATE

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_CREATE_AND_UPDATE' and text_module = 'user_managment'), 'english', 'Create and update');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_CREATE_AND_UPDATE' and text_module = 'user_managment'), 'italian', 'Crea ed aggiorna');

-- label _CREATE_ALL
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_CREATE_ALL', 'user_managment', '');

-- translation _CREATE_ALL

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_CREATE_ALL' and text_module = 'user_managment'), 'english', 'Create all');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_CREATE_ALL' and text_module = 'user_managment'), 'italian', 'Crea tutti');

-- label _ONLY_CREATE
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ONLY_CREATE', 'user_managment', '');

-- translation _ONLY_CREATE

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ONLY_CREATE' and text_module = 'user_managment'), 'english', 'Create only');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ONLY_CREATE' and text_module = 'user_managment'), 'italian', 'Crea soltanto');

-- label _ONLY_UPDATE
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_ONLY_UPDATE', 'user_managment', '');

-- translation _ONLY_UPDATE

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ONLY_UPDATE' and text_module = 'user_managment'), 'english', 'Update only');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_ONLY_UPDATE' and text_module = 'user_managment'), 'italian', 'Aggiorna soltanto');





-- label _SET_PASSWORD
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SET_PASSWORD', 'user_managment', '');

-- translation _SET_PASSWORD

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_SET_PASSWORD' and text_module = 'user_managment'), 'english', 'Set password');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_SET_PASSWORD' and text_module = 'user_managment'), 'italian', 'Imposta password');

-- label _FROM_FILE
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_FROM_FILE', 'user_managment', '');

-- translation _FROM_FILE

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_FROM_FILE' and text_module = 'user_managment'), 'english', 'Load password from file');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_FROM_FILE' and text_module = 'user_managment'), 'italian', 'Carica password dal file');

-- label _INSERT_EMPTY
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_INSERT_EMPTY', 'user_managment', '');

-- translation _INSERT_EMPTY

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_INSERT_EMPTY' and text_module = 'user_managment'), 'english', 'Insert password where the field is empty');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_INSERT_EMPTY' and text_module = 'user_managment'), 'italian', 'Inserisci password se il campo è vuoto');

-- label _INSERT_ALL
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_INSERT_ALL', 'user_managment', '');

-- translation _INSERT_ALL

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_INSERT_ALL' and text_module = 'user_managment'), 'english', 'Insert for all');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_INSERT_ALL' and text_module = 'user_managment'), 'italian', 'Inserisci per tutti');

-- label _PASSWORD_TO_INSERT
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_PASSWORD_TO_INSERT', 'user_managment', '');

-- translation _PASSWORD_TO_INSERT

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_PASSWORD_TO_INSERT' and text_module = 'user_managment'), 'english', 'Password to insert');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_PASSWORD_TO_INSERT' and text_module = 'user_managment'), 'italian', 'Password da inserire');

-- label _MANUAL_PASSWORD
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_MANUAL_PASSWORD', 'user_managment', '');

-- translation _MANUAL_PASSWORD     _SEND_NEW_CREDENTIALS_ALERT

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_MANUAL_PASSWORD' and text_module = 'user_managment'), 'english', 'Manual password');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_MANUAL_PASSWORD' and text_module = 'user_managment'), 'italian', 'Password manuale');

-- label _AUTOMATIC_PASSWORD
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_AUTOMATIC_PASSWORD', 'user_managment', '');

-- translation _AUTOMATIC_PASSWORD

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_AUTOMATIC_PASSWORD' and text_module = 'user_managment'), 'english', 'Automatic password');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_AUTOMATIC_PASSWORD' and text_module = 'user_managment'), 'italian', 'Password automatica');

-- label _SEND_NEW_CREDENTIALS_ALERT
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_SEND_NEW_CREDENTIALS_ALERT', 'user_managment', '');

-- translation _SEND_NEW_CREDENTIALS_ALERT

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_SEND_NEW_CREDENTIALS_ALERT' and text_module = 'user_managment'), 'english', 'Send new login credentials to users');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_SEND_NEW_CREDENTIALS_ALERT' and text_module = 'user_managment'), 'italian', 'Invia le nuove credenziali agli utenti');






-- label _NEED_TO_ALERT
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_NEED_TO_ALERT', 'user_managment', '');

-- translation _NEED_TO_ALERT

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_NEED_TO_ALERT' and text_module = 'user_managment'), 'english', 'You must enable the sending of the alert to users if the password entry is selected');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_NEED_TO_ALERT' and text_module = 'user_managment'), 'italian', 'E'' necessario abilitare l''invio dell''avviso agli utenti se selezionato l''inserimento della password');

-- label _NO_FILE
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_NO_FILE', 'user_managment', '');

-- translation _NO_FILE

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_NO_FILE' and text_module = 'user_managment'), 'english', 'No file loaded');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_NO_FILE' and text_module = 'user_managment'), 'italian', 'Nessun file caricato');

-- label _USERID_NEEDED
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_USERID_NEEDED', 'user_managment', '');

-- translation _USERID_NEEDED

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_USERID_NEEDED' and text_module = 'user_managment'), 'english', 'Userid necessary in the import and to be selected for the comparison');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_USERID_NEEDED' and text_module = 'user_managment'), 'italian', 'Campo userid da importare e da selezionare per il confronto');

-- label _FIELD_REPEATED
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_FIELD_REPEATED', 'user_managment', '');

-- translation _FIELD_REPEATED

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_FIELD_REPEATED' and text_module = 'user_managment'), 'english', 'You can''t import more times the same column');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_FIELD_REPEATED' and text_module = 'user_managment'), 'italian', 'Non è possibile importare più volte la stessa colonna');






-- label _GENERATE_PASSWORD
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_GENERATE_PASSWORD', 'user_managment', '');

-- translation _GENERATE_PASSWORD

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_GENERATE_PASSWORD' and text_module = 'user_managment'), 'english', 'Generate password');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_GENERATE_PASSWORD' and text_module = 'user_managment'), 'italian', 'Genera password');



-- label _USER_ALREADY_EXISTS
                         
INSERT INTO core_lang_text (text_key, text_module, text_attributes) VALUES ('_USER_ALREADY_EXISTS', 'standard', '');

-- translation _USER_ALREADY_EXISTS

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_USER_ALREADY_EXISTS' and text_module = 'standard'), 'english', 'User already exists');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text) values ((SELECT id_text FROM core_lang_text where text_key = '_USER_ALREADY_EXISTS' and text_module = 'standard'), 'italian', 'Utente esistente');




