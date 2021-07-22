-- Creazione permesso view_all per modulo repository

insert into core_st(idst) values(null);

set @v_idst=LAST_INSERT_ID();

insert into core_role(idst, roleid, description) VALUES
(@v_idst, concat('/lms/course/private/light_repo/view_all'), NULL);