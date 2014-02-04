-- INSERT INTO `core_role` (`idst`, `roleid`, `description`) VALUES(228, '/lms/admin/course/add_category', NULL);
-- INSERT INTO `core_role` (`idst`, `roleid`, `description`) VALUES(229, '/lms/admin/course/mod_category', NULL);
-- INSERT INTO `core_role` (`idst`, `roleid`, `description`) VALUES(230, '/lms/admin/course/del_category', NULL);


INSERT INTO `learning_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`)
VALUES
(8, 1, 'coursecategory', '_COURSECATEGORY', '', 'view', NULL, 4, '', '', 'alms/coursecategory/show');


INSERT INTO `core_role` (`idst`, `roleid`, `description`) VALUES(228, '/lms/admin/coursecategory/add', NULL);
INSERT INTO `core_role` (`idst`, `roleid`, `description`) VALUES(229, '/lms/admin/coursecategory/mod', NULL);
INSERT INTO `core_role` (`idst`, `roleid`, `description`) VALUES(230, '/lms/admin/coursecategory/del', NULL);

INSERT INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_COURSECATEGORY', 'standard', '');
INSERT INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` ) VALUES (LAST_INSERT_ID(), 'english', 'Course categories', now());


INSERT INTO core_role_members (idst, idstMember)
SELECT 228 as idst, idst as idstMember
FROM core_group
WHERE (groupid like "/framework/level/godadmin%"
OR     groupid like "/framework/level/publicadmin%"
OR     groupid like "/framework/adminrules%"
OR     groupid like "/framework/publicadminrules%" );


INSERT INTO core_role_members (idst, idstMember)
SELECT 229 as idst, idst as idstMember
FROM core_group
WHERE (groupid like "/framework/level/godadmin%"
OR     groupid like "/framework/level/publicadmin%"
OR     groupid like "/framework/adminrules%"
OR     groupid like "/framework/publicadminrules%" );


INSERT INTO core_role_members (idst, idstMember)
SELECT 230 as idst, idst as idstMember
FROM core_group
WHERE (groupid like "/framework/level/godadmin%"
OR     groupid like "/framework/level/publicadmin%"
OR     groupid like "/framework/adminrules%"
OR     groupid like "/framework/publicadminrules%" );
