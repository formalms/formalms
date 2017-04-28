-- adding catalogue link into the top menu
insert into  learning_module (module_name, default_name, token_associated,module_info, mvc_path ) values ('course','_CATALOGUE','view','all','lms/catalog/show');
insert   into learning_middlearea (obj_index,disabled,idst_list,sequence)  values ('mo_46',0,'a:0:{}',0);
insert into learning_menucourse_under (idCourse, idModule, idMain,sequence) values (0,46,0,3);

-- deleting tab tb_catalog from middle area

DELETE FROM `learning_middlearea` WHERE `obj_index` = "tb_catalog";