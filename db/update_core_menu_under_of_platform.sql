UPDATE core_menu_under
SET of_platform = 'alms'
WHERE of_platform = 'lms';

UPDATE core_menu_under
SET of_platform = 'framework'
WHERE of_platform IS NULL;