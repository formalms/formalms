ALTER TABLE `learning_course_date_user` ADD COLUMN `requesting_unsubscribe` TINYINT(1) UNSIGNED DEFAULT NULL AFTER `overbooking`,
ADD COLUMN `requesting_unsubscribe_date` DATETIME AFTER `requesting_unsubscribe`;

ALTER TABLE `learning_course_editions_user` ADD COLUMN `requesting_unsubscribe` TINYINT(1) UNSIGNED DEFAULT NULL AFTER `subscribed_by`,
ADD COLUMN `requesting_unsubscribe_date` DATETIME AFTER `requesting_unsubscribe`;

ALTER TABLE `learning_course_date` CHANGE `name` `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';