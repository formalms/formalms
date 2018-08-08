--
-- Update database formalms
--
--
-- Update db script from formalms 1.4 to formalms 2.0
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