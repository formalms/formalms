DELETE FROM learning_coursereport
                            WHERE id_report NOT IN (
                                SELECT MIN(id_report) 
                                FROM learning_coursereport 
                                GROUP BY source_of, id_course, id_source
                                );
DELETE FROM learning_coursereport
                            WHERE id_course = 0;
DELETE FROM learning_coursereport_score
                            WHERE id_report NOT IN (
                                SELECT id_report
                                FROM learning_coursereport 
                              
                                );


ALTER TABLE learning_coursereport
        ADD CONSTRAINT unique_coursereport UNIQUE (source_of, id_course, id_source);

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
                        ('force_scorm_finish', 'on', 'enum', 3, '0', 4, 17, 1, 0, '');