INSERT INTO core_event_class ( idClass, class, platform, description )
VALUES 
((SELECT MAX( idClass ) FROM core_event_class b) +1, 'UserCourseInsertOverbooking', 'lms-a', 'A user requests subscription to course that has set overbooking');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES (1, (SELECT MAX( idClass ) FROM core_event_class));

INSERT INTO `core_event_manager` (`idEventMgr`, `idClass`, `permission`, `channel`, `recipients`, `show_level`) VALUES ((SELECT MAX( idClass ) FROM core_event_class), (SELECT MAX( idClass ) FROM core_event_class), 'mandatory', 'email', '_EVENT_RECIPIENTS_TEACHER', 'godadmin,admin,user');
