ALTER TABLE `core_task` DROP PRIMARY KEY, ADD PRIMARY KEY( `sequence`);

ALTER TABLE `core_task` CHANGE `sequence` `sequence` INT(3) NOT NULL AUTO_INCREMENT;