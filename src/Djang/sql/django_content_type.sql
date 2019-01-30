CREATE TABLE IF NOT EXISTS `django_content_type` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `app_label` varchar(100) NOT NULL,
 `model` varchar(100) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `django_content_type_app_label_uniq` (`app_label`,`model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8