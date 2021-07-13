ALTER TABLE `learning_course_date_day`
    ADD COLUMN `id` int(11) NOT NULL AUTO_INCREMENT FIRST,
    ADD COLUMN `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP AFTER `pause_end`,
    ADD COLUMN `updated_at` datetime NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    ADD COLUMN `calendarId` varchar(255) NOT NULL AFTER `pause_end`,
    ADD COLUMN `deleted` tinyint(1) NULL DEFAULT 0 AFTER `calendarId`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`) USING BTREE,
    DROP INDEX `id_date`,
    ADD INDEX `id_day_date`(`id_day`, `id_date`) USING BTREE;

ALTER TABLE `learning_course` ADD COLUMN `sendCalendar` tinyint(1) NULL DEFAULT 0;
ALTER TABLE `learning_course` ADD COLUMN `calendarId` varchar(255) NOT NULL AFTER `sendCalendar`;