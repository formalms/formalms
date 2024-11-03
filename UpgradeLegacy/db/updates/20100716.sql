UPDATE `core_setting` SET  `sequence` = 1 WHERE  `param_name` = 'social_fb_api' LIMIT 1;
UPDATE `core_setting` SET  `sequence` = 2 WHERE  `param_name` = 'social_fb_secret' LIMIT 1;
UPDATE `core_setting` SET  `sequence` = 4 WHERE  `param_name` = 'social_twitter_consumer' LIMIT 1;
UPDATE `core_setting` SET  `sequence` = 5 WHERE  `param_name` = 'social_twitter_secret' LIMIT 1;
UPDATE `core_setting` SET  `sequence` = 7 WHERE  `param_name` = 'social_linkedin_access' LIMIT 1;
UPDATE `core_setting` SET  `sequence` = 8 WHERE  `param_name` = 'social_linkedin_secret' LIMIT 1;

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
	('social_fb_active', 'off', 'enum', '3', 'main', '12', '0', '1', '0', '');

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
	('social_twitter_active', 'off', 'enum', '3', 'main', '12', '3', '1', '0', '');

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
	('social_linkedin_active', 'off', 'enum', '3', 'main', '12', '6', '1', '0', '');

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
	('social_google_active', 'off', 'enum', '3', 'main', '12', '9', '1', '0', '');

ALTER TABLE `learning_coursereport`
	MODIFY `source_of` enum('test','activity','scorm','final_vote','scorm_item') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'test';