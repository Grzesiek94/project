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
  PRIMARY KEY (`id`),
  KEY `FK_users_1` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `users`
  ADD CONSTRAINT `FK_users_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

INSERT INTO `users` (`id`, `login`, `password`, `role_id`) VALUES 
('1', 'Admin', 'DJAhPVmfV76bEZ9xsW5O3oaN9o+zmwpRZ78XW5QspToIjtbBlAFSbd5v3l/QFdj1F5svzjMZ5tuQsugny0MnpA==', '1');
