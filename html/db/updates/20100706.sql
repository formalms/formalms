CREATE TABLE  `learning_rules_log` (
	`id_log` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`log_time` DATETIME NOT NULL ,
	`applied` TEXT NOT NULL
) ENGINE = INNODB;
