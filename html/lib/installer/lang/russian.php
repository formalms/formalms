<?php

define("_INSTALLER_TITLE", "Инсталляция forma.lms");
define("_NEXT", "Следующий шаг");
define("_BACK", "Вернуться");
define("_LOADING", "Загрузка");
define("_TRY_AGAIN", "Попробуйте заново");
//--------------------------------------
define("_TITLE_STEP1", "Шаг 1: Выберите язык");
define("_LANGUAGE", "Язык");
define("_INSTALLER_INTRO_TEXT", "Компания formalms.org разработала собственную обучающую систему с открытым кодом, forma.lms, пригодную для применения в курпных организациях, корпоративном секторе, правительстве и здравоохранении.
	<p><b>Ключевые особенности</b></p>
	<ul>
		<li>поддержка Scorm 1.2 и 2004</li>
		<li>Конфигурируется под различные модели обучения (самообучение, смешанное обучение, совместное обучение, общественные курсы)</li>
		<li>Авторская система, которая позволяет управлять созданием тестов, загрузкой файлов различных форматов, веб-страниц, файлов FAQ, Словарей, Коллекций ссылок</li>
		<li>Инструменты для сотрудничества, такие как <b>Форум</b>, <b>Wiki</b>, <b>Чат</b>, <b>Управление проектами</b>, <b>Репозитарий</b></li>
		<li>План персонального развития, анализ профиля, управление талантами и способностями</li>
		<li>Генерация и печать сертификатов в формате PDF</li>
		<li>Поддержка интерфейсов сторонних систем управления человеческими ресурсами (<b>SAP</b>, <b>Cezanne</b>, <b>Lotus Notes</b>, ...) и других служб компании (<b>LDAP</b>, <b>Active Directory</b>, <b>CRM</b>, <b>Erp</b> и других пользовательских решений)</li>
		<li>Поддержка социальных сетей, таких как <b>Google Apps</b>, <b>Facebook</b>, <b>Twitter</b> e <b>Linkedin</b></li>
		<li>Полностью настраиваемая система Отчетов и бизнес-информации</li>
		<li>Функциональные возможности для суб-администрирования, функции администрирования стран и областей</li>
		<li>Много-языковая поддержка, поддержка письма LTR(слева-направо) и RTL (справа-налево). Поддерживаются 25 языков</li>
		<li>Поддержка мобильных устройств</li>
	</ul>");
// ---------------------------------------
define("_TITLE_STEP2", "Шаг 2: Информация");
define("_SERVERINFO","Информация о сервере");
define("_SERVER_SOFTWARE","ПО сервера: ");
define("_PHPVERSION","Версия PHP : ");
define("_MYSQLCLIENT_VERSION","Версия клиента Mysql: ");
define("_LDAP","LDAP: ");
define("_ONLY_IF_YU_WANT_TO_USE_IT","Примите во внимание только если Вы используете LDAP ");
define("_OPENSSL","Openssl : ");
define("_WARINNG_SOCIAL","Consider this warning only if you use social login");

define("_PHPINFO","Информация о PHP: ");
define("_MAGIC_QUOTES_GPC","magic_quotes_gpc: ");
define("_SAFEMODE","Безопасный режим: ");
define("_REGISTER_GLOBALS","register_global: ");
define("_UPLOAD_MAX_FILESIZE","upload_max_filsize: ");
define("_POST_MAX_SIZE","post_max_size: ");
define("_MAX_EXECUTION_TIME","max_execution_time: ");
define("_ALLOW_URL_FOPEN","allow_url_fopen : ");
define("_ALLOW_URL_INCLUDE","allow_url_include : ");
define("_ON","ВКЛ ");
define("_OFF","ВЫКЛ ");
// -----------------------------------------
define("_TITLE_STEP3", "Шаг 3: Лицензия");
define("_AGREE_LICENSE", "Я согласен с условиями лицензии");
// -----------------------------------------
define("_TITLE_STEP4", "Шаг 4: Configuration");
define("_SITE_BASE_URL", "Базовая ссылка на сайт");
define("_DATABASE_INFO", "Информация о базе данных");
define("_DB_HOST", "Адрес");
define("_DB_NAME", "Имя базы данных");
define("_DB_USERNAME", "Пользователь базы данных");
define("_DB_PASS", "Пароль");
define("_UPLOAD_METHOD", "Способ загрузки файла (предлагается, при использовании Windows, например дома, используйте HTTP");
define("_HTTP_UPLOAD", "Классический метод (HTTP)");
define("_FTP_UPLOAD", "Загрузить файлы используя FTP");
define("_FTP_INFO", "данные для доступа к FTP");
define("_IF_FTP_SELECTED", "(если Вы выбрали FTP как метод загрузки)");
define("_FTP_HOST", "Адрес Сервера");
define("_FTP_PORT", "номер порта (общее значение правильно)");
define("_FTP_USERNAME", "Имя пользователя");
define("_FTP_PASS", "Пароль");
define("_FTP_CONFPASS", "Подтвердите пароль");
define("_FTP_PATH", "путь FTP (каталог в котором сохраняются файлы, напр. /htdocs/ /mainfile_html/");
define("_CANT_CONNECT_WITH_DB", "Не могу соединиться с БД, пожалуйста проверьте данные для подключения");
define("_CANT_SELECT_DB", "Не могу выбрать БД, пожалуйста проверьте данные для подключения");
define("_CANT_CONNECT_WITH_FTP","Не могу подключться к выбранному серверу по FTP протоколу, пожалуйста проверьте параметры");
// -----------------------------------------
define("_TITLE_STEP5", "Шаг 5: Конфигурация");
define("_ADMIN_USER_INFO", "Информация, касающаяся администратора");
define("_ADMIN_USERNAME", "Имя пользователя");
define("_ADMIN_FIRSTNAME", "Имя");
define("_ADMIN_LASTNAME", "Фамилия");
define("_ADMIN_PASS", "Пароль");
define("_ADMIN_CONFPASS", "Подтвердите пароль");
define("_ADMIN_EMAIL", "эл.почта");
define("_LANG_TO_INSTALL", "Языки для установки");
// -----------------------------------------
define("_TITLE_STEP6", "Шаг 6: Настройка Базы Данных");
define("_DATABASE", "База данных");
define("_DB_IMPORTING", "БД для импорта");
define("_LANGUAGES", "Языки");
// -----------------------------------------
define("_TITLE_STEP7", "Шаг 7: Завершение установки");
define("_INSTALLATION_COMPLETED", "Инсталляция только-что была завершена");
define("_INSTALLATION_DETAILS", "Детали");
define("_SITE_HOMEPAGE", "В начало");
define("_REVEAL_PASSWORD", "Показать пароль");
define("_COMMUNITY", "Сообщество");
define("_COMMERCIAL_SERVICES", "Коммерческие услуги");
define("_CONFIG_FILE_NOT_SAVED", "Программа-установщик не смогла сохранить файл config.php, скачайте его и перезапишите в режиме онлайн.");
define("_DOWNLOAD_CONFIG", "Конфигурация загрузки");
define("_CHECKED_DIRECTORIES","Некоторые каталоги, в которых сохраняются файлы, не существуют или не имеют необходимых разрешений");
define("_CHECKED_FILES","Отдельные файлы не имеют необходимых разрешений");
// -----------------------------------------
define("_UPGRADER_TITLE", "forma.lms - Обновление");
define("_UPGRADE_CONFIG","Обновление файла config.php");
define("_UPG_CONFIG_OK","Файл config.php успешно обновлен");
define("_UPG_CONFIG_NOT_CHANGED", "Config.php already updated");
define("_UPG_CONFIG_NOT_SAVED", "Процесс обновления файла config.php завершился неудачей.");
define("_UPGRADING", "Обновление продолжается");
define("_UPGRADING_LANGUAGES", "Обновляемые языки");
define("_UPGRADE_COMPLETE", "Обновление завершено");
