ALTER TABLE `learning_communication` ADD COLUMN `id_course` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `id_category`;

ALTER TABLE `learning_course_date_presence` ADD `note` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

INSERT INTO `learning_module` (`idModule`, `module_name`, `default_op`, `default_name`, `token_associated`, `file_name`, `class_name`, `module_info`, `mvc_path`) VALUES (NULL, 'presence', '', '_PRESENCE', 'view', '', '', '', 'presence/presence');