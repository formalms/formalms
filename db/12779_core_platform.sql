delete from core_platform where platform like 'menu_%';

insert into core_platform
(platform, class_file, class_name, class_file_menu, class_name_menu, class_name_menu_managment, file_class_config, class_name_config, var_default_template, class_default_admin, sequence, is_active, mandatory, dependencies, main, hidden_in_config) VALUES
  ('framework', '', '', 'class.admin_menu_fw.php', 'Admin_Framework', 'Admin_Managment_Framework', 'class.conf_fw.php', 'Config_Framework', 'defaultTemplate', 'Module', 1, 'true', 'true', '', 'false', 'false')
, ('lms', '', '', 'class.admin_menu_lms.php', 'Admin_Lms', 'Admin_Managment_Lms', 'class.conf_lms.php', 'Config_Lms', 'defaultTemplate', 'LmsAdminModule', 2, 'true', 'false', '', 'true', 'false')
;
-- ('scs', '', '', 'class.admin_menu_scs.php', 'Admin_Scs', '', 'class.conf_scs.php', 'Config_Scs', 'defaultTemplate', 'ScsAdminModule', 4, 'true', 'false', '', 'false', 'false')