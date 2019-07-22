INSERT INTO learning_middlearea (`obj_index`, `disabled`, `idst_list`, `sequence`)
VALUES ('tb_dashboard', '1', 'a:0:{}', '0');


-- label
INSERT IGNORE INTO core_lang_text (text_key, text_module, text_attributes)
VALUES ('_DASHBOARD', 'middlearea', '');

INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text)
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_DASHBOARD ' and text_module = ' middlearea '),
        'english', 'Dashboard');
INSERT IGNORE INTO core_lang_translation (id_text, lang_code, translation_text)
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_DASHBOARD ' and text_module = ' middlearea '),
        'italian', 'Dashboard');

INSERT INTO `learning_module` VALUES (47, 'dashboard', 'show', '_DASHBOARD', 'view', '', '', 'all', 'lms/dashboard/show');

INSERT INTO `core_menu`(`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES (600, '_DASHBOARD', '', 4, 'true', 'true', NULL, NULL, 'lms');

INSERT INTO `core_menu_under`(`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES (600, 600, 'course', '_DASHBOARD', NULL, 'view', 'lms', 4, NULL, NULL, 'lms/dashboard/show');