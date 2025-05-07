UPDATE `core_menu_under` SET `default_name` = '_CATEGORIES' WHERE `module_name` = 'reservation' AND `default_name` = '_CATEGORY';

UPDATE `core_menu` SET `name` = '_CATEGORIES' WHERE `idMenu` = ( SELECT `idMenu` FROM `core_menu_under` WHERE `module_name` = 'reservation' AND `default_name` = '_CATEGORIES' LIMIT 1 );

INSERT IGNORE INTO `core_setting` VALUES ('use_email_as_userid', 'off', 'enum', 3, 'register', 3, 16, 1, 0, '');
