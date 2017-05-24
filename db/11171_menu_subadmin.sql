-- LABEL; QUEST_CATEGORY, PERIOD TIME
UPDATE `core_menu_under_config` SET `of_platform` = 'lms' WHERE `core_menu_under_config`.`idUnder` = 31;

UPDATE `core_menu_under_config` SET `of_platform` = 'lms' WHERE `core_menu_under_config`.`idUnder` = 1;

UPDATE `core_menu_under_config` SET `of_platform` = 'lms' WHERE `core_menu_under_config`.`idUnder` = 33;


-- AREA ELERNING
UPDATE core_menu_under_elearning set of_platform = 'lms';

-- NEWSLETTER
UPDATE core_menu_under_content SET of_platform = 'framework' WHERE module_name = 'newsletter';

-- MENU BENVENUTO
INSERT INTO `core_menu_config` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(1, '', '', 1, 'true');

INSERT INTO `core_menu_under_config` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(35, 1, 'dashboard', '_DASHBOARD', '', 'view', NULL, 1, '', '', 'adm/dashboard/show');
