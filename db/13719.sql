ALTER TABLE `learning_middlearea` ADD `is_home` TINYINT(4) NOT NULL DEFAULT '0' AFTER `sequence`
UPDATE `learning_middlearea` SET `is_home` = '1' WHERE `learning_middlearea`.`obj_index` = 'tb_elearning'