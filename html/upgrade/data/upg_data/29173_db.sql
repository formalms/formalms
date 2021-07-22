-- new LO menu
UPDATE `learning_module` SET `mvc_path` = 'lms/lomanager/show' WHERE `learning_module`.`module_name` = 'storage';
UPDATE `learning_module` SET `mvc_path` = 'lms/lo/show' WHERE `learning_module`.`module_name` = 'organization';