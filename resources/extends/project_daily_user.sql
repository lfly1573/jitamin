CREATE TABLE `project_daily_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_num` bigint(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `task_info` text,
  `task_count` text,
  PRIMARY KEY (`id`),
  KEY `date_num` (`date_num`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;