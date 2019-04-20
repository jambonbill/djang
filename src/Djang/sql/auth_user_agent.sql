#store user agent at login
CREATE TABLE IF NOT EXISTS `auth_user_agent` (
 `aua_id` int(11) NOT NULL AUTO_INCREMENT,
 `aua_user_id` int(11) NOT NULL,
 `aua_user_agent` varchar(255) NOT NULL,
 `aua_ip` varchar(16) NOT NULL,
 `aua_created` datetime NOT NULL,
 PRIMARY KEY (`aua_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;