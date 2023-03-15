ALTER TABLE learning_testtrack_answer ADD COLUMN number_time TINYINT(4) NULL DEFAULT '1' COMMENT '' AFTER user_answer;
            
ALTER TABLE learning_test ADD COLUMN retain_answers_history TINYINT(1) NOT NULL DEFAULT '0' COMMENT '' AFTER obj_type;

ALTER TABLE learning_testtrack_answer
CHANGE COLUMN number_time number_time TINYINT(4) NOT NULL DEFAULT '1' COMMENT '' ,
DROP PRIMARY KEY,
ADD PRIMARY KEY (idTrack, idQuest, idAnswer, number_time)  COMMENT '';