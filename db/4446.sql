UPDATE core_lang_translation SET translation_text = 'User stats' WHERE translation_text = 'User stas' AND lang_code = 'english';

UPDATE core_lang_translation SET translation_text = 'Estensione LDAP di PHP' WHERE translation_text = 'Estensione Ldap di PHP' AND lang_code = 'italian';

UPDATE core_lang_translation SET translation_text = 'Porta server LDAP' WHERE translation_text = 'Porta server Ldap' AND lang_code = 'italian';

UPDATE core_lang_translation SET translation_text = 'Indirizzo server LDAP' WHERE translation_text = 'Indirizzo server Ldap' AND lang_code = 'italian';

UPDATE core_lang_translation SET translation_text = 'Usa autenticazione LDAP' WHERE translation_text = 'Usa autenticazione Ldap' AND lang_code = 'italian';
																																													   
UPDATE core_lang_translation SET translation_text = 'Stringa di autenticazione LDAP' WHERE translation_text = 'Stringa di autenticazione Ldap' AND lang_code = 'italian';

UPDATE core_lang_translation SET translation_text = 'Usa le statistiche di Google Analitics' WHERE translation_text = 'Usa le statistiche di Google (Analitycs)' AND lang_code = 'italian';

UPDATE core_lang_translation SET translation_text = 'Aggiungi i seguenti indirizzi in CC per tutte le mail inviate dalla piattaforma' WHERE translation_text = 'Aggiungi i seguenti indirizzi in CC per tutte la mail inviate dalla piattaforma' AND lang_code = 'italian';

UPDATE core_lang_translation SET translation_text = 'Percorso per il caricamento file' WHERE translation_text = 'Percorso per l''Upload files' AND lang_code = 'italian';

UPDATE core_lang_translation SET translation_text = 'Attiva il controllo di coerenza dell''IP durante la sessione' WHERE translation_text = 'Attiva il controllo di coerenza dell''ip durante la sessione' AND lang_code = 'italian';

UPDATE core_lang_translation SET translation_text = 'Utilizza l''API DimDim al posto che l''interfacciamento tramite URL' WHERE translation_text = 'Utilizza l''api DimDim al posto che l''interfacciamento tramite URL' AND lang_code = 'italian';

UPDATE core_lang_translation SET translation_text = 'Utiliser l''API DimDim au lieu de l''interfaçage URL' WHERE translation_text = 'Utiliser l''api DimDim au lieu de l''interfaçage URL' AND lang_code = 'french';

UPDATE core_lang_translation SET translation_text = 'Se all''utente non è assegnato almeno un catalogo corsi mostra tutti i corsi e i curriculum' WHERE translation_text = 'Se all''utente non è assegnato almeno un catalogo corsi mostra tutti i corsi e i curricula' AND lang_code = 'italian';

UPDATE core_lang_translation SET translation_text = 'Email Help Desk' WHERE translation_text = 'Email HelpDesk' AND lang_code = 'italian';   

UPDATE core_lang_translation SET translation_text = 'Oggetto mail Help Desk' WHERE translation_text = 'Oggetto mail HelpDesk' AND lang_code = 'italian';            

UPDATE core_lang_translation SET translation_text = 'Registrazione' WHERE lang_code = 'italian' AND id_text IN (SELECT id_text FROM core_lang_text WHERE text_key = '_REGISTER' AND text_module = 'standard');

UPDATE core_lang_translation SET translation_text = 'Titolo pagina (apparirà come titolo del browser)' WHERE translation_text = 'Titolo pagina (apparirà come titolo del Browser)' AND lang_code = 'italian';  

UPDATE core_lang_translation SET translation_text = 'Editor HTML WYSWYG' WHERE translation_text = 'Editor html WYSWYG' AND lang_code = 'italian';  
                                                                                                                                                                                  																																												  
UPDATE core_lang_translation SET translation_text = 'Configurazioni' WHERE lang_code = 'italian' AND id_text IN (SELECT id_text FROM core_lang_text WHERE text_key = '_CONFIGURATION' AND text_module = 'menu');


