CREATE TABLE IF NOT EXISTS `snake_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_username` varchar(25) NOT NULL,
  `user_password` varchar(32) NOT NULL,
  `user_mail` varchar(75) NOT NULL,
  `user_rank` varchar(10) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_username` (`user_username`),
  UNIQUE KEY `user_mail` (`user_mail`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;