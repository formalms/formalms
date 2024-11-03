ALTER TABLE  `learning_label` ADD  `sequence` INT( 3 ) NOT NULL;

ALTER TABLE `learning_test` ADD `mandatory_answer` tinyint(1) unsigned NOT NULL DEFAULT '0';