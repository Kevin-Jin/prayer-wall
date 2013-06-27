 DROP TABLE IF EXISTS `cookies`;
 DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
	`userid` INT(11) NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(254) NOT NULL,
	`password` BINARY(60) NOT NULL,
	`displayname` VARCHAR(20),
	PRIMARY KEY (`userid`),
	KEY (`email`),
	KEY (`displayname`)
) ENGINE=InnoDB;

CREATE TABLE `posts` (
	`postid` INT(11) NOT NULL AUTO_INCREMENT,
	`posttime` INT(11) NOT NULL,
	`title` VARCHAR(24),
	`message` VARCHAR(255) NOT NULL,
	`poster` INT(11),
	PRIMARY KEY (`postid`),
	KEY (`poster`),
	CONSTRAINT FOREIGN KEY (`poster`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE `cookies` ( 
	`uniqueid` BIGINT(20) NOT NULL AUTO_INCREMENT, 
	`userid` INT(11) NOT NULL, 
	`tokenhash` BINARY(60) NOT NULL, 
	PRIMARY KEY (`uniqueid`), 
	KEY (`userid`), 
	CONSTRAINT FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE
) Engine=InnoDB;

INSERT INTO `posts` (`posttime`, `message`) VALUES
	(0, 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
	(0, 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
	(0, 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.'),
	(0, 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.'),
	(0, 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
	(0, 'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
	(0, 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
	(0, 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
	(0, 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.'),
	(0, 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.'),
	(0, 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.');
UPDATE `posts` SET `message` = CONCAT(CAST(`postid` AS CHAR), '. ', `message`);