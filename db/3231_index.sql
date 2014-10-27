--
-- ------------
-- create indexes  lack

ALTER TABLE learning_certificate_assign ADD INDEX `id_course` ( `id_course` );
ALTER TABLE learning_certificate_assign ADD INDEX `id_user` ( `id_user` ) ;
