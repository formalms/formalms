INSERT INTO `learning_lo_types` (`objectType`,`className`,`fileName`,`classNameTrack`,`fileNameTrack`) VALUES ('test360','Learning_Test360','learning.test360.php','Track_Test','track.test.php');

INSERT INTO `learning_quest_type` (`type_quest`, `type_file`, `type_class`, `sequence`) VALUES ('course_valutation', 'class.course_valutation.php', 'CourseValutation_Question', '11');

ALTER TABLE `learning_organization_access` ADD COLUMN `params` VARCHAR(255) NULL COMMENT '' AFTER `value`;

ALTER TABLE `learning_test` ADD COLUMN `obj_type` VARCHAR(45) NULL DEFAULT 'test' COMMENT '' AFTER `score_max`;
