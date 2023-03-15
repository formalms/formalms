ALTER TABLE `learning_forum`
  ADD COLUMN `max_threads` int(11) NULL DEFAULT 0 AFTER `emoticons`,
  ADD COLUMN `threads_are_private` tinyint(1) NULL DEFAULT 0 AFTER `max_threads`;