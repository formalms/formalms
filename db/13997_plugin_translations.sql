ALTER TABLE `core_lang_text`
    ADD `plugin_id` INT NULL,
    DROP INDEX `text_key`, ADD UNIQUE `text_key` (`text_key`, `text_module`, `plugin_id`);
