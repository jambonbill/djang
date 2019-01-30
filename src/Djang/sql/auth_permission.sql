CREATE TABLE IF NOT EXISTS `auth_permission` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `content_type_id` int(11) NOT NULL,
 `codename` varchar(100) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `content_type_id` (`content_type_id`,`codename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
