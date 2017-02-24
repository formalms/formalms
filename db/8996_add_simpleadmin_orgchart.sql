insert into core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
insert into core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/usermanagement/add_org'), NULL);

insert into core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
insert into core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/usermanagement/mod_org'), NULL);

insert into core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
insert into core_role(idst, roleid, description) VALUES
(@v_idst, concat('/framework/admin/usermanagement/del_org'), NULL);