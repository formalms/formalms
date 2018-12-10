UPDATE core_menu_under mu
INNER JOIN core_menu m
    ON mu.idMenu = m.idMenu
SET mu.of_platform = 'alms'
WHERE mu.of_platform = 'lms'
    AND m.of_platform NOT IN ('lms');

UPDATE core_menu_under
SET of_platform = 'framework'
WHERE of_platform IS NULL;