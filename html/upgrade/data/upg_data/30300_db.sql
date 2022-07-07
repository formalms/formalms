INSERT IGNORE INTO `core_menu` ( `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform` )
VALUES
	( '_MANAGEMENT_COMMUNICATION', '', '4', TRUE, TRUE, (SELECT `idMenu` FROM ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_CONTENTS' LIMIT 1 ) tbl), NULL, 'framework' );

 INSERT IGNORE INTO `core_menu` ( `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform` )
VALUES
	( '_CATEGORIES', '', '1', TRUE, TRUE, (SELECT `idMenu` FROM ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_MANAGEMENT_COMMUNICATION' ) tbl), NULL, 'framework' );

UPDATE `core_menu` SET `idParent` =  (SELECT `idMenu` FROM ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_MANAGEMENT_COMMUNICATION' LIMIT 1) tbl) WHERE name = '_COMMUNICATION_MAN';
INSERT IGNORE INTO `core_menu_under` ( `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path` )
VALUES
	( ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_CATEGORIES' LIMIT 1 ) ,
	'communication',
	'_CATEGORIES',
	NULL,
	'view',
	'framework',
	1,
	NULL,
	NULL,
	'alms/communication/showCategories' 
	);
ALTER TABLE `learning_communication_category_lang` ADD COLUMN IF NOT EXISTS `description` TEXT AFTER `translation`;
CREATE TABLE IF NOT EXISTS learning_communication_lang (
    id_comm int,
    lang_code varchar(255),
    title varchar(255),
    description text
);

INSERT IGNORE INTO `dashboard_blocks`(`block_class`, `created_at`) VALUES ('DashboardBlockCourseAttendanceGraphLms', CURRENT_TIMESTAMP);

UPDATE core_setting SET param_value = 'rar,exe,zip,jpg,gif,png,txt,csv,rtf,xml,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,pdf,xps,mp4,mp3,flv,swf,mov,wav,ogg,flac,wma,wmv,jpeg,m4v,mkv' WHERE param_name = 'file_upload_whitelist';