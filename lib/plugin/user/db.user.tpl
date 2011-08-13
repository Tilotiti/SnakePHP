CREATE TABLE IF NOT EXISTS `{$smarty.const.DBPREF}_user` (
  `user_id` int(11) NOT NULL,
  `user_username` varchar(25) NOT NULL,
  `user_password` varchar(32) NOT NULL,
  `user_mail` varchar(150) NOT NULL,
  `user_hash` varchar(100) NOT NULL,
  `user_time` int(11) NOT NULL,
  `user_rank` varchar(15) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_username` (`user_username`),
  UNIQUE KEY `user_mail` (`user_mail`),
  UNIQUE KEY `user_hash` (`user_hash`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;