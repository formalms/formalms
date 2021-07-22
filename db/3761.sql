-- bug #3761

-- event UserCourseEnded , update wrong label Receipients
UPDATE core_event_manager SET  recipients = '_EVENT_RECIPIENTS_TEACHER'  WHERE idEventMgr = 12;

