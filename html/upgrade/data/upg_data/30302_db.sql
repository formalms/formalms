ALTER TABLE `learning_testquestanswer` ADD INDEX `idQuest_idx` (`idQuest`) USING BTREE;
ALTER TABLE `learning_coursereport` ADD INDEX `idCourse_idReport_idx` (`id_course`,`id_report`) USING BTREE;
ALTER TABLE `core_field_userentry` ADD INDEX `idUser_idCommon_idx` (`id_user`,`id_common`) USING BTREE;
ALTER TABLE `core_field_son` ADD INDEX `idCommonSon_idFiled_langCode_idx` (`id_common_son`,`idField`,`lang_code`) USING BTREE;