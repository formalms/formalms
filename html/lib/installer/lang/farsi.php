<?php

define("_INSTALLER_TITLE", "forma.lms - نصب");
define("_NEXT", "مرحله بعدی");
define("_BACK", "Back");
define("_LOADING", "در حال بارگیری");
define("_TRY_AGAIN", "دوباره سعی کنید");
//--------------------------------------
define("_TITLE_STEP1", "مرحله 1: انتخاب زبان");
define("_LANGUAGE", "زبان");
define("_INSTALLER_INTRO_TEXT", "شرکت دوکبو تولیدکننده چهارچوب یادگیری الکترونیک متن باز forma.lms است که در سازمان های پیچیده، شرکت ها، دستگاه های دولتی و مراکز بهداشتی کاربرد دارد.
	<p><b>ویژگی های کلیدی</b></p>
	<ul>
		<li>پشتیبانی از اسکورم 1.2 و 2004</li>
		<li>قابل پیکربندی برای سازگاری با مدل های آموزشی مختلف (خودآموزی، یادگیری تلفیقی، یادگیری همکارانه، یادگیری اجتماعی))</li>
		<li>ابزار پدیدآوری برای مدیریت آزمون ها، بارگیری پرونده با هر قالب، صفحات وب، پرسش های متداول، واژه نامه ها، مجموعه پیوندها</li>
		<li>ویژگی های همیاری مانند <b>تالار گفتگو<b/>، <b>ویکی</b>، <b>گپ<b/>، <b>مدیریت پروژه<b/>، <b>مخزن<b/></li>
		<li>مدیریت استعداد و قابلیت، تحلیل شکاف و برنامه خودشکوفایی</li>
		<li>تولید و چاپ گواهی های پی دی اف</li>
		<li>پشتیبانی از واسط شخص ثالث با نرم افزار مدیریت منابع انسانی (<b>SAP</b>، <b>Cezanne</b>، <b>Lotus Notes</b>، ...) و خدمات شرکت های دیگر (<b>LDAP</b>، <b>Active Directory</b>، <b>CRM</b>، <b>Erp</b> و دیگر راه حل های سفارشی شده)</li>
		<li>پشتیبانی از ویژگی های اجتماعی مانند <b>برنامه های گوگل</b>، <b>فیسبوک<b/>، <b>توییتر<b/> و <b>لینکدین<b/></li>
		<li>سامانه گزارش دهی و برنامه اطلاعات بازرگانی کاملا قابل سفارشی شدن</li>
		<li>ویژگی های اختصاصی مدیریت ثانوی، تنظیم ناحیه و کشور</li>
		<li>پشتیبانی چندزبانه و پشتیبانی از LTR(چپ به راست) و RTL(راست به چپ). پشتیبانی از 25 زبان</li>
		<li>پشتیبانی از ابزارهای تلفن همراه</li>
	</ul>");
// ---------------------------------------
define("_TITLE_STEP2", "مرحله 2: اطلاعات");
define("_SERVERINFO","اطلاعات سرور");
define("_SERVER_SOFTWARE","نرم افزار سرور :");
define("_PHPVERSION","نسخه PHP :");
define("_MYSQLCLIENT_VERSION","نسخه مشتری Mysql :");
define("_LDAP","Ldap :");
define("_ONLY_IF_YU_WANT_TO_USE_IT","تنها در صورت نیاز به استفاده از LDAP این اخطار را در نظر بگیرید");
define("_OPENSSL","Openssl : ");
define("_WARINNG_SOCIAL","Consider this warning only if you use social login");

define("_PHPINFO","PHP Information : ");
define("_MAGIC_QUOTES_GPC","magic_quotes_gpc : ");
define("_SAFEMODE","Safe mode : ");
define("_REGISTER_GLOBALS","register_global : ");
define("_UPLOAD_MAX_FILESIZE","upload_max_filsize : ");
define("_POST_MAX_SIZE","post_max_size : ");
define("_MAX_EXECUTION_TIME","max_execution_time : ");
define("_ALLOW_URL_FOPEN","allow_url_fopen : ");
define("_ALLOW_URL_INCLUDE","allow_url_include : ");
define("_ON","ON ");
define("_OFF","OFF ");

