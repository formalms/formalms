ALTER TABLE  `core_user` ADD UNIQUE (`facebook_id`);
ALTER TABLE  `core_user` ADD UNIQUE (`google_id`);
ALTER TABLE  `core_user` ADD UNIQUE (`twitter_id`);
ALTER TABLE  `core_user` ADD UNIQUE (`linkedin_id`);


DELETE FROM `learning_menu_under` WHERE module_name = 'classlocation';
UPDATE  `learning_menu_under` SET  `module_name` =  'location', `default_op` =  '', `class_file` =  '', `class_name` =  '', `mvc_path` =  'alms/location/show' WHERE  `module_name` = 'classroom';