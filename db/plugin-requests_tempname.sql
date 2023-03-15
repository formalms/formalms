CREATE TABLE `core_requests` (
  `id` int(11) NOT NULL,
  `app` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `controller` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `plugin` varchar(255) NOT NULL
);

ALTER TABLE `core_requests`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `core_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;