define("_VERSION","نسخه دوکبو");
define("_START","آغاز");
define("_END","پایان");
// -----------------------------------------
define("_TITLE_STEP3", "مرحله 3: مجوز");
define("_AGREE_LICENSE", "با شرایط مندرج در مجوز موافقم");
// -----------------------------------------
define("_TITLE_STEP4", "مرحله 4: پیکربندی");
define("_SITE_BASE_URL", "نشانی اصلی وبسایت");
define("_DATABASE_INFO", "اطلاعات دادگان");
define("_DB_HOST", "نشانی");
define("_DB_NAME", "نام دادگان");
define("_DB_USERNAME", "کاربر دادگان");
define("_DB_PASS", "گذرواژه");
define("_UPLOAD_METHOD", "روش بارگذاری پرونده (پیشنهاد FTP، در صورت استفاده از ویندوز در خانه از HTTP استفاده کنید");
define("_HTTP_UPLOAD", "روش سنتی (HTTP)");
define("_FTP_UPLOAD", "بارگذاری پرونده ها با استفاده از FTP");
define("_FTP_INFO", "داده دسترسی FTP");
define("_IF_FTP_SELECTED", "(در صورت انتخاب FTP به عنوان روش بارگذاری)");
define("_FTP_HOST", "نشانی سرور");
define("_FTP_PORT", "شماره درگاهی (معمولا درست است)");
define("_FTP_USERNAME", "نام کاربری");
define("_FTP_PASS", "گذرواژه");
define("_FTP_CONFPASS", "تایید گذرواژه");
define("_FTP_PATH", "مسیر FTP (ریشه ذخیره سازی پرونده، مثال /htdocs/ /mainfile_html)");
define("_CANT_CONNECT_WITH_DB", "عدم امکان اتصال به دادگان، لطفا داده درج شده را بازبینی کنید");
define("_CANT_SELECT_DB", "عدم امکان انتخاب دادگان، لطفا داده درج شده را بازبینی کنید");
define("_CANT_CONNECT_WITH_FTP","عدم امکان اتصال در ftp به سرور مشخص شده، لطفا پارامترهای درج شده را بازبینی کنید");
// -----------------------------------------
define("_TITLE_STEP5", "مرحله 5: پیکربندی");
define("_ADMIN_USER_INFO", "اطلاعات مربوط به مدیر");
define("_ADMIN_USERNAME", "نام کاربری");
define("_ADMIN_FIRSTNAME", "نام");
define("_ADMIN_LASTNAME", "نام خانوادگی");
define("_ADMIN_PASS", "گذرواژه");
define("_ADMIN_CONFPASS", "تایید گذرواژه");
define("_ADMIN_EMAIL", "رایانامه");
define("_LANG_TO_INSTALL", "زبان موردنظر برای نصب");
// -----------------------------------------
define("_TITLE_STEP6", "مرحله 6: تنظیم داده های دادگان");
define("_DATABASE", "دادگان");
define("_DB_IMPORTING", "در حال درونبری دادگان");
define("_LANGUAGES", "زبان ها");
// -----------------------------------------
define("_TITLE_STEP7", "مرحله 7: نصب انجام شد");
define("_INSTALLATION_COMPLETED", "نصب انجام شده است");
define("_INSTALLATION_DETAILS", "جزئیات");
define("_SITE_HOMEPAGE", "خانه");
define("_REVEAL_PASSWORD", "آشکارسازی گذرواژه");
define("_COMMUNITY", "انجمن");
define("_COMMERCIAL_SERVICES", "خدمات تجاری");
define("_CONFIG_FILE_NOT_SAVED", "نصب کننده نتوانست پرونده config.php را ذخیره کند، پرونده را از اینترنت بارگیری و برنویسی کنید");
define("_DOWNLOAD_CONFIG", "بارگیری config");
define("_CHECKED_DIRECTORIES","یکی از پوشه های ذخیره پرونده ها وجود ندارد یا فاقد مجوز صحیح است");
define("_CHECKED_FILES","برخی پرونده ها فاقد مجوز درست اند");
// -----------------------------------------
define("_UPGRADER_TITLE", "forma.lms - به روز رسانی");
define("_UPGRADE_CONFIG","به روز رسانی پرونده config.php");
define("_UPG_CONFIG_OK","به روز رسانی پرونده config.php با موفقیت انجام شد");
define("_UPG_CONFIG_NOT_CHANGED", "Config.php already updated");
define("_UPG_CONFIG_NOT_SAVED", "به روز رسانی config.php انجام نشد");
define("_UPGRADING", "به روز رسانی در حال انجام است");
define("_UPGRADING_LANGUAGES", "به روز رسانی زبان ها");
define("_UPGRADE_COMPLETE", "به روز رسانی انجام شد");
