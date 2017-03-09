-- edition.date_end DATETIME
ALTER TABLE  `learning_course_editions` CHANGE  `date_end`  `date_end` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00';
