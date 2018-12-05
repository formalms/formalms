INSERT INTO core_menu (name, image, sequence, is_active, collapse, idParent, idPlugin, of_platform)
VALUES ('_MY_COURSES', '', 1, 'true', 'true', NULL, NULL, 'lms');

INSERT INTO core_menu_under (idMenu, module_name, default_name, default_op, associated_token, of_platform, sequence, class_file, class_name, mvc_path)
VALUES (LAST_INSERT_ID(), 'course', '_MY_COURSES', NULL, 'view', 'lms', 1, NULL, NULL, 'elearning/show');


INSERT INTO core_menu (name, image, sequence, is_active, collapse, idParent, idPlugin, of_platform)
VALUES ('_CATALOGUE', '', 2, 'true', 'true', NULL, NULL, 'lms');

INSERT INTO core_menu_under (idMenu, module_name, default_name, default_op, associated_token, of_platform, sequence, class_file, class_name, mvc_path)
VALUES (LAST_INSERT_ID(), 'coursecatalogue', '_CATALOGUE', NULL, 'view', 'lms', 2, NULL, NULL, 'lms/catalog/show');


INSERT INTO core_menu (name, image, sequence, is_active, collapse, idParent, idPlugin, of_platform)
VALUES ('_PUBLIC_FORUM', '', 3, 'true', 'true', NULL, NULL, 'lms');

INSERT INTO core_menu_under (idMenu, module_name, default_name, default_op, associated_token, of_platform, sequence, class_file, class_name, mvc_path)
VALUES (LAST_INSERT_ID(), 'public_forum', '_PUBLIC_FORUM', 'forum', 'view', 'lms', 3, NULL, NULL, NULL);
