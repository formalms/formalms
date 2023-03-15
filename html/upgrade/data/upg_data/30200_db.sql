INSERT IGNORE INTO `dashboard_blocks` (`block_class`, `created_at`) VALUES ('DashboardBlockCommunicationLms', CURRENT_TIMESTAMP);
INSERT IGNORE INTO `dashboard_blocks` (`block_class`, `created_at`) VALUES ('DashboardBlockNewsLms', CURRENT_TIMESTAMP);

ALTER TABLE `learning_course` CHANGE `credits` `credits` double NOT NULL DEFAULT 0;