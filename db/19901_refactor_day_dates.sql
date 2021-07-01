ALTER TABLE `learning_course_date_day`
    ADD COLUMN `id` int(11) NOT NULL AUTO_INCREMENT FIRST,
    ADD COLUMN `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP AFTER `pause_end`,
    ADD COLUMN `updated_at` datetime NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`) USING BTREE,
    DROP INDEX `id_date`,
    ADD UNIQUE INDEX `id_day_date`(`id_day`, `id_date`) USING BTREE;