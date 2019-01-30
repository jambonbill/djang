CREATE TABLE IF NOT EXISTS `auth_user_groups` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `group_id` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `user_id` (`user_id`,`group_id`),
 KEY `auth_user_groups_group_id_33ac548dcf5f8e37_fk_auth_group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8