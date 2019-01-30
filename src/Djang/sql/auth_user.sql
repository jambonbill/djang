CREATE TABLE IF NOT EXISTS `auth_user` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `password` varchar(128) NOT NULL,
 `last_login` datetime DEFAULT NULL,
 `is_superuser` tinyint(1) DEFAULT '0',
 `username` varchar(30) NOT NULL,
 `first_name` varchar(30) DEFAULT NULL,
 `middle_name` varchar(30) DEFAULT NULL,
 `last_name` varchar(30) DEFAULT NULL,
 `email` varchar(254) NOT NULL,
 `is_staff` tinyint(1) DEFAULT '0',
 `is_active` tinyint(1) DEFAULT '0',
 `date_joined` datetime NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `username` (`username`),
 UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8