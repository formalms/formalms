ALTER TABLE learning_course
 ADD id_menucustom INT(11);
ALTER TABLE learning_course
 ADD CONSTRAINT fk_menucustom FOREIGN KEY (id_menucustom) REFERENCES learning_menucustom (idCustom) ON UPDATE CASCADE ON DELETE NO ACTION;
