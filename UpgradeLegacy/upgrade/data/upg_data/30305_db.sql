ALTER TABLE `learning_test` ADD COLUMN `show_quest_score` tinyint(1) NOT NULL;
UPDATE `core_role` SET `roleid` = '/lms/dashboard/view' WHERE `roleid` = '/lms/course/public/dashboard/view';
