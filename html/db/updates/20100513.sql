UPDATE `learning_quest_type_poll`
SET `sequence` = `sequence` + 2
WHERE `sequence` > 3;

INSERT INTO `learning_quest_type_poll` (`type_quest`, `type_file`, `type_class`, `sequence`)
VALUES ('doc_valutation', 'class.doc_valutation.php', 'DocValutation_Question', '4'),
('course_valutation', 'class.course_valutation.php', 'CourseValutation_Question', '5');

INSERT INTO `learning_module` (`idModule`, `module_name`, `default_op`, `default_name`, `token_associated`, `file_name`, `class_name`, `module_info`)
VALUES (NULL, 'presence', 'main', '_PRESENCE', 'view', 'class.presence.php', 'Module_Presence', '');

ALTER TABLE `learning_organization` ADD `publish_for` INT( 1 ) NOT NULL;

ALTER TABLE `learning_course_date_day` ADD `pause_begin` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
ADD `pause_end` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';


ALTER TABLE `learning_commontrack` ADD COLUMN `first_complete` DATETIME AFTER `firstAttempt`, ADD COLUMN `last_complete` DATETIME AFTER `first_complete`;

ALTER TABLE `learning_courseuser` ADD COLUMN `date_begin_validity` DATETIME AFTER `new_forum_post`,
 ADD COLUMN `date_expire_validity` DATETIME AFTER `date_begin_validity`;
