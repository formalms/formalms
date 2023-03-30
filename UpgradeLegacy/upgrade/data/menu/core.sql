TRUNCATE TABLE `core_menu`;
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(1, '', '', 1, 'true'),
(2, '_USER_MANAGMENT', '', 2, 'false'),
(3, '_TRASV_MANAGMENT', '', 3, 'false'),
(4, '_ADMINISTRATORS', '', 4, 'false'),
(5, '_LANGUAGE', '', 5, 'false'),
(6, '', '', 6, 'true');

TRUNCATE TABLE `core_menu_under`;
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(NULL, 1, 'dashboard', '_DASHBOARD', '', 'view', NULL, 1, '', '', 'adm/dashboard/show'),

(NULL, 2, 'usermanagement', '_LISTUSER', '', 'view', NULL, 1, '', '', 'adm/usermanagement/show'),
(NULL, 2, 'groupmanagement', '_MANAGE_GROUPS', '', 'view', NULL, 2, '', '', 'adm/groupmanagement/show'),
(NULL, 2, 'competences', '_COMPETENCES', '', 'view', NULL, 3, '', '', 'adm/competences/show'),
(NULL, 2, 'functionalroles', '_FUNCTIONAL_ROLE', '', 'view', NULL, 4, '', '', 'adm/functionalroles/show'),

(NULL, 3, 'setting', '_CONFIGURATION', '', 'view', NULL, 1, 'class.configuration.php', 'Module_Configuration', 'adm/setting/show'),
(NULL, 3, 'field_manager', '_FIELD_MANAGER', 'field_list', 'view', NULL, 2, 'class.field_manager.php', 'Module_Field_Manager', ''),
(NULL, 3, 'event_manager', '_EVENTMANAGER', 'display', 'view_event_manager', NULL, 3, 'class.event_manager.php', 'Module_Event_Manager', ''),
(NULL, 3, 'iotask', '_IOTASK', 'iotask', 'view', NULL, 4, 'class.iotask.php', 'Module_IOTask', ''),
(NULL, 3, 'code', '_CODE', 'list', 'view', NULL, 5, 'class.code.php', 'Module_Code', ''),
(NULL, 3, 'privacypolicy', '_PRIVACYPOLICIES', '', 'view', NULL, 6, '', '', 'adm/privacypolicy/show'),

(NULL, 4, 'adminrules', '_ADMIN_RULES', '', 'view', NULL, 1, '', '', 'adm/adminrules/show'),
(NULL, 4, 'publicadminrules', '_PUBLIC_ADMIN_RULES', '', 'view', NULL, 2, '', '', 'adm/publicadminrules/show'),
(NULL, 4, 'adminmanager', '_ADMIN_MANAGER', '', 'view', NULL, 3, '', '', 'adm/adminmanager/show'),
(NULL, 4, 'publicadminmanager', '_PUBLIC_ADMIN_MANAGER', '', 'view', NULL, 4, '', '', 'adm/publicadminmanager/show'),

(NULL, 5, 'lang', '_LANG', '', 'view', NULL, 1, '', '', 'adm/lang/show'),

(NULL, 6, 'newsletter', '_NEWSLETTER', 'newsletter', 'view', NULL, 1, 'class.newsletter.php', 'Module_Newsletter', '');