CREATE TABLE `conference_ConferenceBBB` (
  `id` bigint(20) NOT NULL,
  `idConference` bigint(20) NOT NULL DEFAULT '0',
  `confkey` varchar(255) DEFAULT NULL,
  `emailuser` varchar(255) DEFAULT NULL,
  `displayname` varchar(255) DEFAULT NULL,
  `audiovideosettings` int(11) DEFAULT NULL,
  `maxmikes` int(11) DEFAULT NULL,
  `schedule_info` text NOT NULL,
  `extra_conf` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `conference_ConferenceBBB`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idConference` (`idConference`);


ALTER TABLE `conference_ConferenceBBB`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
