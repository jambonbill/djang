CREATE TABLE IF NOT EXISTS `django_session` (
 `session_key` varchar(40) NOT NULL,
 `session_data` longtext NOT NULL,
 `expire_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8