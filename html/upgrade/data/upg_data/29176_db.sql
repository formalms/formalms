SET @max = ((SELECT MAX( idClass )+1 FROM core_event_class));

INSERT INTO core_event_class ( idClass, class, platform, description )
VALUES 
(@max, 'UserCourseInsertOverbooking', 'lms-a', 'A user requests subscription to course that has set overbooking');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES (1, @max);

INSERT INTO `core_event_manager` (`idEventMgr`, `idClass`, `permission`, `channel`, `recipients`, `show_level`) VALUES (@max, @max, 'mandatory', 'email', '_EVENT_RECIPIENTS_TEACHER', 'godadmin,admin,user');
