--
-- Update database formalms
--
--
-- Update db script from formalms 2.0 to formalms 2.1
--

-- ------------------------------------------------------------------

INSERT INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES
('ORG_CHART', 'Org Chart Tree', 'core_org_chart_tree', 'idOrg');

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_menucustom m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcustom, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/coursestats/view')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/coursestats/view_all')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_course m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcourse, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/coursestats/view')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/coursestats/view_all')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_menucustom m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcustom, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/statistic/view')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/statistic/view_all')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_course m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcourse, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/statistic/view')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/statistic/view_all')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_menucustom m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcustom, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/stats/view_user')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/stats/view_all_statuser')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_course m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcourse, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/stats/view_user')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/stats/view_all_statuser')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_menucustom m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcustom, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/stats/view_course')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/stats/view_all_stat_course')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_course m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcourse, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/stats/view_course')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/stats/view_all_stat_course')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_menucustom m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcustom, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/light_repo/view')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/light_repo/view')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_course m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcourse, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/light_repo/view')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/light_repo/view_all')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_menucustom m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcustom, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/coursereport/view')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/coursereport/view')
	) x
);

DELETE FROM core_role_members
WHERE idst IN (
	SELECT idst FROM (
		select DISTINCT(ra.idst)
		from learning_course m
		join (select 1 lvl union select 2 lvl union select 3 lvl union select 4 lvl union select 5 lvl union select 6 lvl union select 7 lvl) core_lvl
		join core_group g on g.groupid like concat('/lms/custom/', m.idcourse, '/', lvl)
		join core_role r on r.roleid like concat('/lms/course/private/coursereport/view')
		join core_role_members rm on r.idst = rm.idst and g.idst = rm.idstMember
		join core_role ra on ra.roleid like concat('/lms/course/private/coursereport/view')
	) x
);


INSERT ignore INTO core_setting
(param_name,param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
VALUES ('on_path_in_mycourses', 'off', 'enum',	3, 	0, 	4, 	2, 	1, 	0, 	'' );

--
-- Impostazioni Smtp
--
INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('use_smtp', '', 'on_off', 255, 'Use Smtp', 14, 1, 1, 0, '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_host', '', 'string', 255, 'Smtp Host', 14, 2, 1, 0, '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_port', '', 'string', 255, 'Smtp Port', 14, 3, 1, 0, '');


INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_secure', '', 'string', 255, 'Smtp Secure', 14, 4, 1, 0, '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_user', '', 'string', 255, 'Smtp User', 14, 5, 1, 0, '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_pwd', '', 'string', 255, 'Smtp Password', 14, 6, 1, 0, '');


DELETE FROM `learning_middlearea` WHERE `learning_middlearea`.`obj_index` = 'tb_label';

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('use_course_label', 'off', 'enum', '3', 'main', '4', '14', '1', '0', '');