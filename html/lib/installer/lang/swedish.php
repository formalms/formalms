<?php

define("_INSTALLER_TITLE", "forma.lms - Installation");
define("_NEXT", "Nästa steg");
define("_BACK", "Tillbaka");
define("_LOADING", "Laddar");
define("_TRY_AGAIN", "Försök pånytt");
//--------------------------------------
define("_TITLE_STEP1", "Steg 1: Välj språk");
define("_LANGUAGE", "Språk");
define("_INSTALLER_INTRO_TEXT", "formalms.org är ett företag som har utvecklat en egen öppen källkods elektronisk lärningsstruktur kallad forma.lms som är lämplig åt komplexa organisationer, företagsmarknaden, regeringen och hälsovården.
	<p><b>Viktiga funktioner</b></p>
	<ul>
		<li>Scorm 1.2 och 2004 understöd</li>
		<li>Konfigurerbar att passa flera träningsmodeller (själv-träning, blandad lärning, samarbetande lärning, social lärning) </li>
		<li>Författar-Redskap som låter dig hantera Test, nerladdning av Filer av diverse format, Webbsidor, Faq, Ordlistor, Länksamlingar</li>
		<li>Samarbets-funktioner såsom <b>Forum</b>, <b>Wiki</b>, <b>Chat</b>, <b>Projekthantering</b>, <b>Lagring</b></li>
		<li>Hantering av Talang och Kompetens, analys av brister och personlig utvecklingsplan. </li>
		<li>Framställning av pdf-certifikat och utskrivning</li>
		<li>Understöd för tredje parters gränssnitt för human resource hanteringsprogram (<b>SAP</b>, <b>Cezanne</b>, <b>Lotus Notes</b>, ...) och andra företags tjänster (<b>LDAP</b>, <b>Active Directory</b>, <b>CRM</b>, <b>Erp</b> och andra skräddarsydda lösningar)</li>
		<li>Stöd för sociala funktioner såsom <b>Google Apps</b>, <b>Facebook</b>, <b>Twitter</b> e <b>Linkedin</b></li>
		<li>Fullt anpassningbara rapporteringssystem och businessintelligens</li>
		<li>Dedikerade lägre administratör-funktioner, region- och lands-chef funktioner</li>
		<li>Stöd för flera språk och stöd för LTR(vänster-till-höger) och RTL (höger-till-vänster). Stöd för 25 språk</li>
		<li>Stöd för mobila anordningar</li>
	</ul>");
// ---------------------------------------
define("_TITLE_STEP2", "Steg 2: Information");
define("_SERVERINFO","Server information");
define("_SERVER_SOFTWARE","Server mjukvara : ");
define("_PHPVERSION","PHP Version : ");
define("_MYSQLCLIENT_VERSION","Mysql Klient Version : ");
define("_LDAP","Ldap : ");
define("_ONLY_IF_YU_WANT_TO_USE_IT","Beakta denhär varningen endast om du behövar använda LDAP ");
define("_OPENSSL","Openssl : ");
define("_WARINNG_SOCIAL","Consider this warning only if you use social login");

define("_PHPINFO","PHP Information : ");
define("_MAGIC_QUOTES_GPC","magic_quotes_gpc : ");#????
define("_SAFEMODE","Säkert läge : ");
define("_REGISTER_GLOBALS","register_global : ");
define("_UPLOAD_MAX_FILESIZE","upload_max_filsize : ");
define("_POST_MAX_SIZE","post_max_size : ");
define("_MAX_EXECUTION_TIME","max_execution_time : ");
define("_ALLOW_URL_FOPEN","allow_url_fopen : ");
define("_ALLOW_URL_INCLUDE","allow_url_include : ");
define("_ON","ON ");
define("_OFF","OFF ");

define("_VERSION","forma.lms version");
define("_START","Start");
define("_END","Final");
// -----------------------------------------
define("_TITLE_STEP3", "Steg 3: Licens");
define("_AGREE_LICENSE", "Jag godkänner dessa licensvillkor");
// -----------------------------------------
define("_TITLE_STEP4", "Steg 4: Konfiguration");
define("_SITE_BASE_URL", "Bas url för webbsidan");
define("_DATABASE_INFO", "Databas information");
define("_DB_HOST", "Address");
define("_DB_NAME", "Databasens namn");
define("_DB_USERNAME", "Databasens användarnamn");
define("_DB_PASS", "Lösenord");
define("_UPLOAD_METHOD", "Filuppladdningsmetod (rekommenderas FTP, om du använder windows hemifrån använd HTTP");
define("_HTTP_UPLOAD", "Klassisk metod (HTTP)");
define("_FTP_UPLOAD", "Ladda upp filer med FTP");
define("_FTP_INFO", "FTP access data");
define("_IF_FTP_SELECTED", "(Om du valt FTP som din uppladdningsmetod)");
define("_FTP_HOST", "Server address");
define("_FTP_PORT", "Port nummer (vanligtvis rätt)");
define("_FTP_USERNAME", "Användarnamn");
define("_FTP_PASS", "Lösenord");
define("_FTP_CONFPASS", "Bekräfta lösenord");
define("_FTP_PATH", "FTP sökväg (roten för sparade filer, t.ex. /htdocs/ /mainfile_html/");
define("_CANT_CONNECT_WITH_DB", "Kan inte kontakta databasen, kontrollera inmatad data");
define("_CANT_SELECT_DB", "Kan inte välja databasen, kontrollera inmatad data");
define("_CANT_CONNECT_WITH_FTP","Fick inte FTP-kontakt med den specifierade servern, kontrollera inmatade parametrar");
// -----------------------------------------
define("_TITLE_STEP5", "Steg 5: Konfiguration");
define("_ADMIN_USER_INFO", "Information om administratoranvändaren");
define("_ADMIN_USERNAME", "Användarnamn");
define("_ADMIN_FIRSTNAME", "Förnamn");
define("_ADMIN_LASTNAME", "Efternamn");
define("_ADMIN_PASS", "Lösenord");
define("_ADMIN_CONFPASS", "Bekräfta lösenord");
define("_ADMIN_EMAIL", "e-post");
define("_LANG_TO_INSTALL", "Språk att installera");
// -----------------------------------------
define("_TITLE_STEP6", "Steg 6: Databasens data setup");
define("_DATABASE", "Databas");
define("_DB_IMPORTING", "Importerar databas");
define("_LANGUAGES", "Språk");
// -----------------------------------------
define("_TITLE_STEP7", "Steg 7: Installation färdig");
define("_INSTALLATION_COMPLETED", "Installation har blivit färdig");
define("_INSTALLATION_DETAILS", "Detaljer");
define("_SITE_HOMEPAGE", "Hem");
define("_REVEAL_PASSWORD", "Visa lösenord");
define("_COMMUNITY", "Community");
define("_COMMERCIAL_SERVICES", "Kommersiella tjänster");
define("_CONFIG_FILE_NOT_SAVED", "Installern kunde inte spara config.php filen, ladda ner den och skriv över den online.");
define("_DOWNLOAD_CONFIG", "Ladda ner config");
define("_CHECKED_DIRECTORIES","Vissa kataloger där filer sparas existerar inte eller har inte rätta rättigheter");
define("_CHECKED_FILES","Vissa filer han inte tillräckliga rättigheter");
// -----------------------------------------
define("_UPGRADER_TITLE", "forma.lms - Uppdatering");
define("_UPGRADE_CONFIG","Uppdaterar config.php fil");
define("_UPG_CONFIG_OK","Config.php fil uppdaterad ");
define("_UPG_CONFIG_NOT_CHANGED", "Config.php already updated");
define("_UPG_CONFIG_NOT_SAVED", "Uppdateringsprocessen för config.php misslyckades.");
define("_UPGRADING", "Uppdatering pågår");
define("_UPGRADING_LANGUAGES", "Uppdatera språk");
define("_UPGRADE_COMPLETE", "Uppdatering färdig");
