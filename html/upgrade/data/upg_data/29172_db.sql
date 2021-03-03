INSERT IGNORE INTO `dashboard_blocks` (`id`, `block_class`, `created_at`)
VALUES (9, 'DashboardBlockWelcomeLms',CURRENT_TIMESTAMP);

delete from learning_module where module_name like 'chat';
