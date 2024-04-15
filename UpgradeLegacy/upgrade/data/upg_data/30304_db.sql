UPDATE core_menu_under SET module_name = 'dashboard' WHERE default_name = '_DASHBOARD' and associated_token = 'view' and of_platform = 'lms';
INSERT INTO core_role ( idst, roleId )
SELECT max(idst)+1, '/lms/course/public/dashboard/view' FROM core_st LIMIT 1;