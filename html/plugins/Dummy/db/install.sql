CREATE TABLE `learning_dummy_userslog` (
  `id` int(11) NOT NULL,
  `username` text NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `learning_dummy_userslog`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `learning_dummy_userslog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;