CREATE TABLE IF NOT EXISTS `bc_quotes` (
	`id` int(11) NOT NULL auto_increment,
	`timestamp` int(11) NOT NULL,
	`ip` varchar(255) NOT NULL,
	`quote` text NOT NULL,
	`active` tinyint(1) NOT NULL,
	`popularity` int(11) NOT NULL,
	PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `bc_votes` (
	`id` int(11) NOT NULL auto_increment,
	`quote_id` int(11) NOT NULL,
	`ip` varchar(255) NOT NULL,
	`type` tinyint(4) NOT NULL,
	PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;
