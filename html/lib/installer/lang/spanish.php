<?php

define("_INSTALLER_TITLE", "forma.lms - Instalacion");
define("_NEXT", "Proximo paso");
define("_BACK", "Atras");
define("_LOADING", "Cargando");
define("_TRY_AGAIN", "Intente nuevamente");
//--------------------------------------
define("_TITLE_STEP1", "Paso 1: Seleccione idioma");
define("_LANGUAGE", "Idioma");

define("_INSTALLER_INTRO_TEXT", "formalms.org es una compañia que ha desarrollado su propio open source e-learning framework llamado forma.lms, apropiado para organizaciones complejas, mercado corporativo, para el gobierno y para la salud.
	<p><b>Principales caracteristicas:</b></p>
	<ul>
		<li>Soporte para Scorm 1.2 y 2004</li>
		<li>Configurable para encajar en varios modelos de entrenamiento (auto-entrenamiento, aprendizaje mezclado, aprendizaje colaborativo, aprendizaje social)</li>
		<li>Herramientas que permiten gestionar Pruebas, Descargas de archivo de cualquier formato, Paginas web, FAQ, Glosarios, Colecciones de enlaces</li>
		<li>Caracteristicas de colaboracion como <b>Foros</b>, <b>Wiki</b>, <b>Chat</b>, <b>Gestion de proyectos</b>, <b>Repositorio</b></li>
		<li>Manejo de talento y habilidades, analisis de deficiencias y plan de desarrollo del personal</li>
		<li>Creacion e impresion de certificados en Pdf</li>
		<li>Soporte para 3rd parties interfaces con software de gestion de recursos humanos (<b>SAP</b>, <b>Cezanne</b>, <b>Lotus Notes</b>, ...) y otros servicios de compañia (<b>LDAP</b>, <b>Active Directory</b>, <b>CRM</b>, <b>Erp</b> y otras herramientas por encargo)</li>
		<li>Soporte de redes sociales como <b>Google Apps</b>, <b>Facebook</b>, <b>Twitter</b> e <b>Linkedin</b></li>
		<li>Sistema de reportes e inteligencia de negocios completamente personalizable</li>
		<li>Caracteristicas de sub-administradores y gestor de area y pais.</li>
		<li>Soporte multi-lenguaje y soporte LTR (izquierda a derecha) y RTL (derecha a izquierda). 25 lenguajes soportados</li>
		<li>Soporte para dispositivos moviles</li>
	</ul>");

define("_TITLE_STEP2", "Paso 2: Informacion");
define("_SERVERINFO","Informacion del servidor");
define("_SERVER_SOFTWARE","Servidor del software : ");
define("_PHPVERSION","Version de PHP : ");
define("_MYSQLCLIENT_VERSION","Version de MySQL : ");
define("_LDAP","Ldap : ");
define("_ONLY_IF_YU_WANT_TO_USE_IT","Considere esta advertencia solamente si Ud. necesita usar LDAP ");
define("_OPENSSL","Openssl : ");
define("_WARINNG_SOCIAL","Consider this warning only if you use social login");

define("_PHPINFO","Informacion de PHP : ");
define("_MAGIC_QUOTES_GPC","magic_quotes_gpc : ");
define("_SAFEMODE","Modo seguro : ");
define("_REGISTER_GLOBALS","register_global : ");
define("_UPLOAD_MAX_FILESIZE","upload_max_filsize : ");
define("_POST_MAX_SIZE","post_max_size : ");
define("_MAX_EXECUTION_TIME","max_execution_time : ");
define("_ALLOW_URL_FOPEN","allow_url_fopen : ");
define("_ALLOW_URL_INCLUDE","allow_url_include : ");
define("_ON","ON ");
define("_OFF","OFF ");
// -----------------------------------------
define("_TITLE_STEP3", "Step 3: Licencia");
define("_AGREE_LICENSE", "Estoy de acuerdo con la licencia");
// -----------------------------------------
define("_TITLE_STEP4", "Step 4: Configuracion");
define("_SITE_BASE_URL", "Direccion web del sitio");
define("_DATABASE_INFO", "Informacion de la base de datos");
define("_DB_HOST", "Direccion");
define("_DB_NAME", "Nombre de la base de datos");
define("_DB_USERNAME", "Usuario en la base de datos");
define("_DB_PASS", "Contraseña");
define("_UPLOAD_METHOD", "Forma de subir archivos (recomendada FTP, si tienes Windows en casa usar HTTP");
define("_HTTP_UPLOAD", "Metodo clasico (HTTP)");
define("_FTP_UPLOAD", "Subir archivos via FTP");
define("_FTP_INFO", "Datos de acceso via FTP");
define("_IF_FTP_SELECTED", "(Si seleccionaste FTP como forma de subir los archivos)");
define("_FTP_HOST", "Direccion del servidor");
define("_FTP_PORT", "Puerto (generalmente es correcto)");
define("_FTP_USERNAME", "Usuario");
define("_FTP_PASS", "Contraseña");
define("_FTP_CONFPASS", "Confirmar contraseña");
define("_FTP_PATH", "Ruta FTP (es la raiz donde estan guardados los ficheros. Ejemplo: /htdocs/ /mainfile_html/");
define("_CANT_CONNECT_WITH_DB", "No es posible conectar con la base de datos, por favor, revise los datos insertados");
define("_CANT_SELECT_DB", "No es posible seleccionar una base de datos, por favor revise los datos insertados");
define("_CANT_CONNECT_WITH_FTP","No se puede conectar via FTP al servidor especificado, por favor revise los parametros insertados");
// -----------------------------------------
define("_TITLE_STEP5", "Paso 5: Configuracion");
define("_ADMIN_USER_INFO", "Informacion relacionada con el usuario administrador");
define("_ADMIN_USERNAME", "Usuario");
define("_ADMIN_FIRSTNAME", "Nombre");
define("_ADMIN_LASTNAME", "Apellidos");
define("_ADMIN_PASS", "Contraseña");
define("_ADMIN_CONFPASS", "Confirmar contraseña");
define("_ADMIN_EMAIL", "e-mail");
define("_LANG_TO_INSTALL", "Idiomas a instalar");
// -----------------------------------------
define("_TITLE_STEP6", "Paso 6: Configuracion de la base de datos");
define("_DATABASE", "Base de datos");
define("_DB_IMPORTING", "Importando base de datos");
define("_LANGUAGES", "Idiomas");
// -----------------------------------------
define("_TITLE_STEP7", "Paso 7: Instalacion completada");
define("_INSTALLATION_COMPLETED", "La instalacion ha sido completada");
define("_INSTALLATION_DETAILS", "Detalles");
define("_SITE_HOMEPAGE", "Inicio");
define("_REVEAL_PASSWORD", "Revelar password");
define("_COMMUNITY", "Comunidad");
define("_COMMERCIAL_SERVICES", "Servicios comerciales");
define("_CONFIG_FILE_NOT_SAVED", "El instalador fue incapaz de salvar el archivo config.php. Descarguelo y sobreescribalo.");
define("_DOWNLOAD_CONFIG", "Descargar configuracion");
define("_CHECKED_DIRECTORIES","Algunos directorios donde los archivos estan guardados no existen o no tienen los permisos correctos");
define("_CHECKED_FILES","Ciertos archivos no tienen el permiso adecuado");
// -----------------------------------------
define("_UPGRADER_TITLE", "forma.lms - Actualizacion");
define("_UPGRADE_CONFIG","Actualizando el archivo config.php");
define("_UPG_CONFIG_OK","El archivo Config.php fue actualizado satisfactoriamente");
define("_UPG_CONFIG_NOT_CHANGED", "Config.php already updated");
define("_UPG_CONFIG_NOT_SAVED", "El proceso de actualizacion del archivo config.php fallo.");
define("_UPGRADING", "Actualizacion en proceso");
define("_UPGRADING_LANGUAGES", "Actualizar idiomas");
define("_UPGRADE_COMPLETE", "Actualizacion completada");
