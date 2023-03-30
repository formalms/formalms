SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `conference_setting`;
DROP TABLE IF EXISTS `learning_setting`;
DROP TABLE IF EXISTS `learning_course_edition`;
DROP TABLE IF EXISTS `learning_competence_category_text`;
DROP TABLE IF EXISTS `learning_competence_text`;

UPDATE `core_user` SET `avatar` = `photo` WHERE `avatar` = '' AND `photo` <> '';
ALTER TABLE `core_user` DROP `photo`;


SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;