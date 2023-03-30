
CREATE TABLE `conference_teleskill_log` (
  `roomid` int(11) NOT NULL default '0',
  `idUser` int(11) NOT NULL default '0',
  `role` int(11) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `duration` int(11) NOT NULL default '0',
  `access` int(11) NOT NULL default '0',
  PRIMARY KEY  (`roomid`,`idUser`)
);
