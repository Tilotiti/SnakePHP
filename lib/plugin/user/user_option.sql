CREATE TABLE IF NOT EXISTS `snake_user_option` (
  `user_option_owner` int(11) NOT NULL COMMENT 'ID de l''utilisateur',
  `user_option_key` varchar(50) NOT NULL COMMENT 'Nom de l''option',
  `user_option_value` text NOT NULL COMMENT 'Valeur de l''option',
  KEY `user_option_user_id` (`user_option_owner`,`user_option_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;