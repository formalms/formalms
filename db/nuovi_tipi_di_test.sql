ALTER TABLE `learning_organization_access` ADD COLUMN `params` VARCHAR(255) NULL COMMENT '' AFTER `value`;

ALTER TABLE `learning_test` ADD COLUMN `obj_type` VARCHAR(45) NULL DEFAULT 'test' COMMENT '' AFTER `score_max`;
