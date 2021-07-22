
-- Evento UserNewApi

INSERT INTO `core_event_class` (`class`, `platform`, `description`) VALUES ('UserNewApi', 'framework', '');
set @v_idst=LAST_INSERT_ID();

INSERT INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, @v_idst);

INSERT INTO `core_event_manager` (`idClass`, `permission`, `channel`, `recipients`, `show_level`) 
VALUES
(@v_idst, 'mandatory', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user');


-- Evento UserNewApi

INSERT INTO `core_event_class` (`class`, `platform`, `description`) VALUES ('UserCourseInsertedApi', 'lms-a', '');
set @v_idst=LAST_INSERT_ID();

INSERT INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, @v_idst);

INSERT INTO `core_event_manager` (`idClass`, `permission`, `channel`, `recipients`, `show_level`) 
VALUES
(@v_idst, 'mandatory', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user');