-- SETTINGS
  -- Campo per i plugin
  ALTER TABLE `core_plugin`  ADD `regroup` INT(11) NOT NULL  AFTER `description`;
  -- Campo per i settaggi
  ALTER TABLE `core_setting` CHANGE `regroup` `regroup` INT(11) NOT NULL DEFAULT '0';

-- GENERAL
  -- Campo per i plugin "core"
  ALTER TABLE `core_plugin` ADD `core` INT(1) NOT NULL AFTER `active`;
  -- Eliminato campo code ora inutile
  ALTER TABLE `core_plugin` DROP COLUMN `code`;

-- REPORT
  -- Aggiungo i report come plugin

  -- Reset tabelle report
--  TRUNCATE `learning_report`;
--  ALTER TABLE `learning_report` DROP COLUMN `file_name`;
--  TRUNCATE `learning_report_filter`;
--  -- Insert new report
--  INSERT INTO `learning_report` (`id_report`, `report_name`, `class_name`, `use_user_selection`, `enabled`) VALUES (NULL, 'user_report', 'report_user', 'true', '1'), (NULL, 'course_report', 'report_course', 'true', '1'), (NULL, 'aggregate_report', 'report_aggregate', 'true', '1');
--  -- Insert new report_filters
--  INSERT INTO `learning_report_filter` (`id_filter`, `id_report`, `author`, `creation_date`, `filter_name`, `filter_data`, `is_public`, `views`) VALUES (NULL, '2', '270', '0000-00-00 00:00:00', 'Courses - Users', 'a:5:{s:9:"id_report";s:1:"4";s:11:"report_name";s:15:"Courses - Users";s:11:"rows_filter";a:2:{s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}}s:23:"columns_filter_category";s:5:"users";s:14:"columns_filter";a:6:{s:9:"time_belt";a:3:{s:10:"time_range";s:1:"0";s:10:"start_date";s:0:"";s:8:"end_date";s:0:"";}s:21:"org_chart_subdivision";i:0;s:11:"showed_cols";a:7:{i:0;s:12:"_CODE_COURSE";i:1;s:12:"_NAME_COURSE";i:2;s:6:"_INSCR";i:3;s:10:"_MUSTBEGIN";i:4;s:18:"_USER_STATUS_BEGIN";i:5;s:15:"_COMPLETECOURSE";i:6;s:14:"_TOTAL_SESSION";}s:12:"show_percent";b:1;s:9:"all_users";b:1;s:5:"users";a:0:{}}}', '1', '10'), (NULL, '1', '270', '0000-00-00 00:00:00', 'Users - Courses', 'a:5:{s:9:"id_report";s:1:"2";s:11:"report_name";s:15:"Users - Courses";s:11:"rows_filter";a:2:{s:5:"users";a:0:{}s:9:"all_users";b:1;}s:23:"columns_filter_category";s:7:"courses";s:14:"columns_filter";a:7:{s:21:"org_chart_subdivision";i:0;s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}s:11:"sub_filters";a:0:{}s:16:"filter_exclusive";s:1:"1";s:14:"showed_columns";a:12:{i:0;s:8:"_TH_CODE";i:1;s:25:"_TH_USER_INSCRIPTION_DATE";i:2;s:19:"_TH_USER_START_DATE";i:3;s:17:"_TH_USER_END_DATE";i:4;s:20:"_TH_LAST_ACCESS_DATE";i:5;s:15:"_TH_USER_STATUS";i:6;s:20:"_TH_USER_START_SCORE";i:7;s:20:"_TH_USER_FINAL_SCORE";i:8;s:21:"_TH_USER_COURSE_SCORE";i:9;s:23:"_TH_USER_NUMBER_SESSION";i:10;s:21:"_TH_USER_ELAPSED_TIME";i:11;s:18:"_TH_ESTIMATED_TIME";}s:13:"custom_fields";a:0:{}}}', '1', '3'), (NULL, '1', '270', '0000-00-00 00:00:00', 'Users - Learning Objects', 'a:5:{s:9:"id_report";s:1:"2";s:11:"report_name";s:24:"Users - Learning Objects";s:11:"rows_filter";a:2:{s:5:"users";a:0:{}s:9:"all_users";b:1;}s:23:"columns_filter_category";s:2:"LO";s:14:"columns_filter";a:6:{s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}s:8:"lo_types";a:8:{s:3:"faq";s:3:"faq";s:8:"glossary";s:8:"glossary";s:8:"htmlpage";s:8:"htmlpage";s:4:"item";s:4:"item";s:4:"link";s:4:"link";s:4:"poll";s:4:"poll";s:8:"scormorg";s:8:"scormorg";s:4:"test";s:4:"test";}s:13:"lo_milestones";a:0:{}s:14:"showed_columns";a:8:{i:0;s:9:"user_name";i:1;s:11:"course_name";i:2;s:13:"course_status";i:3;s:7:"lo_type";i:4;s:7:"lo_name";i:5;s:12:"firstAttempt";i:6;s:11:"lastAttempt";i:7;s:9:"lo_status";}s:13:"custom_fields";a:0:{}}}', '1', '0'), (NULL, '1', '270', '0000-00-00 00:00:00', 'Users - 30 Days Delay', 'a:5:{s:9:"id_report";s:1:"2";s:11:"report_name";s:21:"Users - 30 Days Delay";s:11:"rows_filter";a:2:{s:5:"users";a:0:{}s:9:"all_users";b:1;}s:23:"columns_filter_category";s:5:"delay";s:14:"columns_filter";a:9:{s:21:"report_type_completed";b:1;s:19:"report_type_started";b:1;s:21:"day_from_subscription";s:2:"30";s:20:"day_until_course_end";s:0:"";s:21:"date_until_course_end";s:0:"";s:21:"org_chart_subdivision";i:0;s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}s:14:"showed_columns";a:7:{i:0;s:9:"_LASTNAME";i:1;s:5:"_NAME";i:2;s:7:"_STATUS";i:3;s:6:"_EMAIL";i:4;s:11:"_DATE_INSCR";i:5;s:18:"_DATE_FIRST_ACCESS";i:6;s:22:"_DATE_COURSE_COMPLETED";}}}', '1', '0');

INSERT INTO `core_plugin` (`name`, `title`, `category`, `version`, `author`, `link`, `priority`, `description`, `regroup`, `active`, `core`) VALUES('FormaAuth', 'Forma Auth', '', '1.0', 'Joint Technologies', '', 0, 'forma auth', 1488290190, 1, 1);

UPDATE `core_lang_text` SET `text_key` = 'course_report' WHERE `core_lang_text`.`text_key` = 'courses_report';