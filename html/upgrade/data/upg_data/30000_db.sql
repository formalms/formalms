-- #FAY-44 migrate course info to mvc
UPDATE `learning_module` SET `mvc_path` = 'lms/course/infocourse' WHERE `learning_module`.`module_name` = "course" AND `learning_module`.`default_op` = "infocourse"

-- #19687 Languages - Increase text_key field lenght to 255
ALTER TABLE `core_lang_text` MODIFY COLUMN `text_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `id_text`