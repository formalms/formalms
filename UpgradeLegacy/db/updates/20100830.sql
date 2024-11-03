ALTER TABLE `learning_course` ADD COLUMN `auto_unsubscribe` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `credits`,
 ADD COLUMN `unsubscribe_date_limit` DATETIME NULL DEFAULT 0 AFTER `auto_unsubscribe`;
