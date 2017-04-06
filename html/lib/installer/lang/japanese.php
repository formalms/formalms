<?php

define("_INSTALLER_TITLE", "forma.lms - インストール");
define("_NEXT", "進む");
define("_BACK", "戻る");
define("_LOADING", "ローディング");
define("_TRY_AGAIN", "もう一度試す");
//--------------------------------------
define("_TITLE_STEP1", "ステップ1: 言語を選ぶ");
define("_LANGUAGE", "言語");
define("_INSTALLER_INTRO_TEXT", "formalms.orgは複雑な組織、企業市場、政府、医療等のためのオープンソースのE-ラーニングフレームワークであるforma.lmsを開発している会社です。
	<p><b>主要機能</b></p>
	<ul>
		<li>Scorm 1.2と2004のサポート</li>
		<li>各種トレーニングモデルに設定可能 (セルフトレーニング, ブレンドラーニング, コラボレートラーニング, ソーシャルラーニング)</li>
		<li>テスト、各種フォーマットによるファイルダウンロード、 ウェブページ、FAQ、用語集、リンクコレクションに対応したオーサリングツール</li>
		<li><b>フォーラム</b>, <b>Wiki</b>, <b>チャット</b>, <b>プロジェクト管理</b>, <b>リポジトリ</b>などのコラボレーション機能</li>
		<li>才能や能力の管理、ギャップ解析や自己開発計画</li>
		<li>PDF証明の生成や印刷</li>
		<li>人的資源管理ソフトウェア(<b>SAP</b>, <b>Cezanne</b>, <b>Lotus Notes</b>, ...)や他企業のサービス(<b>LDAP</b>, <b>Active Directory</b>, <b>CRM</b>, <b>Erp</b> などのカスタムメイドソリューション)のサードパーティインターフェイスのサポート</li>
		<li><b>Google Apps</b>, <b>Facebook</b>, <b>Twitter</b>, <b>Linkedin</b>などのソーシャル機能サポート</li>
		<li>フルカスタマイズ可能なリポートシステムとビジネスインテリジェンス</li>
		<li>作りこまれたサブ管理者機能、地域や国の管理機能</li>
		<li>多言語サポートとLTR(左から右)とRTL(右から左)のサポート。25の言語をサポート</li>
		<li>モバイル機器のサポート</li>
	</ul>");
// ---------------------------------------
define("_TITLE_STEP2", "ステップ2: 情報");
define("_SERVERINFO","サーバ情報");
define("_SERVER_SOFTWARE","サーバソフトウェア : ");
define("_PHPVERSION","PHPバージョン : ");
define("_MYSQLCLIENT_VERSION","Mysqlクライアントバージョン : ");
define("_LDAP","LDAP : ");
define("_ONLY_IF_YU_WANT_TO_USE_IT","この警告はLDAPを使う場合のみ考慮してください");
define("_OPENSSL","Openssl : ");
define("_WARINNG_SOCIAL","Consider this warning only if you use social login");

define("_PHPINFO","PHP情報 : ");
define("_MAGIC_QUOTES_GPC","magic_quotes_gpc : ");
define("_SAFEMODE","セーフモード : ");
define("_REGISTER_GLOBALS","register_global : ");
define("_UPLOAD_MAX_FILESIZE","upload_max_filsize : ");
define("_POST_MAX_SIZE","post_max_size : ");
define("_MAX_EXECUTION_TIME","max_execution_time : ");
define("_ALLOW_URL_FOPEN","allow_url_fopen : ");
define("_ALLOW_URL_INCLUDE","allow_url_include : ");
define("_ON","ON ");
define("_OFF","OFF ");

define("_VERSION","forma.lmsバージョン");
define("_START","開始");
define("_END","終了");
// -----------------------------------------
define("_TITLE_STEP3", "ステップ3: ライセンス");
define("_AGREE_LICENSE", "ライセンス条件に同意します");
// -----------------------------------------
define("_TITLE_STEP4", "ステップ4: 設定");
define("_SITE_BASE_URL", "ウェブサイトのベースURL");
define("_DATABASE_INFO", "データベース情報");
define("_DB_HOST", "アドレス");
define("_DB_NAME", "データベース名");
define("_DB_USERNAME", "データベースユーザ");
define("_DB_PASS", "パスワード");
define("_UPLOAD_METHOD", "ファイルアップロードの方法 (FTP推奨、家庭のWindowsの場合はHTTP)");
define("_HTTP_UPLOAD", "普通の方法 (HTTP)");
define("_FTP_UPLOAD", "FTPでファイルをアップロードする");
define("_FTP_INFO", "FTPアクセスデータ");
define("_IF_FTP_SELECTED", "(アップロード方法にFTPを選択した場合)");
define("_FTP_HOST", "サーバアドレス");
define("_FTP_PORT", "ポート番号(大抵はこのまま)");
define("_FTP_USERNAME", "ユーザ名");
define("_FTP_PASS", "パスワード");
define("_FTP_CONFPASS", "パスワード確認");
define("_FTP_PATH", "FTPパス (ファイルが保存されるルートです。 例: /htdocs/ /mainfile_html/)");
define("_CANT_CONNECT_WITH_DB", "DBに接続できません。入力したデータを確認してください");
define("_CANT_SELECT_DB", "DBを選択できません。入力したデータを確認してください");
define("_CANT_CONNECT_WITH_FTP","FTPで指定されたサーバに接続できません。入力したパラメータを確認してください");
// -----------------------------------------
define("_TITLE_STEP5", "ステップ5: 設定");

define("_ADMIN_USER_INFO", "管理者ユーザに関する情報");
define("_ADMIN_USERNAME", "ユーザ名");
define("_ADMIN_FIRSTNAME", "名");
define("_ADMIN_LASTNAME", "姓");
define("_ADMIN_PASS", "パスワード");
define("_ADMIN_CONFPASS", "パスワード確認");
define("_ADMIN_EMAIL", "e-mail");
define("_LANG_TO_INSTALL", "インストールする言語");
// -----------------------------------------
define("_TITLE_STEP6", "ステップ6: データベースデータセットアップ");
define("_DATABASE", "データベース");
define("_DB_IMPORTING", "データベースのインポート");
define("_LANGUAGES", "言語");
// -----------------------------------------
define("_TITLE_STEP7", "ステップ7: インストール完了");
define("_INSTALLATION_COMPLETED", "インストールは完了しました");
define("_INSTALLATION_DETAILS", "詳細");
define("_SITE_HOMEPAGE", "ホーム");
define("_REVEAL_PASSWORD", "パスワードを表示");
define("_COMMUNITY", "コミュニティ");
define("_COMMERCIAL_SERVICES", "コマーシャルサービス");
define("_CONFIG_FILE_NOT_SAVED", "インストーラはconfig.phpを保存できませんでした。ダウンロードして上書きしてください。");
define("_DOWNLOAD_CONFIG", "configをダウンロード");
define("_CHECKED_DIRECTORIES","ファイルが保存されるディレクトリが存在しないか必要なパーミッションがありません");
define("_CHECKED_FILES","一部のファイルに適切なパーミッションがありません");
// -----------------------------------------
define("_UPGRADER_TITLE", "forma.lms - アップグレード");
define("_UPGRADE_CONFIG","config.phpをアップグレード中");
define("_UPG_CONFIG_OK","config.phpは無事アップデートされました");
define("_UPG_CONFIG_NOT_CHANGED", "Config.php already updated");
define("_UPG_CONFIG_NOT_SAVED", "config.phpのアップデートに失敗しました。");
define("_UPGRADING", "アップグレード中");
define("_UPGRADING_LANGUAGES", "言語のアップグレード");
define("_UPGRADE_COMPLETE", "アップグレード完了");
