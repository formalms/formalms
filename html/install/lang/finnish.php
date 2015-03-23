<?php

define("_INSTALLER_TITLE", "forma.lms - Asennus");
define("_NEXT", "Seuraava vaihe");
define("_BACK", "Takaisin");
define("_LOADING", "Ladataan");
define("_TRY_AGAIN", "Yritä uudestaan");
//--------------------------------------
define("_TITLE_STEP1", "Vaihe 1: Valitse kieli");
define("_LANGUAGE", "Kieli");
define("_INSTALLER_INTRO_TEXT", "formalms.org on yhtiö joka on kehittänyt omat avoimen lähdekoodin verkko-oppimispuitteet nimeltään forma.lms jotka sopivat monimutkaisille organisaatioille, yritysmarkkinoille, hallintoon ja terveydenhuoltoon.
	<p><b>Keskeiset ominaisuudet</b></p>
	<ul>
		<li>Scorm 1.2 ja 2004 tuki</li>
		<li>Mukautettavissa monelle oppimismallille (itseoppiminen, sekaoppiminen, yhteistyöoppiminen, sosiaalinen oppiminen)</li>
		<li>Kirjoittaja-työkalu joka salli Kokeiden hallinnan, Tiedostojen lataamisen missä tahansa muodossa, verkkosivut, UKK, Sanalistat, Linkkikokoelmat</li>
		<li>Yhteistyö-ominaisuudet kuten <b>Forum</b>, <b>Wiki</b>, <b>Chatti</b>, <b>Projektin johtaminen</b>, <b>Säilytys</b></li>
		<li>Lahjakkuksien ja Pätevyyksien hallinta, puutteiden analyysi, henkilökohtaiset kehityssuunnitelmat</li>
		<li>Pdf todistusten luominen ja tulostus</li>
		<li>Tuki kolmannen osapuolen rajapinnoille henkilöresurssien hallintaohjelmiin (<b>SAP</b>, <b>Cezanne</b>, <b>Lotus Notes</b>, ...) ja muihin yrityspalveluihin (<b>LDAP</b>, <b>Active Directory</b>, <b>CRM</b>, <b>Erp</b> ja muut mittatilausratkaisut)</li>
		<li>Sosiaaliset ominaisuudet kuten <b>Google Apps</b>, <b>Facebook</b>, <b>Twitter</b> e <b>Linkedin</b></li>
		<li>Täysin mukautettavat raportointijärjestelmät ja business intelligence</li>
		<li>Erilliset ali-hallinnon ominaisuudet, alue- ja maajohtaja ominaisuudet</li>
		<li>Monikielinen tuki ja LTR(vassemmalta-oikealle) ja RTL (oikealta-vasemmalle) tuki. 25 kieltä tuettu</li>
		<li>Mobiililaitteiden tuki</li>
	</ul>");
// ---------------------------------------
define("_TITLE_STEP2", "Vaihe 2: Tiedot");
define("_SERVERINFO","Palvelintiedot ");
define("_SERVER_SOFTWARE","Palvelimen ohjelmisto : ");
define("_PHPVERSION","PHP Versio : ");
define("_MYSQLCLIENT_VERSION","Mysql Clientin Versio : ");
define("_LDAP","Ldap : ");
define("_ONLY_IF_YU_WANT_TO_USE_IT","Huomioi tämä varoitus vain jos sinun tarvitsee käyttää LDAP:ia ");
define("_OPENSSL","Openssl : ");
define("_WARINNG_SOCIAL","Consider this warning only if you use social login");

define("_PHPINFO","PHP Tiedot : ");
define("_MAGIC_QUOTES_GPC","magic_quotes_gpc : ");
define("_SAFEMODE","Suojattu tila : ");
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
define("_TITLE_STEP3", "Vaihe 3: Lisenssi");
define("_AGREE_LICENSE", "Hyväksyn lisenssiehdot");
// -----------------------------------------
define("_TITLE_STEP4", "Vaihe 4: Kokoonpano");
define("_SITE_BASE_URL", "Verkkosivun perusosoite url");
define("_DATABASE_INFO", "Tietokannan tiedot");
define("_DB_HOST", "Osoite");
define("_DB_NAME", "Tietokannan nimi");
define("_DB_USERNAME", "Tietokannan käyttäjänimi");
define("_DB_PASS", "Salasana");
define("_UPLOAD_METHOD", "Tiedostonlähetysmenetelmä (ehdotus FTP, jos käytät Windowsia kotona käytä HTTP");
define("_HTTP_UPLOAD", "Klassinen menetelmä (HTTP)");
define("_FTP_UPLOAD", "lähetä tiedostot FTP:llä");
define("_FTP_INFO", "FTP tietojen haku");
define("_IF_FTP_SELECTED", "(Jos olet valinnut FTP lähetysmenetelmän)");
define("_FTP_HOST", "Palvelimen osoite");
define("_FTP_PORT", "Porttinumero (yleensä oikeassa)");
define("_FTP_USERNAME", "Käyttäjänimi");
define("_FTP_PASS", "Salasana");
define("_FTP_CONFPASS", "Vahvista salasana");
define("_FTP_PATH", "FTP polku (juuri jonne tiedostot on tallennettu, esim. /htdocs/ /mainfile_html/");
define("_CANT_CONNECT_WITH_DB", "Tietokantaa ei voitu yhdistää, tarkasta syötetyt tiedot");
define("_CANT_SELECT_DB", "Tietokantaa ei voitu valita, tarkista syötetyt tiedot");
define("_CANT_CONNECT_WITH_FTP","Määriteltyyn palvelimeen ei saatu ftp-yhteyttä, tarkista syötetyt parametrit");
// -----------------------------------------
define("_TITLE_STEP5", "Vaihe 5: Kokoonpano");
define("_ADMIN_USER_INFO", "Tietoja ylläpitäjä-käyttäjästä");
define("_ADMIN_USERNAME", "Käyttäjänimi");
define("_ADMIN_FIRSTNAME", "Etunimi");
define("_ADMIN_LASTNAME", "Sukunimi");
define("_ADMIN_PASS", "Salasana");
define("_ADMIN_CONFPASS", "Vahvista salasana");
define("_ADMIN_EMAIL", "sähköposti");
define("_LANG_TO_INSTALL", "Asennettavat kielet");
// -----------------------------------------
define("_TITLE_STEP6", "Step 6: Tietokannan tietojen alustus");
define("_DATABASE", "Tietokanta");
define("_DB_IMPORTING", "Tuodaan tietokanta");
define("_LANGUAGES", "Kielet");
// -----------------------------------------
define("_TITLE_STEP7", "Vaihe 7: Asennus valmis");
define("_INSTALLATION_COMPLETED", "Asennus on suoritettu valmiiksi");
define("_INSTALLATION_DETAILS", "Yksityiskohdat");
define("_SITE_HOMEPAGE", "Koti");
define("_REVEAL_PASSWORD", "Näytä salasanat");
define("_COMMUNITY", "Yhteisö");
define("_COMMERCIAL_SERVICES", "Kaupalliset Palvelut");
define("_CONFIG_FILE_NOT_SAVED", "Asennusohjelman ei onnistunut tallentaa config.php tiedostoa, lataa se ja ylitsekirjoita verkossa.");
define("_DOWNLOAD_CONFIG", "Latauksen asetukset");
define("_CHECKED_DIRECTORIES","Jotkin kansiot joissa tiedostoja säilytetään eivät ole olemassa tai eivät omaa oikeita oikeuksia");
define("_CHECKED_FILES","Jotkin tiedostot eivät omaa riittäviä oikeuksia");
// -----------------------------------------
define("_UPGRADER_TITLE", "forma.lms - Päivitys");
define("_UPGRADE_CONFIG","Config.php tiedoston päivitys");
define("_UPG_CONFIG_OK","Config.php tiedosto päivitettiin onnistuneesti");
define("_UPG_CONFIG_NOT_CHANGED", "Config.php already updated");
define("_UPG_CONFIG_NOT_SAVED", "Config.php tiedoston päivitysprosessi epäonnistui");
define("_UPGRADING", "Päivitys käynnissä");
define("_UPGRADING_LANGUAGES", "Päivitettävät kielet");
define("_UPGRADE_COMPLETE", "Päivitys valmis");
