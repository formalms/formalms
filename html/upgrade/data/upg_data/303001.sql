
 INSERT IGNORE INTO `core_menu` ( `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform` )
VALUES
	( '_SMTP_SETTINGS', '', '1', TRUE, TRUE, (SELECT `idMenu` FROM ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_CONFIG_SYS' ) tbl), NULL, 'framework' );


INSERT IGNORE INTO `core_menu_under` ( `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path` )
VALUES
	( ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_SMTP_SETTINGS' LIMIT 1 ) ,
	'smtps_settings',
	'_SMTP_SETTINGS',
	NULL,
	'view',
	'framework',
	1,
	NULL,
	NULL,
	'adm/mailconfig/show' 
	);

CREATE TABLE IF NOT EXISTS core_mail_configs (
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title varchar(255),
    system boolean not null default 0
);

CREATE TABLE IF NOT EXISTS core_mail_configs_fields (
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    mailConfigId int,
    key varchar(255),
	value varchar(255)
);
