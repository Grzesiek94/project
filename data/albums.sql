CREATE TABLE IF NOT EXISTS `albums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(64) COLLATE utf8_bin NOT NULL,
  `artist` char(64) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `albums` (`id`, `title`, `artist`) VALUES
(1, 'Michael Jackson', 'Thriller'),
(2, 'Pink Floyd', 'The Dark Side of the Moon'),
(3, 'Whitney Houston / Various artists', 'The Bodyguard'),
(4, 'Meat Loaf', 'Bat Out of Hell'),
(5, 'Eagles', 'Their Greatest Hits (1971–1975)'),
(6, 'AC/DC', 'Back in Black'),
(7, 'Bee Gees / Various artists', 'Saturday Night Fever'),
(8, 'Fleetwood Mac', 'Rumours');
