UPDATE  `core_event_manager` SET  `recipients` =  '_EVENT_RECIPIENTS_USER' WHERE  `core_event_manager`.`idEventMgr` =10;
UPDATE  `core_event_manager` SET  `recipients` =  '_EVENT_RECIPIENTS_USER' WHERE  `core_event_manager`.`idEventMgr` =11;
UPDATE  `core_event_manager` SET  `recipients` =  '_EVENT_RECIPIENTS_USER' WHERE  `core_event_manager`.`idEventMgr` =12;

DROP TABLE `learning_coursepath_slot`;

ALTER TABLE  `learning_coursepath_user` ADD  `course_completed` INT( 3 ) NOT NULL AFTER  `waiting`;