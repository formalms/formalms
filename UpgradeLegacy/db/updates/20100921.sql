INSERT INTO `core_menu_under` (
`idUnder` ,
`idMenu` ,
`module_name` ,
`default_name` ,
`default_op` ,
`associated_token` ,
`of_platform` ,
`sequence` ,
`class_file` ,
`class_name` ,
`mvc_path`
)
VALUES (
NULL , '3', 'privacypolicy', '_PRIVACYPOLICIES', '', 'view', NULL , '6', '', '', 'adm/privacypolicy/show'
);

ALTER TABLE `core_group_fields` ADD COLUMN `user_inherit` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `useraccess`;