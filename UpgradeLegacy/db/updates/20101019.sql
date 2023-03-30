ALTER TABLE `learning_course_date` ADD `sub_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `learning_course_date` ADD `sub_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `learning_course_date` ADD `unsubscribe_date_limit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';