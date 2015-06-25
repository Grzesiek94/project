CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'ROLE_ADMIN'),
(2, 'ROLE_USER');

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` char(32) COLLATE utf8_bin NOT NULL,
  `password` char(128) COLLATE utf8_bin NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `del` INT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `login_UNIQUE` (`login` ASC),
  KEY `FK_users_1` (`role_id`),
  CONSTRAINT `FK_users_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `users` (`id`, `login`, `password`, `role_id`) VALUES 
('1', 'Admin', 'DJAhPVmfV76bEZ9xsW5O3oaN9o+zmwpRZ78XW5QspToIjtbBlAFSbd5v3l/QFdj1F5svzjMZ5tuQsugny0MnpA==', '1');

CREATE TABLE IF NOT EXISTS `board` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` char(250) collate utf8_bin NOT NULL,
  `answer` char(250) collate utf8_bin default NULL,
  `users_question_id` int(10) unsigned NOT NULL,
  `users_answer_id` int(10) unsigned default NULL,
  `row_ignore` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `FK_board_1` (`users_question_id`),
  KEY `FK_board_2` (`users_answer_id`),
  CONSTRAINT `FK_board_1` FOREIGN KEY (`users_question_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_board_2` FOREIGN KEY (`users_answer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `users_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` char(10) collate utf8_bin NOT NULL,
  `surname` char(20) collate utf8_bin NOT NULL,
  `avatar` char(11) collate utf8_bin NOT NULL default 'default.png',
  `email` char(50) collate utf8_bin NOT NULL,
  `website` char(100) collate utf8_bin default NULL,
  `facebook` char(150) collate utf8_bin default NULL,
  `users_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `FK_users_data_1` (`users_id`),
  CONSTRAINT `FK_users_data_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


