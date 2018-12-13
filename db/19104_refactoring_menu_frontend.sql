ALTER TABLE core_menu 
ADD COLUMN of_platform VARCHAR(255) DEFAULT 'framework';

-- ######################################################################### --

UPDATE core_menu_under
SET of_platform = 'framework'
WHERE of_platform IS NULL;


UPDATE core_menu_under
SET of_platform = 'alms'
WHERE of_platform = 'lms';

-- ######################################################################### --

INSERT INTO core_menu (name, image, sequence, is_active, collapse, idParent, idPlugin, of_platform)
VALUES ('_MYCOURSES', '', 1, 'true', 'true', NULL, NULL, 'lms');

INSERT INTO core_menu_under (idMenu, module_name, default_name, default_op, associated_token, of_platform, sequence, class_file, class_name, mvc_path)
VALUES (LAST_INSERT_ID(), 'course', '_MYCOURSES', NULL, 'view', 'lms', 1, NULL, NULL, 'elearning/show');


INSERT INTO core_menu (name, image, sequence, is_active, collapse, idParent, idPlugin, of_platform)
VALUES ('_CATALOGUE', '', 2, 'true', 'true', NULL, NULL, 'lms');

INSERT INTO core_menu_under (idMenu, module_name, default_name, default_op, associated_token, of_platform, sequence, class_file, class_name, mvc_path)
VALUES (LAST_INSERT_ID(), 'coursecatalogue', '_CATALOGUE', NULL, 'view', 'lms', 2, NULL, NULL, 'lms/catalog/show');


INSERT INTO core_menu (name, image, sequence, is_active, collapse, idParent, idPlugin, of_platform)
VALUES ('_PUBLIC_FORUM', '', 3, 'true', 'true', NULL, NULL, 'lms');

INSERT INTO core_menu_under (idMenu, module_name, default_name, default_op, associated_token, of_platform, sequence, class_file, class_name, mvc_path)
VALUES (LAST_INSERT_ID(), 'public_forum', '_PUBLIC_FORUM', 'forum', 'view', 'lms', 3, NULL, NULL, NULL);


INSERT INTO core_menu (name, image, sequence, is_active, collapse, idParent, idPlugin, of_platform)
VALUES ('_HELPDESK', '<span class="glyphicon glyphicon-question-sign top-menu__label"></span>', 1000, 'true', 'true', NULL, NULL, 'lms');

INSERT INTO core_menu_under (idMenu, module_name, default_name, default_op, associated_token, of_platform, sequence, class_file, class_name, mvc_path)
VALUES (LAST_INSERT_ID(), 'helpdesk', '_HELPDESK', 'popup', 'view', 'lms', 1000, NULL, NULL, NULL);

-- ######################################################################### --

INSERT INTO core_st (idst)
VALUES (NULL);

INSERT INTO core_role (idst, roleid, description)
VALUES (LAST_INSERT_ID(), '/lms/course/public/helpdesk/view', NULL);

INSERT INTO core_role_members (idst, idstMember)
VALUES (LAST_INSERT_ID(), 1);
