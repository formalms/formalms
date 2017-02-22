<?php

define("_INSTALLER_TITLE", "forma.lms - 安装");
define("_NEXT", "下一步");
define("_BACK", "返回");
define("_LOADING", "载入中");
define("_TRY_AGAIN", "请重试");
//--------------------------------------
define("_TITLE_STEP1", "第一步：选择语言");
define("_LANGUAGE", "语言");
define("_INSTALLER_INTRO_TEXT", "formalms.org是一家开发网络学习系统框架的公司，产品的名称叫forma.lms， 是一种开放源码的平台，适合复杂的机构组织、企业集团、政府和卫生部门使用。
	<p><b>特色功能</b></p>
	<ul>
		<li>支持Scorm 1.2和2004</li>
		<li>可以根据不同的训练模式进行配置(自学，混合式学习，合作学习，社交学习)</li>
		<li>授权工具让您可以方便地管理测试，任何类型的文件下载，网页，概念，常见问题以及大量的链接</li>
		<li>互动合作功能，如<b>论坛</b>, <b>维基</b>, <b>聊天</b>, <b>项目管理</b>, <b>知识库</b></li>
		<li>智能管理，差距分析和个人发展计划</li>
		<li>Pdf证书创建和打印</li>
		<li>支持第三方接口，如人力资源管理软件(<b>SAP</b>，<b>Cezanne</b>，<b>Lotus Notes</b>， ...)，还有其它公司的服务，如(<b>LDAP</b>，<b>活动目录</b>，<b>CRM</b>，<b>Erp</b>，以及定制的解决方案)</li>
		<li>社交功能支持，如<b>谷歌应用</b>，<b>Facebook</b>，<b>Twitter</b>和<b>Linkedin</b></li>
		<li>可进行全面定制的报表系统以及商业智能</li>
		<li>完善的分权管理功能，地区和国家经理</li>
		<li>多语言支持，包括支持双向字符集(LTR和RTL）和25种语言</li>
		<li>移动设备支持</li>
	</ul>");
// ---------------------------------------
define("_TITLE_STEP2", "第二步：信息");
define("_SERVERINFO","服务器信息");
define("_SERVER_SOFTWARE","服务器软件：");
define("_PHPVERSION","PHP Version : ");
define("_MYSQLCLIENT_VERSION","Mysql客户端版本：");
define("_LDAP","Ldap协议：");
define("_ONLY_IF_YU_WANT_TO_USE_IT","只有您需要使用LDAP协议时才考虑此提示 ");
define("_OPENSSL","Openssl : ");
define("_WARINNG_SOCIAL","Consider this warning only if you use social login");

define("_PHPINFO","PHP信息：");
define("_MAGIC_QUOTES_GPC","magic_quotes_gpc : ");
define("_SAFEMODE","安全模式：");
define("_REGISTER_GLOBALS","全局注册：");
define("_UPLOAD_MAX_FILESIZE","上传文件最大大小：");
define("_POST_MAX_SIZE","post方法最大值： ");
define("_MAX_EXECUTION_TIME","最大执行时间：");
define("_ALLOW_URL_FOPEN","allow_url_fopen : ");
define("_ALLOW_URL_INCLUDE","allow_url_include : ");
define("_ON","启用 ");
define("_OFF","关闭 ");
// -----------------------------------------
define("_TITLE_STEP3", "第三步：许可协议");
define("_AGREE_LICENSE", "我同意许可协议条款");
// -----------------------------------------
define("_TITLE_STEP4", "第四步：配置");
define("_SITE_BASE_URL", "网站的显式URL");
define("_DATABASE_INFO", "数据库信息");
define("_DB_HOST", "地址");
define("_DB_NAME", "数据库名称");
define("_DB_USERNAME", "数据库用户");
define("_DB_PASS", "密码");
define("_UPLOAD_METHOD", "上传文件方法(推荐FTP，如果您在家中使用windows，可以用HTTP");
define("_HTTP_UPLOAD", "经典方法(HTTP)");
define("_FTP_UPLOAD", "使用FTP上传文件");
define("_FTP_INFO", "FTP登陆数据");
define("_IF_FTP_SELECTED", "(如果您已经选择了FTP作为上传方法)");
define("_FTP_HOST", "服务器地址");
define("_FTP_PORT", "端口号(一般情况下都是正确的)");
define("_FTP_USERNAME", "用户名");
define("_FTP_PASS", "密码");
define("_FTP_CONFPASS", "确认密码");
define("_FTP_PATH", "FTP路径(存储文件的根目录，如/htdocs/ /mainfile_html/");
define("_CANT_CONNECT_WITH_DB", "不能连接到数据库，请检查输入的数据");
define("_CANT_SELECT_DB", "Can't select DB, please check inserted data");
define("_CANT_CONNECT_WITH_FTP","不能与指定的服务器建立ftp连结，请检查输入的参数");
// -----------------------------------------
define("_TITLE_STEP5", "第五步：配置");
define("_ADMIN_USER_INFO", "与管理员有关的信息");
define("_ADMIN_USERNAME", "用户名");
define("_ADMIN_FIRSTNAME", "名字");
define("_ADMIN_LASTNAME", "姓");
define("_ADMIN_PASS", "密码");
define("_ADMIN_CONFPASS", "确认密码");
define("_ADMIN_EMAIL", "电子邮件");
define("_LANG_TO_INSTALL", "要安装的语言");
// -----------------------------------------
define("_TITLE_STEP6", "第六步：数据库信息设置");
define("_DATABASE", "数据库");
define("_DB_IMPORTING", "导入数据库");
define("_LANGUAGES", "语言");
// -----------------------------------------
define("_TITLE_STEP7", "第七步：安装完成");
define("_INSTALLATION_COMPLETED", "已完成安装");
define("_INSTALLATION_DETAILS", "详情");
define("_SITE_HOMEPAGE", "首页");
define("_REVEAL_PASSWORD", "显示密码");
define("_COMMUNITY", "社区");
define("_COMMERCIAL_SERVICES", "商务服务");
define("_CONFIG_FILE_NOT_SAVED", "安装程序无法保存 config.php 文件，请下载文件并覆盖。");
define("_DOWNLOAD_CONFIG", "下载配置文件");
define("_CHECKED_DIRECTORIES","文件存储的某些目录不存在或没有相应的权限");
define("_CHECKED_FILES","某些文件没有足够的权限");
// -----------------------------------------
define("_UPGRADER_TITLE", "forma.lms - 升级");
define("_UPGRADE_CONFIG","正在升级 config.php file 文件");
define("_UPG_CONFIG_OK","Config.php 已经成功升级");
define("_UPG_CONFIG_NOT_CHANGED", "Config.php already updated");
define("_UPG_CONFIG_NOT_SAVED", "升级config.php失败。");
define("_UPGRADING", "正在升级");
define("_UPGRADING_LANGUAGES", "设计语言");
define("_UPGRADE_COMPLETE", "升级完成");
