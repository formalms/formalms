--
-- ------------
-- change collation to default value

ALTER TABLE `learning_certificate_meta` CHANGE `title` `title` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `learning_certificate_meta` CHANGE `description` `description` longtext NOT NULL;

ALTER TABLE `learning_certificate_meta_assign` CHANGE `cert_file` `cert_file` VARCHAR( 255 ) NOT NULL DEFAULT '';

ALTER TABLE `learning_course_date` CHANGE `price` `price` varchar(255) NOT NULL DEFAULT '0';
ALTER TABLE `learning_course_date` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `learning_course_date` CHANGE `code` `code` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `learning_course_date_presence` CHANGE `score` `score` varchar(255) DEFAULT NULL;

ALTER TABLE `learning_course_date_user` CHANGE `presence` `presence` mediumtext;

ALTER TABLE `learning_transaction` CHANGE `method` `method` varchar(255) NULL DEFAULT '';
ALTER TABLE `learning_transaction` CHANGE `payment_note`  `payment_note` text NOT NULL;
ALTER TABLE `learning_transaction` CHANGE `course_note` `course_note` text NOT NULL;
