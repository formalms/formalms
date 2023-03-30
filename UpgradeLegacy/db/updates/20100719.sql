ALTER TABLE `learning_kb_res`
	ADD `is_mobile` tinyint(1) NOT NULL DEFAULT '0' AFTER `force_visible`;

ALTER TABLE `learning_kb_res`
	ADD `r_env_parent_id` int(11) NULL AFTER `r_env`;